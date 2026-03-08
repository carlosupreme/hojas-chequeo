<?php

use App\Models\HojaChequeo;
use App\Models\HojaFilaValor;
use Illuminate\Support\Facades\DB;

DB::transaction(function () {
    $version = HojaChequeo::getCurrentVersion(36);
    $hoja = HojaChequeo::create([
        'equipo_id' => 36,
        'observaciones' => '',
        'version' => $version,
        'encendido' => true,
    ]);

    $colMap = [];

    $col1 = $hoja->columnas()->create(['key' => 'item', 'label' => 'ITEM', 'is_fixed' => true, 'order' => 0]);
    $colMap['item'] = $col1->id;

    $col2 = $hoja->columnas()->create(['key' => 'frecuencia', 'label' => 'FRECUENCIA', 'is_fixed' => true, 'order' => 1]);
    $colMap['frecuencia'] = $col2->id;

    $col3 = $hoja->columnas()->create(['key' => 'criterio', 'label' => 'CRITERIO', 'is_fixed' => true, 'order' => 2]);
    $colMap['criterio'] = $col3->id;

    $col4 = $hoja->columnas()->create(['key' => 'observaciones', 'label' => 'OBSERVACIONES', 'is_fixed' => false, 'order' => 3]);
    $colMap['observaciones'] = $col4->id;

    $filas = [
        ['answer_type_id' => 1, 'categoria' => 'operacion', 'order' => 0, 'vals' => ['item' => 'PURGA DE FONDO', 'frecuencia' => '1 VEZ POR TURNO', 'criterio' => 'APERTURA DE VALVULA', 'observaciones' => '']],
        ['answer_type_id' => 1, 'categoria' => 'operacion', 'order' => 1, 'vals' => ['item' => 'FUGAS', 'frecuencia' => '1 VEZ POR TURNO', 'criterio' => 'FUNCIONAMIENTO OPTIMO', 'observaciones' => '']],
        ['answer_type_id' => 1, 'categoria' => 'operacion', 'order' => 2, 'vals' => ['item' => 'SOPORTES', 'frecuencia' => '1 VEZ POR TURNO', 'criterio' => 'FUNCIONAMIENTO OPTIMO', 'observaciones' => '']],
        ['answer_type_id' => 1, 'categoria' => 'operacion', 'order' => 3, 'vals' => ['item' => 'MOTOR', 'frecuencia' => '1 VEZ POR TURNO', 'criterio' => 'RUIDO EXTRAÑO', 'observaciones' => '']],
        ['answer_type_id' => 1, 'categoria' => 'operacion', 'order' => 4, 'vals' => ['item' => 'ENCENDIDO DE INTERRUPTOR PRINCIPAL', 'frecuencia' => '1 VEZ POR TURNO', 'criterio' => 'REVISAR QUE ESTE ENCENDIDO EL INTERRUPTOR DEL COMPRESOR AL INICIAR TURNO', 'observaciones' => 'PASTILLA TERMOMAGNETICA EN BUEN ESTADO DEL TABLERO']],
        ['answer_type_id' => 1, 'categoria' => 'operacion', 'order' => 5, 'vals' => ['item' => 'ENCENDIDO DE INTERRUPTOR SECUNDARIO', 'frecuencia' => '1 VEZ POR TURNO', 'criterio' => 'REVISAR QUE ESTE ENCENDIDO EL INTERRUPTOR DEL COMPRESOR AL INICIAR TURNO', 'observaciones' => 'COLA DE RATA EN BUEN ESTADO']],
        ['answer_type_id' => 1, 'categoria' => 'limpieza', 'order' => 6, 'vals' => ['item' => 'LIMPIEZA EXTERIOR DE EQUIPO', 'frecuencia' => '1 VEZ POR TURNO', 'criterio' => 'LIBERACION DE PRESION AL LLEGAR A LA PRESION DE TRABAJO', 'observaciones' => 'EXCESO DE PELUSA Y GRASA']],
        ['answer_type_id' => 1, 'categoria' => 'operacion', 'order' => 7, 'vals' => ['item' => 'CABLE DE ENERGIA ELECTRICA', 'frecuencia' => '1 VEZ POR TURNO', 'criterio' => 'REVISAR QUE NO EXISTAN DAÑOS EN LOS CABLES Y QUE TENGA SU PROTECCION', 'observaciones' => 'CABLES EN BUEN ESTADO']],
        ['answer_type_id' => 1, 'categoria' => 'limpieza', 'order' => 8, 'vals' => ['item' => 'LIMPIEZA DE PISOS', 'frecuencia' => '1 VEZ POR TURNO', 'criterio' => 'QUE ESTE LIMPIA EL AREA Y QUE NO EXISTAN OBJETOS EXTRAÑOS AL AREA.', 'observaciones' => 'QUE NO EXISTA POLVO O GRASA EN EL PISO']],
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
