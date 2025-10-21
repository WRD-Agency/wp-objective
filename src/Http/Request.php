<?php
/**
 * Contains the Request class.
 *
 * @package wrd/wp-objective
 */

namespace Wrd\WpObjective\Http;

/**
 * Represents an HTTP request.
 */
class Request {
	/**
	 * The fully qualified URL.
	 *
	 * @var string
	 */
	private string $url;

	/**
	 * The HTTP method of the request.
	 *
	 * @var Method
	 */
	private Method $method;

	/**
	 * The request headers.
	 *
	 * @var array
	 */
	private array $headers;

	/**
	 * The request body.
	 *
	 * @var array
	 */
	private array $body;

	/**
	 * Create a new request.
	 *
	 * @param string $url The fully qualified URL.
	 *
	 * @param Method $method The HTTP method.
	 *
	 * @param array  $body The request body.
	 *
	 * @param array  $headers The request headers.
	 */
	public function __construct( string $url, Method $method, array $body = array(), array $headers = array() ) {
		$this->url     = $url;
		$this->method  = $method;
		$this->body    = $body;
		$this->headers = $headers;
	}

	/**
	 * Get the URL.
	 *
	 * @return string
	 */
	public function get_url(): string {
		return $this->url;
	}

	/**
	 * Set the fully qualified URL.
	 *
	 * @param string $url The new URL.
	 *
	 * @return void
	 */
	public function set_url( string $url ): void {
		$this->url = $url;
	}

	/**
	 * Get the HTTP method.
	 *
	 * @return Method
	 */
	public function get_method(): Method {
		return $this->method;
	}

	/**
	 * Set the HTTP method.
	 *
	 * @param Method $method The new method.
	 *
	 * @return void
	 */
	public function set_method( Method $method ): void {
		$this->method = $method;
	}

	/**
	 * Get the headers.
	 *
	 * @return array
	 */
	public function get_headers(): array {
		return $this->headers;
	}

	/**
	 * Get a header value.
	 *
	 * @param string $key The header key.
	 *
	 * @return string|null The header value, or null if not set.
	 */
	public function get_header( string $key ): ?string {
		return $this->headers[ $key ] ?? null;
	}

	/**
	 * Set a header value.
	 *
	 * @param string $key The header key.
	 *
	 * @param string $value The header value.
	 *
	 * @return void
	 */
	public function set_header( string $key, string $value ): void {
		$this->headers[ $key ] = $value;
	}

	/**
	 * Add multiple headers.
	 *
	 * @param array $headers The headers to add.
	 *
	 * @return void
	 */
	public function add_headers( array $headers ): void {
		$this->headers = array_merge( $this->headers, $headers );
	}

	/**
	 * Get the body.
	 *
	 * @param string|null $key Optional key to get a specific value.
	 *
	 * @return array|string|null
	 */
	public function get_body( ?string $key = null ): array {
		if ( ! is_null( $key ) ) {
			return $this->body[ $key ] ?? null;
		}

		return $this->body;
	}

	/**
	 * Set the body.
	 *
	 * @param string|null $key Optional key to set a specific value.
	 *
	 * @param mixed       $value The value to set.
	 *
	 * @return array|string|null
	 */
	public function set_body( ?string $key, $value ): array {
		if ( ! is_null( $key ) ) {
			$this->body[ $key ] = $value;
		}

		return $this->body;
	}
}
