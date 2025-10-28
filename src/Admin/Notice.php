<?php
/**
 * Contains the Notice class.
 *
 * @package wrd/wp-objective
 */

namespace Wrd\WpObjective\Admin;

use Wrd\WpObjective\Foundation\Service_Provider;
use Wrd\WpObjective\Support\Condition;

/**
 * Class for adding notices to admin screens.
 */
abstract class Notice extends Service_Provider {
	/**
	 * Apply conditions which must be met for this notice to show.
	 *
	 * @return Condition|bool
	 */
	public function get_conditions(): Condition|bool {
		return true;
	}

	/**
	 * Get the ID of the notice.
	 *
	 * @return string
	 */
	public function get_id(): string {
		return static::class;
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
	abstract public function display(): void;

	/**
	 * Get the contents of '$this->display' as a string, rather than outputting them.
	 *
	 * @return string
	 */
	public function get_display(): string {
		ob_start();
		$this->display();
		return ob_get_clean();
	}

	/**
	 * Boot the notice.
	 *
	 * @return void
	 */
	public function boot(): void {
		add_action( 'admin_notices', array( $this, 'render_callback' ) );
	}

	/**
	 * Used to render the metabox.
	 *
	 * @return void
	 */
	public function render_callback(): void {
		$condition = $this->get_conditions();

		if ( ! Condition::check( $condition, 'all' ) ) {
			return;
		}

		wp_admin_notice(
			$this->get_display(),
			array(
				'type' => $this->get_type(),
				'id'   => $this->get_id(),
			)
		);
	}
}
