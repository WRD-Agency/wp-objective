<?php
/**
 * Contains the Log_Message class.
 *
 * @package wrd/wp-objective
 */

namespace Wrd\WpObjective\Log;

use DateTime;
use JsonSerializable;
use WP_Error;

/**
 * Class for holding log data.
 */
class Log_Message implements JsonSerializable {
	/**
	 * The log message ID.
	 *
	 * @var string
	 */
	private string $id;

	/**
	 * The log level.
	 *
	 * @var Level
	 */
	private Level $level;

	/**
	 * The log message.
	 *
	 * @var string
	 */
	private string $message;

	/**
	 * An optional WordPress post ID that this message targets.
	 *
	 * @var ?int
	 */
	private ?int $target;

	/**
	 * The log data.
	 *
	 * @var array
	 */
	private array $data;

	/**
	 * The log timestamp.
	 *
	 * @var int
	 */
	private int $timestamp;

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
	 */
	public function __construct( ?string $id = null, Level $level = Level::DEBUG, string $message = '', ?int $target = null, array $data = array(), int $timestamp = -1 ) {
		if ( $timestamp < 0 ) {
			$timestamp = time();
		}

		$this->id        = $id ?? uniqid( '', true );
		$this->level     = $level;
		$this->message   = $message;
		$this->target    = $target;
		$this->data      = $data;
		$this->timestamp = $timestamp;
	}

	/**
	 * Get the ID.
	 *
	 * @return string
	 */
	public function get_id(): string {
		return $this->id;
	}

	/**
	 * Get the log level.
	 *
	 * @return Level
	 */
	public function get_level(): Level {
		return $this->level;
	}

	/**
	 * Get the log message.
	 *
	 * @return string
	 */
	public function get_message(): string {
		return $this->message;
	}

	/**
	 * Get the log target's ID.
	 *
	 * @return ?int
	 */
	public function get_target_id(): ?int {
		return $this->target;
	}

	/**
	 * Get the log data.
	 *
	 * @return array
	 */
	public function get_data(): array {
		return $this->data;
	}

	/**
	 * Get the log timestamp.
	 *
	 * @return int
	 */
	public function get_timestamp(): int {
		return $this->timestamp;
	}

	/**
	 * Get the log timestamp as a Date Time object.
	 *
	 * @return DateTime
	 */
	public function get_datetime(): DateTime {
		return new DateTime( '@' . $this->get_timestamp() );
	}

	/**
	 * Serialize to JSON.
	 *
	 * @return array
	 */
	public function jsonSerialize(): array {
		return array(
			'id'        => $this->get_id(),
			'level'     => $this->get_level()->value,
			'message'   => $this->get_message(),
			'target'    => $this->get_target_id(),
			'data'      => $this->get_data(),
			'timestamp' => $this->get_timestamp(),
		);
	}

	/**
	 * Convert the JSON serialization back into an object.
	 *
	 * @param array $data The data to deserialize.
	 *
	 * @return Log_Message
	 */
	public static function jsonDeserialize( array $data ): Log_Message {
		return new Log_Message(
			id: $data['id'],
			level: Level::from( $data['level'] ),
			message: $data['message'],
			target: $data['target'],
			data: $data['data'],
			timestamp: $data['timestamp'],
		);
	}

	/**
	 * Create a log message from an error.
	 *
	 * @param WP_Error $error The error.
	 *
	 * @return Log_Message
	 */
	public static function from_wp_error( WP_Error $error ): Log_Message {
		return new Log_Message(
			level: Level::ERROR,
			message: $error->get_error_message(),
			data: $error->get_error_data() ?? array(),
		);
	}
}
