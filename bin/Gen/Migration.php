<?php
/**
 * Contains the CLASS_NAME class.
 *
 * @since PACKAGE_VERSION
 *
 * @package PACKAGE_NAME
 */

namespace PACKAGE_NAMESPACE\Migrations;

use Wrd\WpObjective\Foundation\Migrate\Migration;

/**
 * The CLASS_NAME migration.
 */
class CLASS_NAME extends Migration {
	/**
	 * Get version of the plugin this migration sets up for.
	 *
	 * @return string
	 */
	public function get_version(): string {
		return 'PACKAGE_VERSION';
	}

	/**
	 * Run the migration.
	 *
	 * @return void
	 */
	public function up(): void {
		// Write your migration here.
	}
}
