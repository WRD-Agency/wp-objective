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
use Wrd\WpObjective\Admin\Flash;
use Wrd\WpObjective\Admin\Flash_Manager;
use Wrd\WpObjective\Foundation\Migrate\Migration_Manager;
use Wrd\WpObjective\Log\Log_Manager;
use Wrd\WpObjective\Support\Facades\Facade;

/**
 * Implementation of the service container pattern.
 */
class Container {
	/**
	 * The bindings for the container.
	 *
	 * @var (class-string|object)[]
	 */
	protected array $bindings = array();

	/**
	 * The service providers for the container.
	 *
	 * @var (class-string<Service_Provider>|Service_Provider)[]
	 */
	protected array $providers = array();

	/**
	 * The current globally available container (if any).
	 *
	 * @var static
	 */
	protected static $instance;

	/**
	 * Get the globally available instance of the container.
	 *
	 * @return static
	 */
	public static function get_instance() {
		return static::$instance ??= new static();
	}

	/**
	 * Create a container.
	 */
	public function __construct() {
		// Default bindings.
		$this->bind( static::class, $this );
		$this->bind( self::class, $this );
		// We do not bind the plugin, the consumer should.
		$this->bind( Log_Manager::class );
		$this->bind( Flash_Manager::class );
		$this->bind( Migration_Manager::class );

		// Connect our facades to this instances.
		Facade::set_container( $this );
	}

	/**
	 * Add an entry to the container.
	 *
	 * This will also add the binding as a service provider if it is one.
	 *
	 * @param string                   $abstract The abstract identifier of the binding.
	 *
	 * @param class-string|object|null $concrete The concrete instance or class name to add.
	 *
	 * @return void
	 */
	public function bind( string $abstract, string|object|null $concrete = null ): void {
		if ( ! $concrete ) {
			$concrete = $abstract;
		}

		$this->bindings[ $abstract ] = $concrete;

		$this->maybe_provide( $concrete );
	}

	/**
	 * Check if an ID is explictly bound.
	 *
	 * @param string $id The ID to check for.
	 *
	 * @return bool
	 */
	public function has( string $id ): bool {
		return array_key_exists( $id, $this->bindings );
	}

	/**
	 * Finds an entry of the container by its identifier and returns it.
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
	public function get( $id ) {
		if ( ! array_key_exists( $id, $this->bindings ) ) {
			// If the ID is a valid class name, try and resolve it as a fallback.
			if ( class_exists( $id ) ) {
				return $this->resolve_class_name( $id );
			}

			// ID not found.
			throw new OutOfBoundsException( "The binding '$id' does not exist and cannot be resolved." );
		}

		$concrete = $this->bindings[ $id ];

		if ( ! is_string( $concrete ) ) {
			// Instance is stored, return it.
			return $concrete;
		}

		// A class name is stored, resolve it.
		return $this->resolve_class_name( $concrete );
	}

	/**
	 * Resolves a binding from a class string into a object with dependencies injected.
	 *
	 * @template TObject
	 *
	 * @param class-string<TObject> $concrete The concrete class name.
	 *
	 * @return TObject
	 *
	 * @throws Exception If the class cannot be resolved.
	 */
	public function resolve_class_name( $concrete ) {
		if ( ! class_exists( $concrete ) ) {
			throw new Exception( "The '$concrete' class does not exist and cannot be resolved." );
		}

		$reflection = new ReflectionClass( $concrete );

		if ( ! $reflection->isInstantiable() ) {
			throw new Exception( "The '$concrete' class is not instantiable and cannot be resolved." );
		}

		$constructor = $reflection->getConstructor();

		if ( ! $constructor ) {
			return new $concrete();
		}

		$parameters = $constructor->getParameters();

		if ( ! $parameters ) {
			return new $concrete();
		}

		return $reflection->newInstanceArgs( $this->make_dependency( $parameters ) );
	}

	/**
	 * Get the dependencies for a binding.
	 *
	 * @param \ReflectionParameter[] $parameters The parameters to create dependencies for.
	 *
	 * @return array
	 */
	protected function make_dependency( array $parameters ): array {
		$args = array();

		foreach ( $parameters as $param ) {
			$type = $param->getType();

			if ( $type instanceof ReflectionNamedType && ! $type->isBuiltin() ) {
				$args[] = $this->get( $type->getName() );
			}
		}

		return $args;
	}

	/**
	 * Register a service provider.
	 *
	 * @param class-string<Service_Provider>|Service_Provider $provider The provider.
	 *
	 * @return void
	 */
	public function provide( $provider ): void {
		$this->providers[] = $provider;
	}

	/**
	 * Check if a class inherits from Service_Provider and if it does, add it as a provider.
	 *
	 * @param class-string|Service_Provider $provider The object class to possible add.
	 *
	 * @return bool
	 */
	public function maybe_provide( $provider ) {
		if ( ! is_subclass_of( $provider, Service_Provider::class ) ) {
			return false;
		}

		$this->provide( $provider );
		return true;
	}

	/**
	 * Boots the service providers.
	 *
	 * @return void
	 */
	public function boot(): void {
		if ( ! $this->has( Plugin::class ) ) {
			_doing_it_wrong( 'Container::boot', esc_html_e( 'The container is using the default plugin instance. Did you forget to bind it?', 'wrd' ), '1.0.0' );
		}

		$this->hit_providers( 'boot' );

		$plugin = $this->get( Plugin::class );

		register_activation_hook( $plugin->file(), array( $this, 'activate' ) );
		register_deactivation_hook( $plugin->file(), array( $this, 'deactive' ) );
		register_uninstall_hook( $plugin->file(), array( $this, 'uninstall' ) );

		add_action( 'init', array( $this, 'init' ), PHP_INT_MIN, 0 );
		add_action( 'shutdown', array( $this, 'shutdown' ), PHP_INT_MAX, 0 );
	}

	/**
	 * Calls a method on all registered providers.
	 *
	 * @param string $method The method to call.
	 *
	 * @return void
	 */
	public function hit_providers( string $method ): void {
		foreach ( $this->providers as $concrete ) {
			if ( is_string( $concrete ) ) {
				$concrete = $this->get( $concrete );
			}

			$concrete->{$method}();
		}
	}

	/**
	 * Run in the 'init' hook.
	 *
	 * @return void
	 */
	public function init(): void {
		$this->hit_providers( 'init' );
	}

	/**
	 * Run in the 'shutdown' hook at the end of every request.
	 *
	 * @return void
	 */
	public function shutdown(): void {
		$this->hit_providers( 'shutdown' );
	}

	/**
	 * Runs when the plugin is activated.
	 *
	 * @return void
	 */
	public function activated(): void {
		$this->hit_providers( 'activated' );
	}

	/**
	 * Runs when the plugin is deactivated.
	 *
	 * @return void
	 */
	public function deactivated(): void {
		$this->hit_providers( 'deactivated' );
	}

	/**
	 * Runs when the plugin is uninstall.
	 *
	 * @return void
	 */
	public function uninstall(): void {
		$this->hit_providers( 'uninstall' );
	}
}
