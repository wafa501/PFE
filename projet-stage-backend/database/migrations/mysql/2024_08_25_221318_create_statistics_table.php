<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateStatisticsTable extends Migration
{
    public function up()
    {
        Schema::create('statistics', function (Blueprint $table) {
            $table->id();
            $table->string('organization');
            $table->integer('year');
            $table->json('monthly_stats'); 
            $table->timestamps();

            $table->unique(['organization', 'year']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('statistics');
    }
}
