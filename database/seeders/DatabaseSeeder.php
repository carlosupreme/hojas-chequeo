<?php

namespace Database\Seeders;

use App\Models\AnswerOption;
use App\Models\AnswerType;
use App\Models\Equipo;
use App\Models\HojaChequeo;
use App\Models\HojaColumna;
use App\Models\HojaEjecucion;
use App\Models\HojaFila;
use App\Models\HojaFilaRespuesta;
use App\Models\HojaFilaValor;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call(UserSeeder::class);
        $this->call(RecorridoTintoreriaSeeder::class);

        $iconType = AnswerType::firstOrCreate([
            'key' => 'icon_set',
        ], [
            'label' => 'Estado visual',
            'behavior' => 'enum',
            'aggregable' => false,
        ]);

        $numberType = AnswerType::firstOrCreate([
            'key' => 'number',
        ], [
            'label' => 'Numérico',
            'behavior' => 'numeric',
            'aggregable' => true,
        ]);

        $textType = AnswerType::firstOrCreate([
            'key' => 'text',
        ], [
            'label' => 'Texto',
            'behavior' => 'text',
            'aggregable' => false,
        ]);

        $boolType = AnswerType::firstOrCreate([
            'key' => 'boolean',
        ], [
            'label' => 'Si/No',
            'behavior' => 'boolean',
            'aggregable' => false,
        ]);

        $temperatura = AnswerType::firstOrCreate([
            'key' => 'temperatura',
        ], [
            'label' => 'Temperatura',
            'behavior' => 'numeric',
            'aggregable' => true,
        ]);

        $presion = AnswerType::firstOrCreate([
            'key' => 'presion',
        ], [
            'label' => 'Presion',
            'behavior' => 'numeric',
            'aggregable' => true,
        ]);

        $realizado = AnswerOption::firstOrCreate([
            'answer_type_id' => $iconType->id,
            'key' => 'realizado',
        ], [
            'label' => 'REALIZADO Y ESTA BIEN',
            'icon' => 'heroicon-o-check',
            'color' => 'green',
        ]);

        $realizadoMal = AnswerOption::firstOrCreate([
            'answer_type_id' => $iconType->id,
            'key' => 'realizado_mal',
        ], [
            'label' => 'REALIZADO Y ESTA MAL',
            'icon' => 'heroicon-o-x-mark',
            'color' => 'red',
        ]);

        $noRealizado = AnswerOption::firstOrCreate([
            'answer_type_id' => $iconType->id,
            'key' => 'no_realizado',
        ], [
            'label' => 'NO REALIZADO',
            'icon' => 'heroicon-o-no-symbol',
            'color' => 'yellow',
        ]);

        $noAplica = AnswerOption::firstOrCreate([
            'answer_type_id' => $iconType->id,
            'key' => 'no_aplica',
        ], [
            'label' => 'NO APLICA',
            'icon' => 'heroicon-o-minus-circle',
            'color' => 'gray',
        ]);

        $equipo = Equipo::firstOrCreate([
            'tag' => 'CM-CAL-01',
        ], [
            'nombre' => 'Caldera 1',
            'area' => 'Cuarto de maquinas',
        ]);

        // Hoja Chequeo
        $hoja = HojaChequeo::firstOrCreate([
            'equipo_id' => $equipo->id,
        ]);

        $columns = [
            ['key' => 'item', 'label' => 'Item'],
            ['key' => 'frecuencia', 'label' => 'Frecuencia'],
            ['key' => 'metodo', 'label' => 'Metodo'],
            ['key' => 'observaciones', 'label' => 'Observaciones'],
        ];

        foreach ($columns as $i => $col) {
            HojaColumna::firstOrCreate([
                'hoja_chequeo_id' => $hoja->id,
                'key' => $col['key'],
            ], [
                'label' => $col['label'],
                'is_fixed' => true,
                'order' => $i,
            ]);
        }

        $filaLimpieza = HojaFila::firstOrCreate([
            'hoja_chequeo_id' => $hoja->id,
            'order' => 1,
        ], [
            'answer_type_id' => $iconType->id,
        ]);

        $filaHoras = HojaFila::firstOrCreate([
            'hoja_chequeo_id' => $hoja->id,
            'order' => 2,
        ], [
            'answer_type_id' => $numberType->id,
        ]);

        $setValor = function ($fila, $key, $value) use ($hoja) {
            $col = HojaColumna::where('hoja_chequeo_id', $hoja->id)
                ->where('key', $key)
                ->first();

            if ($col) {
                HojaFilaValor::firstOrCreate([
                    'hoja_fila_id' => $fila->id,
                    'hoja_columna_id' => $col->id,
                ], [
                    'valor' => $value,
                ]);
            }
        };

        // Row values
        $setValor($filaLimpieza, 'item', 'Limpiar filtros');
        $setValor($filaLimpieza, 'frecuencia', 'Diario');
        $setValor($filaLimpieza, 'metodo', 'Manual');
        $setValor($filaLimpieza, 'observaciones', 'Hacer uso del paño');

        $setValor($filaHoras, 'item', 'Horas de uso');
        $setValor($filaHoras, 'frecuencia', 'Diario');
        $setValor($filaHoras, 'metodo', 'Tiempo');
        $setValor($filaHoras, 'observaciones', 'Tomar el tiempo');

        // Get users from different shifts
        $allUsers = User::whereHas('roles', function ($query) {
            $query->where('name', 'Operador');
        })->get();

        $usersByTurno = $allUsers->groupBy('turno_id');

        // Create executions from 30 days ago up to yesterday
        foreach (range(30, 1) as $day) {
            $date = Carbon::now()->subDays($day);

            // Create execution for each turno
            foreach ($usersByTurno as $turnoId => $users) {
                if ($users->isEmpty()) {
                    continue;
                }

                $user = $users->random();
                $answerOptions = [$realizado->id, $noRealizado->id, $noAplica->id, $realizadoMal->id];

                $exec = HojaEjecucion::create([
                    'hoja_chequeo_id' => $hoja->id,
                    'user_id' => $user->id,
                    'turno_id' => $turnoId,
                    'centro_costo_id' => $user->centroCosto->id,
                    'nombre_operador' => $user->name,
                    'firma_operador' => 'firmas/firma.svg',
                    'firma_supervisor' => 'firmas/firma.svg',
                    'observaciones' => 'Ejecución del día '.$day.' - Turno '.$turnoId,
                    'finalizado_en' => $date,
                ]);

                HojaFilaRespuesta::create([
                    'hoja_ejecucion_id' => $exec->id,
                    'hoja_fila_id' => $filaLimpieza->id,
                    'answer_option_id' => $answerOptions[array_rand($answerOptions)],
                ]);

                // Randomize numeric answer
                HojaFilaRespuesta::create([
                    'hoja_ejecucion_id' => $exec->id,
                    'hoja_fila_id' => $filaHoras->id,
                    'numeric_value' => rand(1, 15),
                ]);
            }
        }
    }
}
