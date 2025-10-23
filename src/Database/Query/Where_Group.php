<?php
/**
 * Contains the Where_Group class.
 *
 * @package wrd/wp-objective
 */

namespace Wrd\WpObjective\Database\Query;

/**
 * Class for holding a WHERE clause
 */
class Where_Group {
	/**
	 * The clauses.
	 *
	 * @var Where[]
	 */
	public array $clauses;

	/**
	 * The boolean operator.
	 *
	 * @var Boolean_Operator
	 */
	public Boolean_Operator $relation;

	/**
	 * Create a group of WHERE clauses.
	 *
	 * @param Boolean_Operator $relation The boolean operator.
	 *
	 * @param Where[]          $clauses The clauses.
	 */
	public function __construct( Boolean_Operator $relation, array $clauses = array() ) {
		$this->relation = $relation;
		$this->clauses  = $clauses;
	}

	/**
	 * Add a where clause.
	 *
	 * @param Where $clause The clause to add.
	 *
	 * @return static
	 */
	public function add( Where $clause ): static {
		$this->clauses[] = $clause;

		return $this;
	}

	/**
	 * Set the relation.
	 *
	 * @param Boolean_Operator|string $relation The new relation.
	 *
	 * @return static
	 */
	public function relation( Boolean_Operator|string $relation ): static {
		if ( is_string( $relation ) ) {
			$relation = Boolean_Operator::from( $relation );
		}

		$this->relation = $relation;

		return $this;
	}
}
