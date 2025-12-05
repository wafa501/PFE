<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('facebook_page_posts', function (Blueprint $table) {
            $table->id();
            $table->string('fb_id')->unique();
            $table->text('description')->nullable();
            $table->longText('attachments')->nullable();
            $table->timestamp('created_time')->nullable();
            $table->timestamp('updated_time')->nullable();
            $table->string('status_type')->nullable();
            $table->text('privacy')->nullable();
            $table->longText('pictures')->nullable();
            $table->longText('videos')->nullable();
            $table->timestamps();

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('facebook_page_posts');
    }
}
;
