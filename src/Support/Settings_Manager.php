<?php
/**
 * Contains the Settings_Manager class.
 *
 * @package wrd/wp-objective
 */

namespace Wrd\WpObjective\Support;

/**
 * Utility for plugin settings.
 */
class Settings_Manager {
	/**
	 * Get an option.
	 *
	 * @param string $key The setting key.
	 *
	 * @param mixed  $default The fallback value.
	 *
	 * @return mixed
	 */
	public function get( string $key, mixed $default = null ): mixed {
		return get_option( $key, $default );
	}

	/**
	 * Set an option.
	 *
	 * @param string $key The setting key.
	 *
	 * @param mixed  $value The new value.
	 *
	 * @return mixed
	 */
	public function set( string $key, mixed $value ): mixed {
		return update_option( $key, $value );
	}
}
