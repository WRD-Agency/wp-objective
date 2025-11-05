<?php
/**
 * Contains the Post_Status class.
 *
 * @package wrd/wp-objective
 */

namespace Wrd\WpObjective\Posts;

use Wrd\WpObjective\Foundation\Service_Provider;

/**
 * For building up a post status.
 */
abstract class Post_Status extends Service_Provider {
	/**
	 * Get this class' post status.
	 *
	 * @return string
	 */
	abstract public function get_name(): string;

	/**
	 * Get the label for this post status.
	 *
	 * @return string
	 */
	abstract public function get_label(): string;

	/**
	 * Get the arguments for this post type.
	 *
	 * @return array
	 */
	public function get_args(): array {
		return array(
			'label_count'               => false,
			'exclude_from_search'       => true,
			'public'                    => false,
			'internal'                  => false,
			'protected'                 => false,
			'private'                   => false,
			'publicly_queryable'        => false,
			'show_in_admin_all_list'    => true,
			'show_in_admin_status_list' => true,
		);
	}

	/**
	 * Initialize the post type.
	 *
	 * @return void
	 */
	public function init(): void {
		$args = $this->get_args();

		$args['label'] = $this->get_label();

		register_post_status( $this->get_name(), $args );
	}
}
