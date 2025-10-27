<?php
/**
 * Contains the Facade base class.
 *
 * @package wrd\wp-objective
 */

namespace Wrd\WpObjective\Support\Facades;

use Wrd\WpObjective\Foundation\Plugin;

/**
 * Base implementation of a facade for accessing a plugin binding object.
 *
 * Based on Laravel's Facades system.
 *
 * @template TObject
 */
abstract class Facade {
	/**
	 * Holds the object we're mocking with, replacing the call to the plugin binding.
	 *
	 * @var TObject|null
	 */
	protected static mixed $mock = null;

	/**
	 * The plugin binding instance being facaded.
	 *
	 * @var Plugin
	 */
	protected static Plugin $plugin;

	/**
	 * Get the ID to grab the object from the plugin.
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

		return static::$plugin->make( static::get_facade_accessor() );
	}

	/**
	 * Set the facade's plugin.
	 *
	 * @param Plugin $plugin The plugin.
	 *
	 * @return void
	 */
	public static function set_plugin( Plugin $plugin ): void {
		static::$plugin = $plugin;
	}

	/**
	 * Get the facade's plugin.
	 *
	 * @return Plugin
	 */
	public static function get_plugin(): Plugin {
		return static::$plugin;
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
