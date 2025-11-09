<?php
/**
 * Contains the Post class.
 *
 * @package wrd/wp-objective
 */

namespace Wrd\WpObjective\Posts;

use Exception;
use WP_Post;
use Wrd\WpObjective\Contracts\Apiable;
use Wrd\WpObjective\Posts\Core\Core_Post_Status;
use Wrd\WpObjective\Support\Collection;
use Wrd\WpObjective\Support\Facades\Log;
use Wrd\WpObjective\Support\Image;

/**
 * For building up a post.
 */
class Post implements Apiable {
	/**
	 * The post ID.
	 *
	 * @var int
	 */
	public int $id;

	/**
	 * Create an instance of the post.
	 *
	 * @param int|WP_Post|null $post The post.
	 *
	 * @throws Exception When the wrong post type is given.
	 */
	public function __construct( int|WP_Post|null $post = null ) {
		$post = get_post( $post );

		if ( static::class !== self::class ) {
			// This is an inheriting class.

			if ( ! $post || $this->get_post_type() !== $post->post_type ) {
				throw new Exception( 'Invalid post type.' );
			}
		}

		if ( ! $post ) {
			throw new Exception( 'Invalid post.' );
		}

		$this->id = $post->ID;
	}

	/**
	 * Get the class for this post type.
	 *
	 * @return class-string<Post_Type>|null
	 */
	public static function get_post_type_class(): ?string {
		return Post_Type::class;
	}

	/**
	 * Get this item's post type object.
	 *
	 * @return Post_Type
	 */
	public function get_post_type_object(): Post_Type {
		return new ( static::get_post_type_class() );
	}

	/**
	 * Get this item's post type.
	 *
	 * @return string
	 */
	public function get_post_type(): string {
		return $this->get_post_type_object()->get_name();
	}

	/**
	 * Get the post object.
	 *
	 * @return WP_Post
	 */
	public function get_wp_post(): WP_Post {
		return get_post( $this->id );
	}

	/**
	 * Get the post statii available for this post type.
	 *
	 * @return Collection<Post_Status>
	 */
	public static function get_available_stati(): Collection {
		return Core_Post_Status::all_not_internal();
	}

	/**
	 * Get the default post statii available for this post type.
	 *
	 * @return Post_Status
	 */
	public static function get_default_status(): Post_Status {
		return new Core_Post_Status( 'draft' );
	}

	/**
	 * Get the status of this post.
	 *
	 * @return Post_Status
	 */
	public function get_status(): Post_Status {
		$raw_status = get_post_status( $this->id );

		return static::get_available_stati()
			->find( fn( Post_Status $status ) => $status->get_name() === $raw_status );
	}

