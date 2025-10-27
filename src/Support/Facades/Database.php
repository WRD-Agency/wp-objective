<?php
/**
 * Contains the Database facade.
 *
 * @package wrd\wp-objective
 */

namespace Wrd\WpObjective\Support\Facades;

use Wrd\WpObjective\Database\Database_Manager;

/**
 * Facade for accessing the 'Wrd\WpObjective\Database\Database_Manager' instance in the plugin.
 *
 * @autodoc facade
 *
 * @method static \WP_Error|true sql(string $sql) Run an SQL command. Contents are sanitized.
 * @method static string get_table_name_prefix() Get the prefix for table names.
 * @method static string get_charset_collate() Get the charset for tables.
 * @method static \WP_Error|true create_table(string $name, callable $callback) Create a new database table.
 * @method static string get_create_table_sql(string $name, callable $callback) Get the SQL to create a new database table.
 * @method static \WP_Error|true alter_table(string $name, callable $callback) Change a new database table.
 * @method static string get_alter_table_sql(string $name, callable $callback) Get the SQL to change a new database table.
 * @method static \WP_Error|true rename_table(string $old_name, string $new_name) Rename a database table.
 * @method static string get_rename_table_sql(string $old_name, string $new_name) Get the SQL to rename a database table.
 * @method static \WP_Error|true drop_table(string $name) Drop a database table.
 * @method static string get_drop_table_sql(string $name) Get the SQL to drop a database table.
 * @method static \WP_Error|true insert(string $table, array $row) Insert a row.
 * @method static \Query query(string $table) Begin a query.
 * @method static \WP_Error|true run_query(\Query $query) Run a query.
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
