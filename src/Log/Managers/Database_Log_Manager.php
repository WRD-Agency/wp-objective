<?php
/**
 * Contains the Database_Log_Manager class.
 *
 * @package wrd/wp-objective
 */

namespace Wrd\WpObjective\Log\Managers;

use Wrd\WpObjective\Log\Log;
use Wrd\WpObjective\Log\Log_Manager;

/**
 * Class for managing logs with the database.
 */
class Database_Log_Manager extends Log_Manager {
	/**
	 * Save a log.
	 *
	 * @param Log $log The log to save.
	 *
	 * @return bool
	 */
	public function save( Log $log ): bool {
		// TODO: Implement Database_Log_Manager.
		trigger_error( 'Not implemented', E_USER_WARNING );

		return true;
	}
}
