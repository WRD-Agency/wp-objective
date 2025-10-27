<?php

use Wrd\WpObjective\Database\Database_Manager;
use Wrd\WpObjective\Database\Schema\Blueprint;

beforeEach(function(){
	$this->database = new Database_Manager();
});

test( "Basic database creation", function(){
	$sql = $this->database->get_create_table_sql( "products", function( Blueprint $table ){
		$table->id('id');
		$table->text('name');
	});

	expect( $sql )->toBe( "CREATE TABLE products (" . PHP_EOL . "id int(4) NOT NULL AUTO_INCREMENT," . PHP_EOL . "name text NOT NULL," . PHP_EOL . "PRIMARY KEY  (id)" . PHP_EOL . ") utf8mb4;" );
});

