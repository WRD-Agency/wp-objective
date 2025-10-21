<?php
/**
 * Contains the Log facade.
 *
 * @package wrd\wp-objective
 */

namespace Wrd\WpObjective\Support\Facades;

use Wrd\WpObjective\Log\Log_Manager;

/**
 * Facade for accessing the 'Wrd\WpObjective\Log\Log_Manager' instance in the container.
 *
 * @autodoc facade
 *
 * @method static \Wrd\WpObjective\Log\Log get_current_log()
 * @method static void add(?string $id = NULL, Wrd\WpObjective\Log\Level $level = Wrd\WpObjective\Log\Level::DEBUG, string $message = '', ?int $target = NULL, array $data = array ( ), int $timestamp = -1)
 * @method static void add_wp_error(WP_Error $error)
 * @method static void boot()
 * @method static void init()
 * @method static void shutdown()
 * @method static void activated()
 * @method static void deactivated()
 * @method static void uninstall()
 *
 * @see Wrd\WpObjective\Log\Log_Manager
 */
class Log {
	/**
	 * Get the ID to grab the object from the Log.
	 *
	 * @return string
	 */
	public static function get_facade_accessor(): string {
		return Log_Manager::class;
	}
}
