<?php
/**
 * Contains the Database_Manager class.
 *
 * @package wrd/wp-objective
 */

namespace Wrd\WpObjective\Database;

use WP_Error;

/**
 * Class for interactive with the database.
 */
class Database_Manager {
	/**
	 * Create a new database table.
	 *
	 * @param callable(Blueprint): void $callback Callback to define the schema.
	 *
	 * @return WP_Error | true
	 */
	public function create_table( callable $callback ): WP_Error | true {
		$schema = new Blueprint();

		call_user_func( $callback, $schema );

		$sql = $this->get_create_table_query( $schema );

		return true;
	}

	/**
	 * Get the SQL query.
	 *
	 * Suitable for dbDelta.
	 *
	 * @param Blueprint $schema The table schema.
	 *
	 * @return string
	 */
	private function get_create_table_query( Blueprint $schema ): string {
		global $wpdb;

		$table_name      = $wpdb->prefix . strtolower( $schema->name );
		$charset_collate = $wpdb->get_charset_collate();

		$definitions = array();

		foreach ( $schema->columns as $column ) {
			$column_name = strtolower( $column->name );
			$type        = strtolower( $column->name );

			$line = "{$column_name} {$type} (";

			if ( $column->unsigned ) {
				$line .= ' UNSIGNED';
			}

			if ( $column->default ) {
				$line .= "DEFAULT '" . $column->default . "'";
			}

			if ( ! $column->nullable ) {
				$line .= ' NOT NULL';
			}

			if ( ! $column->autoincrement ) {
				$line .= ' AUTO_INCREMENT';
			}

			$definitions[] = $line;
		}

		foreach ( $schema->columns as $column ) {
			$column_name = strtolower( $column->name );

			if ( $column->unique ) {
				$definitions[] = 'UNIQUE KEY ' . $column_name . ' (' . $column_name . ')';
			}
			if ( $column->unique ) {
				$definitions[] = 'PRIMARY KEY  (' . $column_name . ')';
			}
		}

		$sql = "CREATE TABLE {$table_name} (";

		$sql .= PHP_EOL . join( ',' . PHP_EOL, $definitions );

		$sql .= PHP_EOL . ") {$charset_collate};";

		return $sql;
	}
}
