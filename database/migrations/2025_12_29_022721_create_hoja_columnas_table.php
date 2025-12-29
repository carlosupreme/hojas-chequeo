<?php

use App\Models\HojaChequeo;
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
        Schema::create('hoja_columnas', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(HojaChequeo::class)->constrained()->cascadeOnDelete();

            $table->string('key');
            $table->string('label');
            $table->boolean('is_fixed')->default(false);
            $table->unsignedSmallInteger('order');

            $table->timestamps();

            $table->unique(['hoja_chequeo_id', 'key']);
            $table->index(['hoja_chequeo_id', 'order']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('hoja_columnas');
    }
};
