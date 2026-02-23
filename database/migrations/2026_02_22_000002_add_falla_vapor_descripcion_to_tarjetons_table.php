<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tarjetons', function (Blueprint $table) {
            $table->text('falla_vapor_descripcion')->nullable()->after('falla_vapor');
        });
    }

    public function down(): void
    {
        Schema::table('tarjetons', function (Blueprint $table) {
            $table->dropColumn('falla_vapor_descripcion');
        });
    }
};
