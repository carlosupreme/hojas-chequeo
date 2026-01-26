<?php

use App\Models\ItemRecorrido;
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
        Schema::create('valor_recorridos', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(LogRecorrido::class)->constrained()->cascadeOnDelete();
            $table->foreignIdFor(ItemRecorrido::class)->constrained();
            $table->string('estado')->nullable(); // Guardará √, X, PP, PM
            $table->decimal('valor_numerico', 12, 4)->nullable(); // Para medidores o %
            $table->string('valor_texto')->nullable();
            $table->text('observaciones')->nullable();

            $table->unique(['log_recorrido_id', 'item_recorrido_id']);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('valor_recorridos');
    }
};
