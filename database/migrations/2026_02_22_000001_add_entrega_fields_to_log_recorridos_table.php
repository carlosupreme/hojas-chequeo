<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('log_recorridos', function (Blueprint $table) {
            $table->text('equipos_funcionando')->nullable()->after('firmado_supervisor_at');
            $table->text('observaciones_equipos')->nullable()->after('equipos_funcionando');
            $table->text('servicios_funcionando')->nullable()->after('observaciones_equipos');
            $table->text('observaciones_servicios')->nullable()->after('servicios_funcionando');
        });
    }

    public function down(): void
    {
        Schema::table('log_recorridos', function (Blueprint $table) {
            $table->dropColumn([
                'equipos_funcionando',
                'observaciones_equipos',
                'servicios_funcionando',
                'observaciones_servicios',
            ]);
        });
    }
};
