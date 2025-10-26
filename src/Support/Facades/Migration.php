<?php
/**
 * Contains the Migration facade.
 *
 * @package wrd\wp-objective
 */

namespace Wrd\WpObjective\Support\Facades;

use Wrd\WpObjective\Foundation\Migrate\Migration_Manager;

/**
 * Facade for accessing the 'Wrd\WpObjective\Foundation\Migrate\Migration_Manager' instance in the plugin.
 *
 * @autodoc facade
 *
 * @method static string get_migrated_version()
 * @method static string set_migrated_version(string $version)
 * @method static add_migration( $migration)
 * @method static bool needs_migration()
 * @method static void migrate()
 * @method static void init()
 * @method static void boot()
 * @method static void shutdown()
 *
 * @see Wrd\WpObjective\Foundation\Migrate\Migration_Manager
 */
class Migration extends Facade {
	/**
	 * Get the ID to grab the object from the Migration.
	 *
	 * @return string
	 */
	public static function get_facade_accessor(): string {
		return Migration_Manager::class;
	}
}
