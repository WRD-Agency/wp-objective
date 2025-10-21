<?php
/**
 * Contains the Plugin class.
 *
 * @package wrd/wp-objective
 */

namespace Wrd\WpObjective\Foundation;

/**
 * Base implementation for a plugin.
 */
class Plugin extends Service_Provider {
	/**
	 * The current version of the plugin.
	 *
	 * @var string
	 */
	public string $version = '1.0.0';

	/**
	 * Main plugin file.
	 *
	 * @var string
	 */
	public string $file = '';

	/**
	 * Directory path of the plugin.
	 *
	 * @var string
	 */
	public string $dir = '';

	/**
	 * Files to include when the plugin is loaded.
	 *
	 * @var string[]
	 */
	public array $files = array();

	/**
	 * Create the plugin instance.
	 *
	 * @param string $file Main plugin file.
	 *
	 * @param string $dir Directory path of the plugin.
	 */
	public function __construct( string $file, string $dir ) {
		$this->file = $file;
		$this->dir  = $dir;
	}

	/**
	 * Get the plugin file.
	 *
	 * @return string
	 */
	public function get_file(): string {
		return $this->file;
	}

	/**
	 * Alias of 'get_file'.
	 *
	 * @return string
	 */
	public function file(): string {
		return $this->get_file();
	}

	/**
	 * Get the plugin dir.
	 *
	 * @return string
	 */
	public function get_dir(): string {
		return $this->dir;
	}

	/**
	 * Alias of 'get_dir'.
	 *
	 * @return string
	 */
	public function dir(): string {
		return $this->get_dir();
	}

	/**
	 * Get the current plugin version.
	 *
	 * @return string
	 */
	public function get_version(): string {
		return $this->version;
	}

	/**
	 * Alias of 'get_version'.
	 *
	 * @return string
	 */
	public function version(): string {
		return $this->get_version();
	}

	/**
	 * Load in any additional files.
	 *
	 * @return void
	 */
	public function includes() {
		// This page left intentionally blank.
	}

	/**
	 * Loads up a plugin class.
	 *
	 * @return void
	 */
	public function boot(): void {
		foreach ( $this->files as $file ) {
			require_once plugin_dir_path( $this->file ) . $file;
		}

		$this->includes();
	}

	/**
	 * Runs on the 'init' hook.
	 *
	 * Inheriters must call 'parent::init' for the 'admin', 'public' and 'rest' calls to work.
	 *
	 * @return void
	 */
	public function init(): void {
		if ( is_admin() ) {
			$this->admin();
		} elseif ( wp_is_serving_rest_request() ) {
			$this->api();
		} else {
			$this->public();
		}
	}

	/**
	 * Runs on the 'init' hook in an admin screen.
	 *
	 * @return void
	 */
	public function admin(): void {
		// This page left intentionally blank.
	}

	/**
	 * Runs on the 'rest_api_init' hook in the rest API.
	 *
	 * @return void
	 */
	public function api(): void {
		// This page left intentionally blank.
	}

	/**
	 * Runs on the 'init' hook in an public screen.
	 *
	 * @return void
	 */
	public function public(): void {
		// This page left intentionally blank.
	}
}
