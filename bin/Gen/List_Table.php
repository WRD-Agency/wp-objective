<?php
/**
 * Contains the CLASS_NAME class.
 *
 * @since PACKAGE_VERSION
 *
 * @package PACKAGE_NAME
 */

namespace PACKAGE_NAMESPACE\Admin\List_Tables;

use Wrd\WpObjective\Admin\List_Table;

/**
 * The CLASS_NAME list table.
 */
class CLASS_NAME extends List_Table {
	/**
	 * Get the post type this table affects.
	 *
	 * @var string $post_type
	 */
	public function get_post_type(): string {
		return 'post';
	}

	/**
	 * Run in the 'init' hook.
	 *
	 * @return void
	 */
	public function init(): void {
		// $this->remove_column( 'date' );
	}
}
