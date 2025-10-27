<?php
/**
 * Contains the Post class.
 *
 * @package wrd/wp-objective
 */

namespace Wrd\WpObjective\Posts;

use Exception;
use WP_Post;
use Wrd\WpObjective\Support\Facades\Log;
use Wrd\WpObjective\Support\Image;

/**
 * For building up a post.
 */
abstract class Post {
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

		if ( ! $post || $this->get_post_type() !== $post->post_type ) {
			throw new Exception( 'Invalid post type.' );
		}

		$this->id = $post->ID;
	}

	/**
	 * Get the class for this post type.
	 *
	 * @return class-string<Post_Type>
	 */
	abstract public static function get_post_type_class(): string;

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
	public function get_post(): WP_Post {
		return get_post( $this->id );
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
			default:
				throw new Exception( "Unknown field type: $type" );
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

		switch ( $type ) {
			case 'post':
				$previous = $this->update_post_field( $field, $value );
				break;
			case 'meta':
				$previous = $this->update_meta_field( $field, $value );
				break;
			default:
				throw new Exception( "Unknown field type: $type" );
		}

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
		$previous_value = get_post_field( $field, $this->id );

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
		$previous_value = get_post_meta( $this->id, $field, true );

		update_post_meta( $this->id, $field, $value );

		return $previous_value;
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
	 * @param string      $context Optional. How to output the '&' character. Default '&amp;'.
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
				'postdata' => $postdata
			)
		);
		
		$postdata['ID'] = $this->id;

		wp_update_post($postdata);

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

		return (bool) wp_delete_post( $this->id, $force );
	}

	/**
	 * Create a new post.
	 *
	 * @param array $postdata The post's data. Optional.
	 *
	 * @return static|null
	 */
	public static function create( array $postdata = [] ): static|null {
		$post_type = new (static::get_post_type_class());

		$args = wp_parse_args(
			$postdata,
			array(
				'post_type'    => $post_type->get_name(),
				'post_title'   => gmdate( 'Y-m-d H:i:s' ),
				'post_content' => '',
			)
		);

		$id = wp_insert_post( $args, true );

		if( is_wp_error( $id ) ){
			Log::add_wp_error( $id );
			return null;
		}
		else{
			Log::add( message: __( 'Created new post.', 'wrd' ), data: $args, target: $id );
		}

		return new static( $id );
	}
}