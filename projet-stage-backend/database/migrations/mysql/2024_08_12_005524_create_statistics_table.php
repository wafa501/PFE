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
            $table->string('organization')->unique();
            $table->json('page_statistics_by_seniority')->nullable();
            $table->json('page_statistics_by_country')->nullable();
            $table->json('page_statistics_by_industry')->nullable();
            $table->json('page_statistics_by_targeted_content')->nullable();
            $table->json('total_page_statistics')->nullable();
            $table->json('page_statistics_by_staff_count_range')->nullable();
            $table->json('page_statistics_by_function')->nullable();
            $table->json('page_statistics_by_region')->nullable();
            $table->integer('uniqueImpressionsCount')->default(0);
            $table->integer('shareCount')->default(0);
            $table->integer('shareMentionsCount')->default(0);
            $table->integer('engagement')->default(0);
            $table->integer('clickCount')->default(0);
            $table->integer('likeCount')->default(0);
            $table->integer('impressionCount')->default(0);
            $table->integer('commentMentionsCount')->default(0);
            $table->integer('commentCount')->default(0);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('statistics');
    }
}
