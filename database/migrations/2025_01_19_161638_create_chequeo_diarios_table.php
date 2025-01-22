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
        Schema::create('chequeo_diarios', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(\App\Models\HojaChequeo::class)->constrained()->cascadeOnDelete()->cascadeOnUpdate();
            $table->string('nombre_operador');
            $table->foreignIdFor(\App\Models\User::class, 'operador_id')->nullable();
            $table->text('firma_operador');
            $table->text('firma_supervisor')->nullable();
            $table->text('observaciones')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('chequeo_diarios');
    }
};
