<?php
/**
 * Entrypoint for the API.
 *
 * @package wrd\wp-object
 */

namespace Wrd\WpObjective\Api;

use WP_REST_Request;
use Wrd\WpObjective\Contracts\Apiable;
use Wrd\WpObjective\Foundation\Service_Provider;

/**
 * Represents a Route in the API.
 *
 * 'Route' is a little misleading here as the endpoints can choose their own paths. This is primarily for organisation.
 */
abstract class Route extends Service_Provider {
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
	 * Method by which all of the route's endpoints are registered.
	 *
	 * @return void
	 */
	abstract public function register(): void;

	/**
	 * Registers an endpoint.
	 *
	 * @param string                 $path The path, relative to the namespace.
	 *
	 * @param class-string<Endpoint> $class_name The class name for the endpoint class to register.
	 *
	 * @return void
	 */
	protected function register_endpoint( string $path, $class_name ): void {
		/**
		 * An endpoint object.
		 *
		 * @var Endpoint
		 */
		$endpoint = new $class_name();

		register_rest_route(
			$this->namespace,
			$path,
			array(
				'methods'             => $endpoint->get_methods(),
				'args'                => $endpoint->get_arguments(),
				'permission_callback' => array( $endpoint, 'permissions_callback' ),
				'callback'            => function ( WP_REST_Request $request ) use ( $endpoint ) {
					$response = $endpoint->handle( $request );
					return $this->prepare_for_response( $response );
				},
			)
		);
	}

	/**
	 * Convert an object to an API-compatible array.
	 *
	 * @param Apiable|array $item The object (or array of objects) to convert.
	 *
	 * @return array<string, mixed>
	 */
	protected function prepare_for_response( Apiable|array $item ): array {
		if ( is_array( $item ) ) {
			foreach ( $item as $key => $value ) {
				if ( is_array( $value ) || $value instanceof Apiable ) {
					$item[ $key ] = $this->prepare_for_response( $value );
				}
			}
		} else {
			$item = $this->prepare_for_response( $item->to_api() );
		}

		return $item;
	}

	/**
	 * Build and register a route.
	 *
	 * @return void
	 */
	public function init(): void {
		$this->register();
	}
}
