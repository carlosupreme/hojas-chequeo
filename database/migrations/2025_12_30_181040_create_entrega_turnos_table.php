<?php

use App\Models\LogRecorrido;
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
        Schema::create('entrega_turnos', function (Blueprint $table) {
            $table->id();
            $table->date('fecha');
            $table->time('hora');
            $table->text('entrega_equipos')->nullable();
            $table->text('entrega_observaciones_equipos')->nullable();
            $table->text('entrega_servicios')->nullable();
            $table->text('entrega_observaciones_servicios')->nullable();
            $table->text('recepcion_equipos')->nullable();
            $table->text('recepcion_observaciones_equipos')->nullable();
            $table->text('recepcion_servicios')->nullable();
            $table->text('recepcion_observaciones_servicios')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('entrega_turnos');
    }
};
