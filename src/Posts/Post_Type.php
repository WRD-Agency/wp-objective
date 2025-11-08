<?php
/**
 * Contains the Post_Type class.
 *
 * @package wrd/wp-objective
 */

namespace Wrd\WpObjective\Posts;

use WP_Post;
use Wrd\WpObjective\Foundation\Service_Provider;
use Wrd\WpObjective\Support\Collection;

/**
 * For building up a post type.
 */
class Post_Type extends Service_Provider {
	/**
	 * Get this class' post type.
	 *
	 * @return string
	 */
	public function get_name(): string {
		return 'post';
	}

	/**
	 * Get the related Post class for this type.
	 *
	 * @return string
	 */
	public static function get_post_class(): string {
		return Post::class;
	}

	/**
	 * Get the related Post instance for this type.
	 *
	 * @param int|WP_Post|null $post The post.
	 *
	 * @return Post
	 */
	public function get_post( int|WP_Post|null $post = null ): Post {
		return new ( static::get_post_class() )( $post );
	}

	/**
	 * Query the post type and get the related Post instance for this type.
	 *
	 * @param array $args Arguments to pass to get_posts.
	 *
	 * @return Post[]
	 *
	 * @see get_posts
	 */
	public function query( array $args = array() ): array {
		$args['post_type'] = $this->get_name();

		return Collection::from( get_posts( $args ) )
			->map( fn( WP_Post $post ) => $this->get_post( $post ) )
			->all();
	}

	/**
	 * Check if a given ID is valid for this post type.
	 *
	 * @param int|string|null $id The ID to check.
	 *
	 * @return bool
	 */
	public function has_id( int|string|null $id ): bool {
		return get_post_type( $id ) === $this->get_name();
	}

	/**
	 * Get the label for this post type.
	 *
	 * @param bool $plural Whether to get the plural or singular label.
	 *
	 * @return string
	 */
	public function get_label( bool $plural = true ): string {
		return $plural ? __( 'Posts', 'wrd' ) : __( 'Post', 'wrd' );
	}

	/**
	 * Get all supported labels for this post type.
	 *
	 * @return string[]
	 */
	public function get_labels(): array {
		$label_plural   = $this->get_label( true );
		$label_singular = $this->get_label( false );

		return array(
			'name'               => "$label_plural",
			'singular_name'      => "$label_singular",
			'menu_name'          => "$label_plural",
			'parent_item_colon'  => "Parent $label_singular",
			'all_items'          => "All $label_plural",
			'view_item'          => "View $label_singular",
			'add_new_item'       => "Add New $label_singular",
			'add_new'            => 'Add New',
			'edit_item'          => "Edit $label_singular",
			'update_item'        => "Update $label_singular",
			'search_items'       => "Search $label_plural",
			'not_found'          => 'Not Found',
			'not_found_in_trash' => 'Not found in Trash',
		);
	}

	/**
	 * Get the icon for this post type.
	 *
	 * @return string
	 */
	public function get_icon(): string {
		return 'dashicons-media-default';
	}

	/**
	 * Get the arguments for this post type.
	 *
	 * @return array
	 */
	public function get_args(): array {
		return array(
			'public'              => false,
			'hierarchical'        => false,
			'exclude_from_search' => true,
			'publicly_queryable'  => false,
			'show_ui'             => true,
			'show_in_menu'        => true,
			'show_in_nav_menus'   => false,
			'show_in_admin_bar'   => false,
			'show_in_rest'        => false,

			'menu_position'       => 20.5,

			'supports'            => array( 'title', 'thumbnail' ),
			'taxonomies'          => array(),

			'has_archive'         => false,
			'rewrite'             => false,

			'can_export'          => true,
		);
	}

	/**
	 * Initialize the post type.
	 *
	 * @return void
	 */
	public function init(): void {
		$args = $this->get_args();

		$args['menu_icon'] = $this->get_icon();
		$args['label']     = $this->get_label( true );
		$args['labels']    = $this->get_labels();

		register_post_type( $this->get_name(), $args );
	}

	/**
	 * Get the menu slug for the post type list screen in the admin editor.
	 *
	 * @return string
	 */
	public function get_edit_slug(): string {
		return 'edit.php?post_type=' . $this->get_name();
	}

	/**
	 * Get the URL to for the admin page to edit a list of this post type.
	 *
	 * @return string
	 */
	public function get_edit_link(): string {
		return admin_url( $this->get_edit_slug() );
	}
}
