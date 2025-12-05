<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('my_organization', function (Blueprint $table) {
            $table->id();
            $table->string('organization')->unique();
            $table->unsignedBigInteger('user_id')->nullable(); 
            $table->string('vanity_name')->nullable();;
            $table->string('followers')->nullable();
            $table->string('localized_name'); 
            $table->json('groups')->nullable();
            $table->bigInteger('version_tag')->nullable();;
            $table->string('organization_type')->nullable();;
            $table->json('default_locale')->nullable();;
            $table->json('alternative_names')->nullable();
            $table->json('specialties')->nullable();
            $table->string('staff_count_range')->nullable();;
            $table->json('localized_specialties')->nullable();
            $table->json('industries')->nullable();
            $table->json('name')->nullable();;
            $table->string('primary_organization_type');
            $table->json('locations')->nullable();
            $table->string('linkedin_id');
            $table->json('page_statistics_by_seniority')->nullable();
            $table->json('page_statistics_by_country')->nullable();
            $table->json('page_statistics_by_industry')->nullable();
            $table->json('page_statistics_by_targeted_content')->nullable();
            $table->json('total_page_statistics')->nullable();
            $table->json('page_statistics_by_staff_count_range')->nullable();
            $table->json('page_statistics_by_function')->nullable();
            $table->json('page_statistics_by_region')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('my_organization');
    }
};
