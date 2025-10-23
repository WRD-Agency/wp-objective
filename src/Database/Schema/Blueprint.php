<?php
/**
 * Contains the Table class.
 *
 * @package wrd/wp-objective
 */

namespace Wrd\WpObjective\Database\Schema;

use Exception;

/**
 * Class for defining database tables.
 */
class Blueprint {
	/**
	 * The command related to this column.
	 *
	 * @var ?Command
	 */
	public ?Command $command = null;

	/**
	 * The table name.
	 *
	 * @var string
	 */
	public string $name;

	/**
	 * The table's new name, when renaming it.
	 *
	 * @var string
	 */
	public string $new_name;

	/**
	 * The table's columns.
	 *
	 * @var Column_Definition[]
	 */
	public array $columns;

	/**
	 * The charset collate for tables.
	 *
	 * @var string
	 */
	public string $charset_collate = '';

	/**
	 * Set the table name.
	 *
	 * @param string $name The table name.
	 *
	 * @return static
	 */
	public function name( string $name ): static {
		$this->name = $name;

		return $this;
	}

	/**
	 * Set the table charset_collate.
	 *
	 * @param string $charset_collate The table charset_collate.
	 *
	 * @return static
	 */
	public function charset_collate( string $charset_collate ): static {
		$this->charset_collate = $charset_collate;

		return $this;
	}

	/**
	 * Set the command.
	 *
	 * @param Command $command The new command type.
	 *
	 * @return static
	 */
	public function command( Command $command ): static {
		$this->command = $command;
		return $this;
	}

	/**
	 * Set the new name of the table, if using the Rename command.
	 *
	 * @param string $new_name The new new name of the table.
	 *
	 * @return static
	 */
	public function new_name( string $new_name ): static {
		$this->new_name = $new_name;
		return $this;
	}

	/**
	 * Add a column.
	 *
	 * @param string  $name The column name.
	 *
	 * @param ?string $type The column type.
	 *
	 * @return Column_Definition
	 */
	public function column( string $name, ?string $type = null ): Column_Definition {
		$column          = new Column_Definition( $name, $type );
		$this->columns[] = $column;

		return $column;
	}

	/**
	 * Add a 'bool' column.
	 *
	 * @param string $name The column name.
	 *
	 * @return Column_Definition
	 */
	public function boolean( string $name ): Column_Definition {
		return $this->column( $name, )->boolean();
	}

	/**
	 * Add a 'tinyint' column.
	 *
	 * @param string $name The column name.
	 *
	 * @return Column_Definition
	 */
	public function tiny_integer( string $name ): Column_Definition {
		return $this->column( $name, )->tiny_integer();
	}

	/**
	 * Add a 'smallint' column.
	 *
	 * @param string $name The column name.
	 *
	 * @return Column_Definition
	 */
	public function small_integer( string $name ): Column_Definition {
		return $this->column( $name, )->small_integer();
	}

	/**
	 * Add a 'mediumint' column.
	 *
	 * @param string $name The column name.
	 *
	 * @return Column_Definition
	 */
	public function medium_integer( string $name ): Column_Definition {
		return $this->column( $name, )->medium_integer();
	}

	/**
	 * Add a 'int' column.
	 *
	 * @param string $name The column name.
	 *
	 * @return Column_Definition
	 */
	public function integer( string $name ): Column_Definition {
		return $this->column( $name, )->integer();
	}

	/**
	 * Add a 'bigint' column.
	 *
	 * @param string $name The column name.
	 *
	 * @return Column_Definition
	 */
	public function big_integer( string $name ): Column_Definition {
		return $this->column( $name, )->big_integer();
	}

	/**
	 * Add a 'decimal' column.
	 *
	 * @param string $name The column name.
	 *
	 * @param int    $total The precision (total digits).
	 *
	 * @param int    $places The scale (decimal digits).
	 *
	 * @return Column_Definition
	 */
	public function decimal( string $name, int $total, int $places ): Column_Definition {
		return $this->column( $name, )->decimal( $total, $places );
	}

	/**
	 * Add a 'double' column.
	 *
	 * @param string $name The column name.
	 *
	 * @param int    $total The precision (total digits).
	 *
	 * @param int    $places The scale (decimal digits).
	 *
	 * @return Column_Definition
	 */
	public function double( string $name, int $total, int $places ): Column_Definition {
		return $this->column( $name, )->double( $total, $places );
	}

