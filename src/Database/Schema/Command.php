<?php
/**
 * Contains the Command enum.
 *
 * @package wrd/wp-objective
 */

namespace Wrd\WpObjective\Database\Schema;

enum Command: string {
	case CREATE = 'CREATE';
	case ALTER  = 'ALTER';
	case RENAME = 'RENAME';
	case DROP   = 'DROP';
}
