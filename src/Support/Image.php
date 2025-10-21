<?php
/**
 * Contains the Image class.
 *
 * @package wrd/wp-objective
 */

namespace Wrd\WpObjective\Support;

use Wrd\WpObjective\Contracts\Apiable;

/**
 * Represents an image.
 */
class Image implements Apiable {
	/**
	 * The ID of the image in WordPress.
	 *
	 * @var int
	 */
	private int $id;

	/**
	 * Create a new image instance.
	 *
	 * @param int $id The ID of the image in WordPress.
	 */
	public function __construct( int $id ) {
		$this->id = $id;
	}

	/**
	 * Get the ID.
	 *
	 * @return int
	 */
	public function get_id(): int {
		return $this->id;
	}

	/**
	 * Get a URL for the image.
	 *
	 * @param string $size The size of the image to retrieve. Defaults to 'full'.
	 *
	 * @return string
	 */
	public function get_url( string $size = 'full' ): string {
		return wp_get_attachment_image_url( $this->id, $size );
	}

	/**
	 * Display a URL for the image.
	 *
	 * @param string $size The size of the image to retrieve. Defaults to 'full'.
	 *
	 * @return void
	 */
	public function the_url( string $size = 'full' ): void {
		echo esc_url( $this->get_url( $size ) );
	}

	/**
	 * Get a URL for the image.
	 *
	 * @param string $size The size of the image to retrieve. Defaults to 'full'.
	 *
	 * @param array  $attrs Attributes to add to the image. Defaults to empty array.
	 *
	 * @return string
	 */
	public function get_html( string $size = 'full', array $attrs = array() ): string {
		return wp_get_attachment_image( $this->get_id(), $size, false, $attrs );
	}

	/**
	 * Display HTML for the image.
	 *
	 * @param string $size The size of the image to retrieve. Defaults to 'full'.
	 *
	 * @param array  $attrs Attributes to add to the image. Defaults to empty array.
	 *
	 * @return void
	 */
	public function the_html( string $size = 'full', array $attrs = array() ): void {
		echo wp_kses_post( $this->get_html( $size, $attrs ) );
	}

	/**
	 * Get the alt tag describing the image.
	 *
	 * @return string
	 */
	public function get_alt(): string {
		return get_post_meta( $this->id, '_wp_attachment_image_alt', true );
	}

	/**
	 * Get an array of the image's sizes.
	 *
	 * @return array<string, array<string, int|string|null>>
	 */
	public function get_sizes(): array {
		$sizes            = array();
		$registered_sizes = wp_get_registered_image_subsizes();

		foreach ( $registered_sizes as $size_name => $size_data ) {
			$sizes[ $size_name ] = array(
				'url'    => $this->get_url( $size_name ),
				'width'  => $size_data['width'],
				'height' => $size_data['height'],
				'crop'   => $size_data['crop'],
			);
		}

		return $sizes;
	}

	/**
	 * Convert the address to an API-compatible array.
	 *
	 * @return array<string, string|null>
	 */
	public function to_api(): array {
		return array(
			'id'    => $this->get_id(),
			'alt'   => $this->get_alt(),
			'sizes' => $this->get_sizes(),
		);
	}
}
