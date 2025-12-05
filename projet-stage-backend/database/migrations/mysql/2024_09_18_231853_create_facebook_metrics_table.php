<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateFacebookMetricsTable extends Migration
{
    public function up()
    {
        Schema::create('facebook_metrics', function (Blueprint $table) {
            $table->id();  
            $table->string('name');  
            $table->string('title');  
            $table->text('description'); 
            $table->string('period');  
            $table->string('fb_id')->unique(); 
            $table->integer('value');  
            $table->timestamp('end_time');  
            $table->timestamps();  
        });
    }

    public function down()
    {
        Schema::dropIfExists('facebook_metrics');
    }
}
