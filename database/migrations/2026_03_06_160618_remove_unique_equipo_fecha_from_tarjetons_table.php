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
        Schema::table('tarjetons', function (Blueprint $table) {
            $table->dropUnique('unique_equipo_fecha');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tarjetons', function (Blueprint $table) {
            $table->unique(['equipo_id', 'fecha'], 'unique_equipo_fecha');
        });
    }
};
