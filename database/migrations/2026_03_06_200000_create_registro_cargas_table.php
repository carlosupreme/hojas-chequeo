<?php

use App\Models\CentroCosto;
use App\Models\Equipo;
use App\Models\Turno;
use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('registro_cargas', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(Equipo::class)->constrained()->cascadeOnDelete();
            $table->foreignIdFor(User::class)->constrained()->cascadeOnDelete();
            $table->foreignIdFor(Turno::class)->nullable()->constrained()->nullOnDelete();
            $table->foreignIdFor(CentroCosto::class)->nullable()->constrained()->nullOnDelete();
            $table->timestamp('registrado_en')->useCurrent();
            $table->timestamps();

            $table->index(['equipo_id', 'registrado_en']);
            $table->index(['turno_id', 'registrado_en']);
            $table->index(['user_id', 'registrado_en']);
            $table->index(['centro_costo_id', 'registrado_en']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('registro_cargas');
    }
};
