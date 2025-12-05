<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('organizations', function (Blueprint $table) {
            $table->string('staff_count_range')->nullable()->change(); // Change le type en chaîne
        });
    }

    public function down(): void
    {
        Schema::table('organizations', function (Blueprint $table) {
            // Revertir les changements si nécessaire
            $table->json('staff_count_range')->nullable()->change();
        });
    }
};
