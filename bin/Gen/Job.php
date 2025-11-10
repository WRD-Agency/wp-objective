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
	 * Execute the job.
	 *
	 * @return void
	 */
	public function handle(): void {
		// Execute your job here.
	}
}
