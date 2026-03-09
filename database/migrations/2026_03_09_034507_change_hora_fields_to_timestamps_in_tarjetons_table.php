<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Step 1: Add temporary datetime columns
        Schema::table('tarjetons', function (Blueprint $table): void {
            $table->dateTime('hora_encendido_dt')->nullable()->after('hora_encendido');
            $table->dateTime('hora_apagado_dt')->nullable()->after('hora_apagado');
        });

        // Step 2: Migrate existing data — combine fecha + time string into a full datetime
        DB::statement("
            UPDATE tarjetons
            SET hora_encendido_dt = CAST(CONCAT(DATE(fecha), ' ', hora_encendido, ':00') AS DATETIME)
            WHERE hora_encendido IS NOT NULL
        ");

        // For apagado, handle midnight-crossing (apagado < encendido means it rolled over to next day)
        DB::statement("
            UPDATE tarjetons
            SET hora_apagado_dt = CASE
                WHEN hora_encendido IS NOT NULL AND hora_apagado < hora_encendido
                    THEN CAST(CONCAT(DATE(fecha) + INTERVAL 1 DAY, ' ', hora_apagado, ':00') AS DATETIME)
                ELSE CAST(CONCAT(DATE(fecha), ' ', hora_apagado, ':00') AS DATETIME)
            END
            WHERE hora_apagado IS NOT NULL
        ");

        // Step 3: Drop old string columns
        Schema::table('tarjetons', function (Blueprint $table): void {
            $table->dropColumn(['hora_encendido', 'hora_apagado']);
        });

        // Step 4: Rename new datetime columns to the original names
        Schema::table('tarjetons', function (Blueprint $table): void {
            $table->renameColumn('hora_encendido_dt', 'hora_encendido');
            $table->renameColumn('hora_apagado_dt', 'hora_apagado');
        });
    }

    public function down(): void
    {
        // Step 1: Add temporary string columns
        Schema::table('tarjetons', function (Blueprint $table): void {
            $table->string('hora_encendido_str')->nullable()->after('hora_encendido');
            $table->string('hora_apagado_str')->nullable()->after('hora_apagado');
        });

        // Step 2: Convert datetime back to "H:i" strings
        DB::statement("
            UPDATE tarjetons
            SET hora_encendido_str = TIME_FORMAT(hora_encendido, '%H:%i')
            WHERE hora_encendido IS NOT NULL
        ");

        DB::statement("
            UPDATE tarjetons
            SET hora_apagado_str = TIME_FORMAT(hora_apagado, '%H:%i')
            WHERE hora_apagado IS NOT NULL
        ");

        // Step 3: Drop datetime columns
        Schema::table('tarjetons', function (Blueprint $table): void {
            $table->dropColumn(['hora_encendido', 'hora_apagado']);
        });

        // Step 4: Rename string columns back to original names
        Schema::table('tarjetons', function (Blueprint $table): void {
            $table->renameColumn('hora_encendido_str', 'hora_encendido');
            $table->renameColumn('hora_apagado_str', 'hora_apagado');
        });
    }
};
