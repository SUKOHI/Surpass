<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class RenameImageFilesToThumbnailFiles extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
		Schema::rename('image_files', 'thumbnail_files');	// Rename from `image_files` to `thumbnail_files`
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
		Schema::rename('thumbnail_files', 'image_files');	// Rename from `thumbnail_files` to `image_files`
    }
}
