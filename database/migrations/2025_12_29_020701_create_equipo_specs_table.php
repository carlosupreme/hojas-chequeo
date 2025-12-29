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
        Schema::create('equipo_specs', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(Equipo::class)->constrained()->onDelete('cascade');
            $table->string('tipo');
            $table->string('unidad');
            $table->decimal('min');
            $table->decimal('optimo');
            $table->decimal('max');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('equipo_specs');
    }
};
