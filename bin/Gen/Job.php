<?php
/**
 * Contains the CLASS_NAME class.
 *
 * @since PACKAGE_VERSION
 *
 * @package PACKAGE_NAME
 */

namespace PACKAGE_NAMESPACE\Admin\Jobs;

use WP_Error;
use Wrd\WpObjective\Admin\Job;
use Wrd\WpObjective\Enums\Schedule;

/**
 * The CLASS_NAME job.
 */
class CLASS_NAME extends Job {
	/**
	 * Get how often the task will run.
	 *
	 * If using wp-cron, the duration is the minimum gap between jobs but may not be kept to.
	 *
	 * @return Schedule
	 */
	public function every(): Schedule {
		return Schedule::Daily;
	}

	/**
	 * Execute the action.
	 *
	 * @param array $args The arguments passed to the action.
	 *
	 * @return WP_Error|null
	 */
	public function handle( array $args ): WP_Error|null {

		// Execute your job here.

		return new WP_Error( 'not_implemented', __( 'This action is not implemented yet.' ) );
	}
}
