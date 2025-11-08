<?php
/**
 * Contains the CLASS_NAME class.
 *
 * @since PACKAGE_VERSION
 *
 * @package PACKAGE_NAME
 */

namespace PACKAGE_NAMESPACE\Admin\Actions;

use WP_Error;
use Wrd\WpObjective\Admin\Action;

/**
 * The CLASS_NAME action.
 */
class CLASS_NAME extends Action {
	/**
	 * Check if the current user can undertake this action.
	 *
	 * @return WP_Error|bool
	 */
	public function permissions_callback(): WP_Error|bool {
		return current_user_can( 'manage_options' );
	}

	/**
	 * Get the URL to redirect to upon success.
	 *
	 * @param array $args The arguments passed to the action.
	 *
	 * @return string
	 */
	public function get_destination( array $args ): string {
		return admin_url();
	}

	/**
	 * Get the argument schema for the action.
	 *
	 * @return array
	 */
	public function get_arguments(): array {
		return array();
	}

	/**
	 * Execute the action.
	 *
	 * @param array $args The arguments passed to the action.
	 *
	 * @return WP_Error|null
	 */
	public function handle( array $args ): WP_Error|null {

		// Execute your action here.

		return new WP_Error( 'not_implemented', __( 'This action is not implemented yet.' ) );
	}
}
