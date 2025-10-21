<?php
/**
 * Contains the Flash_Manager class.
 *
 * @package wrd/wp-objective
 */

namespace Wrd\WpObjective\Admin;

use Wrd\WpObjective\Foundation\Service_Provider;
use Wrd\WpObjective\Support\Settings_Manager;

/**
 * Class for handling flash notifications.
 */
class Flash_Manager extends Service_Provider {
	/**
	 * Accessor to the settings system
	 *
	 * @var Settings_Manager
	 */
	private Settings_Manager $settings;

	/**
	 * Create the plugin.
	 *
	 * @param Settings_Manager $settings Accessor to the settings system.
	 */
	public function __construct( Settings_Manager $settings ) {
		$this->settings = $settings;
	}

	/**
	 * Initialize the flash system.
	 *
	 * @return void
	 */
	public function boot(): void {
		add_action( 'admin_notices', array( $this, 'render_callback' ) );
	}

	/**
	 * Renders all current flashes.
	 *
	 * @return void
	 */
	public function render_callback(): void {
		foreach ( $this->get() as $flash ) {
			if ( ! array_key_exists( 'message', $flash ) || ! is_string( $flash['message'] ) ) {
				// Message is malformed. We cannot continue.
				continue;
			}

			if ( array_key_exists( 'args', $flash ) && ! is_array( $flash['args'] ) ) {
				// Args are malformed. We can use the defaults.
				$flash['args'] = array();
			}

			wp_admin_notice( $flash['message'], $flash['args'] );
		}

		// We successfully rendered the messages, now clear them.
		$this->clear();
	}

	/**
	 * Add a new flash notice.
	 *
	 * @see 'wp_admin_notice'
	 *
	 * @param string $message The notice content.
	 *
	 * @param array  $args An array of arguments for the admin notice.
	 *
	 * @return bool
	 */
	public function add( string $message, array $args = array() ): bool {
		$flashes = $this->get();

		$flashes[] = array(
			'message' => $message,
			'args'    => $args,
		);

		return update_option( 'wrd_flashes', $flashes );
	}

	/**
	 * Get the currently stored flash notices.
	 *
	 * Array of arrays, with each having a 'message' and 'args' key.
	 *
	 * @return array
	 */
	public function get(): array {
		return $this->settings->get( 'wrd_flashes', array() );
	}

	/**
	 * Clear the currently stored flash notices.
	 *
	 * @return bool
	 */
	public function clear(): bool {
		return $this->settings->set( 'wrd_flashes', array() );
	}

	/**
	 * Add a new success flash notice.
	 *
	 * @see 'Flash::add'
	 *
	 * @param string $message The notice content.
	 *
	 * @param array  $args An array of arguments for the admin notice.
	 *
	 * @return void
	 */
	public function success( string $message, array $args = array() ): void {
		$args = wp_parse_args( $args, array( 'type' => 'success' ) );
		$this->add( $message, $args );
	}

	/**
	 * Add a new error flash notice.
	 *
	 * @see 'Flash::add'
	 *
	 * @param string $message The notice content.
	 *
	 * @param array  $args An array of arguments for the admin notice.
	 *
	 * @return void
	 */
	public function error( string $message, array $args = array() ): void {
		$args = wp_parse_args( $args, array( 'type' => 'error' ) );
		$this->add( $message, $args );
	}

	/**
	 * Add a new warning flash notice.
	 *
	 * @see 'Flash::add'
	 *
	 * @param string $message The notice content.
	 *
	 * @param array  $args An array of arguments for the admin notice.
	 *
	 * @return void
	 */
	public function warning( string $message, array $args = array() ): void {
		$args = wp_parse_args( $args, array( 'type' => 'warning' ) );
		$this->add( $message, $args );
	}

	/**
	 * Add a new info flash notice.
	 *
	 * @see 'Flash::add'
	 *
	 * @param string $message The notice content.
	 *
	 * @param array  $args An array of arguments for the admin notice.
	 *
	 * @return void
	 */
	public function info( string $message, array $args = array() ): void {
		$args = wp_parse_args( $args, array( 'type' => 'info' ) );
		$this->add( $message, $args );
	}
}
