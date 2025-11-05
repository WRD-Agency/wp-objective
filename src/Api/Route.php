<?php
/**
 * Entrypoint for the API.
 *
 * @package wrd\wp-object
 */

namespace Wrd\WpObjective\Api;

use WP_Error;
use WP_REST_Request;
use Wrd\WpObjective\Contracts\Apiable;
use Wrd\WpObjective\Foundation\Service_Provider;
use Wrd\WpObjective\Log\Level;
use Wrd\WpObjective\Log\Log_Manager;

/**
 * Represents a Route in the API.
 *
 * 'Route' is a little misleading here as the endpoints can choose their own paths. This is primarily for organisation.
 */
abstract class Route extends Service_Provider {
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
	 * @param string                          $path The path, relative to the namespace.
	 *
	 * @param class-string<Endpoint>|Endpoint $endpoint The class name or instance for the endpoint class to register.
	 *
	 * @return void
	 */
	protected function register_endpoint( string $path, $endpoint ): void {
		if ( is_string( $endpoint ) ) {
			/**
			 * An endpoint object.
			 *
			 * @var Endpoint
			 */
			$endpoint = new $endpoint();
		}

		register_rest_route(
			$this->namespace,
			$path,
			array(
				'methods'             => $endpoint->get_methods(),
				'args'                => $endpoint->get_arguments(),
				'permission_callback' => array( $endpoint, 'permissions_callback' ),
				'callback'            => function ( WP_REST_Request $request ) use ( $endpoint ) {
					$this->logger->add(
						message: __( 'Started API request.', 'wrd' ),
						data: array(
							'route'  => $request->get_route(),
							'method' => $request->get_method(),
							'params' => $request->get_params(),
						)
					);

					$response = $endpoint->handle( $request );

					if ( is_wp_error( $response ) ) {
						$this->logger->add(
							level: Level::ERROR,
							message: __( 'API request handler hit an error.', 'wrd' ),
							data: array(
								'error' => $response,
							)
						);
					} else {
						$this->logger->add(
							message: __( 'API request handled.', 'wrd' ),
							data: array(
								'response' => $response,
							)
						);
					}

					return $this->prepare_for_response( $response );
				},
			)
		);
	}

	/**
	 * Convert an object to an API-compatible array.
	 *
	 * @param Apiable|WP_Error|array $item The object (or array of objects) to convert.
	 *
	 * @return array<string, mixed>|WP_Error
	 */
	protected function prepare_for_response( Apiable|WP_Error|array $item ): array|WP_Error {
		if ( is_wp_error( $item ) ) {
			return $item;
		} elseif ( is_array( $item ) ) {
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
		add_action( 'rest_api_init', array( $this, 'register' ) );
	}
}
