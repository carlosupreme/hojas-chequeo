<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Step 1: Create a session-scoped helper function to parse varied time strings
        // Handles: "16:40", "16:40pm", "16:40 pm", "5:34 PM", "4:29 am", "14:50 pm", etc.
        DB::statement("
            CREATE OR REPLACE FUNCTION pg_temp.parse_hora(val TEXT)
            RETURNS TIME AS \$\$
            DECLARE
                clean TEXT;
                h INTEGER;
                m INTEGER;
                is_pm BOOLEAN;
                is_am BOOLEAN;
            BEGIN
                IF val IS NULL OR trim(val) = '' THEN RETURN NULL; END IF;

                is_pm := trim(val) ~* 'pm\s*$';
                is_am := trim(val) ~* 'am\s*$';

                -- Strip trailing am/pm (with optional space), case insensitive
                clean := regexp_replace(trim(val), '\s*(am|pm)\s*$', '', 'i');

                h := split_part(clean, ':', 1)::INTEGER;
                m := split_part(clean, ':', 2)::INTEGER;

                -- Apply 12h → 24h conversion
                IF is_pm AND h < 12 THEN h := h + 12; END IF;
                IF is_am AND h = 12 THEN h := 0; END IF;

                RETURN make_time(h, m, 0);
            END;
            \$\$ LANGUAGE plpgsql;
        ");

        // Step 2: Add temporary timestamp columns
        Schema::table('tarjetons', function (Blueprint $table): void {
            $table->timestamp('hora_encendido_dt')->nullable();
            $table->timestamp('hora_apagado_dt')->nullable();
        });

        // Step 3: Migrate hora_encendido — combine fecha + parsed time into a full timestamp
        DB::statement('
            UPDATE tarjetons
            SET hora_encendido_dt = fecha + pg_temp.parse_hora(hora_encendido)
            WHERE hora_encendido IS NOT NULL
        ');

        // Step 4: Migrate hora_apagado — handle midnight-crossing (apagado < encendido → next day)
        DB::statement("
            UPDATE tarjetons
            SET hora_apagado_dt = CASE
                WHEN hora_encendido IS NOT NULL
                     AND pg_temp.parse_hora(hora_apagado) < pg_temp.parse_hora(hora_encendido)
                    THEN (fecha + INTERVAL '1 day') + pg_temp.parse_hora(hora_apagado)
                ELSE fecha + pg_temp.parse_hora(hora_apagado)
            END
            WHERE hora_apagado IS NOT NULL
        ");

        // Step 5: Drop old string columns
        Schema::table('tarjetons', function (Blueprint $table): void {
            $table->dropColumn(['hora_encendido', 'hora_apagado', 'fecha']);
        });

        // Step 6: Rename new timestamp columns to the original names
        Schema::table('tarjetons', function (Blueprint $table): void {
            $table->renameColumn('hora_encendido_dt', 'hora_encendido');
            $table->renameColumn('hora_apagado_dt', 'hora_apagado');
        });
    }

    public function down(): void
    {
        // Step 1: Add temporary string columns and restore fecha
        Schema::table('tarjetons', function (Blueprint $table): void {
            $table->string('hora_encendido_str')->nullable();
            $table->string('hora_apagado_str')->nullable();
            $table->date('fecha')->nullable();
        });

        // Step 2: Convert timestamp back to "HH:MM" strings and extract fecha
        DB::statement("
            UPDATE tarjetons
            SET hora_encendido_str = TO_CHAR(hora_encendido, 'HH24:MI')
            WHERE hora_encendido IS NOT NULL
        ");

        DB::statement("
            UPDATE tarjetons
            SET hora_apagado_str = TO_CHAR(hora_apagado, 'HH24:MI')
            WHERE hora_apagado IS NOT NULL
        ");

        DB::statement('
            UPDATE tarjetons
            SET fecha = hora_encendido::date
            WHERE hora_encendido IS NOT NULL
        ');

        // Step 3: Drop timestamp columns
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
