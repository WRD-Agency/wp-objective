<?php
/**
 * Contains the Where class.
 *
 * @package wrd/wp-objective
 */

namespace Wrd\WpObjective\Database\Query;

use DateTime;
use Exception;

/**
 * Class for holding a WHERE clause
 */
class Where {
	/**
	 * The column name.
	 *
	 * @var string
	 */
	public string $column;

	/**
	 * The value.
	 *
	 * @var mixed
	 */
	public mixed $value;

	/**
	 * The comaparison operator.
	 *
	 * @var Comparison_Operator
	 */
	public Comparison_Operator $operator;

	/**
	 * Whether this is a NOT WHERE clause.
	 *
	 * @var bool
	 */
	public bool $not;

	/**
	 * Create a WHERE clause.
	 *
	 * @param string              $column The column name.
	 *
	 * @param Comparison_Operator $operator The comaparison operator.
	 *
	 * @param mixed               $value The value.
	 *
	 * @param bool                $not Whether this is a NOT WHERE clause.
	 *
	 * @throws Exception When operator & value combinations are invalid.
	 */
	public function __construct( string $column, Comparison_Operator $operator, mixed $value, bool $not = false ) {
		$this->column   = $column;
		$this->value    = $value;
		$this->operator = $operator;
		$this->not      = $not;

		if ( ! $this->operator_is_valid() ) {
			throw new Exception( 'INVALID_OPERATOR' );
		}
	}

	/**
	 * Check the combination of operator and value type.
	 *
	 * @return bool
	 */
	public function operator_is_valid(): bool {
		switch ( $this->operator ) {
			case Comparison_Operator::GT:
			case Comparison_Operator::GTE:
			case Comparison_Operator::LT:
			case Comparison_Operator::LTE:
				return is_int( $this->value ) || is_float( $this->value ) || is_a( $this->value, DateTime::class );

			case Comparison_Operator::IS:
				return is_bool( $this->value ) || is_null( $this->value );

			case Comparison_Operator::LIKE:
				return is_string( $this->value );

			case Comparison_Operator::IN:
				return is_array( $this->value ) && array_is_list( $this->value );
		}

		return true;
	}
}
