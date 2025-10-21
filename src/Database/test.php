<?php
/**
 * Contains the Migration_Manager class.
 *
 * @package wrd/wp-objective
 */

namespace Wrd\WpObjective\Foundation\Migrate;

use Wrd\WpObjective\Database\Blueprint;
use Wrd\WpObjective\Database\Column_Definition;

/**
 * Represents a migration.
 */
class My_Migration extends Migration {


	/**
	 * Get version of the plugin this migration sets up for.
	 *
	 * @return string
	 */
	public function get_version(): string {
		return '1.0.0';
	}

	/**
	 * Run the migration.
	 *
	 * @return void
	 */
	public function up(): void {
		// TODO: Facades?

		// Create a table.
		Database::create(
			function( Blueprint $table ) {
				$table->name( 'products' );
				$table->id( 'id' );
				$table->text( 'title' );
			}
		);

		// Alter a table.
		Database::alter(
			'products' function( Blueprint $table ) {
				$table->add_column()->text( 'colour' );

				$table->alter_column()->decimal( 'price', 16, 2 );

				$table->rename_column( 'title', 'product_title' );

				$table->drop_column( 'stock' );
			}
		);

		Database::rename( 'products', 'product' );

		// Drop a table.
		Database::drop( 'products' );

		// Query the rows.
		$rows_collection = Database::query( 'products' )->where( 'id', '>=', '19' )->get();

		// Create a row.
		$row = Database::insert(
			'products',
			array(
				'title' => 'My New Product',
			)
		);

		// Update a row.
		$row->update(
			array(
				'title' => 'My New Product',
			)
		);

		// Delete a row.
		$row->delete();

		// Bulk update rows without querying.
		Database::query( 'products' )->where( 'id', '>=', '19' )->update(
			array(
				'title' => 'Untitled Product',
			)
		);

		// Delete a row without querying.
		Database::query( 'products' )->where( 'id', '>=', '19' )->delete();
	}
}
