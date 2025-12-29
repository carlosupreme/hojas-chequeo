<?php

use App\Models\Equipo;
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
        Schema::create('reportes', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(Equipo::class)->nullable();
            $table->foreignIdFor(User::class)->nullable();
            $table->date('fecha');
            $table->string('name')->nullable();
            $table->string('area')->nullable();
            $table->string('priority')->nullable();
            $table->string('observations')->nullable();
            $table->string('failure')->nullable();
            $table->string('photo')->nullable();
            $table->string('estado')->default('pendiente');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reportes');
    }
};
