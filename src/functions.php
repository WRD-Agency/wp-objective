<?php
/**
 * Contains functions.
 *
 * @package wrd/wp-objective
 */

namespace Wrd\WpObjective;

use Wrd\WpObjective\Foundation\Plugin;

/**
 * Get the global plugin instance.
 *
 * @return Plugin
 */
function plugin(): Plugin {
	return Plugin::get_instance();
}
