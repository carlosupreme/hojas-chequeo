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
            // Agregar los campos que faltan
            $table->string('encendido_por')->nullable()->after('hora_apagado');
            $table->string('apagado_por')->nullable()->after('encendido_por');
            
            // Campo calculado para tiempo de operación en minutos
            $table->integer('tiempo_operacion_minutos')->nullable()->after('apagado_por');
            
            // Campo para observaciones adicionales
            $table->text('observaciones')->nullable()->after('tiempo_operacion_minutos');
            
            // Estado del equipo
            $table->enum('estado', ['encendido', 'apagado', 'mantenimiento'])
                  ->default('apagado')
                  ->after('observaciones');
            
            // Índices para mejor rendimiento
            $table->index(['equipo_id', 'fecha'], 'idx_equipo_fecha');
            $table->unique(['equipo_id', 'fecha'], 'unique_equipo_fecha');

            $table->index('estado', 'idx_estado');
            $table->index('fecha', 'idx_fecha');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tarjetons', function (Blueprint $table) {
            // Eliminar índices primero
            $table->dropIndex('idx_equipo_fecha');
            $table->dropIndex('idx_estado');
            $table->dropIndex('idx_fecha');
            $table->dropUnique('unique_equipo_fecha');

            // Eliminar columnas
            $table->dropColumn([
                'encendido_por',
                'apagado_por', 
                'tiempo_operacion_minutos',
                'observaciones',
                'estado'
            ]);
        });
    }
};