<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->string('given_name')->nullable();
            $table->string('family_name')->nullable();
            $table->string('locale')->nullable();
            $table->string('picture')->nullable();
            $table->boolean('email_verified')->default(false);
            $table->text('localizedHeadline')->nullable();
            $table->boolean('blocked')->default(false);
            $table->string('role')->default('admin'); 
            $table->timestamps();
        });

        Schema::create('sessions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->timestamp('last_activity')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sessions');
        Schema::dropIfExists('users');
    }
};
