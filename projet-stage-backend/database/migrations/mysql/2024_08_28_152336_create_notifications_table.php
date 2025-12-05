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
        Schema::create('notifications', function (Blueprint $table) {
            $table->id();
            $table->string('post_id', 255)->unique();
            $table->text('like_message')->nullable(); 
            $table->text('comment_message')->nullable(); 
            $table->boolean('is_read')->default(false);
            $table->string('videoUrl')->nullable();
            $table->text('image_url')->nullable();
            $table->timestamps();
        
            // Contrainte de clé étrangère
            $table->foreign('post_id')->references('idPost')->on('posts')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notifications');
    }
};
