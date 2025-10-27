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
 * @template TObject
 *
 * @param class-string<TObject>|null $id Optional. If provided, this function will resolve a binding.
 *
 * @return TObject|Plugin
 */
function plugin( $id = null ): Plugin {
	$plugin = Plugin::get_instance();

	if ( is_null( $id ) ) {
		return $plugin;
	}

	return $plugin->make( $id );
}
