<?php
/**
 * Contains the Template class.
 *
 * @package wrd/wp-objective
 */

namespace Wrd\WpObjective\Public;

use Exception;
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
	 * Get the template's file.
	 *
	 * @return string
	 *
	 * @throws Exception If not file is foumd.
	 */
	public function get_file(): string {
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

		throw new Exception( 'No file exists for template.' );
	}

	/**
	 * Get the title for the page.
	 *
	 * @return string
	 */
	abstract public function get_title(): string;

	/**
	 * Run in the 'init' hook.
	 *
	 * @return void
	 */
	public function init(): void {
		// Wait until the query is parsed as our conditions may rely on it.
		add_action(
			'wp',
			function () {
				if ( Condition::check( $this->get_conditions(), 'all' ) ) {
					$this->include();
				}
			}
		);

		add_filter(
			'status_header',
			function ( string $status_header, int $code, string $description, string $protocol ) {
				if ( Condition::check( $this->get_conditions(), 'all' ) ) {
					// Prevent 404.
					$code          = 200;
					$status_header = "$protocol $code $description";
				}

				return $status_header;
			},
			10,
			4
		);

		add_filter(
			'pre_handle_404',
			function ( $value ) {
				if ( Condition::check( $this->get_conditions(), 'all' ) ) {
					// Prevent 404.
					return true;
				}

				return $value;
			}
		);
	}

	/**
	 * Include the template directly.
	 *
	 * @return void
	 */
	public function include(): void {
		add_filter( 'template_include', array( $this, 'get_file' ), 10, 0 );
		add_filter( 'document_title_parts', array( $this, 'title_callback' ) );
	}

	/**
	 * Filters the parts of the document title.
	 *
	 * @param array $title The document title parts.
	 *
	 * @return array
	 */
	public function title_callback( $title ) {
		$condition = $this->get_conditions();

		if ( ! Condition::check( $condition, 'all' ) ) {
			return $title;
		}

		$title['title'] = $this->get_title();

		return $title;
	}
}
