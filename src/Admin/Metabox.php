<?php
/**
 * Contains the Metabox class.
 *
 * @package wrd/wp-objective
 */

namespace Wrd\WpObjective\Admin;

use WP_Post;
use Wrd\WpObjective\Foundation\Service_Provider;
use Wrd\WpObjective\Support\Condition;

/**
 * Class for adding metaboxes to post edit screens.
 */
abstract class Metabox extends Service_Provider {
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
	 * Get the ID of the metabox.
	 *
	 * @return string
	 */
	public function get_id(): string {
		return sanitize_key( static::class );
	}

	/**
	 * Get the title of this metabox.
	 *
	 * @return string
	 */
	abstract public function get_title(): string;

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
	abstract public function display( ?WP_Post $post ): void;

	/**
	 * Initialize the metabox.
	 *
	 * Should be run on the 'init' hook.
	 *
	 * @return void
	 */
	public function boot(): void {
		add_action( 'add_meta_boxes', array( $this, 'register' ) );
	}

	/**
	 * Register the metabox.
	 *
	 * @return void
	 */
	public function register(): void {
		$condition = $this->get_conditions();

		if ( ! Condition::check( $condition, 'all' ) ) {
			return;
		}

		foreach ( $this->get_hidden() as $context => $ids ) {
			if ( ! is_array( $ids ) ) {
				$ids = array( $ids );
			}

			foreach ( $ids as $id ) {
				remove_meta_box( $id, get_current_screen(), $context );
			}
		}

		add_meta_box( $this->get_id(), $this->get_title(), array( $this, 'display' ), null, $this->get_context(), $this->get_priority() );
	}
}
