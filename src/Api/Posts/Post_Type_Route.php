<?php
/**
 * Contains the Post_Type_Route class.
 *
 * @package wrd/wp-objective
 */

namespace Wrd\WpObjective\Api\Posts;

use Wrd\WpObjective\Api\Route;
use Wrd\WpObjective\Posts\Post_Type;
use Wrd\WpObjective\Support\Facades\Plugin;

/**
 * Route for displaying posts of a specific type.
 */
class Post_Type_Route extends Route {
	/**
	 * The namespace of the route.
	 *
	 * @var string
	 */
	public string $namespace = 'demo/v1';

	/**
	 * The name of the resource to use in slugs.
	 *
	 * @var string
	 */
	public string $resource_name = 'post';

	/**
	 * The class name of the post type to query.
	 *
	 * @var class-string<Post_Type>
	 */
	public string $post_type = Post_Type::class;

	/**
	 * Method by which all of the route's endpoints are registered.
	 *
	 * @return void
	 */
	public function register(): void {
		$post_type = $this->post_type::make();

		$this->register_endpoint( '/' . $this->resource_name, new Post_Type_Index_Endpoint( $post_type ) );
		$this->register_endpoint( '/' . $this->resource_name . '/(?P<id>[\d]+)', new Post_Type_Singular_Endpoint( $post_type ) );
	}
}
