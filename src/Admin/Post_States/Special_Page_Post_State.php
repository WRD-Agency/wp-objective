<?php
/**
 * Contains the Special_Page_Post_State class.
 *
 * @package wrd/wp-objective
 */

namespace Wrd\WpObjective\Admin\Post_States;

use WP_Post;
use Wrd\WpObjective\Support\Settings_Manager;

/**
 * Class for adding custom post states.
 */
abstract class Special_Page_Post_State extends Post_State {
	/**
	 * Accessor to the settings system
	 *
	 * @var Settings_Manager
	 */
	private Settings_Manager $settings;

	/**
	 * Create the plugin.
	 *
	 * @param Settings_Manager $settings Accessor to the settings system.
	 */
	public function __construct( Settings_Manager $settings ) {
		$this->settings = $settings;
	}

	/**
	 * Get the key of the options field used to store the special page.
	 *
	 * @return string
	 */
	abstract public function get_option_key(): string;

	/**
	 * Check if a special page is set.
	 *
	 * @return bool
	 */
	public function has_post(): bool {
		return $this->get_post_id() !== null;
	}

	/**
	 * Get the ID of the special page.
	 *
	 * @return ?int
	 */
	public function get_post_id(): ?int {
		return $this->settings->get( $this->get_option_key(), null );
	}

	/**
	 * Get the special page.
	 *
	 * @return ?WP_Post
	 */
	public function get_post(): ?WP_Post {
		if ( ! $this->has_post() ) {
			return null;
		}

		return get_post( $this->get_post_id() );
	}

	/**
	 * Check if a post has this state.
	 *
	 * @param int|WP_Post $post The post to check.
	 *
	 * @return bool
	 */
	public function has_state( int|WP_Post $post ): bool {
		$post = get_post( $post );

		return $this->get_post_id() === $post->ID;
	}
}
