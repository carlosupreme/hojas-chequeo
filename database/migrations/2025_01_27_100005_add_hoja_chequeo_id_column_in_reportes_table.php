<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void {
        Schema::table('reportes', function (Blueprint $table) {
            $table->foreignIdFor(\App\Models\HojaChequeo::class)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void {
        Schema::table('reportes', function (Blueprint $table) {
            $table->dropColumn('hoja_chequeo_id');
        });
    }
};
