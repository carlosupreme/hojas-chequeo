<?php

use App\Models\FormularioRecorrido;
use App\Models\Turno;
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
        Schema::create('log_recorridos', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(FormularioRecorrido::class)->constrained();
            $table->foreignIdFor(User::class)->constrained();
            $table->foreignIdFor(Turno::class)->constrained();
            $table->date('fecha');
            $table->string('firma_operador')->nullable();
            $table->string('firma_supervisor')->nullable();
            $table->foreignId('supervisor_id')->nullable()->constrained('users');
            $table->timestamp('firmado_operador_at')->nullable();
            $table->timestamp('firmado_supervisor_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('log_recorridos');
    }
};
