<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('posts', function (Blueprint $table) {
            $table->id();
            $table->string('idPost', 255)->unique(); 
            $table->string('author')->nullable();
            $table->timestamp('published_at')->nullable();
            $table->text('content')->nullable();
            $table->text('likes_count')->nullable();
            $table->text('Comments_count')->nullable();
            $table->text('uniqueImpressionsCount')->nullable();
            $table->timestamp('last_modified_at')->nullable();
            $table->string('lifecycle_state')->default('draft');
            $table->string('visibility')->nullable();
            $table->json('distribution')->nullable();
            $table->text('commentary')->nullable();
            $table->string('alt_text')->nullable();
            $table->text('image_url')->nullable(); 
            $table->string('video_url')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('posts');
    }
};
