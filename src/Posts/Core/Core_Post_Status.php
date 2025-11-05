<?php
/**
 * Contains the Post_Status class.
 *
 * @package wrd/wp-objective
 */

namespace Wrd\WpObjective\Posts;

use stdClass;
use Wrd\WpObjective\Foundation\Service_Provider;
use Wrd\WpObjective\Support\Collection;

/**
 * For utilising a core post status.
 */
abstract class Core_Post_Status extends Service_Provider {
	/**
	 * The status.
	 *
	 * @var stdClass $status
	 */
	protected stdClass $status;

	/**
	 * Create a core post status.
	 *
	 * @param string $status The name of a reigstered post status.
	 */
	public function __construct( string $status ) {
		$this->status = get_post_status_object( $status );
	}

	/**
	 * Get this class' post status.
	 *
	 * @return string
	 */
	public function get_name(): string {
		return $this->status->name;
	}

	/**
	 * Get the label for this post status.
	 *
	 * @return string
	 */
	public function get_label(): string {
		return $this->status->label;
	}

	/**
	 * Get the arguments for this post type.
	 *
	 * @return array
	 */
	public function get_args(): array {
		return (array) $this->status;
	}

	/**
	 * Initialize the post type.
	 *
	 * @return void
	 */
	public function init(): void {
		// Silence is golden.
	}

	/**
	 * Get all the core post statii.
	 *
	 * @return Collection<self>
	 */
	public static function all(): Collection {
		return Collection::from( get_post_stati() )
			->map( fn( string $name ) => new self( $name ) )
			->filter( fn( self $status ) => true === $status->get_args()['_builtin'] );
	}

	/**
	 * Get all the core post statii.
	 *
	 * @return Collection<self>
	 */
	public static function all_not_internal(): Collection {
		return static::all()
			->filter( fn( self $status ) => false === $status->get_args()['internal'] );
	}
}
