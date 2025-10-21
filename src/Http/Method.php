<?php
/**
 * Contains the Method enum.
 *
 * @package wrd/wp-objective
 */

namespace Wrd\WpObjective\Http;

enum Method: string {
	case GET    = 'GET';
	case POST   = 'POST';
	case PUT    = 'PUT';
	case DELETE = 'DELETE';
	case PATCH  = 'PATCH';

	/**
	 * Get all editable methods.
	 *
	 * @return Method[]
	 */
	public const READABLE = array( self::GET );

	/**
	 * Get all creatable methods.
	 *
	 * @return Method[]
	 */
	public const CREATABLE = array( self::POST );

	/**
	 * Get all editable methods.
	 *
	 * @return Method[]
	 */
	public const EDITABLE = array( self::POST, self::PUT, self::PATCH );

	/**
	 * Get all deletable methods.
	 *
	 * @return Method[]
	 */
	public const DELETABLE = array( self::DELETE );

	/**
	 * Check if the method has URL params.
	 *
	 * @return bool
	 */
	public function has_url_params(): bool {
		return match ( $this ) {
			self::GET => false,
			self::POST => false,
			self::PUT => false,
			self::DELETE => false,
			self::PATCH => false,
		};
	}

	/**
	 * Check if the method is readable.
	 *
	 * @return bool
	 */
	public function is_readable(): bool {
		return in_array( $this, self::READABLE, true );
	}



	/**
	 * Check if the method is creatable.
	 *
	 * @return bool
	 */
	public function is_creatable(): bool {
		return in_array( $this, self::CREATABLE, true );
	}



	/**
	 * Check if the method is editable.
	 *
	 * @return bool
	 */
	public function is_editable(): bool {
		return in_array( $this, self::EDITABLE, true );
	}



	/**
	 * Check if the method is deletable.
	 *
	 * @return bool
	 */
	public function is_deletable(): bool {
		return in_array( $this, self::DELETABLE, true );
	}
}
