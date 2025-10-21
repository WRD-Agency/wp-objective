<?php
/**
 * Defines the API contract for classes that can be converted to an array.
 *
 * @package wrd/wp-objective
 */

namespace Wrd\WpObjective\Contracts;

interface Apiable {
	/**
	 * Converts the object to an array representation.
	 *
	 * @return array The array representation of the object.
	 */
	public function to_api(): array;
}
