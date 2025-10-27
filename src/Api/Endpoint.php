<?php
/**
 * Contains the Endpoint class.
 *
 * @package wrd\wp-object
 */

namespace Wrd\WpObjective\Api;

use WP_Error;
use WP_REST_Request;
use Wrd\WpObjective\Contracts\Apiable;

/**
 * Class for creating an API endpoint.
 */
abstract class Endpoint {
	/**
	 * The HTTP methods this endpoint supports.
	 *
	 * @var Wrd\WpObjective\Http\Method[]
	 */
	public array $methods = array();

	/**
	 * Called to check if the current user can undertake this action.
	 *
	 * @return WP_Error|bool
	 */
	abstract public function permissions_callback(): WP_Error|bool;

	/**
	 * Get the methods for this endpoint, as an array of strings.
	 *
	 * @return string[]
	 */
	public function get_methods(): array {
		return array_column( $this->methods, 'value' );
	}

	/**
	 * Get the arguments for this endpoint.
	 *
	 * @return array
	 */
	public function get_arguments(): array {
		return array();
	}

	/**
	 * Handle a request to this endpoint.
	 *
	 * @param \WP_REST_Request $request The request to handle.
	 *
	 * @return Apiable|Apiable[] Any API compatible object or an array of API compatible objects.
	 */
	abstract public function handle( WP_REST_Request $request ): Apiable|array;
}
