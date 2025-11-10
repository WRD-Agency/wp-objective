<?php
/**
 * Contains the Action class.
 *
 * @package wrd/wp-objective
 */

namespace Wrd\WpObjective\Admin;

use WP_Error;
use Wrd\WpObjective\Enums\Schedule;
use Wrd\WpObjective\Foundation\Service_Provider;
use Wrd\WpObjective\Log\Log_Manager;

/**
 * Base class for actions.
 */
abstract class Job extends Service_Provider {
	/**
	 * The logger.
	 *
	 * @var Log_Manager
	 */
	protected Log_Manager $logger;

	/**
	 * Create an instance.
	 *
	 * @param Log_Manager $logger The logger to use.
	 */
	public function __construct( Log_Manager $logger ) {
		$this->logger = $logger;
	}

	/**
	 * Get how often the task will run.
	 *
	 * If using wp-cron, the duration is the minimum gap between jobs but may not be kept to.
	 *
	 * @return Schedule
	 */
	abstract public function every(): Schedule;

	/**
	 * Execute the job.
	 *
	 * @return void
	 */
	abstract public function handle(): void;

	/**
	 * Boot the job.
	 *
	 * @return void
	 */
	public function boot(): void {
		add_action( $this->get_hook(), array( $this, 'handle' ) );

		if ( ! wp_next_scheduled( $this->get_hook() ) ) {
			wp_schedule_event( time(), $this->every()->get_hook(), $this->get_hook() );
		}
	}

	/**
	 * Get the hook name for wp-cron.
	 *
	 * @return string
	 */
	public function get_hook(): string {
		return static::class;
	}
}
