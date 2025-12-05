<?php
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterPostsTable extends Migration
{
    public function up()
    {
        Schema::table('posts', function (Blueprint $table) {
            $table->MEDIUMTEXT('image_url')->change(); 
        });
    }

    public function down()
    {
        Schema::table('posts', function (Blueprint $table) {
            $table->string('image_url', 255)->change(); 
        });
    }
}
