<?php

use App\Models\Equipo;
use App\Models\Turno;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('equipo_turno', function (Blueprint $table) {
            $table->foreignIdFor(Equipo::class)->constrained()->cascadeOnDelete();
            $table->foreignIdFor(Turno::class)->constrained()->cascadeOnDelete();
            $table->primary(['equipo_id', 'turno_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('equipo_turno');
    }
};
