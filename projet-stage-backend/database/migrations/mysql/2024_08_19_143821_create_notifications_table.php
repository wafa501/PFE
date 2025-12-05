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
            $table->string('post_id', 255); // Match the type of idPost
            $table->string('message')->default('');
            $table->boolean('is_read')->default(false);
            $table->timestamps();
        
            // Foreign key constraint
            $table->foreign('post_id')->references('idPost')->on('myposts')->onDelete('cascade');
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
