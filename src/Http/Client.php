<?php

/**
 * Contains the Client class.
 *
 * @package wrd/wp-objective
 */

namespace Wrd\WpObjective\Http;

use WpOrg\Requests\Exception;
use WpOrg\Requests\Requests;
use Wrd\WpObjective\Foundation\Service_Provider;
use Wrd\WpObjective\Log\Level;
use Wrd\WpObjective\Log\Log_Manager;
use Wrd\WpObjective\Support\Collection;

/**
 * Base class for handling API clients.
 */
abstract class Client extends Service_Provider {



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
	 * The base URL of the client.
	 *
	 * @var string
	 */
	private string $base = '';

	/**
	 * Stack of middlewares to apply to requests.
	 *
	 * @var (callable(Request): void)[]
	 */
	private array $middlewares = array();

	/**
	 * Update the base URL of the client.
	 *
	 * @param string $base The new base URL.
	 *
	 * @return void
	 */
	public function base( string $base ): void {
		$this->base = $base;
	}

	/**
	 * Add a middleware which affects the requests.
	 *
	 * @param callable(Request): void $middleware The middleware.
	 *
	 * @return void
	 */
	public function add_middleware( callable $middleware ): void {
		$this->middlewares[] = $middleware;
	}

	/**
	 * Get a fully qualified URL.
	 *
	 * @param string $path The path, relative to the client's base.
	 *
	 * @param array  $params Optional query parameters to add.
	 *
	 * @return string
	 */
	public function get_url( string $path, array $params = array() ): string {
		$url = rtrim( $this->base, '/' ) . '/' . ltrim( $path, '/' );

		return add_query_arg( $params, $url );
	}

	/**
	 * Create a new request.
	 *
	 * @param Method $method The HTTP method.
	 *
	 * @param string $path The path, relative to the client's base.
	 *
	 * @param array  $params Parameters to add to the request. Can be URL or body parameters, dictated by the method.
	 *
	 * @return Request
	 */
	public function create_request( Method $method, string $path, array $params = array() ): Request {
		$request = new Request(
			$this->get_url( $path, $method->has_url_params() ? $params : array() ),
			$method,
			$method->has_url_params() ? array() : $params
		);

		foreach ( $this->middlewares as $middleware ) {
			call_user_func( $middleware, $request );
		}

		return $request;
	}

	/**
	 * Dispatch a request.
	 *
	 * @param Request $request The request.
	 *
	 * @param array   $args Optional. Arguments for `wp_remote_request`.
	 *
	 * @return Response
	 */
	public function dispatch( Request $request, array $args = array() ): Response {
		$this->logger->add(
			message: __( 'Started HTTP request.', 'wrd' ),
			data: array(
				'url'    => $request->get_url(),
				'method' => $request->get_method()->value,
			)
		);

		$args['method']  = $request->get_method()->value;
		$args['headers'] = $request->get_headers();

		if ( $request->get_body() && ! $request->get_method()->has_url_params() ) {
			$body                            = $request->get_body();
			$args['body']                    = is_array( $body ) ? wp_json_encode( $body ) : $body;
			$args['headers']['Content-Type'] = 'application/json';
		}

		$response = wp_remote_request( $request->get_url(), $args );

		if ( is_wp_error( $response ) ) {
			$this->logger->add_wp_error( $response, Level::ERROR );
			return new Response( 503, array(), '' );
		}

		$response = new Response(
			wp_remote_retrieve_response_code( $response ),
			(array) wp_remote_retrieve_headers( $response ),
			wp_remote_retrieve_body( $response ),
		);

		$this->logger->add(
			message: __( 'Completed HTTP request.', 'wrd' ),
			data: array(
				'status_code' => $response->get_status_code(),
			)
		);

		return $response;
	}

