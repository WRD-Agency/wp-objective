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
 * @method static static get_instance() Get the globally available instance of the plugin.
 * @method static static create_global(string $file, string $dir) Create the global plugin instance.
 * @method static void bind_default_bindings() Apply the default bindings.
 * @method static void bind(?string $id, class-string|object|null $concrete) Bind an ID to an object/class.
 * @method static \TObject get(class-string<\TObject> $id) Finds a binding by its identifier and returns it.
 * @method static \TObject make(class-string<\TObject> $class_name) Inject dependencies into the constructor of a class.
 * @method static void provide(class-string<\Service_Provider>|\Service_Provider $provider) Register a service provider.
 * @method static string get_file() Get the plugin file.
 * @method static string file() Alias of 'get_file'.
 * @method static string get_dir() Get the plugin dir.
 * @method static string dir() Alias of 'get_dir'.
 * @method static array get_data() Get the metadata about the plugin.
 * @method static string get_version() Get the current plugin version.
 * @method static string version() Alias of 'get_version'.
 * @method static void includes() Load in any additional files.
 * @method static void hit_providers(string $method) Calls a method on all registered providers.
 * @method static void boot() Loads up a plugin class.
 * @method static void init() Runs on the 'init' hook.
 * @method static void admin() Runs on the 'init' hook in an admin screen.
 * @method static void api() Runs on the 'init' hook in the rest API.
 * @method static void public() Runs on the 'init' hook in an public screen.
 * @method static void shutdown() Run in the 'shutdown' hook at the end of every request.
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
