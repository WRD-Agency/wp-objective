<?php
/**
 * Contains the Facade base class.
 *
 * @package wrd\wp-objective
 */

namespace Wrd\WpObjective\Support\Facades;

use Wrd\WpObjective\Foundation\Container;

/**
 * Base implementation of a facade for accessing a container object.
 *
 * Based on Laravel's Facades system.
 *
 * @template TObject
 */
abstract class Facade {
	/**
	 * Holds the object we're mocking with, replacing the call to the container.
	 *
	 * @var TObject|null
	 */
	protected static mixed $mock = null;

	/**
	 * The container instance being facaded.
	 *
	 * @var Container
	 */
	protected static Container $container;

	/**
	 * Get the ID to grab the object from the container.
	 *
	 * @return class-string<TObject>
	 */
	abstract public static function get_facade_accessor();

	/**
	 * Get the resolved object.
	 *
	 * @return TObject
	 */
	public static function get_resolved_instance(): mixed {
		if ( static::$mock ) {
			return static::$mock;
		}

		return static::$container->resolve( static::get_facade_accessor() );
	}

	/**
	 * Set the facade's container.
	 *
	 * @param Container $container The container.
	 *
	 * @return void
	 */
	public static function set_container( Container $container ): void {
		static::$container = $container;
	}

	/**
	 * Get the facade's container.
	 *
	 * @return Container
	 */
	public static function get_container(): Container {
		return static::$container;
	}

	/**
	 * Mock this facade to a different object.
	 *
	 * @param TObject|null $object The object to replace with.
	 *
	 * @return void
	 */
	public static function mock_as( mixed $object ): void {
		static::$mock = $object;
	}

	/**
	 * Stop mocking.
	 *
	 * @return void
	 */
	public static function stop_mock(): void {
		static::mock_as( null );
	}

	/**
	 * Maps dynamic & static calls to the instance.
	 *
	 * @param  string $method The method being called.
	 *
	 * @param  array  $args Arguments to the method.
	 *
	 * @return mixed
	 */
	public static function __callStatic( $method, $args ) {
		$instance = static::get_resolved_instance();
		return $instance->$method( ...$args );
	}
}