	/**
	 * Set the status of this post.
	 *
	 * @param string|Post_Status $status The new status.
	 *
	 * @return static
	 */
	public function set_status( string|Post_Status $status ): static {
		do_action( 'objective/post/before_set_status', $this, $status );

		$this->update(
			array(
				'post_status' => is_string( $status ) ? $status : $status->get_name(),
			)
		);

		do_action( 'objective/post/after_set_status', $this, $status );

		Log::add(
			message: __( 'Status updated', 'wrd' ),
			target: $this->id,
			data: array(
				'new_status' => is_string( $status ) ? $status : $status->get_name(),
			)
		);

		return $this;
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
	 * Get the title.
	 *
	 * @return string
	 */
	public function get_title(): string {
		return get_the_title( $this->id );
	}

	/**
	 * Get the excerpt.
	 *
	 * @return string
	 */
	public function get_excerpt(): string {
		return get_the_excerpt( $this->id );
	}

	/**
	 * Get the content.
	 *
	 * @return string
	 */
	public function get_content(): string {
		return get_the_content( $this->id );
	}

	/**
	 * Get the parent of this post.
	 *
	 * @return ?Post
	 */
	public function get_parent(): ?Post {
		$post = get_post_parent( $this->id );

		if ( ! $post ) {
			return null;
		}

		return new static( $post );
	}

	/**
	 * Get the ancestors of this post.
	 *
	 * @return Collection<Post>
	 */
	public function get_ancestors(): Collection {
		$ancestor_ids = get_ancestors( $this->id );
		$collection   = new Collection();

		foreach ( $ancestor_ids as $id ) {
			$collection->add( new static( $id ) );
		}

		return $collection;
	}

	/**
	 * Get a field on the post.
	 *
	 * @param string $key The field key.
	 *
	 * @throws Exception When an unknown field type is given.
	 *
	 * @return mixed The field value.
	 */
	public function get_field( string $key ): mixed {
		list( $type, $field ) = explode( '/', $key, 2 );

		switch ( $type ) {
			case 'post':
				return $this->get_post_field( $field );
			case 'meta':
				return $this->get_meta_field( $field );
			case 'acf':
				return $this->get_acf_field( $field );
			default:
				throw new Exception( 'Unknown field type: ' . esc_html( $type ) );
		}
	}

	/**
	 * Update a field on the post.
	 *
	 * @param string $key The field key.
	 *
	 * @param mixed  $value The value to set.
	 *
	 * @throws Exception When an unknown field type is given.
	 *
	 * @return mixed The previous value.
	 */
	public function update_field( string $key, mixed $value ): mixed {
		list( $type, $field ) = explode( '/', $key, 2 );
		$previous             = null;

		do_action( 'objective/post/before_update_field', $this, $key, $value );

		switch ( $type ) {
			case 'post':
				$previous = $this->update_post_field( $field, $value );
				break;
			case 'meta':
				$previous = $this->update_meta_field( $field, $value );
				break;
			case 'acf':
				$previous = $this->update_acf_field( $field, $value );
				break;
			default:
				throw new Exception( 'Unknown field type: ' . esc_html( $type ) );
		}

		do_action( 'objective/post/after_update_field', $this, $key, $value, $previous );

		Log::add(
			message: __( 'Field updated', 'wrd' ),
			target: $this->id,
			data: array(
				'previous_value' => $previous,
				'new_value'      => $value,
				'field_key'      => $key,
			)
		);

		return $previous;
	}

	/**
	 * Get a post field value.
	 *
	 * @param string $field The field key.
	 *
	 * @return mixed
	 */
	public function get_post_field( string $field ): mixed {
		return get_post_field( $field, $this->id );
	}

	/**
	 * Update a post field.
	 *
	 * @param string $field The field to update.
	 *
	 * @param mixed  $value The value to set.
	 *
	 * @return mixed The previous value.
	 */
	public function update_post_field( string $field, mixed $value ): mixed {
		$previous_value = $this->get_post_field( $field );

		wp_update_post(
			array(
				'ID'   => $this->id,
				$field => $value,
			)
		);

		return $previous_value;
	}

	/**
	 * Get a post meta value.
	 *
	 * @param string $key The meta key.
	 *
	 * @return mixed
	 */
	public function get_meta_field( string $key ): mixed {
		return get_post_meta( $this->id, $key, true );
	}

	/**
	 * Update a meta field.
	 *
	 * @param string $field The field to update.
	 *
	 * @param mixed  $value The value to set.
	 *
	 * @return mixed The previous value.
	 */
	public function update_meta_field( string $field, mixed $value ): mixed {
		$previous_value = $this->get_meta_field( $field );

		update_post_meta( $this->id, $field, $value );

		return $previous_value;
	}

	/**
	 * Get a ACF field value.
	 *
	 * @param string $key The ACF key.
	 *
	 * @return mixed
	 */
	public function get_acf_field( string $key ): mixed {
		if ( ! function_exists( 'get_field' ) ) {
			return $this->get_meta_field( $key );
		}

		/**
		 * Provided by ACF.
		 *
		 * @disregard P1010
		 */
		return get_field( $key, $this->id );
	}

	/**
	 * Update a ACF field.
	 *
	 * @param string $field The field to update.
	 *
	 * @param mixed  $value The value to set.
	 *
	 * @return mixed The previous value.
	 */
	public function update_acf_field( string $field, mixed $value ): mixed {
		if ( ! function_exists( 'update_field' ) ) {
			return $this->update_meta_field( $field, $value );
		}

		$previous_value = $this->get_acf_field( $field );

		/**
		 * Provided by ACF.
		 *
		 * @disregard P1010
		 */
		update_field( $field, $value, $this->id );

		return $previous_value;
	}

	/**
	 * Get the post slug.
	 *
	 * @return string
	 */
	public function get_slug(): string {
		return $this->get_wp_post()->post_name;
	}

	/**
	 * Get the post route.
	 *
	 * This is the slug, prefixed with any parent slugs.
	 *
	 * @return string
	 */
	public function get_route(): string {
		return str_replace( home_url(), '', $this->get_permalink() );
	}

	/**
	 * Get the permalink.
	 *
	 * @return string
	 */
	public function get_permalink(): string {
		return get_the_permalink( $this->id );
	}

	/**
	 * Get the edit_link.
	 *
	 * @param string $context Optional. How to output the '&' character. Default '&amp;'.
	 *
	 * @return string
	 */
	public function get_edit_link( $context = 'display' ): string {
		return get_edit_post_link( $this->id, $context );
	}

	/**
	 * Check if this post has a featured image.
	 *
	 * @return bool
	 */
	public function has_featured_image(): bool {
		return has_post_thumbnail( $this->id );
	}

	/**
	 * Get the featured image for this post.
	 *
	 * @return ?int
	 */
	public function get_featured_image_id(): ?int {
		$id = get_post_thumbnail_id( $this->id );

		if ( ! $id ) {
			return null;
		}

		return $id;
	}

	/**
	 * Get the featured image for this post.
	 *
	 * @return ?Image
	 */
	public function get_featured_image(): ?Image {
		if ( $this->has_featured_image() ) {
			return new Image( $this->get_featured_image_id() );
		}

		return null;
	}

	/**
	 * Update the post.
	 *
	 * @param array $postdata The post's data.
	 *
	 * @return static
	 */
	public function update( array $postdata ): static {
		Log::add(
			message: __( 'Updated post.', 'wrd' ),
			target: $this->id,
			data: array(
				'postdata' => $postdata,
			)
		);

		$postdata['ID'] = $this->id;

		do_action( 'objective/post/before_update', $this, $postdata );

		wp_update_post( $postdata );

		do_action( 'objective/post/after_update', $this, $postdata );

		return $this;
	}

	/**
	 * Delete the post.
	 *
	 * @param bool $force When enabled, the post is deleted immediately, skipping the trash.
	 *
	 * @return bool
	 */
	public function delete( bool $force = false ): bool {
		Log::add(
			message: __( 'Deleted post.', 'wrd' ),
			target: $this->id,
			data: array(
				'force' => $force,
			)
		);

		do_action( 'objective/post/before_delete', $this );

		$success = (bool) wp_delete_post( $this->id, $force );

		do_action( 'objective/post/after_delete', $this );

		return $success;
	}

	/**
	 * Converts the object to an array representation.
	 *
	 * @return array The array representation of the object.
	 */
	public function to_api(): array {
		return array(
			'id'             => $this->id,
			'title'          => $this->get_title(),
			'featured_image' => $this->get_featured_image(),
		);
	}

	/**
	 * Create a new post.
	 *
	 * @param array $postdata The post's data. Optional.
	 *
	 * @return static|null
	 */
	public static function create( array $postdata = array() ): static|null {
		$post_type = new ( static::get_post_type_class() );

		$args = wp_parse_args(
			$postdata,
			array(
				'post_type'    => $post_type->get_name(),
				'post_title'   => gmdate( 'Y-m-d H:i:s' ),
				'post_content' => '',
				'post_status'  => static::get_default_status()->get_name(),
			)
		);

		$id = wp_insert_post( $args, true );

		if ( is_wp_error( $id ) ) {
			Log::add_wp_error( $id );
			return null;
		} else {
			Log::add( message: __( 'Created new post.', 'wrd' ), data: $args, target: $id );
		}

		return new static( $id );
	}

	/**
	 * Get a post.
	 *
	 * @param int|null|WP_Post|static|Post $post The post to get, or the default post if null.
	 *
	 * @return ?static
	 */
	public static function get_post( int|null|WP_Post|Post $post ): ?static {
		if ( is_a( $post, static::class ) ) {
			return $post;
		}

		if ( is_a( $post, self::class ) ) {
			return new static( $post->get_id() );
		}

		if ( null === $post && ! get_post() ) {
			// No global post to return.
			return null;
		}

		return new static( $post );
	}
}
