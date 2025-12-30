<?php

use App\Models\FormularioRecorrido;
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
        Schema::create('categoria_recorridos', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(FormularioRecorrido::class)->constrained()->cascadeOnDelete();
            $table->string('nombre');
            $table->integer('orden')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('categoria_recorridos');
    }
};
