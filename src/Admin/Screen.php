<?php
/**
 * Contains the Screen class.
 *
 * @package wrd/wp-objective
 */

namespace Wrd\WpObjective\Admin;

use Wrd\WpObjective\Foundation\Service_Provider;

/**
 * Class for adding a custom screen to the admin area.
 */
abstract class Screen extends Service_Provider {
	/**
	 * Get the parent this page should appear under.
	 *
	 * @return ?string
	 */
	public function get_parent(): ?string {
		return null;
	}

	/**
	 * Check if this screen is a sub-page.
	 *
	 * @return bool
	 */
	public function has_parent(): bool {
		return ! is_null( $this->get_parent() );
	}

	/**
	 * Get the title of the screen.
	 *
	 * @return string
	 */
	abstract public function get_title(): string;

	/**
	 * Get the icon for this page.
	 *
	 * @return string
	 */
	public function get_icon(): string {
		return 'dashicons-admin-generic';
	}

	/**
	 * Get the position in the menu for this page.
	 *
	 * @return string
	 */
	public function get_position(): string {
		return $this->has_parent() ? 15 : 22.5;
	}

	/**
	 * Get the slug for this page.
	 *
	 * @return string
	 */
	public function get_slug(): string {
		return static::class;
	}

	/**
	 * Get the URL to this screen.
	 *
	 * @return string
	 */
	public function get_url(): string {
		return admin_url( 'admin.php?page=' . $this->get_slug() );
	}

	/**
	 * Get the hook suffix for this page.
	 *
	 * @return string
	 */
	public function get_hook_suffix(): string {
		return get_plugin_page_hookname( $this->get_slug(), $this->get_parent() ?? '' );
	}

	/**
	 * Get the capability required to view this page.
	 *
	 * @return string
	 */
	public function get_capability(): string {
		return 'manage_options';
	}

	/**
	 * Get the menu title.
	 *
	 * @return string
	 */
	public function get_menu_title(): string {
		return $this->get_title();
	}

	/**
	 * Run in the 'init' hook.
	 *
	 * @return void
	 */
	public function init(): void {
		add_action(
			'admin_menu',
			function () {
				if ( $this->has_parent() ) {
					add_submenu_page(
						$this->get_parent(),
						$this->get_title(),
						$this->get_menu_title(),
						$this->get_capability(),
						$this->get_slug(),
						array( $this, 'display' ),
						$this->get_position(),
					);
				} else {
					add_menu_page(
						$this->get_title(),
						$this->get_menu_title(),
						$this->get_capability(),
						$this->get_slug(),
						array( $this, 'display' ),
						$this->get_icon(),
						$this->get_position(),
					);
				}
			}
		);

		add_action(
			'admin_enqueue_scripts',
			function ( string $hook_suffix ) {
				if ( $this->get_hook_suffix() === $hook_suffix ) {
					$this->enqueue();
				}
			}
		);
	}

	/**
	 * A space to enqueue assets for this screen.
	 *
	 * @return void
	 */
	public function enqueue(): void {
		// This page left intentionally blank.
	}

	/**
	 * Display the screen.
	 *
	 * @return void
	 */
	abstract public function display(): void;
}
