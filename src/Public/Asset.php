<?php
/**
 * Contains the Asset class.
 *
 * @package wrd/wp-objective
 */

namespace Wrd\WpObjective\Public;

use Wrd\WpObjective\Foundation\Service_Provider;
use Wrd\WpObjective\Support\Condition;
use Wrd\WpObjective\Support\Facades\Plugin;

/**
 * Class for managing a style or script asset.
 */
abstract class Asset extends Service_Provider {
	/**
	 * Get the conditions required to enqueue this assets.
	 *
	 * By default assets are only registered and should be manually enqueued.
	 *
	 * @return Condition|bool
	 */
	public function get_conditions(): Condition|bool {
		return false;
	}

	/**
	 * Get the URL of the asset.
	 *
	 * @return string
	 */
	abstract public function get_url();

	/**
	 * Get the type of asset.
	 *
	 * @return "script"|"style"
	 */
	public function get_type(): string {
		return str_ends_with( $this->get_url(), '.js' ) ? 'script' : 'style';
	}

	/**
	 * Get the assets this depends on.
	 *
	 * @return string[]
	 */
	public function get_dependencies(): array {
		return array();
	}

	/**
	 * Get the asset version.
	 *
	 * @return string
	 */
	public function get_version(): string {
		$cache_bust_environment_types = array(
			'local',
			'development',
		);

		return in_array( wp_get_environment_type(), $cache_bust_environment_types, true ) ? uniqid() : Plugin::get_version();
	}

	/**
	 * Get the handle.
	 *
	 * @return string
	 */
	public function get_handle(): string {
		return static::class;
	}

	/**
	 * Register this assets.
	 *
	 * @return bool
	 */
	public function register(): bool {
		switch ( $this->get_type() ) {
			case 'script':
				return wp_register_script( $this->get_handle(), $this->get_url(), $this->get_dependencies(), $this->get_version(), array( 'strategy' => 'defer' ) );

			case 'style':
				return wp_register_style( $this->get_handle(), $this->get_url(), $this->get_dependencies(), $this->get_version() );
		}

		return false;
	}

	/**
	 * Enqueue the asset.
	 *
	 * Must be registered first.
	 *
	 * @return bool
	 */
	public function enqueue(): bool {
		switch ( $this->get_type() ) {
			case 'script':
				return wp_enqueue_script( $this->get_handle() );

			case 'style':
				return wp_enqueue_style( $this->get_handle() );
		}

		return false;
	}

	/**
	 * Check the conditions and enqueue the asset if they pass.
	 *
	 * @return bool
	 */
	public function maybe_enqueue(): bool {
		if ( ! Condition::check( $this->get_conditions(), 'all' ) ) {
			return false;
		}

		return $this->enqueue();
	}

	/**
	 * Run in the 'init' hook.
	 *
	 * @return void
	 */
	public function init(): void {
		$this->register();

		add_action( 'wp_enqueue_scripts', array( $this, 'maybe_enqueue' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'maybe_enqueue' ) );
	}
}
