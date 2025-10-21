<?php
/**
 * Contains the Database facade.
 *
 * @package wrd\wp-objective
 */

namespace Wrd\WpObjective\Support\Facades;

use Wrd\WpObjective\Database\Database_Manager;

/**
 * Facade for accessing the 'Wrd\WpObjective\Database\Database_Manager' instance in the container.
 *
 * @autodoc facade
 *
 * @method static WP_Error|true create_table(callable $callback)
 * @method static string get_create_table_query(Wrd\WpObjective\Database\Blueprint $schema)
 *
 * @see Wrd\WpObjective\Database\Database_Manager
 */
class Database {
	/**
	 * Get the ID to grab the object from the Database.
	 *
	 * @return string
	 */
	public static function get_facade_accessor(): string {
		return Database_Manager::class;
	}
}
