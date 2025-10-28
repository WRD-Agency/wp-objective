<?php
/**
 * Contains the Condition class.
 *
 * @package wrd/wp-objective
 */

namespace Wrd\WpObjective\Support;

use Closure;
use Exception;
use Wrd\WpObjective\Support\Facades\Plugin;

/**
 * Used for building up a conditional check.
 */
class Condition {
	/**
	 * The array of values.
	 *
	 * @var bool[] $values
	 */
	private array $values = array();

	/**
	 * Create a new condition.
	 *
	 * @return static
	 */
	public static function if(): static {
		return new static();
	}

	/**
	 * Check if a condition or boolean passes.
	 *
	 * @param Condition|bool $condition The condition to check.
	 *
	 * @param string         $strategy The strategy to check conditions with. Accepts 'all' or 'any'.
	 *
	 * @return bool
	 *
	 * @throws Exception If the strategy is not recognized.
	 */
	public static function check( Condition|bool $condition, string $strategy = 'all' ): bool {
		if ( is_bool( $condition ) ) {
			return $condition;
		}

		switch ( $strategy ) {
			case 'all':
				return $condition->all();
			case 'any':
				return $condition->any();
		}

		throw new Exception( 'Unknown strategy.' );
	}

	/**
	 * Checks if a value is a callable and resolve it to a boolean if it is.
	 *
	 * Return values are crushed into booleans.
	 *
	 * If not, returns exactly what it was given.
	 *
	 * @param mixed $value The value.
	 *
	 * @return mixed
	 */
	private function resolve( mixed $value ): mixed {
		if ( ! is_callable( $value ) ) {
			return $value;
		}

		$return = call_user_func( $value );

		return boolval( $return );
	}

	/**
	 * Check if all conditions are met.
	 *
	 * @return bool
	 */
	public function any(): bool {
		return array_any( $this->values, fn( bool $v ) => $v );
	}

	/**
	 * Check if any of the conditions are met.
	 *
	 * @return bool
	 */
	public function all(): bool {
		return array_all( $this->values, fn( bool $v ) => $v );
	}

	/**
	 * Allows if the given expression resolves to true.
	 *
	 * @param callable|bool $expression The expression to check.
	 *
	 * @return static
	 */
	public function is( callable|bool $expression ): static {
		$this->values[] = $this->resolve( $expression );

		return $this;
	}

	/**
	 * Alias for 'is'.
	 *
	 * @see 'Condition::is'
	 *
	 * @param callable|bool $expression The expression to check.
	 *
	 * @return static
	 */
	public function is_true( callable|bool $expression ): static {
		return $this->is( $expression );
	}

	/**
	 * Allows if the given expression resolves to false.
	 *
	 * @param callable|bool $expression The expression to check.
	 *
	 * @return static
	 */
	public function is_not( callable|bool $expression ): static {
		return $this->is( ! $this->resolve( $expression ) );
	}

	/**
	 * Allows if the given expression is truthy.
	 *
	 * @param mixed $expression The expression to check.
	 *
	 * @return static
	 */
	public function is_truthy( mixed $expression ): static {
		return $this->is( boolval( $this->resolve( $expression ) ) );
	}

	/**
	 * Allows if the given expression is falsey.
	 *
	 * @param mixed $expression The expression to check.
	 *
	 * @return static
	 */
	public function is_falsey( mixed $expression ): static {
		return $this->is( ! boolval( $this->resolve( $expression ) ) );
	}

	/**
	 * Allows if all given items are equal.
	 *
	 * @param mixed ...$items The items to check.
	 *
	 * @return static
	 */
	public function is_equal( mixed ...$items ): static {
		return $this->is( count( array_unique( $items ) ) === 1 );
	}

	/**
	 * Allows if the needle is found in the haystack.
	 *
	 * @param mixed $needle What to search for.
	 *
	 * @param mixed $haystack What to search in. If an array is not provided it will be wrapped in one.
	 *
	 * @param bool  $strict If the third parameter strict is set to true then the in_array function will also check the types of the needle in the haystack. Defaults to true.
	 *
	 * @return static
	 */
	public function in_array( mixed $needle, mixed $haystack, bool $strict = true ): static {
		if ( ! is_array( $haystack ) ) {
			$haystack = array( $haystack );
		}

		return $this->is( in_array( $needle, $haystack, $strict ) ); // phpcs:ignore WordPress.PHP.StrictInArray.MissingTrueStrict -- True by default.
	}

	/**
	 * Check if 'is_admin' passed.
	 *
	 * @see is_admin
	 *
	 * @return static
	 */
	public function is_admin(): static {
		return $this->is( is_admin() );
	}

	/**
	 * Check if 'is_admin' fails.
	 *
	 * @see is_admin
	 *
	 * @return static
	 */
	public function is_public(): static {
		return $this->is( ! is_admin() );
	}

