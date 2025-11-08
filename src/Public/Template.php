<?php
/**
 * Contains the Template class.
 *
 * @package wrd/wp-objective
 */

namespace Wrd\WpObjective\Public;

use Wrd\WpObjective\Foundation\Service_Provider;
use Wrd\WpObjective\Support\Condition;

/**
 * Class for managing a template.
 */
abstract class Template extends Service_Provider {
	/**
	 * Apply conditions which must be met for this notice to show.
	 *
	 * @return Condition|bool
	 */
	abstract public function get_conditions(): Condition|bool;

	/**
	 * Get the files which will be searched for to be the template.
	 *
	 * @return string[]
	 */
	abstract public function get_files(): array;

	/**
	 * Boot the template.
	 *
	 * @return void
	 */
	public function boot(): void {
		add_filter( 'template_include', array( $this, 'include_callback' ), 10, 1 );
	}

	/**
	 * Used to include the template.
	 *
	 * @param string $current_template The current selected template.
	 *
	 * @return string
	 */
	public function include_callback( string $current_template ): string {
		$condition = $this->get_conditions();

		if ( ! Condition::check( $condition, 'all' ) ) {
			return $current_template;
		}

		/**
		 * We check if the exact files given exist.
		 * This allows us to use files from plugins.
		 *
		 * If the consumer wants to use theme files,
		 * they can use 'locate_template' in their 'get_files' function.
		 */
		foreach ( $this->get_files() as $file ) {
			if ( file_exists( $file ) ) {
				return $file;
			}
		}

		return $current_template;
	}
}
