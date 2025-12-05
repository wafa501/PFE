<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('other_organizations', function (Blueprint $table) {
            $table->id();
            $table->string('vanity_name');
            $table->json('localized_name')->nullable();
            $table->json('name')->nullable();
            $table->string('primary_organization_type')->nullable();
            $table->json('locations')->nullable(); 
            $table->string('linkedin_id')->unique();
            $table->string('localized_website')->nullable();
            $table->json('logo_v2')->nullable();
            $table->json('paging')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('other_organizations');
    }
};
