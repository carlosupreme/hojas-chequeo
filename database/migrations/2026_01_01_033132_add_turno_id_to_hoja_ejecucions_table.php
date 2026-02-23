<?php

use App\Models\CentroCosto;
use App\Models\Turno;
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
        Schema::table('hoja_ejecucions', function (Blueprint $table) {
            $table->foreignIdFor(Turno::class)->nullable()->constrained()->nullOnDelete();
            $table->foreignIdFor(CentroCosto::class)->nullable()->constrained()->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('hoja_ejecucions', function (Blueprint $table) {
            $table->dropForeign(['turno_id']);
            $table->dropColumn('turno_id');
            $table->dropForeign(['centro_costo_id']);
            $table->dropColumn('centro_costo_id');
        });
    }
};
