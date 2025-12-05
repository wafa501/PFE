<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateFacebookPageFanHistoryTable extends Migration
{
    public function up()
    {
        Schema::create('facebook_page_fan_history', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('page_id');
            $table->integer('fan_count');
            $table->timestamp('checked_at');
            $table->timestamps(); 

            $table->foreign('page_id')->references('id')->on('facebook_page_details')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('facebook_page_fan_history');
    }
}
