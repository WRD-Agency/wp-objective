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
 * @method static WP_Error|true sql(string $sql)
 * @method static WP_Error|true create_table(string $name, callable $callback)
 * @method static WP_Error|true alter_table(string $name, callable $callback)
 * @method static string get_alter_table_query(Wrd\WpObjective\Database\Blueprint $schema)
 * @method static WP_Error|true rename_table(string $old_name, string $new_name)
 * @method static WP_Error|true drop_table(string $name)
 * @method static WP_Error|true insert(string $table, array $row)
 * @method static \Wrd\WpObjective\Database\Query\Query find(string $table)
 * @method static WP_Error|true run_query(Wrd\WpObjective\Database\Query\Query $query)
 *
 * @see Wrd\WpObjective\Database\Database_Manager
 */
class Database extends Facade {
	/**
	 * Get the ID to grab the object from the Database.
	 *
	 * @return string
	 */
	public static function get_facade_accessor(): string {
		return Database_Manager::class;
	}
}
