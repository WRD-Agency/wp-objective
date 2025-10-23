<?php
/**
 * Contains the Plugin facade.
 *
 * @package wrd\wp-objective
 */

namespace Wrd\WpObjective\Support\Facades;

use Wrd\WpObjective\Foundation\Plugin as FoundationPlugin;

/**
 * Facade for accessing the 'Wrd\WpObjective\Foundation\Plugin' instance in the container.
 *
 * @autodoc facade
 *
 * @method static string get_file()
 * @method static string file()
 * @method static string get_dir()
 * @method static string dir()
 * @method static string get_version()
 * @method static string version()
 * @method static includes()
 * @method static void boot()
 * @method static void init()
 * @method static void admin()
 * @method static void api()
 * @method static void public()
 * @method static void shutdown()
 * @method static void activated()
 * @method static void deactivated()
 * @method static void uninstall()
 *
 * @see Wrd\WpObjective\Foundation\Plugin
 */
class Plugin extends Facade {
	/**
	 * Get the ID to grab the object from the Plugin.
	 *
	 * @return string
	 */
	public static function get_facade_accessor(): string {
		return FoundationPlugin::class;
	}
}
