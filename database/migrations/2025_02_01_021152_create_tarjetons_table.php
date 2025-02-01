<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void {
        Schema::create('tarjetons', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(\App\Models\Equipo::class);
            $table->date("fecha");
            $table->string("hora_encendido")->nullable();
            $table->string('hora_apagado')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void {
        Schema::dropIfExists('tarjetons');
    }
};
