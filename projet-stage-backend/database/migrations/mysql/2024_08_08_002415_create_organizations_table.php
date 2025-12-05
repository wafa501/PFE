<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('organizations', function (Blueprint $table) {
            $table->id();
            $table->string('vanity_name');
            $table->string('followers')->nullable();
            $table->string('localized_name'); 
            $table->json('groups')->nullable();
            $table->bigInteger('version_tag');
            $table->string('organization_type');
            $table->json('default_locale');
            $table->json('alternative_names')->nullable();
            $table->json('specialties')->nullable();
            $table->string('staff_count_range');
            $table->json('localized_specialties')->nullable();
            $table->json('industries')->nullable();
            $table->json('name');
            $table->string('primary_organization_type');
            $table->json('locations')->nullable();
            $table->string('linkedin_id');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('organizations');
    }
};
