<?php
/**
 * Contains the List_Table class.
 *
 * @package wrd/wp-objective
 */

namespace Wrd\WpObjective\Admin;

use WP_Query;
use Wrd\WpObjective\Foundation\Service_Provider;
use Wrd\WpObjective\Support\Collection;

/**
 * Class for managing admin list tables.
 */
abstract class List_Table extends Service_Provider {
	/**
	 * Get the post type this table affects.
	 *
	 * @var string $post_type
	 */
	public function get_post_type(): string {
		return 'post';
	}

	/**
	 * Add a new column to the table.
	 *
	 * @param string                  $title The title of the column.
	 *
	 * @param callable(WP_Post): void $callback Callback used to render the column.
	 *
	 * @return void
	 */
	public function add_column( string $title, callable $callback ): void {
		$id = sanitize_title( $this->get_post_type() . '_' . $title );

		add_filter(
			"manage_{$this->get_post_type()}_posts_columns",
			function ( array $posts_columns ) use ( $id, $title ) {
				$posts_columns[ $id ] = $title;
				return $posts_columns;
			}
		);

		add_action(
			"manage_{$this->get_post_type()}_posts_custom_column",
			function ( string $column, int $post_id ) use ( $id, $callback ) {
				if ( $column !== $id ) {
					return;
				}

				call_user_func( $callback, get_post( $post_id ) );
			},
			10,
			2
		);
	}

	/**
	 * Remove a column from the table.
	 *
	 * @param string $id The ID of the column to remove.
	 *
	 * @return void
	 */
	public function remove_column( string $id ): void {
		add_filter(
			"manage_{$this->get_post_type()}_posts_columns",
			function ( array $posts_columns ) use ( $id ) {
				if ( array_key_exists( $id, $posts_columns ) ) {
					unset( $posts_columns[ $id ] );
				}

				return $posts_columns;
			}
		);
	}

	/**
	 * Add a filtering option to the table.
	 *
	 * @param string                             $name The name of the filter.
	 *
	 * @param array                              $values Dropdown options.
	 *
	 * @param callable(Collection, string): void $callback Callback to apply the filter.
	 */
	public function add_filter( string $name, array $values, callable $callback ): void {
		add_action(
			'restrict_manage_posts',
			function () use ( $name, $values ) {
				$screen = get_current_screen();

				if ( 'edit-' . $this->get_post_type() !== $screen->id ) {
					return;
				}

				$selected = isset( $_GET[ $name ] ) ? sanitize_text_field( $_GET[ $name ] ) : '';

				printf( '<select name="%s">', esc_attr( $name ) );

				foreach ( $values as $label => $value ) {
					printf( '<option value="%s" %s>%s</option>', esc_attr( $value ), selected( $selected, $value, false ), esc_html( $label ) );
				}

				echo '</select>';
			}
		);

		add_filter(
			'posts_results',
			function ( array $posts, WP_Query $query ) use( $name, $values, $callback ) {
				$screen = get_current_screen();

				if ( 'edit-' . $this->get_post_type() !== $screen->id || ! $query->is_main_query() ) {
					return $posts;
				}

				$value = isset( $_GET[ $name ] ) ? sanitize_text_field( $_GET[ $name ] ) : '';

				if ( ! in_array( $value, $values, true ) ) {
					return $posts;
				}

				$posts = call_user_func( $callback, Collection::from( $posts ), $value );

				return $posts->all();
			},
			10,
			2
		);
	}
}
