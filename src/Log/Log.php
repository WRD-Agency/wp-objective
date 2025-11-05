<?php
/**
 * Contains the Log class.
 *
 * @package wrd/wp-objective
 */

namespace Wrd\WpObjective\Log;

use JsonSerializable;
use Wrd\WpObjective\Support\Collection;
use Wrd\WpObjective\Support\Server;

/**
 * Class for holding log data.
 */
class Log implements JsonSerializable {
	/**
	 * The log ID.
	 *
	 * @var string
	 */
	private string $id;

	/**
	 * The log messages
	 *
	 * @var Log_Message[]
	 */
	private array $messages = array();

	/**
	 * Targets IDs, in addition to those in log messages.
	 *
	 * @var int[]
	 */
	private array $targets = array();

	/**
	 * The log's overall status.
	 *
	 * @var Status
	 */
	private Status $status;

	/**
	 * The user's IP.
	 *
	 * @var string
	 */
	private string $user_ip;

	/**
	 * The user's user_agent.
	 *
	 * @var string
	 */
	private string $user_agent;

	/**
	 * The user's WordPress ID.
	 *
	 * @var string
	 */
	private int $user_id;

	/**
	 * The requested URL.
	 *
	 * @var string
	 */
	private string $url;

	/**
	 * Create a new log.
	 *
	 * @param string        $id The log ID.
	 *
	 * @param Log_Message[] $messages The log messages.
	 *
	 * @param int[]      $targets Targets IDs, in addition to those in log messages.
	 *
	 * @param Status        $status The log's overall status.
	 *
	 * @param ?string       $user_ip The user's IP.
	 *
	 * @param ?string       $user_agent The user's user_agent.
	 *
	 * @param ?int          $user_id The user's WordPress ID.
	 *
	 * @param ?string       $url The requested URL.
	 */
	public function __construct( ?string $id = null, array $messages = array(), array $targets = array(), Status $status = Status::NONE, ?string $user_ip = null, ?string $user_agent = null, ?int $user_id = null, ?string $url = null ) {
		$this->id         = $id ?? uniqid( '', true );
		$this->messages   = $messages;
		$this->targets    = $targets;
		$this->status     = $status;
		$this->user_ip    = $user_ip ?? Server::get_ip();
		$this->user_agent = $user_agent ?? Server::get_user_agent();
		$this->user_id    = $user_id ?? get_current_user_id();
		$this->url        = $url ?? Server::get_request_url();
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
	 * Add a log message.
	 *
	 * @param Log_Message $message The message to add.
	 *
	 * @return void
	 */
	public function append( Log_Message $message ): void {
		$this->messages[] = $message;

		if ( $message->get_level() === Level::FATAL ) {
			// We inherit fatal status if it occurs.
			// Unlike errors, there can be no way to recover.
			$this->status = Status::FATAL;
		}
	}

	/**
	 * Add a target to the log.
	 *
	 * @param int $id The ID to target.
	 *
	 * @return void
	 */
	public function target( int $id ): void {
		$this->targets[] = $id;
	}

	/**
	 * Get all log messages.
	 *
	 * @param ?Level $level Optional. Filters the messages by log level.
	 *
	 * @return Collection<Log_Message>
	 */
	public function get_messages( ?Level $level = null ): Collection {
		$messages = Collection::from( $this->messages );

		if ( ! is_null( $level ) ) {
			return $messages->filter(
				fn( $message ) => $message->get_level() === $level
			);
		}

		return $messages;
	}

	/**
	 * Get all log message targets.
	 *
	 * @return Collection<int>
	 */
	public function get_targets(): Collection {
		return $this
			->get_messages()
			->map( fn( Log_Message $message ) => $message->get_target_id() )
			->add( ...$this->targets )
			->unique()
			->filter();
	}

	/**
	 * The log's overall status.
	 *
	 * @return Status
	 */
	public function get_status(): Status {
		return $this->status;
	}

	/**
	 * Set the log's overall status.
	 *
	 * @param Status $status The new status.
	 *
	 * @return void
	 */
	public function set_status( Status $status ): void {
		$this->status = $status;
	}

	/**
	 * Get the user's IP.
	 *
	 * @return string
	 */
	public function get_user_ip(): string {
		return $this->user_ip;
	}

	/**
	 * Get the user's user_agent.
	 *
	 * @return string
	 */
	public function get_user_agent(): string {
		return $this->user_agent;
	}

	/**
	 * Get the user's WordPress ID.
	 *
	 * @return string
	 */
	public function get_user_id(): int {
		return $this->user_id;
	}

	/**
	 * Get the requested URL.
	 *
	 * @return string
	 */
	public function get_url(): string {
		return $this->url;
	}

	/**
	 * Serialize to JSON.
	 *
	 * @return array
	 */
	public function jsonSerialize(): array {
		return array(
			'id'         => $this->get_id(),
			'messages'   => $this->get_messages(),
			'status'     => $this->get_status()->value,
			'user_ip'    => $this->get_user_ip(),
			'user_agent' => $this->get_user_agent(),
			'user_id'    => $this->get_user_id(),
			'url'        => $this->get_url(),
		);
	}

	/**
	 * Convert the JSON serialization back into an object.
	 *
	 * @param array $data The data to deserialize.
	 *
	 * @return Log
	 */
	public function jsonDeserialize( array $data ): Log {
		return new Log(
			id: $data['id'],
			messages: $data['messages'],
			status: Status::from( $data['status'] ),
			user_ip: $data['user_ip'],
			user_agent: $data['user_agent'],
			user_id: $data['user_id'],
			url: $data['url'],
		);
	}
}
