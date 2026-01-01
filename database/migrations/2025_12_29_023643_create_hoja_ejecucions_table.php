<?php

use App\Models\HojaChequeo;
use App\Models\User;
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
        Schema::create('hoja_ejecucions', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(HojaChequeo::class)->constrained()->cascadeOnDelete();
            $table->foreignIdFor(User::class)->constrained()->cascadeOnDelete();
            $table->string('nombre_operador')->nullable();
            $table->string('firma_operador')->nullable();
            $table->string('firma_supervisor')->nullable();
            $table->string('observaciones')->nullable();
            $table->timestamp('finalizado_en')->nullable()->index();
            $table->timestamps();

            $table->index(['hoja_chequeo_id', 'finalizado_en']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('hoja_ejecucions');
    }
};
