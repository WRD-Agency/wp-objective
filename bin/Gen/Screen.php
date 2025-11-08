<?php
/**
 * Contains the CLASS_NAME class.
 *
 * @since PACKAGE_VERSION
 *
 * @package PACKAGE_NAME
 */

namespace PACKAGE_NAMESPACE\Admin\Screens;

use Wrd\WpObjective\Admin\Screen;

/**
 * The CLASS_NAME screen.
 */
class CLASS_NAME extends Screen {
	/**
	 * Get the parent this page should appear under.
	 *
	 * @return ?string
	 */
	public function get_parent(): ?string {
		return null;
	}

	/**
	 * Get the title of the screen.
	 *
	 * @return string
	 */
	public function get_title(): string {
		return __( 'My Screen' );
	}

	/**
	 * Get the icon for this page.
	 *
	 * @return string
	 */
	public function get_icon(): string {
		return 'dashicons-admin-generic';
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
	public function display(): void {
		esc_html_e( 'Hello world!' );
	}
}
