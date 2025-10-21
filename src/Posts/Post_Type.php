<?php
/**
 * Contains the Post_Type class.
 *
 * @package wrd/wp-objective
 */

namespace Wrd\WpObjective\Posts;

use WP_Post;
use Wrd\WpObjective\Foundation\Service_Provider;
use Wrd\WpObjective\Log\Level;
use Wrd\WpObjective\Log\Log_Manager;
use Wrd\WpObjective\Log\Log_Message;

/**
 * For building up a post type.
 */
abstract class Post_Type extends Service_Provider {
	/**
	 * The logger.
	 *
	 * @var Log_Manager
	 */
	private Log_Manager $logger;

	/**
	 * Create an instance.
	 *
	 * @param Log_Manager $logger The logger to use.
	 */
	public function __construct( Log_Manager $logger ) {
		$this->logger = $logger;
	}

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
	abstract public function get_post_class(): string;

	/**
	 * Get the related Post instance for this type.
	 *
	 * @param int|WP_Post|null $post The post.
	 *
	 * @return Post
	 */
	public function get_post( int|WP_Post|null $post = null ): Post {
		return new ( $this->get_post_class() )( $post );
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
	 * Create a new post.
	 *
	 * @param array $postdata The post's data.
	 *
	 * @return Post
	 */
	public function create( array $postdata ): Post {
		$args = wp_parse_args(
			$postdata,
			array(
				'post_type'    => $this->get_name(),
				'post_title'   => gmdate( 'Y-m-d H:i:s' ),
				'post_content' => '',
			)
		);

		$id = wp_insert_post( $args );

		$this->logger->add( message: __( 'Created new post.', 'wrd' ), data: $args, target: $id );

		return $this->get_post( $id );
	}
}
