<?php
/**
 * Contains the Plugin class.
 *
 * @package wrd/wp-objective
 */

namespace Wrd\WpObjective\Foundation;

use OutOfBoundsException;
use wpdb;
use Wrd\WpObjective\Admin\Flash_Manager;
use Wrd\WpObjective\Foundation\Migrate\Migration_Manager;
use Wrd\WpObjective\Log\Log_Manager;
use Wrd\WpObjective\Support\Facades\Facade;

/**
 * Base implementation for a plugin.
 */
class Plugin {
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
	 * Migrations to load in.
	 *
	 * @var class-string<Migration>[]
	 */
	protected array $migrations = array();

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
	 * The container for managing dependency injection.
	 *
	 * @var Container $container;
	 */
	protected Container $container;

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
	public static function create( string $file, string $dir ): static {
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

		$this->container = new Container();

		$this->require_files();

		$this->bind_default_bindings();

		$this->register_bindings();
		$this->register_providers();

		$this->register_migrations();
	}

	/**
	 * Load in any additional files.
	 *
	 * @return void
	 */
	protected function require_files() {
		foreach ( $this->files as $file ) {
			require_once $file;
		}

		$this->include();
	}

	/**
	 * Apply the default bindings.
	 *
	 * @return void
	 */
	protected function bind_default_bindings(): void {
		$this->container->add_binding( self::class, $this );
		$this->container->add_binding( static::class, $this );

		global $wpdb;
		$this->container->add_binding( wpdb::class, $wpdb );

		$this->container->add_binding( Log_Manager::class );
		$this->container->add_binding( Flash_Manager::class );
	}

	/**
	 * Register the assigned bindings.
	 *
	 * @return void
	 */
	protected function register_bindings(): void {
		foreach ( $this->bindings as $id => $concrete ) {
			$this->container->add_binding( $id, $concrete );
		}
	}

	/**
	 * Register the assigned providers.
	 *
	 * @return void
	 */
	protected function register_providers(): void {
		foreach ( $this->providers as $provider ) {
			$this->container->add_provider( $provider );
		}
	}

	/**
	 * Register the assigned migrations.
	 *
	 * @return void
	 */
	protected function register_migrations(): void {
		foreach ( $this->migrations as $migration ) {
			$this->make( Migration_Manager::class )->add_migration( $migration );
		}
	}

	/**
	 * Attach the plugin functionality to WordPress.
	 *
	 * Hooks into WordPress, run migrations etc.
	 *
	 * @return void
	 */
	public function attach(): void {
		$migration_manager = $this->make( Migration_Manager::class );

		if ( $migration_manager->needs_migration() ) {
			$migration_manager->run_needed_migrations();
		}

		$this->container->hit_service_providers( 'boot' );

		add_action(
			'init',
			function () {
				$this->init();

				if ( is_admin() ) {
					$this->admin();
				} elseif ( wp_is_serving_rest_request() ) {
					$this->api();
				} else {
					$this->public();
				}

				$this->container->hit_service_providers( 'init' );
			}
		);

		add_action(
			'shutdown',
			function () {
				$this->shutdown();
				$this->container->hit_service_providers( 'shutdown' );
			}
		);
	}

	/**
	 * Finds a binding by its identifier and returns it with it's dependencies injected.
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
	public function make( $id ) {
		return $this->container->get_bound_instance( $id );
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
	 * Get the plugin dir.
	 *
	 * @return string
	 */
	public function get_dir(): string {
		return $this->dir;
	}

	/**
	 * Get the plugin container.
	 *
	 * @return Container
	 */
	public function get_container(): Container {
		return $this->container;
	}

	/**
	 * Get the metadata about the plugin.
	 *
	 * @see get_plugin_data
	 *
	 * @return array
	 */
	public function get_data(): array {
		$should_translate = did_action( 'init' );

		return get_plugin_data( $this->file, true, $should_translate );
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
	 * Run when files are being included.
	 *
	 * @return void
	 */
	public function include(): void {
		// This page left intentionally blank.
	}

	/**
	 * Loads up a plugin class.
	 *
	 * @return void
	 */
	public function boot(): void {
		// This page left intentionally blank.
	}

	/**
	 * Runs on the 'init' hook.
	 *
	 * @return void
	 */
	public function init(): void {
		// This page left intentionally blank.
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
		// This page left intentionally blank.
	}
}
