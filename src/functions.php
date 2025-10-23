<?php
/**
 * Contains functions.
 *
 * @package wrd/wp-objective
 */

namespace Wrd\WpObjective;

use Wrd\WpObjective\Foundation\Container;
use Wrd\WpObjective\Foundation\Migrate\Migration_Manager;

/**
 * Get the global service container instance.
 *
 * @return Container
 */
function container(): Container {
	return Container::get_instance();
}

/**
 * Load providers & bindings from a configuration array.
 *
 * @param array $config The config array.
 *
 * @return Container
 */
function config( array $config ): Container {
	$container = Container::get_instance();

	$bindings   = array_key_exists( 'bindings', $config ) ? $config['bindings'] : array();
	$providers  = array_key_exists( 'providers', $config ) ? $config['providers'] : array();
	$migrations = array_key_exists( 'migrations', $config ) ? $config['migrations'] : array();

	foreach ( $bindings as $abstract => $concrete ) {
		$container->bind( $abstract, $concrete );
	}

	foreach ( $providers as $provider ) {
		$container->provide( $provider );
	}

	foreach ( $migrations as $migration ) {
		$container->get( Migration_Manager::class )->add_migration( $migration );
	}

	return $container;
}