	/**
	 * Add a 'float' column.
	 *
	 * @param string $name The column name.
	 *
	 * @param int    $precision The precision (total digits).
	 *
	 * @return Column_Definition
	 */
	public function float( string $name, int $precision ): Column_Definition {
		return $this->column( $name, )->float( $precision );
	}

	/**
	 * Add a 'char' column.
	 *
	 * For fixed length strings.
	 *
	 * @param string $name The column name.
	 *
	 * @param int    $length The length.
	 *
	 * @return Column_Definition
	 */
	public function char( string $name, int $length ): Column_Definition {
		return $this->column( $name, )->char( $length );
	}

	/**
	 * Add a 'varchar' column.
	 *
	 * For variable length strings.
	 *
	 * @param string $name The column name.
	 *
	 * @param int    $length The length.
	 *
	 * @return Column_Definition
	 */
	public function string( string $name, int $length ): Column_Definition {
		return $this->column( $name )->string( $length );
	}

	/**
	 * Add a 'tinytext' column.
	 *
	 * @param string $name The column name.
	 *
	 * @return Column_Definition
	 */
	public function tiny_text( string $name ): Column_Definition {
		return $this->column( $name, )->tiny_text();
	}

	/**
	 * Add a 'text' column.
	 *
	 * @param string $name The column name.
	 *
	 * @return Column_Definition
	 */
	public function text( string $name ): Column_Definition {
		return $this->column( $name, )->text();
	}

	/**
	 * Add a 'mediumtext' column.
	 *
	 * @param string $name The column name.
	 *
	 * @return Column_Definition
	 */
	public function medium_text( string $name ): Column_Definition {
		return $this->column( $name, )->medium_text();
	}

	/**
	 * Add a 'longtext' column.
	 *
	 * @param string $name The column name.
	 *
	 * @return Column_Definition
	 */
	public function long_text( string $name ): Column_Definition {
		return $this->column( $name, )->long_text();
	}

	/**
	 * Add a 'datetime' column.
	 *
	 * @param string $name The column name.
	 *
	 * @return Column_Definition
	 */
	public function date_time( string $name ): Column_Definition {
		return $this->column( $name, )->date_time();
	}

	/**
	 * Add a 'date' column.
	 *
	 * @param string $name The column name.
	 *
	 * @return Column_Definition
	 */
	public function date( string $name ): Column_Definition {
		return $this->column( $name, )->date();
	}

	/**
	 * Add a 'timestamp' column.
	 *
	 * @param string $name The column name.
	 *
	 * @return Column_Definition
	 */
	public function timestamp( string $name ): Column_Definition {
		return $this->column( $name, )->timestamp();
	}

	/**
	 * Add a 'time' column.
	 *
	 * @param string $name The column name.
	 *
	 * @return Column_Definition
	 */
	public function time( string $name ): Column_Definition {
		return $this->column( $name, )->time();
	}

	/**
	 * Add an auto-incrementing ID column.
	 *
	 * @param string $name The column name.
	 *
	 * @return Column_Definition
	 */
	public function id( string $name ): Column_Definition {
		return $this->column( $name )->id();
	}

	/**
	 * Create an empty column.
	 *
	 * @param string $name The column name.
	 *
	 * @return Column_Definition
	 */
	public function create( string $name ): Column_Definition {
		return $this->column( $name );
	}

	/**
	 * Create a column alteration.
	 *
	 * @param string $name The column to alter.
	 *
	 * @return Column_Definition
	 */
	public function alter( string $name ): Column_Definition {
		return $this->column( $name )->alter();
	}

	/**
	 * Drop a column.
	 *
	 * @param string $name The column to drop.
	 *
	 * @return Column_Definition
	 */
	public function drop( string $name ): Column_Definition {
		return $this->column( $name )->drop();
	}

	/**
	 * Rename a column.
	 *
	 * @param string $name The column to rename.
	 *
	 * @param string $new_name The new column name.
	 *
	 * @return Column_Definition
	 */
	public function rename( string $name, string $new_name ): Column_Definition {
		return $this->column( $name )->rename( $new_name );
	}

	/**
	 * Get the SQL for this blueprint.
	 *
	 * @return string
	 *
	 * @throws Exception For unknown commands.
	 */
	public function get_sql(): string {
		return match ( $this->command ) {
			Command::CREATE => $this->get_create_sql(),
			Command::ALTER => $this->get_alter_sql(),
			Command::RENAME => $this->get_rename_sql(),
			Command::DROP => $this->get_drop_sql(),
			default => throw new Exception( 'UNKNOWN_COMMAND' )
		};
	}

