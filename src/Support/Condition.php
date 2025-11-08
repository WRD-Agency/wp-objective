<?php
/**
 * Contains the Condition class.
 *
 * @package wrd/wp-objective
 */

namespace Wrd\WpObjective\Support;

use Exception;
use WP_Post;
use Wrd\WpObjective\Admin\Action;
use Wrd\WpObjective\Admin\Post_States\Special_Page_Post_State;
use Wrd\WpObjective\Admin\Screen;
use Wrd\WpObjective\Posts\Post;
use Wrd\WpObjective\Posts\Post_Status;
use Wrd\WpObjective\Posts\Post_Type;

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
	 * @param string|Post_Type|(string|Post_Type)[] $post_type The post type, or types, to check for.
	 *
	 * @return static
	 */
	public function is_post_type( string|Post_Type|array $post_type ): static {
		if ( ! is_array( $post_type ) ) {
			$post_type = array( $post_type );
		}

		foreach ( $post_type as $i => $type ) {
			if ( is_a( $type, Post_Type::class, true ) ) {
				if ( is_object( $type ) ) {
					$post_type[ $i ] = $type->get_name();
				} else {
					$post_type[ $i ] = $type::make()->get_name();
				}
			}
		}

		return $this->in_array( get_post_type(), $post_type );
	}

	/**
	 * Check if the current post status is given.
	 *
	 * @param string|class-string<Post_Status>|Post_Status|(string|class-string<Post_Status>|Post_Status)[] $post_status The post status, or statii, to check for.
	 *
	 * @return static
	 */
	public function is_post_status( string|array $post_status ): static {
		if ( ! is_array( $post_status ) ) {
			$post_status = array( $post_status );
		}

		foreach ( $post_status as $i => $status ) {
			if ( is_a( $status, Post_Status::class, true ) ) {
				if ( is_object( $status ) ) {
					$post_status[ $i ] = $status->get_name();
				} else {
					$post_status[ $i ] = $status::make()->get_name();
				}
			}
		}

		return $this->in_array( get_post_status(), $post_status );
	}

	/**
	 * Check if the current page is a singular for a post type.
	 *
	 * @param string|string[] $post_types Optional. Post type or array of post types to check against. Default empty.
	 *
	 * @see 'is_singular'
	 *
	 * @return static
	 */
	public function is_singular( $post_types = '' ): static {
		return $this->is( is_singular( $post_types ) );
	}

	/**
	 * Checks if the request is for a single post.
	 *
	 * @param int|WP_Post $post The post.
	 *
	 * @return static
	 */
	public function is_post( int|WP_Post|Post $post ): static {
		$post = Post::get_post( $post );

		if ( ! $post ) {
			return $this->is( false );
		}

		return $this->is( get_the_ID() === $post->get_id() );
	}

	/**
	 * Check if the current post is a descendent of another post.
	 *
	 * @param int|WP_Post|Post $post The post.
	 *
	 * @return static
	 */
	public function is_child_of( int|WP_Post|Post $post ): static {
		$parent = Post::get_post( $post )?->get_parent();

		if ( ! $parent ) {
			return $this->is( false );
		}

		return $this->is_post( $parent );
	}

	/**
	 * Check if the current post is a descendent of another post.
	 *
	 * @param int|WP_Post $post The post.
	 *
	 * @return static
	 */
	public function is_descendent_of( int|WP_Post $post ): static {
		$post      = get_post( $post );
		$ancestors = get_post_ancestors( $post );

		return $this->in_array( $post->ID, $ancestors, true );
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
	 * @param ?string                   $tax Taxonomies to check for.
	 *
	 * @param int|string|int[]|string[] $term Optional. Term ID, name, slug, or array of such to check against. Default empty.
	 *
	 * @see 'is_tax'
	 *
	 * @return static
	 */
	public function is_tax( ?string $tax = null, int|string|array $term = '' ): static {
		if ( 'category' === $tax ) {
			return $this->is( is_category( $term ) );
		} elseif ( 'post_tag' === $tax ) {
			return $this->is( is_tag( $term ) );
		} elseif ( ! is_null( $tax ) ) {
			return $this->is( is_tax( $tax, $term ) );
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
	 * @param string|string[]|null $post_types The post type to check for.
	 *
	 * @return static
	 */
	public function is_post_type_archive( ?string $post_types = null ): static {
		if ( 'post' === $post_types ) {
			return $this->is( is_home() );
		} elseif ( ! is_null( $post_types ) ) {
			return $this->is( is_post_type_archive( $post_types ) );
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
	 * @param class-string<Special_Page_Post_State>|Special_Page_Post_State $state The post state, extending 'Special_Page_Post_State'.
	 *
	 * @return static
	 */
	public function is_special_page( string|Special_Page_Post_State $state ): static {
		if ( is_string( $state ) ) {
			$state = $state::make();
		}

		return $this->is( $state->has_state() );
	}

	/**
	 * Check if the request triggers an action.
	 *
	 * @see Action
	 *
	 * @param class-string<Action>|Action $action The action, extending 'Action'.
	 *
	 * @return static
	 */
	public function is_action( string|Action $action ): static {
		if ( is_string( $action ) ) {
			$action = $action::make();
		}

		return $this->is( $action->is_requested() );
	}

	/**
	 * Check if the request is for an admin screen.
	 *
	 * @see Screen
	 *
	 * @param class-string<Screen>|Screen $screen The screen class/object, extending 'Screen'.
	 *
	 * @return static
	 */
	public function is_screen( string|Screen $screen ): static {
		if ( is_string( $screen ) ) {
			$screen = $screen::make();
		}

		return $this->is_admin()->is( $screen->is_current_screen() );
	}

	/**
	 * Check if the currently requested URL matches a pattern.
	 *
	 * Accepts '*' for wildcard routes.
	 *
	 * @param string $route The route.
	 *
	 * @return static
	 *
	 * @see 'fnmatch'
	 */
	public function is_route( string $route ): static {
		global $wp;
		$current_route = '/' . trim( $wp->request, '//' ) . '/';

		return $this->is( fnmatch( $route, $current_route ) );
	}
}
