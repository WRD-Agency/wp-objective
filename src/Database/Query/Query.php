<?php
/**
 * Contains the Query class.
 *
 * @package wrd/wp-objective
 */

namespace Wrd\WpObjective\Database\Query;

use Wrd\WpObjective\Database\Database_Manager;
use Wrd\WpObjective\Support\Collection;

/**
 * Class for creating a database query.
 */
class Query {
	/**
	 * The database manager to use.
	 *
	 * @var Database_Manager
	 */
	protected Database_Manager $db;

	/**
	 * The table being queried.
	 *
	 * @var string
	 */
	public string $table;

	/**
	 * The values being sanitized for the query.
	 *
	 * @var string[]
	 */
	public array $bindings;

	/**
	 * Where clauses to add to the query.
	 *
	 * @var Where_Group
	 */
	public Where_Group $where;

	/**
	 * The order.
	 *
	 * @var Order
	 */
	public Order $order = Order::ASC;

	/**
	 * The order columns.
	 *
	 * @var ?string
	 */
	public ?string $order_by = null;

	/**
	 * The limit.
	 *
	 * @var int
	 */
	public int $limit = -1;

	/**
	 * The offset.
	 *
	 * @var int
	 */
	public int $offset = 0;

	/**
	 * Create a query.
	 *
	 * @param Database_Manager $db The database manager to use.
	 */
	public function __construct( Database_Manager $db ) {
		$this->db    = $db;
		$this->where = new Where_Group( Boolean_Operator::AND );
	}

	/**
	 * Set the table to query.
	 *
	 * @param string $table The table name.
	 *
	 * @return static
	 */
	public function table( string $table ): static {
		$this->table = $table;
		return $this;
	}

	/**
	 * Add a WHERE clause.
	 *
	 * @param string                     $column The column name.
	 *
	 * @param Comparison_Operator|string $operator The comaparison operator.
	 *
	 * @param mixed                      $value The value.
	 *
	 * @param bool                       $not Negate the clause.
	 *
	 * @return static
	 */
	public function where( string $column, string|Comparison_Operator $operator, mixed $value, bool $not = false ): static {
		if ( is_string( $operator ) ) {
			$operator = Comparison_Operator::from( $operator );
		}

		$this->where->add( new Where( $column, $operator, $value, $not ) );

		return $this;
	}

	/**
	 * Add a WHERE NOT clause.
	 *
	 * @param string                     $column The column name.
	 *
	 * @param Comparison_Operator|string $operator The comaparison operator.
	 *
	 * @param mixed                      $value The value.
	 *
	 * @return static
	 */
	public function where_not( string $column, string|Comparison_Operator $operator, mixed $value ): static {
		return $this->where( $column, $operator, $value, true );
	}

	/**
	 * Add a WHERE IN clause.
	 *
	 * @param string $column The column name.
	 *
	 * @param array  $values The values to look in.
	 *
	 * @return static
	 */
	public function where_in( string $column, array $values ): static {
		return $this->where( $column, Comparison_Operator::IN, $values );
	}

	/**
	 * Add a WHERE NOT IN clause.
	 *
	 * @param string $column The column name.
	 *
	 * @param array  $values The values to look in.
	 *
	 * @return static
	 */
	public function where_not_in( string $column, array $values ): static {
		return $this->where( $column, Comparison_Operator::IN, $values, true );
	}

	/**
	 * Add a WHERE IS NULL clause.
	 *
	 * @param string $column The column name.
	 *
	 * @return static
	 */
	public function where_null( string $column ): static {
		return $this->where( $column, Comparison_Operator::IS, null );
	}

	/**
	 * Add a WHERE IS NOT NULL clause.
	 *
	 * @param string $column The column name.
	 *
	 * @return static
	 */
	public function where_not_null( string $column ): static {
		return $this->where( $column, Comparison_Operator::IS, null, true );
	}

	/**
	 * Bulk add WHERE clauses.
	 *
	 * @param string[]                   $columns The array mapping columns to values.
	 *
	 * @param Comparison_Operator|string $operator Operator to compare with. Defaults to Equals.
	 *
	 * @return static
	 */
	public function where_all( array $columns, Comparison_Operator|string $operator = Comparison_Operator::EQ ): static {
		foreach ( $columns as $column => $value ) {
			$this->where( $column, $operator, $value );
		}

		return $this;
	}

	/**
	 * Bulk add WHERE NOT clauses.
	 *
	 * @param string[]                   $columns The array mapping columns to values.
	 *
	 * @param Comparison_Operator|string $operator Operator to compare with. Defaults to Equals.
	 *
	 * @return static
	 */
	public function where_all_not( array $columns, Comparison_Operator|string $operator = Comparison_Operator::EQ ): static {
		foreach ( $columns as $column => $value ) {
			$this->where( $column, $operator, $value, true );
		}

		return $this;
	}

	/**
	 * Set the order.
	 *
	 * @param string|Order $order The order.
	 *
	 * @return static
	 */
	public function order( string|Order $order ): static {
		if ( is_string( $order ) ) {
			$order = Order::from( $order );
		}

		$this->order = $order;

		return $this;
	}

	/**
	 * Set the order column.
	 *
	 * @param string            $order_by The column to order by.
	 *
	 * @param Order|string|null $order The order.
	 *
	 * @return static
	 */
	public function order_by( string $order_by, Order|string|null $order = null ): static {
		$this->order_by = $order_by;

		if ( ! is_null( $order ) ) {
			$this->order( $order );
		}

		return $this;
	}

	/**
	 * Set the limit.
	 *
	 * @param int $limit The limit.
	 *
	 * @return static
	 */
	public function limit( int $limit ): static {
		$this->limit = $limit;

		return $this;
	}

	/**
	 * Set the offset.
	 *
	 * @param int $offset The offset.
	 *
	 * @return static
	 */
	public function offset( int $offset ): static {
		$this->offset = $offset;

		return $this;
	}

	/**
	 * Get the found rows.
	 *
	 * @return Collection<Row>
	 */
	public function get(): Collection {
		// TODO.
		return Collection::from( array() );
	}

	/**
	 * Delete the found rows.
	 *
	 * @return bool
	 */
	public function delete(): bool {
		// TODO.
		return true;
	}

	/**
	 * Bulk update the found rows.
	 *
	 * @param string[] $data The row data.
	 *
	 * @return bool
	 */
	public function update( array $data ): bool {
		// TODO.
		return true;
	}

	/**
	 * Create the SQL command for this query.
	 *
	 * @return string
	 */
	public function to_sql(): string {
		// TODO.
		return '';
	}
}
