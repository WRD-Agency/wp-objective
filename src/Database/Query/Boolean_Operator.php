<?php
/**
 * Contains the Boolean_Operator enum.
 *
 * @package wrd/wp-objective
 */

namespace Wrd\WpObjective\Database\Query;

enum Boolean_Operator: string {
	case AND = 'AND';
	case OR  = 'OR';
}
