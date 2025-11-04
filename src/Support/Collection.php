<?php
/**
 * Contains the Collection class.
 *
 * @package wrd/wp-objective;
 */

namespace Wrd\WpObjective\Support;

use ArrayIterator;
use IteratorAggregate;
use JsonSerializable;
use Wrd\WpObjective\Contracts\Apiable;
use Traversable;

/**
 * Collection implementation.
 *
 * @template T
 */
class Collection implements IteratorAggregate, Apiable, JsonSerializable {

	/**
	 * Collection items.
	 *
	 * @var array<T>
	 */
	protected array $elements;

	/**
	 * Create a new collection.
	 *
	 * @param ?array<T> $elements Collection items.
	 */
	public function __construct( ?array $elements = null ) {
		if ( is_null( $elements ) ) {
			$elements = array();
		}

		$this->elements = $elements;
	}

	/**
	 * Reduce the collection to a single value.
	 *
	 * @param callable $callback Callback function.
	 * @param mixed    $initial Initial value.
	 *
	 * @return mixed
	 */
	public function reduce( callable $callback, mixed $initial ): mixed {
		return array_reduce( $this->elements, $callback, $initial );
	}

	/**
	 * Map the collection to a new array.
	 *
	 * @template NewT
	 *
	 * @param callable(T): NewT $callback Callback function.
	 *
	 * @return static<NewT>
	 */
	public function map( callable $callback ): static {
		return new static( array_map( $callback, $this->elements ) );
	}

	/**
	 * Iterate over the collection.
	 *
	 * @param callable $callback Callback function.
	 *
	 * @return static<T>
	 */
	public function each( callable $callback ): static {
		array_walk( $this->elements, $callback );
		return $this;
	}

	/**
	 * Filter the collection.
	 *
	 * @param callable(T): bool $callback Callback function.
	 * @return static<T>
	 */
	public function filter( ?callable $callback = null ): static {
		return new static( array_filter( $this->elements, $callback, ARRAY_FILTER_USE_BOTH ) );
	}

	/**
	 * Remove a key from the collection.
	 *
	 * @param string|int|null $key The key to remove.
	 * @return static<T>
	 */
	public function without( string|int|null $key ): static {
		if ( is_null( $key ) || ! array_key_exists( $key, $this->elements ) ) {
			return $this;
		}

		$copy = array_merge( array(), $this->elements );
		unset( $copy[ $key ] );

		return new static( $copy );
	}

	/**
	 * Find the first element in the collection to match a criteria.
	 *
	 * @param callable(T): bool $callback Callback function.
	 * @return T
	 */
	public function find( ?callable $callback ): mixed {
		return array_find( $this->elements, $callback );
	}

	/**
	 * Find the key of the first element in the collection to match a criteria.
	 *
	 * @param callable(T): bool $callback Callback function.
	 * @return string|int|null
	 */
	public function find_key( ?callable $callback ): string|int|null {
		return array_find_key( $this->elements, $callback );
	}

	/**
	 * Get the first element in the collection.
	 *
	 * @return T
	 */
	public function first(): mixed {
		return reset( $this->elements );
	}

	/**
	 * Get the last element in the collection.
	 *
	 * @return T
	 */
	public function last(): mixed {
		return end( $this->elements );
	}

	/**
	 * Check if the collection is empty.
	 *
	 * @return bool
	 */
	public function is_empty(): bool {
		return empty( $this->elements );
	}

