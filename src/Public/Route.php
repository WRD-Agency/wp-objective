<?php
/**
 * Contains the Route class.
 *
 * @package wrd/wp-objective
 */

namespace Wrd\WpObjective\Public;

use Wrd\WpObjective\Foundation\Service_Provider;

/**
 * Class for managing a Route.
 */
abstract class Route extends Service_Provider {
	/**
	 * Get the rewrite tags for this route.
	 *
	 * @return array<string, string>
	 */
	public function get_tags(): array {
		return array();
	}

	/**
	 * Get the path for this route.
	 *
	 * Template tags are dynamically swaped in for the regex.
	 *
	 * @return string
	 */
	abstract public function get_path(): string;

	/**
	 * Get the name of this route.
	 *
	 * @return string
	 */
	public function get_name(): string {
		return strtolower( str_replace( '\\', '_', static::class ) );
	}

	/**
	 * Get the priority.
	 *
	 * @return "top"|"bottom"
	 */
	public function get_priority(): string {
		return 'top';
	}

	/**
	 * Display the route.
	 *
	 * @return void
	 */
	abstract public function handle(): void;

	/**
	 * Get the regex for this route.
	 *
	 * Auto-generated from the path.
	 *
	 * @return string
	 */
	public function get_regex(): string {
		$path = $this->get_path();
		$path = trim( $path, '\/' );
		$path = '^' . $path . '[/]?$';

		$tags = $this->get_tags();

		foreach ( $tags as $name => $regex ) {
			$path = str_replace( $name, $regex, $path );
		}

		return $path;
	}

	/**
	 * Get the query for this route.
	 *
	 * Auto-generated from the path.
	 *
	 * @return string
	 */
	public function get_query(): string {
		$path  = $this->get_path();
		$tags  = $this->get_tags();
		$query = 'index.php?objective_route=' . $this->get_name();

		// In WordPress, the $matches index starts at 1.
		$i = 1;
		foreach ( $tags as $name => $regex ) {
			if ( ! str_contains( $path, $name ) ) {
				continue;
			}

			$query_name = str_replace( '%', '', $name );
			$query     .= "&$query_name=\$matches[$i]";

			++$i;
		}

		return $query;
	}

	/**
	 * Runs on the 'init' hook.
	 *
	 * @return void
	 */
	public function init(): void {
		foreach ( $this->get_tags() as $name => $regex ) {
			add_rewrite_tag( $name, $regex );
		}
		add_rewrite_tag( '%objective_route%', '([a-z0-9_]+)' );
		add_rewrite_rule( $this->get_regex(), $this->get_query(), $this->get_priority() );

		add_action(
			'wp',
			function () {
				if ( ! is_admin() && get_query_var( 'objective_route' ) === $this->get_name() ) {
					$this->handle();
				}
			}
		);
	}
}
