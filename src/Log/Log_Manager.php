<?php
/**
 * Contains the Log_Manager class.
 *
 * @package wrd/wp-objective
 */

namespace Wrd\WpObjective\Log;

use WP_Error;
use Wrd\WpObjective\Foundation\Service_Provider;

/**
 * Class for managing logs.
 */
class Log_Manager extends Service_Provider {
	/**
	 * The current working log.
	 *
	 * @var ?Log
	 */
	protected ?Log $current_log = null;

	/**
	 * Get the log of the current request.
	 *
	 * @return Log
	 */
	public function get_current_log(): Log {
		if ( is_null( $this->current_log ) ) {
			$this->current_log = new Log();
		}

		return $this->current_log;
	}

	/**
	 * Create a log message.
	 *
	 * @param ?string $id The log line ID.
	 *
	 * @param Level   $level The log level.
	 *
	 * @param string  $message The log message.
	 *
	 * @param ?int    $target The WordPress post ID being targetted.
	 *
	 * @param array   $data The log data.
	 *
	 * @param int     $timestamp The log timestamp.
	 *
	 * @return void
	 */
	public function add( ?string $id = null, Level $level = Level::DEBUG, string $message = '', ?int $target = null, array $data = array(), int $timestamp = -1 ): void {
		$this->get_current_log()->append( new Log_Message( $id, $level, $message, $target, $data ) );
	}

	/**
	 * Add a log message from an error.
	 *
	 * @param WP_Error $error The error.
	 *
	 * @return void
	 */
	public function add_wp_error( WP_Error $error ): void {
		$this->get_current_log()->append( Log_Message::from_wp_error( $error ) );
	}

	// TODO: Querying Log_Managers.
}