	/**
	 * Get the column definition as SQL for use in CREATE and ALTER statements.
	 *
	 * @param Column_Definition $column The column to define.
	 *
	 * @return string
	 */
	protected function get_column_definition_sql( Column_Definition $column ): string {
		if ( is_null( $column->type ) ) {
			_doing_it_wrong( 'Blueprint::get_create_sql', esc_html__( 'No type set on column. Skipping.', 'wrd' ), '1.0.1' );
			return '';
		}

		if ( is_null( $column->name ) ) {
			_doing_it_wrong( 'Blueprint::get_create_sql', esc_html__( 'No name set on column. Skipping.', 'wrd' ), '1.0.1' );
			return '';
		}

		$column_name = strtolower( $column->name );
		$type        = strtolower( $column->type );

		$line = "{$column_name} {$type}";

		if ( $column->unsigned ) {
			$line .= ' UNSIGNED';
		}

		if ( $column->default ) {
			$line .= " DEFAULT '" . $column->default . "'";
		}

		if ( ! $column->nullable ) {
			$line .= ' NOT NULL';
		}

		if ( ! $column->autoincrement ) {
			$line .= ' AUTO_INCREMENT';
		}

		return $line;
	}

	/**
	 * Get the SQL query to create a table.
	 *
	 * Suitable for dbDelta.
	 *
	 * @return string
	 */
	protected function get_create_sql(): string {
		$table_name      = strtolower( $this->name );
		$charset_collate = $this->charset_collate;

		$sql = "CREATE TABLE {$table_name} (" . PHP_EOL;

		foreach ( $this->columns as $column ) {
			$definition = $this->get_column_definition_sql( $column );

			if ( ! $definition ) {
				continue;
			}

			$sql .= $definition . ',' . PHP_EOL;
		}

		foreach ( $this->columns as $column ) {
			$column_name = strtolower( $column->name );

			if ( $column->unique ) {
				$sql .= 'UNIQUE KEY ' . $column_name . ' (' . $column_name . '),' . PHP_EOL;
			}
			if ( $column->unique ) {
				$sql .= 'PRIMARY KEY  (' . $column_name . '),' . PHP_EOL;
			}
		}

		// Remove the last ', PHP_EOL' as it shouldn't be there.
		$sql = substr( $sql, 0, strlen( ',', PHP_EOL ) );

		$sql .= PHP_EOL . ") {$charset_collate};";

		return $sql;
	}

	/**
	 * Get the SQL query for altering the table.
	 *
	 * @return string
	 */
	protected function get_alter_sql(): string {
		if ( ! $this->columns ) {
			return '';
		}

		$table_name = strtolower( $this->name );

		$sql = "ALTER TABLE {$table_name}" . PHP_EOL;

		foreach ( $this->columns as $column ) {
			switch ( $column->command ) {
				case Command::CREATE:
					$sql .= 'ADD ' . $this->get_column_definition_sql( $column ) . ',' . PHP_EOL;
					break;

				case Command::ALTER:
					$sql .= 'ALTER COLUMN ' . $this->get_column_definition_sql( $column ) . ',' . PHP_EOL;
					break;

				case Command::DROP:
					$sql .= 'DROP COLUMN ' . strtolower( $column->name ) . ',' . PHP_EOL;
					break;

				case Command::RENAME:
					$sql .= 'RENAME COLUMN ' . strtolower( $column->name ) . ' to ' . strtolower( $column->new_name ) . ',' . PHP_EOL;
					break;
			}
		}

		// Remove the last ', PHP_EOL' as it shouldn't be there.
		$sql = substr( $sql, 0, strlen( ',', PHP_EOL ) );

		$sql .= ';';

		return $sql;
	}

	/**
	 * Get the SQL query for renaming the table.
	 *
	 * @return string
	 */
	protected function get_rename_sql(): string {
		return 'RENAME TABLE ' . strtolower( $this->name ) . ' TO ' . strtolower( $this->new_name ) . ';';
	}

	/**
	 * Get the SQL query for dropping the table.
	 *
	 * @return string
	 */
	protected function get_drop_sql(): string {
		return 'DROP TABLE ' . strtolower( $this->name ) . ';';
	}
}
