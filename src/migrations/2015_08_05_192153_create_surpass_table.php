<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSurpassTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('image_files', function(Blueprint $table)
        {
            $table->increments('id');
            $table->string('dir');
            $table->string('filename');
            $table->string('extension');
            $table->integer('size');
            $table->text('attributes');
            $table->dateTime('created_at');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('image_files');
    }
}
