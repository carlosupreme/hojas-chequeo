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
        Schema::create('tarjetons', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(\App\Models\Equipo::class);
            $table->date('fecha');
            $table->string('hora_encendido')->nullable();
            $table->string('hora_apagado')->nullable();
            $table->string('encendido_por')->nullable();
            $table->string('apagado_por')->nullable();

            $table->boolean('falla_vapor')->default(false);

            // Campo calculado para tiempo de operación en minutos
            $table->integer('tiempo_operacion_minutos')->nullable();

            // Campo para observaciones adicionales
            $table->text('observaciones')->nullable();

            // Estado del equipo
            $table->enum('estado', ['encendido', 'apagado', 'mantenimiento'])->default('apagado');

            $table->timestamps();

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
        Schema::dropIfExists('tarjetons');
    }
};
