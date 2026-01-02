<?php

use App\Models\Perfil;
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
        Schema::create('perfils', function (Blueprint $table) {
            $table->id();
            $table->string('nombre')->unique();
            $table->json('hoja_ids');
            $table->boolean('acceso_total')->default(false);
            $table->timestamps();
        });

        Schema::table('users', function (Blueprint $table) {
            $table->foreignIdFor(Perfil::class)->constrained()->restrictOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('perfils');
    }
};
