<?php
/**
 * Contains the Migration_Manager class.
 *
 * @package wrd/wp-objective
 */

namespace Wrd\WpObjective\Foundation\Migrate;

use Wrd\WpObjective\Database\Query\Query;
use Wrd\WpObjective\Database\Query\Where_Group;
use Wrd\WpObjective\Database\Schema\Blueprint;
use Wrd\WpObjective\Support\Facades\Database;

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
		// Create a table.
		Database::create_table(
			'products',
			function( Blueprint $table ) {
				$table->id( 'id' );
				$table->text( 'title' );
			}
		);

		// Alter a table.
		Database::alter_table(
			'products',
			function( Blueprint $table ) {
				$table->text( 'colour' );

				$table->alter( 'colour' )->decimal( 'price', 16, 2 );

				$table->rename( 'title', 'product_title' );

				$table->drop( 'stock' );
			}
		);

		Database::rename_table( 'products', 'product' );

		// Drop a table.
		Database::drop_table( 'products' );

		// Query the rows.
		$rows_collection = Database::find( 'products' )->where( 'id', '>=', '19' )->get();

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

		Database::find( 'products' )
			->where( 'votes', '>', 100 )
			->or_where(
				function( Query $query ) {
					$query
					->where( 'name', '=', 'Abigail' )
					->where( 'votes', '>', 50 );
				}
			)
			->order_by( 'id', 'ASC' )
			->limit( 50 )
			->offset( 12 )
			->get();

		// SELECT * FROM users WHERE votes > 100 OR (name = 'Abigail' and votes > 50) ORDER BY id ASC LIMIT 50 OFFSET 12.

		// Bulk update rows without querying.
		Database::find( 'products' )->where( 'id', '>=', '19' )->update(
			array(
				'title' => 'Untitled Product',
			)
		);

		// Delete a row without querying.
		Database::find( 'products' )->where( 'id', '>=', '19' )->delete();
	}
}
