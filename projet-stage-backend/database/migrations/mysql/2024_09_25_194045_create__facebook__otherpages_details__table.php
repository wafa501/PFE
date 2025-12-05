<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('_facebook__otherpages_details_', function (Blueprint $table) {
            $table->string('id')->primary(); 
            $table->string('name')->nullable();;
            $table->string('location')->nullable();
            $table->string('link')->nullable();;
            $table->text('about')->nullable();
            $table->string('category')->nullable();;
            $table->string('picture')->nullable();
            $table->integer('fan_count')->nullable();;
            $table->string('website')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('_facebook__otherpages_details_');
    }
};
