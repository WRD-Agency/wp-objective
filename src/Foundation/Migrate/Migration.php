<?php
/**
 * Contains the Migration class.
 *
 * @package wrd/wp-objective
 */

namespace Wrd\WpObjective\Foundation\Migrate;

use Wrd\WpObjective\Database\Database_Manager;

/**
 * Represents a migration.
 */
abstract class Migration {
	/**
	 * Get version of the plugin this migration sets up for.
	 *
	 * @return string
	 */
	abstract public function get_version(): string;

	/**
	 * Run the migration.
	 *
	 * @return void
	 */
	abstract public function up(): void;
}
