<?php
/**
 * Contains the Plugin class.
 *
 * @package wrd/wp-objective
 */

namespace Wrd\WpObjective\Foundation;

use Exception;
use OutOfBoundsException;
use ReflectionClass;
use ReflectionNamedType;
use Wrd\WpObjective\Admin\Flash_Manager;
use Wrd\WpObjective\Foundation\Migrate\Migration_Manager;
use Wrd\WpObjective\Log\Log_Manager;
use Wrd\WpObjective\Support\Facades\Facade;

/**
 * Base implementation for a plugin.
 */
class Plugin extends Service_Provider {
	/**
	 * Files to include when the plugin is loaded.
	 *
	 * @var string[]
	 */
	public array $files = array();

	/**
	 * Bindings to bind upon boot.
	 *
	 * @var array<string, class-string<Service_Provider>|Service_Provider>
	 */
	protected array $bindings = array();

	/**
	 * Service providers to register upon boot.
	 *
	 * @var (class-string<Service_Provider>|Service_Provider)[]
	 */
	protected array $providers = array();

	/**
	 * Main plugin file.
	 *
	 * @var string
	 */
	protected string $file = '';

	/**
	 * Directory path of the plugin.
	 *
	 * @var string
	 */
	protected string $dir = '';

	/**
	 * The current globally available plugin (if any).
	 *
	 * @var ?static
	 */
	protected static $instance;

	/**
	 * Get the globally available instance of the plugin.
	 *
	 * @return static
	 */
	public static function get_instance(): ?static {
		return static::$instance;
	}

	/**
	 * Create the global plugin instance.
	 *
	 * @param string $file Main plugin file.
	 *
	 * @param string $dir Directory path of the plugin.
	 *
	 * @return static
	 */
	public static function create_global( string $file, string $dir ): static {
		$instance = new static( $file, $dir );

		static::$instance = $instance;
		Facade::set_plugin( $instance );

		return $instance;
	}

	/**
	 * Create the plugin instance.
	 *
	 * @param string $file Main plugin file.
	 *
	 * @param string $dir Directory path of the plugin.
	 */
	protected function __construct( string $file, string $dir ) {
		$this->file = $file;
		$this->dir  = $dir;

		$this->bind_default_bindings();
	}

	/**
	 * Apply the default bindings.
	 *
	 * @return void
	 */
	protected function bind_default_bindings(): void {
		$this->bind( self::class, $this );
		$this->bind( static::class, $this );

		$this->bind( Log_Manager::class );
		$this->bind( Flash_Manager::class );
		$this->bind( Migration_Manager::class );
	}

	/**
	 * Bind an ID to an object/class.
	 *
	 * @param ?string                  $id The binding ID. If a class name is provided then it can be given with no second parameter.
	 *
	 * @param class-string|object|null $concrete The concrete object. A class name or instance of it.
	 *
	 * @return void
	 */
	public function bind( ?string $id, $concrete = null ): void {
		if ( is_null( $concrete ) && class_exists( $id ) ) {
			$concrete = $id;
		}

		$this->bindings[ $id ] = $concrete;

		if ( is_subclass_of( $concrete, Service_Provider::class ) ) {
			$this->provide( $concrete );
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
	public function get( $id ) {
		if ( ! array_key_exists( $id, $this->bindings ) ) {
			// ID is not explictly bound.

			// If the ID is a valid class name, try and resolve it as a fallback.
			if ( class_exists( $id ) ) {
				return $this->make( $id );
			}

			throw new OutOfBoundsException( "The binding '$id' does not exist and cannot be resolved." );
		}

		$concrete = $this->bindings[ $id ];

		if ( ! is_string( $concrete ) ) {
			// Instance is stored, return it.
			return $concrete;
		}

		// A class name is stored, resolve it.
		return $this->make( $concrete );
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
	public function make( $class_name ) {
		if ( ! class_exists( $class_name ) ) {
			throw new Exception( "The '$class_name' class does not exist and cannot be resolved." );
		}

		$reflection = new ReflectionClass( $class_name );

		if ( ! $reflection->isInstantiable() ) {
			throw new Exception( "The '$class_name' class is not instantiable and cannot be resolved." );
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
				$arguments[] = $this->get( $type->getName() );
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
	public function provide( $provider ): void {
		$this->providers[] = $provider;
	}

	/**
	 * Get the plugin file.
	 *
	 * @return string
	 */
	public function get_file(): string {
		return $this->file;
	}

	/**
	 * Alias of 'get_file'.
	 *
	 * @return string
	 */
	public function file(): string {
		return $this->get_file();
	}

	/**
	 * Get the plugin dir.
	 *
	 * @return string
	 */
	public function get_dir(): string {
		return $this->dir;
	}

	/**
	 * Alias of 'get_dir'.
	 *
	 * @return string
	 */
	public function dir(): string {
		return $this->get_dir();
	}

	/**
	 * Get the metadata about the plugin.
	 *
	 * @see get_plugin_data
	 *
	 * @return array
	 */
	public function get_data(): array {
		return get_plugin_data( $this->file );
	}

	/**
	 * Get the current plugin version.
	 *
	 * @return string
	 */
	public function get_version(): string {
		$data = $this->get_data();

		if ( ! isset( $data['Version'] ) ) {
			return '1.0.0';
		}

		return $data['Version'];
	}

	/**
	 * Alias of 'get_version'.
	 *
	 * @return string
	 */
	public function version(): string {
		return $this->get_version();
	}

	/**
	 * Load in any additional files.
	 *
	 * @return void
	 */
	public function includes() {
		foreach ( $this->files as $file ) {
			require_once $file;
		}
	}

	/**
	 * Calls a method on all registered providers.
	 *
	 * @param string $method The method to call.
	 *
	 * @return void
	 */
	protected function hit_providers( string $method ): void {
		foreach ( $this->providers as $concrete ) {
			if ( is_string( $concrete ) ) {
				$concrete = $this->get( $concrete );
			}

			$concrete->{$method}();
		}
	}

	/**
	 * Loads up a plugin class.
	 *
	 * @return void
	 */
	public function boot(): void {
		$this->includes();

		$this->hit_providers( 'boot' );

		add_action( 'init', array( $this, 'init' ) );
		add_action( 'shutdown', array( $this, 'shutdown' ) );
	}

	/**
	 * Runs on the 'init' hook.
	 *
	 * @return void
	 */
	public function init(): void {
		if ( is_admin() ) {
			$this->admin();
		} elseif ( wp_is_serving_rest_request() ) {
			$this->api();
		} else {
			$this->public();
		}

		$this->hit_providers( 'init' );
	}

	/**
	 * Runs on the 'init' hook in an admin screen.
	 *
	 * @return void
	 */
	public function admin(): void {
		// This page left intentionally blank.
	}

	/**
	 * Runs on the 'init' hook in the rest API.
	 *
	 * @return void
	 */
	public function api(): void {
		// This page left intentionally blank.
	}

	/**
	 * Runs on the 'init' hook in an public screen.
	 *
	 * @return void
	 */
	public function public(): void {
		// This page left intentionally blank.
	}

	/**
	 * Run in the 'shutdown' hook at the end of every request.
	 *
	 * @return void
	 */
	public function shutdown(): void {
		$this->hit_providers( 'shutdown' );
	}
}
