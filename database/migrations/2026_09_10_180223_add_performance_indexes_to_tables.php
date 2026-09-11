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
        Schema::table('tarjetons', function (Blueprint $table): void {
            $table->index(['equipo_id', 'hora_encendido'], 'idx_tarjetons_equipo_hora_encendido');
            $table->index('hora_encendido', 'idx_tarjetons_hora_encendido');
        });

        Schema::table('reportes', function (Blueprint $table): void {
            $table->index(['estado', 'prioridad'], 'idx_reportes_estado_prioridad');
            $table->index('fecha', 'idx_reportes_fecha');
            $table->index('equipo_id', 'idx_reportes_equipo_id');
            $table->index('hoja_chequeo_id', 'idx_reportes_hoja_chequeo_id');
            $table->index('user_id', 'idx_reportes_user_id');
        });

        Schema::table('log_recorridos', function (Blueprint $table): void {
            $table->index(['formulario_recorrido_id', 'fecha'], 'idx_log_recorridos_form_fecha');
            $table->index('fecha', 'idx_log_recorridos_fecha');
            $table->index('user_id', 'idx_log_recorridos_user_id');
            $table->index('turno_id', 'idx_log_recorridos_turno_id');
        });

        Schema::table('hoja_ejecucions', function (Blueprint $table): void {
            $table->index('turno_id', 'idx_hoja_ejecucions_turno_id');
            $table->index(['user_id', 'finalizado_en'], 'idx_hoja_ejecucions_user_finalizado');
            $table->index('created_at', 'idx_hoja_ejecucions_created_at');
        });

        Schema::table('hoja_fila_respuestas', function (Blueprint $table): void {
            $table->index('hoja_fila_id', 'idx_hoja_fila_respuestas_fila_id');
            $table->index('answer_option_id', 'idx_hoja_fila_respuestas_option_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tarjetons', function (Blueprint $table): void {
            $table->dropIndex('idx_tarjetons_equipo_hora_encendido');
            $table->dropIndex('idx_tarjetons_hora_encendido');
        });

        Schema::table('reportes', function (Blueprint $table): void {
            $table->dropIndex('idx_reportes_estado_prioridad');
            $table->dropIndex('idx_reportes_fecha');
            $table->dropIndex('idx_reportes_equipo_id');
            $table->dropIndex('idx_reportes_hoja_chequeo_id');
            $table->dropIndex('idx_reportes_user_id');
        });

        Schema::table('log_recorridos', function (Blueprint $table): void {
            $table->dropIndex('idx_log_recorridos_form_fecha');
            $table->dropIndex('idx_log_recorridos_fecha');
            $table->dropIndex('idx_log_recorridos_user_id');
            $table->dropIndex('idx_log_recorridos_turno_id');
        });

        Schema::table('hoja_ejecucions', function (Blueprint $table): void {
            $table->dropIndex('idx_hoja_ejecucions_turno_id');
            $table->dropIndex('idx_hoja_ejecucions_user_finalizado');
            $table->dropIndex('idx_hoja_ejecucions_created_at');
        });

        Schema::table('hoja_fila_respuestas', function (Blueprint $table): void {
            $table->dropIndex('idx_hoja_fila_respuestas_fila_id');
            $table->dropIndex('idx_hoja_fila_respuestas_option_id');
        });
    }
};