	/**
	 * Dispatch multiple requests in parallel.
	 *
	 * @param Request[] $requests The requests.
	 *
	 * @param array     $args Optional. Arguments for `wp_remote_request`.
	 *
	 * @return Response[]
	 */
	public function dispatch_in_parallel( array $requests, array $args = array() ): array {
		$raw_requests    = array();
		$request_summary = array();

		foreach ( $requests as $request ) {
			$raw_requests[] = array(
				'url'     => $request->get_url(),
				'type'    => $request->get_method()->value,
				'headers' => $request->get_headers(),
				'data'    => $request->get_body(),
			);

			$request_summary[] = array(
				'url'    => $request->get_url(),
				'method' => $request->get_method()->value,
			);
		}

		$this->logger->add(
			message: __( 'Started parallel HTTP requests.', 'wrd' ),
			data: $request_summary,
		);

		$raw_responses = Requests::request_multiple( $raw_requests, $args );
		$responses     = array();

		$this->logger->add(
			message: __( 'Completed parallel HTTP request.', 'wrd' )
		);

		foreach ( $raw_responses as $response ) {
			if ( is_wp_error( $response ) ) {
				$this->logger->add_wp_error( $response, Level::ERROR );
				$responses[] = new Response( 503, array(), '' );
				continue;
			} elseif ( is_a( $response, Exception::class ) ) {
				$this->logger->add(
					level: Level::ERROR,
					message: __( 'Parallel HTTP request failed.', 'wrd' ),
					data: array(
						'code' => $response->getCode(),
						'type' => $response->getType(),
					)
				);
				$responses[] = new Response( 503, array(), '' );
				continue;
			}

			$responses[] = new Response(
				$response->status_code ? $response->status_code : 200,
				(array) $response->headers,
				$response->body,
			);
		}

		return $responses;
	}

	/**
	 * Make a GET request.
	 *
	 * @param string $path The path, relative to the client's base.
	 *
	 * @param array  $params Parameters to add to the request.
	 *
	 * @param array  $args Optional. Arguments for `wp_remote_request`.
	 *
	 * @return Response
	 */
	public function get( string $path, array $params = array(), array $args = array() ): Response {
		$request = $this->create_request( Method::GET, $path, $params );
		return $this->dispatch( $request, $args );
	}

	/**
	 * Make a POST request.
	 *
	 * @param string $path The path, relative to the client's base.
	 *
	 * @param array  $params Parameters to add to the request.
	 *
	 * @param array  $args Optional. Arguments for `wp_remote_request`.
	 *
	 * @return Response
	 */
	public function post( string $path, array $params = array(), array $args = array() ): Response {
		$request = $this->create_request( Method::POST, $path, $params );
		return $this->dispatch( $request, $args );
	}

	/**
	 * Make a PUT request.
	 *
	 * @param string $path The path, relative to the client's base.
	 *
	 * @param array  $params Parameters to add to the request.
	 *
	 * @param array  $args Optional. Arguments for `wp_remote_request`.
	 *
	 * @return Response
	 */
	public function put( string $path, array $params = array(), array $args = array() ): Response {
		$request = $this->create_request( Method::PUT, $path, $params );
		return $this->dispatch( $request, $args );
	}

	/**
	 * Make a DELETE request.
	 *
	 * @param string $path The path, relative to the client's base.
	 *
	 * @param array  $params Parameters to add to the request.
	 *
	 * @param array  $args Optional. Arguments for `wp_remote_request`.
	 *
	 * @return Response
	 */
	public function delete( string $path, array $params = array(), array $args = array() ): Response {
		$request = $this->create_request( Method::DELETE, $path, $params );
		return $this->dispatch( $request, $args );
	}

	/**
	 * Make a PATCH request.
	 *
	 * @param string $path The path, relative to the client's base.
	 *
	 * @param array  $params Parameters to add to the request.
	 *
	 * @param array  $args Optional. Arguments for `wp_remote_request`.
	 *
	 * @return Response
	 */
	public function patch( string $path, array $params = array(), array $args = array() ): Response {
		$request = $this->create_request( Method::PATCH, $path, $params );
		return $this->dispatch( $request, $args );
	}
}
