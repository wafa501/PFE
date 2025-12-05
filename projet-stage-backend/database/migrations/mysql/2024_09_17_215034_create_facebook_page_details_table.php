<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateFacebookPageDetailsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('facebook_page_details', function (Blueprint $table) {
            $table->id();
            $table->string('fb_id')->unique(); 
            $table->string('name');
            $table->integer('fan_count')->nullable();
            $table->text('about')->nullable();
            $table->string('category')->nullable();
            $table->string('website')->nullable();
            $table->string('phone')->nullable();
            $table->string('price_range')->nullable();
            $table->text('mission')->nullable();
            $table->json('products')->nullable(); 
            $table->json('hours')->nullable(); 
            $table->json('location')->nullable(); 
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
        Schema::dropIfExists('facebook_page_details');
    }
}
