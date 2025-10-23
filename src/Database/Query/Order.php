<?php
/**
 * Contains the Order enum.
 *
 * @package wrd/wp-objective
 */

namespace Wrd\WpObjective\Database\Query;

enum Order: string {
	case ASC = 'ASC';
	case DESC = 'DESC';
}
