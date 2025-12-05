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
        Schema::create('facebook_reactions_posts', function (Blueprint $table) {
            $table->id();
            $table->string('post_id')->unique();
            $table->unsignedBigInteger('like_count')->default(0);
            $table->unsignedBigInteger('love_count')->default(0);
            $table->unsignedBigInteger('wow_count')->default(0);
            $table->unsignedBigInteger('haha_count')->default(0);
            $table->unsignedBigInteger('sad_count')->default(0);
            $table->unsignedBigInteger('angry_count')->default(0);
            $table->unsignedBigInteger('total_reactions')->default(0);
            $table->unsignedBigInteger('comments_count')->default(0);
            $table->text('message_comments')->nullable(); 
            $table->timestamps();
        });
    }


    public function down(): void
    {
        Schema::dropIfExists('facebook_reactions_posts');
    }
};
