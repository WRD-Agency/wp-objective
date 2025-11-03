<?php
/**
 * Contains the Container class.
 *
 * @package wrd/wp-objective
 */

namespace Wrd\WpObjective\Foundation;

use Exception;
use OutOfBoundsException;
use ReflectionClass;
use ReflectionNamedType;

/**
 * Base implementation for a Container.
 */
class Container {
	/**
	 * Bindings to bind upon boot.
	 *
	 * @var array<string, class-string<object>|object>
	 */
	protected array $bindings = array();

	/**
	 * Service providers to register upon boot.
	 *
	 * @var array<class-string<Service_Provider>, Service_Provider>
	 */
	protected array $providers = array();

	/**
	 * Bind an ID to an object/class.
	 *
	 * @param ?string                  $id The binding ID. If a class name is provided then it can be given with no second parameter.
	 *
	 * @param class-string|object|null $concrete The concrete object. A class name or instance of it.
	 *
	 * @return void
	 */
	public function add_binding( string $id, $concrete = null ): void {
		if ( is_null( $concrete ) && class_exists( $id ) ) {
			$concrete = $id;
		}

		$this->bindings[ $id ] = $concrete;

		if ( is_subclass_of( $concrete, Service_Provider::class ) ) {
			$this->add_provider( $concrete );
		}
	}

	/**
	 * Finds a binding by its identifier and returns it.
	 *
	 * If the identifier is a valid class name, it will be used as the fallback if no matching binding is found.
	 *
	 * @template TObject
	 *
	 * @throws OutOfBoundsException If the ID is not found.
	 *
	 * @param class-string<TObject> $id Identifier of the entry to look for.
	 *
	 * @return TObject
	 */
	public function get_bound_instance( $id ) {
		if ( ! array_key_exists( $id, $this->bindings ) ) {
			// ID is not explictly bound.

			// If the ID is a valid class name, try and resolve it as a fallback.
			if ( class_exists( $id ) ) {
				return $this->make_instance( $id );
			}

			throw new OutOfBoundsException( "The binding '" . esc_html( $id ) . "' does not exist and cannot be resolved." );
		}

		$concrete = $this->bindings[ $id ];

		if ( is_string( $concrete ) ) {
			// No instance has been create yet.
			$this->bindings[ $id ] = $this->make_instance( $concrete );
		}

		return $this->bindings[ $id ];
	}

	/**
	 * Inject dependencies into the constructor of a class.
	 *
	 * @template TObject
	 *
	 * @param class-string<TObject> $class_name The class name to instantiate.
	 *
	 * @return TObject
	 *
	 * @throws Exception If the class cannot be resolved.
	 */
	protected function make_instance( $class_name ) {
		if ( ! class_exists( $class_name ) ) {
			throw new Exception( "The '" . esc_html( $class_name ) . "' class does not exist and cannot be resolved." );
		}

		$reflection = new ReflectionClass( $class_name );

		if ( ! $reflection->isInstantiable() ) {
			throw new Exception( "The '" . esc_html( $class_name ) . "' class is not instantiable and cannot be resolved." );
		}

		$constructor = $reflection->getConstructor();

		if ( ! $constructor ) {
			return new $class_name();
		}

		$parameters = $constructor->getParameters();

		if ( ! $parameters ) {
			return new $class_name();
		}

		$arguments = array();

		foreach ( $parameters as $param ) {
			$type = $param->getType();

			if ( $type instanceof ReflectionNamedType && ! $type->isBuiltin() ) {
				$arguments[] = $this->get_bound_instance( $type->getName() );
			}
		}

		return $reflection->newInstanceArgs( $arguments );
	}

	/**
	 * Register a service provider.
	 *
	 * @param class-string<Service_Provider>|Service_Provider $provider The provider.
	 *
	 * @return void
	 */
	public function add_provider( $provider ): void {
		$class_name = is_string( $provider ) ? $provider : $provider::class;
		$instance   = is_string( $provider ) ? $this->get_bound_instance( $provider ) : $provider;

		$this->providers[ $class_name ] = $instance;

		$instance->connect( $this );
	}

	/**
	 * Calls a method on all registered providers.
	 *
	 * @param string $method The method to call.
	 *
	 * @return void
	 */
	public function hit_service_providers( string $method ): void {
		foreach ( $this->providers as $class_name => $instance ) {
			$instance->{$method}();
		}
	}
}
