<?php
/**
 * Contains the Column_Definition class.
 *
 * @package wrd/wp-objective
 */

namespace Wrd\WpObjective\Database\Schema;

/**
 * Class for a column definition.
 */
class Column_Definition {
	/**
	 * The command related to this column.
	 *
	 * @var Command
	 */
	public Command $command = Command::CREATE;

	/**
	 * The column name.
	 *
	 * @var string
	 */
	public string $name;

	/**
	 * The new name of the column.
	 *
	 * Only applicable if the command is Command::RENAME;
	 *
	 * @var ?string
	 */
	public ?string $new_name = null;

	/**
	 * The column type.
	 *
	 * @var ?string
	 */
	public ?string $type;

	/**
	 * Whether the column is nullable or not.
	 *
	 * @var bool
	 */
	public bool $nullable = true;

	/**
	 * Whether the column is unsigned or not.
	 *
	 * @var bool
	 */
	public bool $unsigned = false;

	/**
	 * The default value.
	 *
	 * @var ?string
	 */
	public ?string $default = null;

	/**
	 * Whether the column is autoincrementing or not.
	 *
	 * @var bool
	 */
	public bool $autoincrement = false;

	/**
	 * Whether the column is the primary key or not.
	 *
	 * @var bool
	 */
	public bool $primary = false;

	/**
	 * Whether the column is unique or not.
	 *
	 * @var bool
	 */
	public bool $unique = false;

	/**
	 * Create a column definition.
	 *
	 * @param string  $name The column name.
	 *
	 * @param ?string $type The column type. Optional.
	 */
	public function __construct( string $name, ?string $type = null ) {
		$this->name = $name;
		$this->type = $type;
	}

	/**
	 * Set the type of this column.
	 *
	 * @param string $type The column type.
	 *
	 * @return static
	 */
	public function type( string $type ): static {
		$this->type = $type;

		return $this;
	}

	/**
	 * Set to a 'bool' column.
	 *
	 * @return static
	 */
	public function boolean(): static {
		return $this->type( 'MEDIUMINT' );
	}

	/**
	 * Set to a 'tinyint' column.
	 *
	 * @return static
	 */
	public function tiny_integer(): static {
		return $this->type( 'TINYINT(1)' );
	}

	/**
	 * Set to a 'smallint' column.
	 *
	 * @return static
	 */
	public function small_integer(): static {
		return $this->type( 'SMALLINT(2)' );
	}

	/**
	 * Set to a 'mediumint' column.
	 *
	 * @return static
	 */
	public function medium_integer(): static {
		return $this->type( 'MEDIUMINT(3)' );
	}

	/**
	 * Set to a 'int' column.
	 *
	 * @return static
	 */
	public function integer(): static {
		return $this->type( 'INT(4)' );
	}

	/**
	 * Set to a 'bigint' column.
	 *
	 * @return static
	 */
	public function big_integer(): static {
		return $this->type( 'BIGINT(8)' );
	}

	/**
	 * Set to a 'decimal' column.
	 *
	 * @param int $total The precision (total digits).
	 *
	 * @param int $places The scale (decimal digits).
	 *
	 * @return static
	 */
	public function decimal( int $total, int $places ): static {
		return $this->type( "DECIMAL($total, $places)" );
	}

	/**
	 * Set to a 'double' column.
	 *
	 * @param int $total The precision (total digits).
	 *
	 * @param int $places The scale (decimal digits).
	 *
	 * @return static
	 */
	public function double( int $total, int $places ): static {
		return $this->type( "DOUBLE($total, $places)" );
	}

	/**
	 * Set to a 'float' column.
	 *
	 * @param int $precision The precision (total digits).
	 *
	 * @return static
	 */
	public function float( int $precision ): static {
		return $this->type( "FLOAT($precision)" );
	}

	/**
	 * Set to a 'char' column.
	 *
	 * For fixed length strings.
	 *
	 * @param int $length The length.
	 *
	 * @return static
	 */
	public function char( int $length ): static {
		return $this->type( "CHAR($length)" );
	}

	/**
	 * Set to a 'varchar' column.
	 *
	 * For variable length strings.
	 *
	 * @param int $length The length.
	 *
	 * @return static
	 */
	public function string( int $length ): static {
		return $this->type( "VARCHAR($length)" );
	}

	/**
	 * Set to a 'tinytext' column.
	 *
	 * @return static
	 */
	public function tiny_text(): static {
		return $this->type( 'TINYTEXT' );
	}

	/**
	 * Set to a 'text' column.
	 *
	 * @return static
	 */
	public function text(): static {
		return $this->type( 'TEXT' );
	}

	/**
	 * Set to a 'mediumtext' column.
	 *
	 * @return static
	 */
	public function medium_text(): static {
		return $this->type( 'MEDIUMTEXT' );
	}

	/**
	 * Set to a 'longtext' column.
	 *
	 * @return static
	 */
	public function long_text(): static {
		return $this->type( 'LONGTEXT' );
	}

	/**
	 * Set to a 'datetime' column.
	 *
	 * @return static
	 */
	public function date_time(): static {
		return $this->type( 'DATETIME' );
	}

	/**
	 * Set to a 'date' column.
	 *
	 * @return static
	 */
	public function date(): static {
		return $this->type( 'DATE' );
	}

	/**
	 * Set to a 'timestamp' column.
	 *
	 * @return static
	 */
	public function timestamp(): static {
		return $this->type( 'TIMESTAMP' );
	}

	/**
	 * Set to a 'time' column.
	 *
	 * @return static
	 */
	public function time(): static {
		return $this->type( 'TIME' );
	}

	/**
	 * Set to an auto-incrementing ID column.
	 *
	 * @return static
	 */
	public function id(): static {
		return $this->integer()->autoincrement()->primary();
	}

	/**
	 * Make the column nullable.
	 *
	 * @return static
	 */
	public function nullable(): static {
		$this->nullable = true;
		return $this;
	}

	/**
	 * Make the column unsigned.
	 *
	 * @return static
	 */
	public function unsigned(): static {
		$this->unsigned = true;
		return $this;
	}

	/**
	 * Make the column autoincrement.
	 *
	 * @return static
	 */
	public function autoincrement(): static {
		$this->autoincrement = true;
		return $this;
	}

	/**
	 * Set the default value.
	 *
	 * @param string $value The new default value.
	 *
	 * @return static
	 */
	public function default( string $value ): static {
		$this->default = $value;
		return $this;
	}

	/**
	 * Make the column the primary key.
	 *
	 * @return static
	 */
	public function primary(): static {
		$this->primary = true;
		return $this;
	}

	/**
	 * Make the column unique.
	 *
	 * @return static
	 */
	public function unique(): static {
		$this->unique = true;
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
	 * Set the command to CREATE.
	 *
	 * @return static
	 */
	public function create(): static {
		$this->command( Command::CREATE );
		return $this;
	}


	/**
	 * Set the command to ALTER.
	 *
	 * @return static
	 */
	public function alter(): static {
		$this->command( Command::ALTER );
		return $this;
	}

	/**
	 * Set the command to RENAME.
	 *
	 * @param string $new_name The new column name.
	 *
	 * @return static
	 */
	public function rename( string $new_name ): static {
		$this->new_name = $new_name;
		$this->command( Command::RENAME );

		return $this;
	}

	/**
	 * Set the command to DROP.
	 *
	 * @return static
	 */
	public function drop(): static {
		$this->command( Command::DROP );
		return $this;
	}
}
