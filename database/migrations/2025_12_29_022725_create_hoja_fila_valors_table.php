<?php

use App\Models\HojaColumna;
use App\Models\HojaFila;
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
        Schema::create('hoja_fila_valors', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(HojaFila::class)->constrained()->cascadeOnDelete();
            $table->foreignIdFor(HojaColumna::class)->constrained()->cascadeOnDelete();

            $table->text('valor');

            $table->timestamps();

            $table->unique(['hoja_fila_id', 'hoja_columna_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('hoja_fila_valors');
    }
};
