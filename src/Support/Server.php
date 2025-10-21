<?php
/**
 * Contains the Server class.
 *
 * @package wrd/wp-objective
 */

namespace Wrd\WpObjective\Support;

/**
 * Static utility for accessing server variables.
 */
class Server {
	/**
	 * Get the IP address of the current user.
	 *
	 * @return string
	 */
	public static function get_ip(): string {
		$ip_keys = array(
			'HTTP_CLIENT_IP',
			'HTTP_X_FORWARDED_FOR',
			'HTTP_X_FORWARDED',
			'HTTP_X_CLUSTER_CLIENT_IP',
			'HTTP_FORWARDED_FOR',
			'HTTP_FORWARDED',
			'REMOTE_ADDR',
		);

		foreach ( $ip_keys as $key ) {
			if ( array_key_exists( $key, $_SERVER ) === true ) {
				foreach ( explode( ',', sanitize_text_field( wp_unslash( $_SERVER[ $key ] ) ) ) as $ip ) {
					$ip = trim( $ip );

					if ( filter_var( $ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE ) !== false ) {
						return $ip;
					}
				}
			}
		}

		return 'UNKNOWN';
	}

	/**
	 * Get the user agent of the current user.
	 *
	 * @return string
	 */
	public static function get_user_agent(): string {
		if ( ! isset( $_SERVER['HTTP_USER_AGENT'] ) ) {
			return 'UNKNOWN';
		}

		return sanitize_text_field( wp_unslash( $_SERVER['HTTP_USER_AGENT'] ) );
	}

	/**
	 * Get the request URL.
	 *
	 * @return string
	 */
	public static function get_request_url(): string {
		if ( ! isset( $_SERVER['REQUEST_URI'] ) ) {
			return 'UNKNOWN';
		}

		return home_url( sanitize_text_field( wp_unslash( $_SERVER['REQUEST_URI'] ) ) );
	}
}
