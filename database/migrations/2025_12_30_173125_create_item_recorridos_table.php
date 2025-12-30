<?php

use App\Models\CategoriaRecorrido;
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
        Schema::create('item_recorridos', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(CategoriaRecorrido::class)->constrained()->cascadeOnDelete();
            $table->string('nombre');
            $table->string('tipo_entrada')->default('estado'); // 'estado' (v/x), 'numero' (medidores), 'texto'
            $table->integer('orden')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('item_recorridos');
    }
};
