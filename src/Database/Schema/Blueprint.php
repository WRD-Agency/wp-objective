<?php
/**
 * Contains the Table class.
 *
 * @package wrd/wp-objective
 */

namespace Wrd\WpObjective\Database;

/**
 * Class for defining database tables.
 */
class Blueprint {
	/**
	 * The table name.
	 *
	 * @var string
	 */
	public string $name;

	/**
	 * The table's columns.
	 *
	 * @var Column_Definition[]
	 */
	public array $columns;

	/**
	 * Set the table name.
	 *
	 * @param string $name The table name.
	 */
	public function name( string $name ): void {
		$this->name = $name;
	}

	/**
	 * Add a column.
	 *
	 * @param string $name The column name.
	 *
	 * @param string $type The column type.
	 *
	 * @return Column_Definition
	 */
	public function column( string $name, string $type ): Column_Definition {
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
		return $this->column( $name, 'MEDIUMINT' );
	}

	/**
	 * Add a 'tinyint' column.
	 *
	 * @param string $name The column name.
	 *
	 * @return Column_Definition
	 */
	public function tiny_integer( string $name ): Column_Definition {
		return $this->column( $name, 'TINYINT(1)' );
	}

	/**
	 * Add a 'smallint' column.
	 *
	 * @param string $name The column name.
	 *
	 * @return Column_Definition
	 */
	public function small_integer( string $name ): Column_Definition {
		return $this->column( $name, 'SMALLINT(2)' );
	}

	/**
	 * Add a 'mediumint' column.
	 *
	 * @param string $name The column name.
	 *
	 * @return Column_Definition
	 */
	public function medium_integer( string $name ): Column_Definition {
		return $this->column( $name, 'MEDIUMINT(3)' );
	}

	/**
	 * Add a 'int' column.
	 *
	 * @param string $name The column name.
	 *
	 * @return Column_Definition
	 */
	public function integer( string $name ): Column_Definition {
		return $this->column( $name, 'INT(4)' );
	}

	/**
	 * Add a 'bigint' column.
	 *
	 * @param string $name The column name.
	 *
	 * @return Column_Definition
	 */
	public function big_integer( string $name ): Column_Definition {
		return $this->column( $name, 'BIGINT(8)' );
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
		return $this->column( $name, "DECIMAL($total, $places)" );
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
		return $this->column( $name, "DOUBLE($total, $places)" );
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
		return $this->column( $name, "FLOAT($precision)" );
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
		return $this->column( $name, "CHAR($length)" );
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
		return $this->column( $name, "VARCHAR($length)" );
	}

	/**
	 * Add a 'tinytext' column.
	 *
	 * @param string $name The column name.
	 *
	 * @return Column_Definition
	 */
	public function tiny_text( string $name ): Column_Definition {
		return $this->column( $name, 'TINYTEXT' );
	}

	/**
	 * Add a 'text' column.
	 *
	 * @param string $name The column name.
	 *
	 * @return Column_Definition
	 */
	public function text( string $name ): Column_Definition {
		return $this->column( $name, 'TEXT' );
	}

	/**
	 * Add a 'mediumtext' column.
	 *
	 * @param string $name The column name.
	 *
	 * @return Column_Definition
	 */
	public function medium_text( string $name ): Column_Definition {
		return $this->column( $name, 'MEDIUMTEXT' );
	}

	/**
	 * Add a 'longtext' column.
	 *
	 * @param string $name The column name.
	 *
	 * @return Column_Definition
	 */
	public function long_text( string $name ): Column_Definition {
		return $this->column( $name, 'LONGTEXT' );
	}

	/**
	 * Add a 'datetime' column.
	 *
	 * @param string $name The column name.
	 *
	 * @return Column_Definition
	 */
	public function date_time( string $name ): Column_Definition {
		return $this->column( $name, 'DATETIME' );
	}

	/**
	 * Add a 'date' column.
	 *
	 * @param string $name The column name.
	 *
	 * @return Column_Definition
	 */
	public function date( string $name ): Column_Definition {
		return $this->column( $name, 'DATE' );
	}

	/**
	 * Add a 'timestamp' column.
	 *
	 * @param string $name The column name.
	 *
	 * @return Column_Definition
	 */
	public function timestamp( string $name ): Column_Definition {
		return $this->column( $name, 'TIMESTAMP' );
	}

	/**
	 * Add a 'time' column.
	 *
	 * @param string $name The column name.
	 *
	 * @return Column_Definition
	 */
	public function time( string $name ): Column_Definition {
		return $this->column( $name, 'TIME' );
	}

	/**
	 * Add an auto-incrementing ID column.
	 *
	 * @param string $name The column name.
	 *
	 * @return Column_Definition
	 */
	public function id( string $name ): Column_Definition {
		return $this->integer( $name )->autoincrement()->primary();
	}
}
