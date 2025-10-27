<?php
/**
 * Contains the Html class.
 *
 * @package wrd/wp-objective
 */

namespace Wrd\WpObjective\Support;

use Stringable;

/**
 * Builder for creating HTML markup.
 */
class Html implements Stringable {
	/**
	 * The HTML output.
	 *
	 * @var string
	 */
	private string $out;

	/**
	 * Create a HTML instance.
	 *
	 * @param string|Stringable $markup The HTML markup to start with. Defaults to empty string.
	 */
	public function __construct( string|Stringable $markup = '' ) {
		$this->out = $markup;
	}

	/**
	 * Convert to a string.
	 *
	 * @return string
	 */
	public function __toString(): string {
		return $this->out;
	}

	/**
	 * Static method for creating an instance.
	 *
	 * @param string|Stringable $markup The HTML markup to start with. Defaults to empty string.
	 *
	 * @return static
	 */
	public static function of( string|Stringable $markup = '' ): static {
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
			$attrs = $this->merge_attrs( ...$attrs );
		}

		foreach ( $attrs as $key => $value ) {
			if ( is_bool( $value ) ) {
				if ( $value ) {
					$flattened[] = esc_html( $key );
				}

				continue;
			}

			$flattened[] = esc_html( $key ) . '="' . esc_attr( $value ) . '"';
		}

		return join( ' ', $flattened );
	}

	/**
	 * Append some raw HTML content.
	 *
	 * @param (callable():string)|string|Stringable $content The content to add.
	 *
	 * @return static
	 */
	public function raw( callable|string|Stringable $content = '' ): static {
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

		$this->raw( '<' . esc_html( $tag ) . ' ' );
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
	 * @param string|Stringable   $content The text content.
	 *
	 * @return static
	 */
	public function tag( string $name, array $attrs = array(), string|Stringable|null $content = null ): static {
		$this->open( $name, $attrs );

		if ( $content ) {
			$this->out .= (string) $content;
		}

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
	public function h1( string $text, array $attrs = array() ): static {
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
	public function h2( string $text, array $attrs = array() ): static {
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
	public function h3( string $text, array $attrs = array() ): static {
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
	public function h4( string $text, array $attrs = array() ): static {
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
	public function h5( string $text, array $attrs = array() ): static {
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
	public function h6( string $text, array $attrs = array() ): static {
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
			'class' => 'wrd_btn',
		);

		$attrs = is_array( $type ) ? $type : array( 'type' => $type );

		return $this->tag( 'button', array( $default_attrs, $attrs ), $text );
	}

	/**
	 * Add a table.
	 *
	 * @param array $rows The rows of data. Column headings are taken from the keys of the first row.
	 *
	 * @return static
	 */
	public function table( array $rows ): static {
		if ( ! $rows ) {
			return $this;
		}

		$columns = array_keys( $rows[0] );

		$this->open( 'table', array( 'class' => 'wrd_table' ) );

		$this->open( 'thead', array( 'class' => 'wrd_table__head' ) );
		$this->open( 'tr', array( 'class' => 'wrd_table__row' ) );

		foreach ( $columns as $col ) {
			$this->tag( 'th', array( 'class' => 'wrd_table__header' ), $col );
		}

		$this->close( 'tr' );
		$this->close( 'thead' );

		$this->open( 'tbody', array( 'class' => 'wrd_table__body' ) );
		foreach ( $rows as $row ) {
			$this->open( 'tr', array( 'class' => 'wrd_table__row' ) );

			foreach ( $row as $col ) {
				$this->tag( 'td', array( 'class' => 'wrd_table__cell' ), $col );
			}

			$this->close( 'tr' );
		}
		$this->close( 'tbody' );

		$this->close( 'table' );

		return $this;
	}

	/**
	 * Add a details table.
	 *
	 * @param array $data The items of data. Column headings are taken from the keys array.
	 *
	 * @return static
	 */
	public function details( array $data ): static {
		if ( ! $data ) {
			return $this;
		}

		$this->open( 'table', array( 'class' => 'wrd_details' ) );

		$this->open( 'tbody', array( 'class' => 'wrd_details__body' ) );

		foreach ( $data as $heading => $cell ) {
			$this->open( 'tr', array( 'class' => 'wrd_details__row' ) );

			$this->tag( 'td', array( 'class' => 'wrd_details__heading' ), $heading );
			$this->tag( 'td', array( 'class' => 'wrd_details__cell' ), $cell );

			$this->close( 'tr' );
		}

		$this->close( 'tbody' );

		$this->close( 'table' );

		return $this;
	}

	/**
	 * Display a field.
	 *
	 * @param string                        $label The field label.
	 *
	 * @param array                         $attrs Input element's attributes.
	 *
	 * @param null|(callable(static): void) $contents Function to render the contents, if a default HTML input is not used.
	 */
	public function field( string $label, array $attrs = array(), ?callable $contents = null ): static {
		$attrs = wp_parse_args(
			$attrs,
			array(
				'id'   => uniqid( 'field__' ),
				'type' => 'text',
				'name' => '',
			)
		);

		$default_types = array(
			'button',
			'checkbox',
			'color',
			'date',
			'datetime-local',
			'email',
			'file',
			'hidden',
			'image',
			'month',
			'number',
			'password',
			'radio',
			'range',
			'reset',
			'search',
			'submit',
			'tel',
			'text',
			'time',
			'url',
			'week',
		);

		$id   = $attrs['id'];
		$name = $attrs['name'];
		$type = $attrs['type'];

		$this->open( 'div', array( 'class' => "wrd_field wrd_field--$name wrd_field--$type" ) );

		if ( $label ) {
			$this->tag(
				'label',
				array(
					'class' => 'wrd_field__label',
					'for'   => $id,
				)
			);
		}

		if ( in_array( $type, $default_types, true ) ) {
			$this->tag( 'input', $attrs );
		} elseif ( is_callable( $contents ) ) {
			$this->raw( $contents );
		}

		$this->close( 'div' );

		return $this;
	}

	/**
	 * Add a select field.
	 *
	 * @param string   $label The label.
	 *
	 * @param string[] $options Possible values.
	 *
	 * @param array    $attrs HTML attributes for the select.
	 *
	 * @return static
	 */
	public function select( string $label, array $options, array $attrs = array() ): static {
		$attrs['type'] = 'select';

		$this->field(
			$label,
			$attrs,
			function () use ( $attrs, $options ) {
				$selected_value = null;

				if ( array_key_exists( 'value', $attrs ) ) {
					$selected_value = $attrs['value'];
					unset( $attrs['value'] );
				}
				unset( $attrs['type'] );

				$html = Html::of();
				$html->open( 'select', $attrs );

				foreach ( $options as $value => $label ) {
					if ( wp_is_numeric_array( $options ) ) {
						$value = sanitize_title( $label );
					}

					$html->tag(
						'option',
						array(
							'value'    => $value,
							'selected' => $value === $selected_value,
						),
						$label
					);
				}

				return $html;
			}
		);

		return $this;
	}

	/**
	 * Add a text area field.
	 *
	 * @param string $label The label.
	 *
	 * @param array  $attrs HTML attributes for the text area.
	 *
	 * @return static
	 */
	public function textarea( string $label, array $attrs = array() ): static {
		$attrs['type'] = 'textarea';

		$this->field(
			$label,
			$attrs,
			function () use ( $attrs ) {
				unset( $attrs['type'] );
				return Html::of()->tag( 'textarea', $attrs );
			}
		);

		return $this;
	}
}
