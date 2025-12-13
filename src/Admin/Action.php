<?php
/**
 * Contains the Action class.
 *
 * @package wrd/wp-objective
 */

namespace Wrd\WpObjective\Admin;

use WP_Error;
use Wrd\WpObjective\Foundation\Service_Provider;
use Wrd\WpObjective\Log\Level;
use Wrd\WpObjective\Log\Log_Manager;
use Wrd\WpObjective\Support\Validator;

/**
 * Base class for actions.
 */
abstract class Action extends Service_Provider {
	/**
	 * The logger.
	 *
	 * @var Log_Manager
	 */
	protected Log_Manager $logger;

	/**
	 * Create an instance.
	 *
	 * @param Log_Manager $logger The logger to use.
	 */
	public function __construct( Log_Manager $logger ) {
		$this->logger = $logger;
	}

	/**
	 * Called to check if the current user can undertake this action.
	 *
	 * @return WP_Error|bool
	 */
	abstract public function permissions_callback(): WP_Error|bool;

	/**
	 * Get the redirection URL upon success.
	 *
	 * @param array $args The arguments passed to the action.
	 *
	 * @return string
	 */
	public function get_destination( array $args ): string {
		return admin_url();
	}

	/**
	 * Get the arguments for the action.
	 *
	 * @return array
	 */
	public function get_arguments(): array {
		return array();
	}

	/**
	 * Execute the action.
	 *
	 * @param array $args The arguments passed to the action.
	 *
	 * @return WP_Error|null
	 */
	abstract public function handle( array $args ): WP_Error|null;

	/**
	 * Handle the action if triggered.
	 *
	 * @return void
	 */
	public function run(): void {
		if ( ! $this->is_requested() ) {
			// Not our action, bail out.
			return;
		}

		// Nonce check.
		check_admin_referer( $this->get_id() );

		// Capabilities check.
		$capability_check = $this->permissions_callback();

		if ( false === $capability_check ) {
			$capability_check = new WP_Error( 'permission_denied', __( "You don't have permission to perform this action.", 'wrd' ) );
		}

		if ( is_wp_error( $capability_check ) ) {
			// User is not allowed to do this.
			$this->logger->add_wp_error( $capability_check, Level::WARN );
			wp_die( $capability_check ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- WP_Error handled by wp_die.
		}

		$args      = $this->get_arguments();
		$validator = new Validator( $args );

		// Sanitize inputs.
		$values = wp_unslash( $_REQUEST ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$values = $validator->sanitize( $values );

		if ( is_wp_error( $values ) ) {
			// Values could not be sanitized.
			$this->logger->add_wp_error( $values, Level::WARN );
			wp_die( $values ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- WP_Error handled by wp_die.
		}

		// Validate inputs.
		$is_valid = $validator->validate( $values );

		if ( is_wp_error( $is_valid ) ) {
			// Values did not validate.
			$this->logger->add_wp_error( $is_valid, Level::WARN );
			wp_die( $is_valid ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- WP_Error handled by wp_die.
		}

		// Run the action.
		$result = $this->handle( $values );

		if ( is_wp_error( $result ) ) {
			// Execution resulted in an error.
			$this->logger->add_wp_error( $result, Level::ERROR );
			wp_die( $result ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- WP_Error handled by wp_die.
		}

		$loggable_args = array(
			'action' => static::class,
		);

		foreach ( $this->get_arguments() as $key => $args ) {
			if ( isset( $args['log'] ) && true === $args['log'] && array_key_exists( $key, $values ) ) {
				$loggable_args[ $key ] = $values[ $key ];
			}
		}

		// Log the action.
		$this->logger->add( message: __( 'Successfully ran action.', 'wrd' ), data: $loggable_args );

		// Exit.
		wp_redirect( $this->get_destination( $values ) ); // phpcs:ignore WordPress.Security.SafeRedirect.wp_redirect_wp_redirect -- Admin URL.
		nocache_headers();
		exit;
	}

	/**
	 * Boot the action.
	 *
	 * @return void
	 */
	public function boot(): void {
		add_action( 'admin_init', array( $this, 'run' ) );
	}

	/**
	 * Get name of the action.
	 *
	 * @return string
	 */
	public function get_id(): string {
		return sanitize_key( static::class );
	}

	/**
	 * Check if this action is being requested.
	 *
	 * @return bool
	 */
	public function is_requested(): bool {
		return ( array_key_exists( 'action', $_REQUEST ) && $this->get_id() === $_REQUEST['action'] ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Nonce verified elsewhere.
	}

	/**
	 * Get the action URL to trigger the action in a form.
	 *
	 * @return string
	 */
	public function get_submit_url(): string {
		return add_query_arg(
			array(
				'action' => $this->get_id(),
			),
			admin_url( 'admin.php' )
		);
	}

	/**
	 * Get the URL to trigger the action.
	 *
	 * @param array $params Additional parameters to include in the URL.
	 *
	 * @return string
	 */
	public function get_url( array $params = array() ): string {
		$url = add_query_arg(
			array(
				'action' => $this->get_id(),
				...$params,
			),
			admin_url( 'admin.php' )
		);

		return wp_nonce_url( $url, $this->get_id() );
	}

	/**
	 * Get the form fields to trigger & secure the action.
	 *
	 * @return string
	 */
	public function get_fields(): string {
		$html = '';

		$html .= sprintf( '<input type="hidden" name="%s" value="%s" />', esc_attr( 'action' ), esc_attr( $this->get_id() ) );
		$html .= wp_nonce_field( $this->get_id(), '_wpnonce', true, false );

		return $html;
	}
}
