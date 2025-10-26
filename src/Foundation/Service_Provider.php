<?php
/**
 * Contains the Service_Provider class.
 *
 * @package wrd/wp-objective
 */

namespace Wrd\WpObjective\Foundation;

/**
 * Base class for an object which needs to boot or init.
 */
abstract class Service_Provider {
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
}
