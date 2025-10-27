<?php
/**
 * Contains the Database_Manager class.
 *
 * @package wrd/wp-objective
 */

namespace Wrd\WpObjective\Database;

use WP_Error;
use wpdb;
use Wrd\WpObjective\Database\Query\Query;
use Wrd\WpObjective\Database\Schema\Blueprint;
use Wrd\WpObjective\Database\Schema\Command;

/**
 * Class for interactive with the database.
 */
class Database_Manager {
	/**
	 * The WordPress database.
	 *
	 * @var wpdb $db
	 */
	protected wpdb $db;

	/**
	 * Create a Database_Manager.
	 *
	 * @param wpdb $db The WordPress database to manage.
	 */
	public function __construct( wpdb $db ) {
		$this->db = $db;
	}

	/**
	 * Run an SQL command. Contents are sanitized.
	 *
	 * @param string $sql The SQL command to run.
	 *
	 * @return WP_Error|true
	 */
	public function sql( string $sql ): WP_Error|true {

		return true;
	}

	/**
	 * Get the prefix for table names.
	 *
	 * @return string
	 */
	public function get_table_name_prefix(): string {
		return $this->db->prefix;
	}

	/**
	 * Get the charset for tables.
	 *
	 * @return string
	 */
	public function get_charset_collate(): string {
		return $this->db->charset;
	}

	/**
	 * Create a new database table.
	 *
	 * @param string                    $name The table name.
	 *
	 * @param callable(Blueprint): void $callback Callback to define the schema.
	 *
	 * @return WP_Error | true
	 */
	public function create_table( string $name, callable $callback ): WP_Error|true {
		require_once ABSPATH . 'wp-admin/includes/upgrade.php';
		dbDelta( $this->get_create_table_sql( $name, $callback ) );

		// TODO: Error detection on dbDelta.

		return true;
	}

	/**
	 * Get the SQL to create a new database table.
	 *
	 * @param string                    $name The table name.
	 *
	 * @param callable(Blueprint): void $callback Callback to define the schema.
	 *
	 * @return string
	 */
	public function get_create_table_sql( string $name, callable $callback ): string {
		$schema = new Blueprint();

		call_user_func( $callback, $schema );

		$schema
			->name( $this->get_table_name_prefix() . $name )
			->charset_collate( $this->get_charset_collate() )
			->command( Command::CREATE );

		return $schema->get_sql();
	}

	/**
	 * Change a new database table.
	 *
	 * @param string                    $name The table name.
	 *
	 * @param callable(Blueprint): void $callback Callback to define the schema.
	 *
	 * @return WP_Error | true
	 */
	public function alter_table( string $name, callable $callback ): WP_Error|true {
		return $this->sql( $this->get_alter_table_sql( $name, $callback ) );
	}

	/**
	 * Get the SQL to change a new database table.
	 *
	 * @param string                    $name The table name.
	 *
	 * @param callable(Blueprint): void $callback Callback to define the schema.
	 *
	 * @return string
	 */
	public function get_alter_table_sql( string $name, callable $callback ): string {
		$schema = new Blueprint();

		call_user_func( $callback, $schema );

		$schema
			->name( $this->get_table_name_prefix() . $name )
			->charset_collate( $this->get_charset_collate() )
			->command( Command::ALTER );

		return $schema->get_sql();
	}

	/**
	 * Rename a database table.
	 *
	 * @param string $old_name The original table name.
	 *
	 * @param string $new_name The new table name.
	 *
	 * @return WP_Error | true
	 */
	public function rename_table( string $old_name, string $new_name ): WP_Error|true {
		return $this->sql( $this->get_rename_table_sql( $old_name, $new_name ) );
	}

	/**
	 * Get the SQL to rename a database table.
	 *
	 * @param string $old_name The original table name.
	 *
	 * @param string $new_name The new table name.
	 *
	 * @return string
	 */
	public function get_rename_table_sql( string $old_name, string $new_name ): string {
		$schema = ( new Blueprint() )
			->name( $this->get_table_name_prefix() . $old_name )
			->charset_collate( $this->get_charset_collate() )
			->command( Command::RENAME )
			->new_name( $new_name );

		return $schema->get_sql();
	}

	/**
	 * Drop a database table.
	 *
	 * @param string $name The table name.
	 *
	 * @return WP_Error | true
	 */
	public function drop_table( string $name ): WP_Error|true {
		return $this->sql( $this->get_drop_table_sql( $name ) );
	}

	/**
	 * Get the SQL to drop a database table.
	 *
	 * @param string $name The table name.
	 *
	 * @return string
	 */
	public function get_drop_table_sql( string $name ): string {
		$schema = ( new Blueprint() )
			->name( $this->get_table_name_prefix() . $name )
			->charset_collate( $this->get_charset_collate() )
			->command( Command::DROP );

		return $schema->get_sql( Command::DROP );
	}

	/**
	 * Insert a row.
	 *
	 * @param string $table The table to query.
	 *
	 * @param array  $row The row to add.
	 *
	 * @return WP_Error|true
	 */
	public function insert( string $table, array $row ): WP_Error|true {
		$success = $this->db->insert( $table, $row );

		if ( false === $success ) {
			return new WP_Error( 'INSERT_FAILED', esc_html( $this->db->last_error ) );
		}

		return true;
	}

	/**
	 * Begin a query.
	 *
	 * @param string $table The table to query.
	 *
	 * @return Query
	 */
	public function query( string $table ): Query {
		return ( new Query( $this ) )->table( $table );
	}

	/**
	 * Run a query.
	 *
	 * @param Query $query Query to run.
	 *
	 * @return WP_Error|true
	 */
	public function run_query( Query $query ): WP_Error|true {
		return $this->sql( $query->to_sql() );
	}
}
