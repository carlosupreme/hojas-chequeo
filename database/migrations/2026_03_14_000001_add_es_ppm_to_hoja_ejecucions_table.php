<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('hoja_ejecucions', function (Blueprint $table) {
            $table->boolean('es_ppm')->default(false)->after('finalizado_en');
        });
    }

    public function down(): void
    {
        Schema::table('hoja_ejecucions', function (Blueprint $table) {
            $table->dropColumn('es_ppm');
        });
    }
};
