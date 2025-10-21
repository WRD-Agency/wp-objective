<?php
/**
 * Contains the Post_State class.
 *
 * @package wrd/wp-objective
 */

namespace Wrd\WpObjective\Admin\Post_States;

use WP_Post;
use Wrd\WpObjective\Foundation\Service_Provider;

/**
 * Class for adding custom post states.
 */
abstract class Post_State extends Service_Provider {
	/**
	 * Get the post type this table affects.
	 *
	 * @return string
	 */
	public function get_label(): string {
		return __( 'State', 'wrd' );
	}

	/**
	 * Get the ID of the post state.
	 *
	 * @return string
	 */
	public function get_id(): string {
		return static::class;
	}

	/**
	 * Check if a post has this state.
	 *
	 * @param int|WP_Post $post The post to check.
	 *
	 * @return bool
	 */
	abstract public function has_state( int|WP_Post $post ): bool;

	/**
	 * Initalize the post state.
	 *
	 * @return void
	 */
	public function boot(): void {
		add_filter( 'display_post_states', array( $this, 'append_state' ), 10, 2 );
	}

	/**
	 * Filters the default post display states used in the posts list table.
	 *
	 * @param string[] $post_states An array of post display states.
	 *
	 * @param \WP_Post $post        The current post object.
	 *
	 * @return string[] An array of post display states.
	 */
	public function append_state( array $post_states, \WP_Post $post ): array {
		if ( $this->has_state( $post ) ) {
			$post_states[ $this->get_id() ] = $this->get_label();
		}

		return $post_states;
	}
}
