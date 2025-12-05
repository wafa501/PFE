<?php

use Illuminate\Database\Migrations\Migration;
use Jenssegers\Mongodb\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::connection('mongodb')->create('users', function (Blueprint $collection) {
            // Crée un index sur le champ 'email'
            $collection->index('email');
            // Vous pouvez également ajouter d'autres index si nécessaire
        });

        Schema::connection('mongodb')->create('sessions', function (Blueprint $collection) {
            // Crée un index composé sur les champs 'user_id' et 'last_activity'
            $collection->index(['user_id' => 1, 'last_activity' => -1]);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::connection('mongodb')->dropIfExists('users');
        Schema::connection('mongodb')->dropIfExists('sessions');
    }
};
