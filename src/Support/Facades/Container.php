<?php
/**
 * Contains the Container facade.
 *
 * @package wrd\wp-objective
 */

namespace Wrd\WpObjective\Support\Facades;

use Wrd\WpObjective\Foundation\Container as FoundationContainer;

/**
 * Facade for accessing the 'Wrd\WpObjective\Foundation\Container' instance in the container.
 *
 * @autodoc facade
 *
 * @method static get_instance()
 * @method static void bind(string $abstract, object|string|null $concrete = NULL)
 * @method static bool has(string $id)
 * @method static get( $id)
 * @method static resolve_class_name( $concrete)
 * @method static array make_dependency(array $parameters)
 * @method static void provide( $provider)
 * @method static maybe_provide( $provider)
 * @method static void boot()
 * @method static void hit_providers(string $method)
 * @method static void init()
 * @method static void shutdown()
 *
 * @see Wrd\WpObjective\Foundation\Container
 */
class Container extends Facade {
	/**
	 * Get the ID to grab the object from the container.
	 *
	 * @return string
	 */
	public static function get_facade_accessor(): string {
		return FoundationContainer::class;
	}
}