	/**
	 * Check if any element in the collection passes a truth test.
	 *
	 * @param callable $callback Callback function.
	 *
	 * @return bool
	 */
	public function some( callable $callback ): bool {
		foreach ( $this->elements as $key => $value ) {
			if ( $callback( $value, $key ) ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Combines an array of arrays into a single collection.
	 *
	 * @return static
	 */
	public function expand(): static {
		$collection = new Collection();

		foreach ( $this->elements as $item ) {
			if ( is_array( $item ) ) {
				$collection->add( ...$item );
			} elseif ( is_a( $item, static::class ) ) {
				$collection->add( ...$item->all() );
			}
		}

		return $collection;
	}

	/**
	 * Get a value from a the collection.
	 *
	 * @param string|int|null $key     The key to retrieve.
	 *
	 * @param mixed           $fallback The default value to return if the key is not found.
	 *
	 * @return mixed The value from the array or the default value.
	 */
	public function get( int|string|null $key, mixed $fallback = null ) {
		if ( is_null( $key ) ) {
			return null;
		}

		if ( ! array_key_exists( $key, $this->elements ) ) {
			return $fallback;
		}

		return $this->elements[ $key ];
	}

	/**
	 * Get a value from a nested array using "dot" notation.
	 *
	 * @param string|null $key     The key in dot notation.
	 *
	 * @param mixed       $fallback The default value to return if the key is not found.
	 *
	 * @return mixed The value from the array or the default value.
	 */
	public function dot( ?string $key, mixed $fallback = null ): mixed {
		if ( is_null( $key ) ) {
			return $fallback;
		}

		$array = $this->elements;

		if ( isset( $array[ $key ] ) ) {
			return $array[ $key ];
		}

		foreach ( explode( '.', $key ) as $segment ) {
			if ( ! is_array( $array ) || ! array_key_exists( $segment, $array ) ) {
				return $fallback;
			}

			$array = $array[ $segment ];
		}

		return $array;
	}

	/**
	 * Get the number of elements in the collection.
	 *
	 * @return int
	 */
	public function count(): int {
		return count( $this->elements );
	}

	/**
	 * Add an element to the collection.
	 *
	 * @param T ...$elements Element to add.
	 *
	 * @return static<T>
	 */
	public function add( ...$elements ): static {
		array_push( $this->elements, ...$elements );
		return $this;
	}

	/**
	 * Get all the unique values in the collection.
	 *
	 * @return static<T>
	 */
	public function unique(): static {
		$this->elements = array_unique( $this->elements );
		return $this;
	}

	/**
	 * Get all the values in the collection.
	 *
	 * @return array<T>
	 */
	public function values(): array {
		return array_values( $this->elements );
	}

	/**
	 * Get all the items in the collection.
	 *
	 * @return array<T>
	 */
	public function all(): array {
		return $this->elements;
	}

	/**
	 * Sum all the values.
	 *
	 * @return float|int
	 */
	public function sum(): float|int {
		return array_sum( $this->elements );
	}

	/**
	 * Max all the values.
	 *
	 * @return float|int
	 */
	public function max(): float|int {
		return max( $this->elements );
	}

	/**
	 * Min all the values.
	 *
	 * @return float|int
	 */
	public function min(): float|int {
		return min( $this->elements );
	}

	/**
	 * Average all the values.
	 *
	 * @return float|int
	 */
	public function avg(): float|int {
		return $this->sum() / $this->count();
	}

	/**
	 * Create a new collection.
	 *
	 * @template TValue
	 *
	 * @param TValue[]|Collection<TValue>|null $elements Array of items.
	 *
	 * @return Collection<TValue>
	 */
	public static function from( array|Collection|null $elements = array() ): static {
		if ( is_null( $elements ) ) {
			return new static( array() );
		}

		if ( is_array( $elements ) ) {
			return new static( $elements );
		}

		return $elements;
	}

	/**
	 * Convert a possible collection-like value to an array.
	 *
	 * @template TValue
	 *
	 * @param TValue[]|Collection<TValue>|null $elements Array of items.
	 *
	 * @retrun TValue[]
	 */
	public static function to( array|Collection|null $elements = array() ): array {
		if ( is_null( $elements ) ) {
			return array();
		}

		if ( is_array( $elements ) ) {
			return $elements;
		}

		return $elements->all();
	}

	/**
	 * Get an iterator for the collection.
	 *
	 * @return Traversable<T>
	 */
	public function getIterator(): Traversable {
		return new ArrayIterator( $this->elements );
	}

	/**
	 * Convert to API representation.
	 *
	 * @return array<T>
	 */
	public function to_api(): array {
		return $this->elements;
	}

	/**
	 * Convert to JSON representation.
	 *
	 * @return array<T>
	 */
	public function jsonSerialize(): array {
		return $this->elements;
	}
}
