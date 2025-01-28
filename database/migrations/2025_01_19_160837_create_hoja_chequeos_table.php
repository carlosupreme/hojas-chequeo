<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void {
        Schema::create('hoja_chequeos', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(\App\Models\Equipo::class)->constrained()->cascadeOnDelete()->cascadeOnUpdate();
            $table->integer('version')->default(1);
            $table->text('observaciones')->nullable();
            $table->timestamps();

            $table->unique(['equipo_id', 'version']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void {
        Schema::dropIfExists('hoja_chequeos');
    }
};
