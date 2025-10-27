<?php
/**
 * Contains the Response class.
 *
 * @package wrd/wp-objective
 */

namespace Wrd\WpObjective\Http;

/**
 * Represents an HTTP response.
 */
class Response {
	/**
	 * The request which triggered this response.
	 *
	 * @var Request
	 */
	private Request $request;

	/**
	 * The response status code.
	 *
	 * @var int
	 */
	private int $status_code;

	/**
	 * The response headers.
	 *
	 * @var array
	 */
	private array $headers;

	/**
	 * The response body.
	 *
	 * @var string
	 */
	private string $body;

	/**
	 * Create a new response from a WP HTTP response array.
	 *
	 * @param int    $status_code The status of the response.
	 *
	 * @param array  $headers Response headers.
	 *
	 * @param string $body Response body as a raw string.
	 */
	public function __construct( int $status_code, array $headers, string $body ) {
		$this->status_code = $status_code;
		$this->headers     = $headers;
		$this->body        = $body;
	}

	/**
	 * Check if the response status code indicates success.
	 *
	 * @return bool
	 */
	public function ok(): bool {
		return $this->status_code >= 200 && $this->status_code < 300;
	}

	/**
	 * Get the response status code.
	 *
	 * @return int
	 */
	public function get_status_code(): int {
		return $this->status_code;
	}

	/**
	 * Get the response headers.
	 *
	 * @return array
	 */
	public function get_headers(): array {
		return $this->headers;
	}

	/**
	 * Get the response body.
	 *
	 * @return string
	 */
	public function get_body(): string {
		return $this->body;
	}

	/**
	 * Get the response body as JSON.
	 *
	 * @return array
	 */
	public function get_body_json(): array {
		return json_decode( $this->body, true );
	}

	/**
	 * Get the initial request.
	 *
	 * @return Request
	 */
	public function get_request(): Request {
		return $this->request;
	}
}
