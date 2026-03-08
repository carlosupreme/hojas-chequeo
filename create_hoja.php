<?php

use App\Models\HojaChequeo;
use App\Models\HojaFilaValor;
use Illuminate\Support\Facades\DB;

DB::transaction(function () {
    $version = HojaChequeo::getCurrentVersion(37);
    $hoja = HojaChequeo::create([
        'equipo_id' => 37,
        'observaciones' => '',
        'version' => $version,
        'encendido' => true,
    ]);

    $colMap = [];

    $col1 = $hoja->columnas()->create(['key' => 'item', 'label' => 'Item', 'is_fixed' => true, 'order' => 0]);
    $colMap['item'] = $col1->id;

    $col2 = $hoja->columnas()->create(['key' => 'frecuencia', 'label' => 'Frecuencia', 'is_fixed' => true, 'order' => 1]);
    $colMap['frecuencia'] = $col2->id;

    $col3 = $hoja->columnas()->create(['key' => 'criterio', 'label' => 'Criterio', 'is_fixed' => true, 'order' => 2]);
    $colMap['criterio'] = $col3->id;

    $filas = [
        ['answer_type_id' => 1, 'categoria' => 'limpieza', 'order' => 0, 'vals' => ['item' => 'Revisar que no haya material inadecuado en su área de trabajo', 'frecuencia' => '1 vez por turno', 'criterio' => '']],
        ['answer_type_id' => 1, 'categoria' => 'limpieza', 'order' => 1, 'vals' => ['item' => 'Realizar limpieza con trapo húmedo', 'frecuencia' => '1 ver por turno', 'criterio' => 'Plancha de vapor, mesa, cabeza superior e inferior']],
        ['answer_type_id' => 1, 'categoria' => 'revision', 'order' => 2, 'vals' => ['item' => 'Revisar cabeza superior que no se trabe', 'frecuencia' => '1 ver por turno', 'criterio' => '']],
        ['answer_type_id' => 1, 'categoria' => 'limpieza', 'order' => 3, 'vals' => ['item' => 'Verificar que exista vapor en la cabeza inferior y  en la cabeza superior.', 'frecuencia' => '1 ver por turno', 'criterio' => 'Con el pedal y palanca']],
        ['answer_type_id' => 1, 'categoria' => 'operacion', 'order' => 4, 'vals' => ['item' => 'Verificar que exista vacío en la cabeza inferior ', 'frecuencia' => '1 ver por turno', 'criterio' => 'Con el pedal La guata debe estar succionada']],
        ['answer_type_id' => 1, 'categoria' => 'operacion', 'order' => 5, 'vals' => ['item' => 'Purgar rociador ', 'frecuencia' => '1 vez por turno', 'criterio' => 'Verificar que no esté sucia el agua y no contenga sarro']],
        ['answer_type_id' => 1, 'categoria' => 'operacion', 'order' => 6, 'vals' => ['item' => 'Purgar plancha de vapor.', 'frecuencia' => '1 vez por turno', 'criterio' => 'Verificar que no esté sucia el agua y no contenga sarro.']],
        ['answer_type_id' => 1, 'categoria' => 'operacion', 'order' => 7, 'vals' => ['item' => 'Cierra a 3⁄4 la válvula de purga', 'frecuencia' => 'Al iniciar el turno', 'criterio' => 'El turno anterior debe dejarla abierta']],
        ['answer_type_id' => 1, 'categoria' => 'operacion', 'order' => 8, 'vals' => ['item' => 'Abre al 100% la válvula de vapor.', 'frecuencia' => 'Al iniciar el turno', 'criterio' => '']],
        ['answer_type_id' => 1, 'categoria' => 'operacion', 'order' => 9, 'vals' => ['item' => 'Cierra completamente la válvula de purga.', 'frecuencia' => 'Al final del turno', 'criterio' => 'Cuando ya sale vapor ']],
        ['answer_type_id' => 1, 'categoria' => 'operacion', 'order' => 10, 'vals' => ['item' => 'Abre a un 1⁄4 la válvula de purga y después de 5 min ábrela completamente', 'frecuencia' => 'Al final del turno', 'criterio' => 'Para evitar que el condensado se quede dentro del equipo y las líneas.']],
    ];

    foreach ($filas as $filaData) {
        $vals = $filaData['vals'];
        unset($filaData['vals']);
        $fila = $hoja->filas()->create($filaData);

        foreach ($vals as $key => $valor) {
            if (! empty($valor)) {
                $fila->valores()->create([
                    'hoja_columna_id' => $colMap[$key],
                    'valor' => $valor,
                ]);
            }
        }
    }

    echo "HojaChequeo created with ID: {$hoja->id}, version: {$hoja->version}\n";
    echo "Columnas: {$hoja->columnas()->count()}\n";
    echo "Filas: {$hoja->filas()->count()}\n";
    echo 'Valores: '.HojaFilaValor::whereIn('hoja_fila_id', $hoja->filas()->pluck('id'))->count()."\n";
});
