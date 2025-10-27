<?php
/**
 * Contains the Log facade.
 *
 * @package wrd\wp-objective
 */

namespace Wrd\WpObjective\Support\Facades;

use Wrd\WpObjective\Log\Log_Manager;

/**
 * Facade for accessing the 'Wrd\WpObjective\Log\Log_Manager' instance in the plugin.
 *
 * @autodoc facade
 *
 * @method static \Log get_current_log() Get the log of the current request.
 * @method static void add(?string $id, \Level $level, string $message, ?int $target, array $data, int $timestamp) Create a log message.
 * @method static void add_wp_error(\WP_Error $error) Add a log message from an error.
 * @method static void boot() Runs when the plugin is booted.
 * @method static void init() Run in the 'init' hook.
 * @method static void shutdown() Run in the 'shutdown' hook.
 *
 * @see Wrd\WpObjective\Log\Log_Manager
 */
class Log extends Facade {
	/**
	 * Get the ID to grab the object from the Log.
	 *
	 * @return string
	 */
	public static function get_facade_accessor(): string {
		return Log_Manager::class;
	}
}
