<?php
/**
 * Contains the Status enum.
 *
 * @package wrd/wp-objective
 */

namespace Wrd\WpObjective\Log;

enum Status: string {
	case NONE    = 'NONE';
	case SUCCESS = 'SUCCESS';
	case ERROR   = 'ERROR';
	case FATAL   = 'FATAL';
}
