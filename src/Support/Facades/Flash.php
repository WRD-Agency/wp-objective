<?php
/**
 * Contains the Flash facade.
 *
 * @package wrd\wp-objective
 */

namespace Wrd\WpObjective\Support\Facades;

use Wrd\WpObjective\Admin\Flash_Manager;

/**
 * Facade for accessing the 'Wrd\WpObjective\Admin\Flash_Manager' instance in the container.
 *
 * @autodoc facade
 *
 * @method static void boot()
 * @method static void render_callback()
 * @method static bool add(string $message, array $args = array ( ))
 * @method static array get()
 * @method static bool clear()
 * @method static void success(string $message, array $args = array ( ))
 * @method static void error(string $message, array $args = array ( ))
 * @method static void warning(string $message, array $args = array ( ))
 * @method static void info(string $message, array $args = array ( ))
 * @method static void init()
 * @method static void shutdown()
 * @method static void activated()
 * @method static void deactivated()
 * @method static void uninstall()
 *
 * @see Wrd\WpObjective\Admin\Flash_Manager
 */
class Flash {
	/**
	 * Get the ID to grab the object from the Flash.
	 *
	 * @return string
	 */
	public static function get_facade_accessor(): string {
		return Flash_Manager::class;
	}
}
