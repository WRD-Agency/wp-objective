<?php
/**
 * Contains the Migration_Manager class.
 *
 * @package wrd/wp-objective
 */

namespace Wrd\WpObjective\Foundation\Migrate;

use Wrd\WpObjective\Foundation\Plugin;
use Wrd\WpObjective\Foundation\Service_Provider;
use Wrd\WpObjective\Support\Settings_Manager;

/**
 * Handles migrating upward in plugin versions.
 */
class Migration_Manager {
	/**
	 * The list of migration callbacks.
	 *
	 * @var array<string, class-string<Migration>[]>
	 */
	private array $migrations = array();

	/**
	 * The plugin to migrate.
	 *
	 * @var Plugin
	 */
	private Plugin $plugin;

	/**
	 * The settings manager.
	 *
	 * @var Settings_Manager
	 */
	private Settings_Manager $settings;

	/**
	 * Create a migrator.
	 *
	 * @param Plugin           $plugin The plugin to migrate.
	 *
	 * @param Settings_Manager $settings The settings manager.
	 */
	public function __construct( Plugin $plugin, Settings_Manager $settings ) {
		$this->plugin   = $plugin;
		$this->settings = $settings;
	}

	/**
	 * Get the migrated to plugin version.
	 *
	 * @return string
	 */
	public function get_migrated_version(): string {
		return $this->settings->get( self::class . '__migrated_version', '0.0.0' );
	}

	/**
	 * Update the migrated to plugin version.
	 *
	 * @param string $version The new version.
	 *
	 * @return string
	 */
	public function set_migrated_version( string $version ): string {
		return $this->settings->set( self::class . '__migrated_version', $version );
	}

	/**
	 * Register a function to run when migrating to the given version.
	 *
	 * @param class-string<Migration> $migration The migration class name.
	 *
	 * @return void
	 */
	public function add_migration( $migration ) {
		$version = ( new $migration() )->get_version();

		if ( ! array_key_exists( $version, $this->migrations ) ) {
			$this->migrations[ $version ] = array();
		}

		$this->migrations[ $version ][] = $migration;

		// Sort into correct order.
		uksort( $this->migrations, 'version_compare' );
	}

	/**
	 * Checks if the installed version of the plugin has migrations to be run.
	 *
	 * @return bool
	 */
	public function needs_migration(): bool {
		return version_compare( $this->plugin->get_version(), $this->get_migrated_version(), '>' );
	}

	/**
	 * Calls migration functions to upgrade to the latest plugin version.
	 *
	 * Migrations can be hooked using the 'add_migration' hook.
	 *
	 * @return void
	 */
	public function run_needed_migrations(): void {
		$current_version  = $this->plugin->get_version();
		$migrated_version = $this->get_migrated_version();

		if ( version_compare( $migrated_version, $current_version, '>=' ) ) {
			// We don't currently support downgrading migrations.
			_doing_it_wrong( esc_html( static::class . '::migrate' ), esc_html__( 'The last migrated version is more recent than the one currently actived. Has the plugin been downgraded?', 'wrd' ), '1.0.0' );
			return;
		}

		// Keys are already in version order.
		foreach ( $this->migrations as $version => $migrations ) {
			if ( version_compare( $version, $current_version, '>' ) ) {
				// Future migration? We will run this when our version catches up.
				continue;
			}

			if ( version_compare( $version, $migrated_version, '<' ) ) {
				// Past migration. Should have been run in a previous migration.
				continue;
			}

			// Any remaining migrations must be after our last migration,
			// and less than/equal to the current version.
			foreach ( $migrations as $migration ) {
				( new $migration() )->up();
			}
		}

		$this->set_migrated_version( $current_version );
	}
}
