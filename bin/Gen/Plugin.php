<?php
/**
 * Contains the CLASS_NAME class.
 *
 * @since PACKAGE_VERSION.
 *
 * @package PACKAGE_NAME
 */

namespace PACKAGE_NAMESPACE;

use Wrd\WpObjective\Foundation\Plugin;

/**
 * The CLASS_NAME plugin.
 */
class CLASS_NAME extends Plugin {
	/**
	 * Files to include when the plugin is loaded.
	 *
	 * @var string[]
	 */
	public array $files = array();

	/**
	 * Bindings to register upon boot.
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
