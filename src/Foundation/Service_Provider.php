<?php
/**
 * Contains the Service_Provider class.
 *
 * @package wrd/wp-objective
 */

namespace Wrd\WpObjective\Foundation;

use Wrd\WpObjective\Support\Facades\Plugin;

/**
 * Base class for an object which needs to boot or init.
 */
abstract class Service_Provider {
	/**
	 * The container the provider is registered to.
	 *
	 * @var Container $container
	 */
	protected Container $container;

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
	 * Registers the service provider to a container.
	 *
	 * @param Container $container The container this provider is being registered to.
	 *
	 * @return void
	 */
	public function connect( Container $container ): void {
		$this->container = $container;

		// Register all bindings.
		foreach ( $this->bindings as $id => $concrete ) {
			$this->container->add_binding( $id, $concrete );
		}

		// Register all sub-providers.
		foreach ( $this->providers as $provider ) {
			$this->container->add_provider( $provider );
		}
	}

	/**
	 * Runs when the plugin is booted.
	 *
	 * Useful for registering hooks.
	 *
	 * @return void
	 */
	public function boot(): void {
		// This page left intentionally blank.
	}

	/**
	 * Run in the 'init' hook.
	 *
	 * @return void
	 */
	public function init(): void {
		// This page left intentionally blank.
	}

	/**
	 * Run in the 'shutdown' hook.
	 *
	 * @return void
	 */
	public function shutdown(): void {
		// This page left intentionally blank.
	}

	/**
	 * Get the currently provided instance.
	 *
	 * @return static
	 */
	public static function make(): static {
		return Plugin::make( static::class );
	}
}
