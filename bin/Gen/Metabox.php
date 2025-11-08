<?php
/**
 * Contains the CLASS_NAME class.
 *
 * @since PACKAGE_VERSION
 *
 * @package PACKAGE_NAME
 */

namespace PACKAGE_NAMESPACE\Admin\Metaboxes;

use WP_Post;
use Wrd\WpObjective\Admin\Metabox;
use Wrd\WpObjective\Support\Condition;

/**
 * The CLASS_NAME metabox.
 */
class CLASS_NAME extends Metabox {
	/**
	 * Apply conditions which must be met for this metabox to show.
	 *
	 * @return Condition|bool
	 */
	public function get_conditions(): Condition|bool {
		return true;
	}

	/**
	 * Get the context where this metabox should be shown.
	 *
	 * @return string One of 'advanced', 'normal' or 'side'.
	 */
	public function get_context(): string {
		return 'advanced';
	}

	/**
	 * Get the priority of this metabox.
	 *
	 * @return string One of 'high', 'core', 'default', or 'low'.
	 */
	public function get_priority(): string {
		return 'default';
	}

	/**
	 * Get the title of this metabox.
	 *
	 * @return string
	 */
	public function get_title(): string {
		return __( 'My Metabox' );
	}

	/**
	 * Get the other metaboxes to hide when this one is shown.
	 *
	 * The key is the context (e.g. side) and the value is an ID or array of IDs.
	 *
	 * @return array<string, string|string[]>
	 */
	public function get_hidden(): array {
		return array();
	}

	/**
	 * Display the metabox.
	 *
	 * @param WP_Post $post The post being edited. Null if it's being created.
	 */
	public function display( ?WP_Post $post ): void {
		esc_html_e( 'Hello world!' );
	}
}
