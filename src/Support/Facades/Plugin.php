<?php
/**
 * Contains the Plugin facade.
 *
 * @package wrd\wp-objective
 */

namespace Wrd\WpObjective\Support\Facades;

use Wrd\WpObjective\Foundation\Plugin as FoundationPlugin;

/**
 * Facade for accessing the 'Wrd\WpObjective\Foundation\Plugin' instance in the plugin.
 *
 * @autodoc facade
 *
 * @method static ?static get_instance()
 * @method static static create_global(string $file, string $dir)
 * @method static void bind_default_bindings()
 * @method static void bind(?string $id,  $concrete = NULL)
 * @method static get( $id)
 * @method static make( $class_name)
 * @method static void provide( $provider)
 * @method static string get_file()
 * @method static string file()
 * @method static string get_dir()
 * @method static string dir()
 * @method static array get_data()
 * @method static string get_version()
 * @method static string version()
 * @method static includes()
 * @method static void hit_providers(string $method)
 * @method static void boot()
 * @method static void init()
 * @method static void admin()
 * @method static void api()
 * @method static void public()
 * @method static void shutdown()
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