	/**
	 * Check if is in the block editor.
	 *
	 * @return static
	 */
	public function is_block_editor(): static {
		$current_screen = get_current_screen();
		return $this->is( $current_screen->is_block_editor() );
	}

	/**
	 * Check if 'wp_is_serving_rest_request' passed.
	 *
	 * @see wp_is_serving_rest_request
	 *
	 * @return static
	 */
	public function is_rest(): static {
		return $this->is( wp_is_serving_rest_request() );
	}

	/**
	 * Check if 'wp_doing_ajax' passed.
	 *
	 * @see wp_doing_ajax
	 *
	 * @return static
	 */
	public function is_ajax(): static {
		return $this->is( wp_doing_ajax() );
	}

	/**
	 * Check if the current request is for the screen to edit an existing post.
	 *
	 * @return static
	 */
	public function is_post_edit_screen(): static {
		global $pagenow;
		return $this->is_equal( $pagenow, 'post.php' );
	}

	/**
	 * Check if the current request is for the screen to create a new post.
	 *
	 * @return static
	 */
	public function is_post_create_screen(): static {
		global $pagenow;
		return $this->is_equal( $pagenow, 'post-new.php' );
	}

	/**
	 * Check if the current request is for the screen to create or edit a post.
	 *
	 * @return static
	 */
	public function is_post_edit_or_create_screen(): static {
		global $pagenow;
		return $this->in_array( $pagenow, array( 'post-new.php', 'post.php' ) );
	}

	/**
	 * Check if the current post type is given.
	 *
	 * @param string|array $post_type The post type, or types, to check for.
	 *
	 * @return static
	 */
	public function is_post_type( string|array $post_type ): static {
		return $this->in_array( get_post_type(), $post_type );
	}

	/**
	 * Check if the current post status is given.
	 *
	 * @param string|array $post_status The post status, or statii, to check for.
	 *
	 * @return static
	 */
	public function is_post_status( string|array $post_status ): static {
		return $this->in_array( get_post_status(), $post_status );
	}

	/**
	 * Check if the current page is a singular for a post type.
	 *
	 * @see 'is_singular'
	 *
	 * @return static
	 */
	public function is_singular(): static {
		return $this->is( is_singular() );
	}

	/**
	 * Check if the current page is an archive.
	 *
	 * @see 'is_archive'
	 *
	 * @return static
	 */
	public function is_archive(): static {
		return $this->is( is_archive() );
	}

	/**
	 * Check if the current page is a taxonomy archive.
	 *
	 * Includes core taxonomies ('tag' and 'category') in the check.
	 *
	 * @param ?string $tax Taxonomies to check for.
	 *
	 * @see 'is_tax'
	 *
	 * @return static
	 */
	public function is_tax( ?string $tax = null ): static {
		if ( 'category' === $tax ) {
			return $this->is( is_category() );
		} elseif ( 'post_tag' === $tax ) {
			return $this->is( is_tag() );
		} elseif ( ! is_null( $tax ) ) {
			return $this->is( is_tax( $tax ) );
		}

		return $this->is( is_tax() || is_category() || is_tag() );
	}

	/**
	 * Check if the current page is a post type archive.
	 *
	 * This passes for custom post type archives and the blog home (core post archive).
	 *
	 * @see 'is_post_type_archive'
	 * @see 'is_home'
	 *
	 * @param ?string $post_type The post type to check for.
	 *
	 * @return static
	 */
	public function is_post_type_archive( ?string $post_type = null ): static {
		if ( 'post' === $post_type ) {
			return $this->is( is_home() );
		} elseif ( ! is_null( $post_type ) ) {
			return $this->is( is_post_type_archive() );
		}

		return $this->is( is_post_type_archive() || is_home() );
	}

	/**
	 * Check if the current page is the front page.
	 *
	 * If the blog homepage is the front page, this will also pass.
	 *
	 * @see 'is_front_page'
	 *
	 * @return static
	 */
	public function is_front_page(): static {
		return $this->is( is_front_page() );
	}

	/**
	 * Check if the current page is a Special Page.
	 *
	 * @see Special_Page_Post_State
	 *
	 * @param class-string<\Wrd\WpObjective\Admin\Post_States\Special_Page_Post_State> $class_name Class name, extending 'Special_Page_Post_State'.
	 *
	 * @return static
	 */
	public function is_special_page( $class_name ): static {
		return $this->is( Plugin::make( $class_name )->get_post_id() === get_the_ID() );
	}

	/**
	 * Check if the request triggers an action.
	 *
	 * @see Action
	 *
	 * @param class-string<\Wrd\WpObjective\Admin\Action> $class_name Class name, extending 'Action'.
	 *
	 * @return static
	 */
	public function is_action( $class_name ): static {
		return $this->is( Plugin::make( $class_name )->is_requested() );
	}
}
