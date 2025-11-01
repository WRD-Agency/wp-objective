<?php
/**
 * Contains the Post_Type_Index_Endpoint class.
 *
 * @package wrd/wp-objective
 */

namespace Wrd\WpObjective\Api\Posts;

use WP_Error;
use WP_REST_Request;
use Wrd\WpObjective\Api\Endpoint;
use Wrd\WpObjective\Contracts\Apiable;
use Wrd\WpObjective\Http\Method;
use Wrd\WpObjective\Posts\Post_Type;

/**
 * Endpoint for getting a list of clubs.
 */
class Post_Type_Index_Endpoint extends Endpoint {
	/**
	 * The HTTP methods this endpoint supports.
	 *
	 * @var Method[]
	 */
	public array $methods = Method::READABLE;

	/**
	 * The post type.
	 *
	 * @var Post_Type
	 */
	public Post_Type $post_type;

	/**
	 * Create a post type singular enpoint.
	 *
	 * @param Post_Type $post_type The post type.
	 */
	public function __construct( Post_Type $post_type ) {
		$this->post_type = $post_type;
	}

	/**
	 * Called to check if the current user can undertake this action.
	 *
	 * @return WP_Error|bool
	 */
	public function permissions_callback(): WP_Error|bool {
		return true;
	}

	/**
	 * Handle a request to this endpoint.
	 *
	 * @param \WP_REST_Request $request The request to handle.
	 *
	 * @return Apiable|Apiable[] Any API compatible object or an array of API compatible objects.
	 */
	public function handle( WP_REST_Request $request ): Apiable|array {
		return $this->post_type->query();
	}
}
