<?php
/**
 * Contains the Column_Definition class.
 *
 * @package wrd/wp-objective
 */

namespace Wrd\WpObjective\Database;

/**
 * Class for a column definition.
 */
class Column_Definition {
	/**
	 * The column name.
	 *
	 * @var string
	 */
	public string $name;

	/**
	 * The column type.
	 *
	 * @var string
	 */
	public string $type;

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
	 * @param string $name The column name.
	 *
	 * @param string $type The column type.
	 */
	public function __construct( string $name, string $type ) {
		$this->name = $name;
		$this->type = $type;
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
}
