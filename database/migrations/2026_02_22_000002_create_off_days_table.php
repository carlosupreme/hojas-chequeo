<?php

use App\Models\Turno;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('off_days', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(Turno::class)->constrained()->cascadeOnDelete();
            $table->date('fecha');
            $table->string('motivo')->nullable();
            $table->timestamps();

            $table->unique(['turno_id', 'fecha']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('off_days');
    }
};
