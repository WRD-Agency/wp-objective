<?php
/**
 * Contains the Settings facade.
 *
 * @package wrd\wp-objective
 */

namespace Wrd\WpObjective\Support\Facades;

use Wrd\WpObjective\Support\Settings_Manager;

/**
 * Facade for accessing the 'Wrd\WpObjective\Support\Settings_Manager' instance in the container.
 *
 * @autodoc facade
 *
 * @method static mixed get(string $key, mixed $default = NULL)
 * @method static mixed set(string $key, mixed $value)
 *
 * @see Wrd\WpObjective\Support\Settings_Manager
 */
class Settings extends Facade {
	/**
	 * Get the ID to grab the object from the Settings.
	 *
	 * @return string
	 */
	public static function get_facade_accessor(): string {
		return Settings_Manager::class;
	}
}
