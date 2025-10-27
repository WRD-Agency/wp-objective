<?php
/**
 * Contains the Flash facade.
 *
 * @package wrd\wp-objective
 */

namespace Wrd\WpObjective\Support\Facades;

use Wrd\WpObjective\Admin\Flash_Manager;

/**
 * Facade for accessing the 'Wrd\WpObjective\Admin\Flash_Manager' instance in the plugin.
 *
 * @autodoc facade
 *
 * @method static void boot() Initialize the flash system.
 * @method static void render_callback() Renders all current flashes.
 * @method static bool add(string $message, array $args) Add a new flash notice.
 * @method static array get() Get the currently stored flash notices.
 * @method static bool clear() Clear the currently stored flash notices.
 * @method static void success(string $message, array $args) Add a new success flash notice.
 * @method static void error(string $message, array $args) Add a new error flash notice.
 * @method static void warning(string $message, array $args) Add a new warning flash notice.
 * @method static void info(string $message, array $args) Add a new info flash notice.
 * @method static void init() Run in the 'init' hook.
 * @method static void shutdown() Run in the 'shutdown' hook.
 *
 * @see Wrd\WpObjective\Admin\Flash_Manager
 */
class Flash extends Facade {
	/**
	 * Get the ID to grab the object from the Flash.
	 *
	 * @return string
	 */
	public static function get_facade_accessor(): string {
		return Flash_Manager::class;
	}
}
