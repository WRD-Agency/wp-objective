<?php
/**
 * Contains the Html class.
 *
 * @package wrd/wp-objective
 */

namespace Wrd\WpObjective\Support;

/**
 * Builder for creating HTML markup.
 */
class Html {
	/**
	 * The HTML output.
	 *
	 * @var string
	 */
	private string $out;

	/**
	 * Create a HTML instance.
	 *
	 * @param string $markup The HTML markup to start with. Defaults to empty string.
	 */
	public function __construct( string $markup = '' ) {
		$this->out = $markup;
	}

	/**
	 * Static method for creating an instance.
	 *
	 * @param string $markup The HTML markup to start with. Defaults to empty string.
	 *
	 * @return static
	 */
	public static function of( string $markup = '' ): static {
		return new static( $markup );
	}

	/**
	 * Get the output.
	 *
	 * @return string
	 */
	public function get(): string {
		return $this->out;
	}

	/**
	 * Display the output.
	 *
	 * @return void
	 */
	public function echo(): void {
		echo $this->out; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Other functions responsible for cleaning.
	}

	/**
	 * Merges multiple attribute arrays into a single array.
	 *
	 * @param string[][] ...$arrays The attribute arrays.
	 *
	 * @return array
	 */
	private function merge_attrs( array ...$arrays ): array {
		$merged_attributes = array(
			'class' => ' ',
			'style' => ';',
		);

		$merged = array_merge( ...$arrays );

		foreach ( $merged_attributes as $key => $separator ) {
			$merged[ $key ] = array();

			foreach ( $arrays as $attrs ) {
				if ( array_key_exists( $key, $attrs ) ) {
					$merged[ $key ][] = $attrs[ $key ];
				}
			}

			$merged[ $key ] = join( $separator, $merged[ $key ] );
		}

		return $merged;
	}

	/**
	 * Flatten attributes into a string.
	 *
	 * @param string[]|string[][] $attrs The attributes.
	 *
	 * @return string
	 */
	private function flatten_attrs( array $attrs ): string {
		$flattened = array();

		if ( array_is_list( $attrs ) ) {
			$attrs = $this->merge_attrs( $attrs );
		}

		foreach ( $attrs as $key => $value ) {
			$flattened[] = esc_html( $key ) . '="' . esc_attr( $value ) . '"';
		}

		return join( ' ', $flattened );
	}

	/**
	 * Append some raw HTML content.
	 *
	 * @param (callable():string)|string $content The content to add.
	 *
	 * @return static
	 */
	public function raw( callable|string $content = '' ): static {
		if ( is_callable( $content ) ) {
			$content = call_user_func( $content );
		}

		$this->out .= $content;

		return $this;
	}

	/**
	 * Open a tag.
	 *
	 * @param string              $tag The tag name.
	 *
	 * @param string[]|string[][] $attrs Array of attributes.
	 *
	 * @return void
	 */
	private function open( string $tag, array $attrs ): void {

		$this->raw( '<' . esc_html( $tag ) );
		$this->raw( $this->flatten_attrs( $attrs ) );
		$this->raw( '>' );
	}

	/**
	 * Close a tag.
	 *
	 * @param string $tag The tag name.
	 *
	 * @return void
	 */
	private function close( string $tag ): void {
		$this->raw( '</' . esc_html( $tag ) . '>' );
	}

	/**
	 * Append a tag.
	 *
	 * @param string              $name The tag name.
	 *
	 * @param string[]|string[][] $attrs Array of attributes.
	 *
	 * @param string              $content The text content.
	 *
	 * @return static
	 */
	public function tag( string $name, array $attrs = array(), string $content = '' ): static {
		$this->open( $name, $attrs );

		echo esc_html( $content );

		$this->close( $name );

		return $this;
	}

	/**
	 * Add a heading tag.
	 *
	 * @param string $text The text content.
	 *
	 * @param int    $level The heading level. Defaults to 2.
	 *
	 * @param array  $attrs Additional attributes to add.
	 *
	 * @return static
	 */
	public function heading( string $text, int $level = 2, array $attrs = array() ): static {
		$default_attrs = array(
			'class' => 'wrd_heading wrd_heading--' . $level,
		);

		return $this->tag( 'h' . $level, array( $default_attrs, $attrs ), $text );
	}

	/**
	 * Add a h1 tag.
	 *
	 * @param string $text The text content.
	 *
	 * @param array  $attrs Additional attributes to add.
	 *
	 * @return static
	 */
	public function h1( string $text, array $attrs = array() ):static {
		return $this->heading( $text, 1, $attrs );
	}

	/**
	 * Add a h2 tag.
	 *
	 * @param string $text The text content.
	 *
	 * @param array  $attrs Additional attributes to add.
	 *
	 * @return static
	 */
	public function h2( string $text, array $attrs = array() ):static {
		return $this->heading( $text, 2, $attrs );
	}

	/**
	 * Add a h3 tag.
	 *
	 * @param string $text The text content.
	 *
	 * @param array  $attrs Additional attributes to add.
	 *
	 * @return static
	 */
	public function h3( string $text, array $attrs = array() ):static {
		return $this->heading( $text, 3, $attrs );
	}

	/**
	 * Add a h4 tag.
	 *
	 * @param string $text The text content.
	 *
	 * @param array  $attrs Additional attributes to add.
	 *
	 * @return static
	 */
	public function h4( string $text, array $attrs = array() ):static {
		return $this->heading( $text, 4, $attrs );
	}

	/**
	 * Add a h5 tag.
	 *
	 * @param string $text The text content.
	 *
	 * @param array  $attrs Additional attributes to add.
	 *
	 * @return static
	 */
	public function h5( string $text, array $attrs = array() ):static {
		return $this->heading( $text, 5, $attrs );
	}

	/**
	 * Add a h6 tag.
	 *
	 * @param string $text The text content.
	 *
	 * @param array  $attrs Additional attributes to add.
	 *
	 * @return static
	 */
	public function h6( string $text, array $attrs = array() ):static {
		return $this->heading( $text, 6, $attrs );
	}

	/**
	 * Add an anchor tag.
	 *
	 * @param string       $text The text content.
	 *
	 * @param string|array $url The link URL or additional attributes to add.
	 *
	 * @return static
	 */
	public function a( string $text, string|array $url = array() ): static {
		$default_attrs = array(
			'class' => 'wrd_link',
		);

		$attrs = is_array( $url ) ? $url : array( 'href' => $url );

		return $this->tag( 'a', array( $default_attrs, $attrs ), $text );
	}

	/**
	 * Add an anchor tag.
	 *
	 * Alias of Html::a.
	 *
	 * @see Html::a
	 *
	 * @param string       $text The text content.
	 *
	 * @param string|array $url The link URL or additional attributes to add.
	 *
	 * @return static
	 */
	public function link( string $text, string|array $url = array() ): static {
		return $this->a( $text, $url );
	}

	/**
	 * Add an button tag.
	 *
	 * @param string       $text The text content.
	 *
	 * @param string|array $type The button type or additional attributes to add.
	 *
	 * @return static
	 */
	public function button( string $text, string|array $type = array() ): static {
		$default_attrs = array(
			'class' => 'wrd_link',
		);

		$attrs = is_array( $type ) ? $type : array( 'type' => $type );

		return $this->tag( 'button', array( $default_attrs, $attrs ), $text );
	}
}
