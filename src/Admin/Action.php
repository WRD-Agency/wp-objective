<?php
/**
 * Contains the Action class.
 *
 * @package wrd/wp-objective
 */

namespace Wrd\WpObjective\Admin;

use WP_Error;
use Wrd\WpObjective\Foundation\Service_Provider;
use Wrd\WpObjective\Log\Log_Manager;

/**
 * Base class for actions.
 */
abstract class Action extends Service_Provider {
	/**
	 * The logger.
	 *
	 * @var Log_Manager
	 */
	private Log_Manager $logger;

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
	 * Get the attributes.
	 *
	 * Allows us to use some core functions designed for the Rest API to validate and sanitize.
	 *
	 * @return array
	 */
	public function get_attributes(): array {
		return array(
			'args' => $this->get_arguments(),
		);
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
	 * Sanitize the parameters for the action.
	 *
	 * @param array $values The values to sanitize.
	 *
	 * @return WP_Error|array The sanitized values, or an error if sanitisation failed.
	 */
	public function sanitize_params( array $values ): WP_Error|array {
		$args = $this->get_arguments();

		$sanitized_values = array();

		$invalid_params  = array();
		$invalid_details = array();

		foreach ( $values as $key => $value ) {
			if ( ! isset( $args[ $key ] ) ) {
				// Unknown parameter, skip it.
				continue;
			}

			$param_args = $args[ $key ];

			// If the arg has a type but no sanitize_callback attribute, default to rest_parse_request_arg.
			if ( ! array_key_exists( 'sanitize_callback', $param_args ) && ! empty( $param_args['type'] ) ) {
				$param_args['sanitize_callback'] = 'rest_parse_request_arg';
			}

			// If there's still no sanitize_callback, nothing to do here.
			if ( empty( $param_args['sanitize_callback'] ) ) {
				continue;
			}

			/**
			 * Sanitized value.
			 *
			 * @var mixed|WP_Error $sanitized_value
			 */
			$sanitized_value = call_user_func( $param_args['sanitize_callback'], $value, $this, $key );

			if ( is_wp_error( $sanitized_value ) ) {
				$invalid_params[ $key ]  = implode( ' ', $sanitized_value->get_error_messages() );
				$invalid_details[ $key ] = rest_convert_error_to_response( $sanitized_value )->get_data();
			} else {
				$sanitized_values[ $key ] = $sanitized_value;
			}
		}

		if ( $invalid_params ) {
			return new WP_Error(
				'rest_invalid_param',
				/* translators: %s: List of invalid parameters. */
				sprintf( __( 'Invalid parameter(s): %s' ), implode( ', ', array_keys( $invalid_params ) ) ),
				array(
					'status'  => 400,
					'params'  => $invalid_params,
					'details' => $invalid_details,
				)
			);
		}

		return $sanitized_values;
	}

	/**
	 * Validate the parameters for the action.
	 *
	 * @param array $values The values to validate.
	 *
	 * @return WP_Error|true True if validation passed, or an error if validation failed.
	 */
	public function validate_params( array $values ): WP_Error|true {
		$args = $this->get_arguments();

		$required = array();

		foreach ( $args as $key => $arg ) {
			$param = $values[ $key ] ?? null;
			if ( isset( $arg['required'] ) && true === $arg['required'] && null === $param ) {
				$required[] = $key;
			}
		}

		if ( ! empty( $required ) ) {
			return new WP_Error(
				'rest_missing_callback_param',
				/* translators: %s: List of required parameters. */
				sprintf( __( 'Missing parameter(s): %s' ), implode( ', ', $required ) ),
				array(
					'status' => 400,
					'params' => $required,
				)
			);
		}

		/*
		 * Check the validation callbacks for each registered arg.
		 *
		 * This is done after required checking as required checking is cheaper.
		 */
		$invalid_params  = array();
		$invalid_details = array();

		foreach ( $args as $key => $arg ) {

			$param = $values[ $key ] ?? null;

			if ( null !== $param && ! empty( $arg['validate_callback'] ) ) {
				/**
				 * Validation check.
				 *
				 * @var bool|\WP_Error $valid_check
				 */
				$valid_check = call_user_func( $arg['validate_callback'], $param, $this, $key );

				if ( false === $valid_check ) {
					$invalid_params[ $key ] = __( 'Invalid parameter.' );
				}

				if ( is_wp_error( $valid_check ) ) {
					$invalid_params[ $key ]  = implode( ' ', $valid_check->get_error_messages() );
					$invalid_details[ $key ] = rest_convert_error_to_response( $valid_check )->get_data();
				}
			}
		}

		if ( $invalid_params ) {
			return new WP_Error(
				'rest_invalid_param',
				/* translators: %s: List of invalid parameters. */
				sprintf( __( 'Invalid parameter(s): %s' ), implode( ', ', array_keys( $invalid_params ) ) ),
				array(
					'status'  => 400,
					'params'  => $invalid_params,
					'details' => $invalid_details,
				)
			);
		}

		return true;
	}

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
			$this->logger->add_wp_error( $capability_check );
			wp_die( $capability_check ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- WP_Error handled by wp_die.
		}

		// Sanitize inputs.
		$values = wp_unslash( $_REQUEST ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$values = $this->sanitize_params( $values );

		if ( is_wp_error( $values ) ) {
			// Values could not be sanitized.
			$this->logger->add_wp_error( $values );
			wp_die( $values ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- WP_Error handled by wp_die.
		}

		// Validate inputs.
		$validation = $this->validate_params( $values );

		if ( is_wp_error( $validation ) ) {
			// Values did not validate.
			$this->logger->add_wp_error( $validation );
			wp_die( $validation ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- WP_Error handled by wp_die.
		}

		// Run the action.
		$result = $this->handle( $values );

		if ( is_wp_error( $result ) ) {
			// Execution resulted in an error.
			$this->logger->add_wp_error( $result );
			wp_die( $result ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- WP_Error handled by wp_die.
		}

		$loggable_args = array(
			'action' => static::class,
		);

		foreach ( $this->get_arguments() as $key => $args ) {
			if ( isset( $args['log'] ) && true === $args['log'] ) {
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
