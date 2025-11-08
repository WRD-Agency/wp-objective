<?php
/**
 * Contains the CLASS_NAME class.
 *
 * @since PACKAGE_VERSION
 *
 * @package PACKAGE_NAME
 */

namespace PACKAGE_NAMESPACE\Admin\Notices;

use Wrd\WpObjective\Admin\Notice;
use Wrd\WpObjective\Support\Condition;

/**
 * The CLASS_NAME notice.
 */
class CLASS_NAME extends Notice {
	/**
	 * Apply conditions which must be met for this notice to show.
	 *
	 * @return Condition|bool
	 */
	public function get_conditions(): Condition|bool {
		return true;
	}

	/**
	 * Get the type of notice.
	 *
	 * @return string One of 'error', 'success', 'warning', 'info'.
	 */
	public function get_type(): string {
		return 'info';
	}

	/**
	 * Display the notice.
	 *
	 * @return void
	 */
	public function display(): void {
		esc_html_e( 'Hello world!' );
	}
}
