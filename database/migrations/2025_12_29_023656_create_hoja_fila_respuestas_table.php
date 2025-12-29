<?php

use App\Models\AnswerOption;
use App\Models\HojaEjecucion;
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
        Schema::create('hoja_fila_respuestas', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(HojaEjecucion::class)->constrained()->cascadeOnDelete();
            $table->foreignIdFor(HojaFila::class)->constrained()->cascadeOnDelete();
            $table->foreignIdFor(AnswerOption::class)->nullable()->constrained()->nullOnDelete();

            $table->decimal('numeric_value')->nullable();
            $table->text('text_value')->nullable();
            $table->boolean('boolean_value')->nullable();

            $table->timestamps();

            $table->unique(['hoja_ejecucion_id', 'hoja_fila_id']);
            $table->index('numeric_value');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('hoja_fila_respuestas');
    }
};
