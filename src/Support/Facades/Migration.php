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
 * @method static string get_migrated_version() Get the migrated to plugin version.
 * @method static string set_migrated_version(string $version) Update the migrated to plugin version.
 * @method static void add_migration(class-string<\Migration> $migration) Register a function to run when migrating to the given version.
 * @method static bool needs_migration() Checks if the installed version of the plugin has migrations to be run.
 * @method static void run_needed_migrations() Calls migration functions to upgrade to the latest plugin version.
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
