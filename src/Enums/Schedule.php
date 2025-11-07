<?php
/**
 * Contains the Condition class.
 *
 * @package wrd/wp-objective
 */

namespace Wrd\WpObjective\Enums;

use DateInterval;
use RuntimeException;

enum Schedule {
	case Hourly;
	case TwiceDaily;
	case Daily;
	case Weekly;

	/**
	 * Get the duration string for the schedule.
	 *
	 * @throws RuntimeException For unknown schedules.
	 *
	 * @return string
	 */
	public function get_duration(): string {
		return match ( $this ) {
			Schedule::Hourly => 'PT1H',
			Schedule::TwiceDaily => 'PT12H',
			Schedule::Daily => 'P1D',
			Schedule::Weekly => 'P1W',
			default => throw new RuntimeException( 'Unknown Schedule' ),
		};
	}

	/**
	 * Get the schedule as a date interval.
	 *
	 * @return DateInterval
	 */
	public function get_interval(): DateInterval {
		return new DateInterval( $this->get_duration() );
	}

	/**
	 * Get the hook name for this schedule.
	 *
	 * @throws RuntimeException For unknown schedules.
	 *
	 * @return string
	 */
	public function get_hook(): string {
		return match ( $this ) {
			Schedule::Hourly => 'hourly',
			Schedule::TwiceDaily => 'twicedaily',
			Schedule::Daily => 'daily',
			Schedule::Weekly => 'weekly',
			default => throw new RuntimeException( 'Unknown Schedule' ),
		};
	}
}
