<?php

use App\Models\Equipo;
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
        Schema::create('hoja_chequeos', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(Equipo::class)->constrained()->cascadeOnDelete();
            $table->string('observaciones')->nullable();
            $table->boolean('encendido')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('hoja_chequeos');
    }
};
