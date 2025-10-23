<?php
/**
 * Contains the Comparison_Operator enum.
 *
 * @package wrd/wp-objective
 */

namespace Wrd\WpObjective\Database\Query;

enum Comparison_Operator: string {
	case EQ   = '=';
	case NEQ  = '<>';
	case GT   = '>';
	case GTE  = '>=';
	case LT   = '<';
	case LTE  = '<=';
	case IS   = 'IS';
	case LIKE = 'LIKE';
	case IN   = 'IN';
}
