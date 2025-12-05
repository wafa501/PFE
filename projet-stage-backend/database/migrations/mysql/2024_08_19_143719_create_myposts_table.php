<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('myposts', function (Blueprint $table) {
            $table->id();
            $table->string('idPost', 255)->unique(); 
            $table->timestamp('published_at')->nullable();
            $table->text('content')->nullable();
            $table->timestamp('last_modified_at')->nullable();
            $table->string('lifecycle_state')->default('draft');
            $table->string('visibility')->nullable();
            $table->json('distribution')->nullable();
            $table->text('commentary')->nullable();
            $table->string('alt_text')->nullable();
            $table->text('image_url')->nullable(); 
            $table->string('video_url')->nullable();
            $table->json('likes')->nullable(); 
            $table->json('comments')->nullable(); 
            $table->integer('numberLikes')->default(0);
            $table->integer('numberComments')->default(0);
            $table->text('CommentsList')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('myposts');
    }
};
