<?php
/**
 * Contains the Level enum.
 *
 * @package wrd/wp-objective
 */

namespace Wrd\WpObjective\Log;

enum Level: string {
	case DEBUG = 'DEBUG';
	case WARN  = 'WARN';
	case ERROR = 'ERROR';
	case FATAL = 'FATAL';
}
