<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddAttributes extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('image_files', function($table)
		{
		    $table->text('attributes');
		});
		DB::statement('ALTER TABLE `'. DB::getTablePrefix() .'image_files` CHANGE `attributes` `attributes` TEXT NOT NULL AFTER `size`');
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		$table->dropColumn('attributes');
	}

}
