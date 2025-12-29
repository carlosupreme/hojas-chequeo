<?php

use App\Models\AnswerType;
use App\Models\HojaChequeo;
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
        Schema::create('hoja_filas', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(HojaChequeo::class)->constrained()->cascadeOnDelete();
            $table->foreignIdFor(AnswerType::class)->constrained();
            $table->unsignedSmallInteger('order');
            $table->enum('categoria', ['limpieza', 'operacion', 'revision'])->default('limpieza');
            $table->timestamps();

            $table->index(['hoja_chequeo_id', 'order']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('hoja_filas');
    }
};
