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

        $iconType = AnswerType::create([
            'key' => 'icon_set',
            'label' => 'Estado visual',
            'behavior' => 'enum',
            'aggregable' => false,
        ]);

        $numberType = AnswerType::create([
            'key' => 'number',
            'label' => 'Numérico',
            'behavior' => 'numeric',
            'aggregable' => true,
        ]);

        $realizado = AnswerOption::create([
            'answer_type_id' => $iconType->id,
            'key' => 'realizado',
            'label' => 'REALIZADO',
            'icon' => 'check',
            'color' => 'green',
        ]);

        $equipo = Equipo::create([
            'nombre' => 'Caldera 1',
            'tag' => 'CM-CAL-01',
            'area' => 'Cuarto de maquinas',
        ]);

        // Hoja Chequeo
        $hoja = HojaChequeo::create([
            'equipo_id' => $equipo->id,
        ]);

        $columns = [
            ['key' => 'item', 'label' => 'Item'],
            ['key' => 'frecuencia', 'label' => 'Frecuencia'],
            ['key' => 'metodo', 'label' => 'Metodo'],
            ['key' => 'observaciones', 'label' => 'Observaciones'],
        ];

        foreach ($columns as $i => $col) {
            HojaColumna::create([
                'hoja_chequeo_id' => $hoja->id,
                'key' => $col['key'],
                'label' => $col['label'],
                'is_fixed' => true,
                'order' => $i,
            ]);
        }

        $filaLimpieza = HojaFila::create([
            'hoja_chequeo_id' => $hoja->id,
            'answer_type_id' => $iconType->id,
            'order' => 1,
        ]);

        $filaHoras = HojaFila::create([
            'hoja_chequeo_id' => $hoja->id,
            'answer_type_id' => $numberType->id,
            'order' => 2,
        ]);

        $setValor = function ($fila, $key, $value) use ($hoja) {
            $col = HojaColumna::where('hoja_chequeo_id', $hoja->id)
                ->where('key', $key)
                ->first();

            HojaFilaValor::create([
                'hoja_fila_id' => $fila->id,
                'hoja_columna_id' => $col->id,
                'valor' => $value,
            ]);
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

        $user = User::find(1);
        // Create executions (simulate a month)
        foreach (range(1, 30) as $day) {
            $exec = HojaEjecucion::create([
                'hoja_chequeo_id' => $hoja->id,
                'user_id' => $user->id,
                'nombre_operador' => $user->name,
                'firma_operador' => 'Firma de '.$user->name,
                'firma_supervisor' => 'Firma de supervisor',
                'observaciones' => 'Ejecución del día '.$day,
                'finalizado_en' => Carbon::now()->startOfMonth()->addDays($day - 1),
            ]);

            HojaFilaRespuesta::create([
                'hoja_ejecucion_id' => $exec->id,
                'hoja_fila_id' => $filaLimpieza->id,
                'answer_option_id' => $realizado->id,
            ]);

            HojaFilaRespuesta::create([
                'hoja_ejecucion_id' => $exec->id,
                'hoja_fila_id' => $filaHoras->id,
                'numeric_value' => rand(1, 10) * 10,
            ]);
        }
    }
}
