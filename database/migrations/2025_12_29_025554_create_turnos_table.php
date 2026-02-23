<?php

use App\Models\CentroCosto;
use App\Models\Turno;
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
        Schema::create('turnos', callback: function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(model: CentroCosto::class)->constrained();
            $table->string('nombre')->unique();
            $table->json('dias');
            $table->time('hora_inicio')->nullable();
            $table->time('hora_final')->nullable();
            $table->boolean('activo')->default(true);
            $table->timestamps();
        });

        Schema::table('users', function (Blueprint $table) {
            $table->foreignIdFor(Turno::class)->nullable()->constrained()->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['turno_id']);
            $table->dropColumn('turno_id');
        });
        Schema::dropIfExists('turnos');
    }
};
