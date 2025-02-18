<?php

namespace Database\Seeders;

use App\HojaChequeoArea;
use App\Models\Equipo;
use App\Models\HojaChequeo;
use App\Models\Item;
use Illuminate\Database\Seeder;

class EquiposSeeder extends Seeder
{
    /**
     * Run the database seeds.b
     */
    public function run(): void {
        $this->cuartoDeMaquinas();
        $this->tintoreria();
        $this->lavanderia();
    }

    public function cuartoDeMaquinas(): void {
        $this->laHid02();
        $this->cmCal(1);
        $this->cmCal(2);
        $this->cmCom01();
        $this->cmTco01();
        $this->sua05();
        $this->fze03();
        $this->fca02();
        $this->cm02Hid01();
    }
    public function cm02Hid01(): void {
        $equipo = Equipo::create([
            'tag' => 'CM02-HID-01',
            'nombre' => 'HIDRONEUMATICO 1',
            'numeroControl' => '01',
            'revision' => 'NOM-020-STPS-2012',
            'area' => HojaChequeoArea::CUARTO_DE_MAQUINAS->value
        ]);

        $hoja = HojaChequeo::create([
            'equipo_id' => $equipo->id,
            'area' => HojaChequeoArea::CUARTO_DE_MAQUINAS->value
        ]);

        $items = [
            [
                'items' => [
                    'ITEM' => 'ENCENDIDO DE INTERRUPTOR PRINCIPAL',
                    'FRECUENCIA' => '1 VEZ POR TURNO',
                    'METODO' => 'VISUAL',
                    'CRITERIO' => 'REVISAR QUE ESTE ENCENDIDO EL INTERRUPTOR DEL COMPRESOR DEL HIDRONEUMATICO',
                    'QUIEN REALIZA' => 'OPERARIO'
                ],
                'categoria' => 'operacion'
            ],
            [
                'items' => [
                    'ITEM' => 'ENCENDIDO DE INTERRUPTOR SECUNDARIO',
                    'FRECUENCIA' => '1 VEZ POR TURNO',
                    'METODO' => 'VISUAL Y AUDITIVO',
                    'CRITERIO' => 'REVISAR QUE ESTE ENCENDIDO EL INTERRUPTOR DEL COMPRESOR DEL HIDRONEUMATICO',
                    'QUIEN REALIZA' => 'OPERARIO'
                ],
                'categoria' => 'operacion'
            ],
            [
                'items' => [
                    'ITEM' => 'LIMPIEZA EXTERIOR DE EQUIPO',
                    'FRECUENCIA' => '1 VEZ CADA TURNO',
                    'METODO' => 'VISUAL',
                    'CRITERIO' => 'REALIZAR AL INICIO DEL TURNO',
                    'QUIEN REALIZA' => 'OPERARIO'
                ],
                'categoria' => 'limpieza'
            ],
            [
                'items' => [
                    'ITEM' => 'REVISION DE TUBERIA PRINCIPAL',
                    'FRECUENCIA' => '1 VEZ CADA SEMANA',
                    'METODO' => 'VISUAL',
                    'CRITERIO' => 'CHECAR SI HAY FUGAS EN UNIONES',
                    'QUIEN REALIZA' => 'OPERARIO'
                ],
                'categoria' => 'revision'
            ],
            [
                'items' => [
                    'ITEM' => 'REVISION DE LLENADO DE CISTERNA',
                    'FRECUENCIA' => '1 VEZ POR TURNO',
                    'METODO' => 'VISUAL',
                    'CRITERIO' => 'VERIFICAR EL LLEANDO DE LA CISTERNA',
                    'QUIEN REALIZA' => 'OPERARIO'
                ],
                'categoria' => 'revision'
            ],
            [
                'items' => [
                    'ITEM' => 'PRESION DE TRABAJO MANOMETRO',
                    'FRECUENCIA' => '1 VEZ POR TURNO',
                    'METODO' => '',
                    'CRITERIO' => '60 PSI A 80 PSI',
                    'QUIEN REALIZA' => 'OPERARIO'
                ],
                'categoria' => 'revision'
            ],
            [
                'items' => [
                    'ITEM' => 'PRESION DEL AIRE',
                    'FRECUENCIA' => '1 VEZ POR TURNO',
                    'METODO' => '',
                    'CRITERIO' => '58 PSI',
                    'QUIEN REALIZA' => 'OPERARIO'
                ],
                'categoria' => 'revision'
            ]
        ];

        foreach ($items as $item) {
            Item::create([
                'hoja_chequeo_id' => $hoja->id,
                'valores' => $item['items'],
                'categoria' => $item['categoria']
            ]);
        }
    }
    public function fca02(): void {
        $equipo = Equipo::create([
            'tag' => 'FCA-02',
            'nombre' => 'FILTRO CARBON ACTIVADO',
            'numeroControl' => '002',
            'revision' => 'N',
            'area' => HojaChequeoArea::CUARTO_DE_MAQUINAS->value
        ]);

        $hoja = HojaChequeo::create([
            'equipo_id' => $equipo->id,
            'area' => HojaChequeoArea::CUARTO_DE_MAQUINAS->value
        ]);

        $items = [
            [
                'items' => [
                    'ITEM' => 'ANALISIS DE CLORO RESIDUAL',
                    'NORMA' => 'ENTRADA',
                    'FRECUENCIA' => '1 VEZ POR DIA'
                ],
                'categoria' => 'revision'
            ],
            [
                'items' => [
                    'ITEM' => 'ANALISIS DE CLORO RESIDUAL',
                    'NORMA' => 'SALIDA',
                    'FRECUENCIA' => '1 VEZ POR DIA'
                ],
                'categoria' => 'revision'
            ],
            [
                'items' => [
                    'ITEM' => 'RETROLAVADO',
                    'NORMA' => 'CON CLORO>1 PPM',
                    'FRECUENCIA' => 'CADA SEMANA'
                ],
                'categoria' => 'operacion'
            ],
            [
                'items' => [
                    'ITEM' => 'LIMPIEZA GENERAL',
                    'NORMA' => 'LIMPIEZA PROFUNDA',
                    'FRECUENCIA' => 'CADA DIA'
                ],
                'categoria' => 'limpieza'
            ]
        ];

        foreach ($items as $item) {
            Item::create([
                'hoja_chequeo_id' => $hoja->id,
                'valores' => $item['items'],
                'categoria' => $item['categoria']
            ]);
        }
    }
    public function fze03(): void {
        $equipo = Equipo::create([
            'tag' => 'FZE-03',
            'nombre' => 'FILTRO DE ZEOLITA',
            'numeroControl' => '001',
            'revision' => 'N',
            'area' => HojaChequeoArea::CUARTO_DE_MAQUINAS->value
        ]);

        $hoja = HojaChequeo::create([
            'equipo_id' => $equipo->id,
            'area' => HojaChequeoArea::CUARTO_DE_MAQUINAS->value
        ]);

        $items = [
            [
                'items' => [
                    'ITEM' => 'TURBIDEZ',
                    'NORMA' => '<5 NTU',
                    'FRECUENCIA' => '1 VEZ POR DIA'
                ],
                'categoria' => 'revision'
            ],
            [
                'items' => [
                    'ITEM' => 'COLOR DE AGUA EN EL ROTAMETRO',
                    'NORMA' => 'CLARA',
                    'FRECUENCIA' => '1 VEZ POR DIA'
                ],
                'categoria' => 'revision'
            ],
            [
                'items' => [
                    'ITEM' => 'RETROLAVADO',
                    'NORMA' => 'CON TURBIDEZ > 5 NTU',
                    'FRECUENCIA' => 'CADA SEMANA'
                ],
                'categoria' => 'operacion'
            ],
            [
                'items' => [
                    'ITEM' => 'LIMPIEZA GENERAL',
                    'NORMA' => 'LIMPIEZA PROFUNDA',
                    'FRECUENCIA' => 'CADA DIA'
                ],
                'categoria' => 'limpieza'
            ]
        ];

        foreach ($items as $item) {
            Item::create([
                'hoja_chequeo_id' => $hoja->id,
                'valores' => $item['items'],
                'categoria' => $item['categoria']
            ]);
        }
    }

    public function cmTco01(): void {
        $equipo = Equipo::create([
            'tag' => 'CM-TCO-01',
            'nombre' => 'TANQUE DE CONDENSADOS',
            'numeroControl' => '001',
            'revision' => 'N',
            'area' => HojaChequeoArea::CUARTO_DE_MAQUINAS->value
        ]);

        $hoja = HojaChequeo::create([
            'equipo_id' => $equipo->id,
            'area' => HojaChequeoArea::CUARTO_DE_MAQUINAS->value
        ]);

        $items = [
            [
                'items' => [
                    'ITEM DE CHEQUEO' => 'REVISION DE LLENADO',
                    'FRECUENCIA' => '1 VECES POR TURNO',
                    'METODO DE CHEQUEO' => 'VISUAL',
                    'CRITERIO DE DETERMINACION' => 'REVISION DE NIVEL EN MANGUERA',
                    'QUIEN REALIZA' => 'OPERARIO',
                    'OBSERVACIONES' => 'PREVENIR EL VACIADO DEL TANQUE'
                ],
                'categoria' => 'revision'
            ],
            [
                'items' => [
                    'ITEM DE CHEQUEO' => 'REVISION DE VALVULA DE LLENADO',
                    'FRECUENCIA' => '1 VECES POR TURNO',
                    'METODO DE CHEQUEO' => 'VISUAL',
                    'CRITERIO DE DETERMINACION' => 'REVISAR EL CERRADO DE LA LLAVE AL LLENAR TANQUE DE CONDENSADOS',
                    'QUIEN REALIZA' => 'OPERARIO',
                    'OBSERVACIONES' => 'PREVENIR DERRAME POR SOBRELLENADO'
                ],
                'categoria' => 'revision'
            ],
            [
                'items' => [
                    'ITEM DE CHEQUEO' => 'REVISION VALVULA DE SURTIDO',
                    'FRECUENCIA' => '3 VECES POR TURNO',
                    'METODO DE CHEQUEO' => 'VISUAL',
                    'CRITERIO DE DETERMINACION' => 'REVISAR LA APERTURA DE LAS VALVULAS QUE SUMINISTRAN AGUA A LA CALDERA',
                    'QUIEN REALIZA' => 'OPERARIO',
                    'OBSERVACIONES' => 'VERIFICAR QUE LA CALDERA NO SE QUEDE SIN SUMINISTRO DE AGUA'
                ],
                'categoria' => 'revision'
            ],
            [
                'items' => [
                    'ITEM DE CHEQUEO' => 'TEMPERATURA INTERIOR',
                    'FRECUENCIA' => '1 VECES DURANTE EL TURNO',
                    'METODO DE CHEQUEO' => 'MANUAL',
                    'CRITERIO DE DETERMINACION' => '70 °C a 95°C',
                    'QUIEN REALIZA' => 'OPERARIO',
                    'OBSERVACIONES' => ''
                ],
                'categoria' => 'revision'
            ],
            [
                'items' => [
                    'ITEM DE CHEQUEO' => 'COLOR DE AGUA EN LA PRUEBA',
                    'FRECUENCIA' => '1 VECES DURANTE EL TURNO',
                    'METODO DE CHEQUEO' => 'MANUAL',
                    'CRITERIO DE DETERMINACION' => 'A/M',
                    'QUIEN REALIZA' => 'OPERARIO',
                    'OBSERVACIONES' => ''
                ],
                'categoria' => 'revision'
            ],
            [
                'items' => [
                    'ITEM DE CHEQUEO' => '% DUREZA (EDTA)',
                    'FRECUENCIA' => '1 VECES DURANTE EL TURNO',
                    'METODO DE CHEQUEO' => 'MANUAL',
                    'CRITERIO DE DETERMINACION' => '0 PPM',
                    'QUIEN REALIZA' => 'OPERARIO',
                    'OBSERVACIONES' => ''
                ],
                'categoria' => 'revision'
            ],
            [
                'items' => [
                    'ITEM DE CHEQUEO' => 'FLOTADOR',
                    'FRECUENCIA' => 'CADA 4 MESES',
                    'METODO DE CHEQUEO' => 'VISUAL',
                    'CRITERIO DE DETERMINACION' => '',
                    'QUIEN REALIZA' => 'OPERARIO',
                    'OBSERVACIONES' => ''
                ],
                'categoria' => 'revision'
            ],
            [
                'items' => [
                    'ITEM DE CHEQUEO' => 'LIMPIEZA DE MALLA',
                    'FRECUENCIA' => 'CADA 10 DIAS',
                    'METODO DE CHEQUEO' => '',
                    'CRITERIO DE DETERMINACION' => '',
                    'QUIEN REALIZA' => '',
                    'OBSERVACIONES' => ''
                ],
                'categoria' => 'limpieza'
            ],
            [
                'items' => [
                    'ITEM DE CHEQUEO' => 'CUERPO DE TANQUE (LIMPIEZA)',
                    'FRECUENCIA' => 'CADA 4 MESES',
                    'METODO DE CHEQUEO' => '',
                    'CRITERIO DE DETERMINACION' => '',
                    'QUIEN REALIZA' => '',
                    'OBSERVACIONES' => ''
                ],
                'categoria' => 'limpieza'
            ]
        ];

        foreach ($items as $item) {
            Item::create([
                'hoja_chequeo_id' => $hoja->id,
                'valores' => $item['items'],
                'categoria' => $item['categoria']
            ]);
        }
    }
    public function cmCom01(): void {
        $equipo = Equipo::create([
            'tag' => 'CM-COM-01',
            'nombre' => 'COMPRESOR -01',
            'numeroControl' => 'AC-ES-248-RSP-012-2024',
            'revision' => 'NOM-020-STPS-2011',
            'area' => HojaChequeoArea::CUARTO_DE_MAQUINAS->value
        ]);

        $hoja = HojaChequeo::create([
            'equipo_id' => $equipo->id,
            'area' => HojaChequeoArea::CUARTO_DE_MAQUINAS->value
        ]);

        $items = [
            [
                'items' => [
                    'ITEM DE CHEQUEO' => 'PURGA DE FONDO',
                    'FRECUENCIA' => '1 VEZ DURANTE EL TURNO',
                    'CRITERIO DE DETERMINACION' => 'APERTURA DE VALVULA',
                    'OBSERVACIONES' => ''
                ],
                'categoria' => 'operacion'
            ],
            [
                'items' => [
                    'ITEM DE CHEQUEO' => 'DISPARO VALVULA DE SEGURIDAD',
                    'FRECUENCIA' => '1 VEZ DURANTE EL TURNO',
                    'CRITERIO DE DETERMINACION' => 'ACCIOANMIENTO DE VALVULA MODO MANUAL',
                    'OBSERVACIONES' => ''
                ],
                'categoria' => 'operacion'
            ],
            [
                'items' => [
                    'ITEM DE CHEQUEO' => 'ENCENDIDO DE INTERRUPTOR PRINCIPAL',
                    'FRECUENCIA' => '1 VEZ POR TURNO',
                    'CRITERIO DE DETERMINACION' => 'REVISAR QUE ESTE ENCENDIDO EL INTERRUPTOR DEL COMPRESOR AL INICIAR TURNO',
                    'OBSERVACIONES' => 'Pastilla termomagnetica en buen estado del tablero'
                ],
                'categoria' => 'operacion'
            ],
            [
                'items' => [
                    'ITEM DE CHEQUEO' => 'ENCENDIDO DE INTERRUPTOR SECUNDARIO',
                    'FRECUENCIA' => '1 VEZ POR TURNO',
                    'CRITERIO DE DETERMINACION' => 'REVISAR QUE ESTE ENCENDIDO EL INTERRUPTOR DEL COMPRESOR AL INICIAR TURNO',
                    'OBSERVACIONES' => 'Cola de rata en buen estado'
                ],
                'categoria' => 'operacion'
            ],
            [
                'items' => [
                    'ITEM DE CHEQUEO' => 'TEMPERATURA DE PARTES A PRESIÓN',
                    'FRECUENCIA' => '1 VEZ DURANTE EL TURNO',
                    'CRITERIO DE DETERMINACION' => '70°C A 140°C',
                    'OBSERVACIONES' => ''
                ],
                'categoria' => 'revision'
            ],
            [
                'items' => [
                    'ITEM DE CHEQUEO' => 'PRESIÓN MANOMETRICA',
                    'FRECUENCIA' => '1 VEZ DURANTE EL TURNO',
                    'CRITERIO DE DETERMINACION' => '7 kg a 9 kg',
                    'OBSERVACIONES' => ''
                ],
                'categoria' => 'revision'
            ],
            [
                'items' => [
                    'ITEM DE CHEQUEO' => 'FUNCIONAMIENTO DE VALVULA DE ALIVIO',
                    'FRECUENCIA' => '1 VEZ POR TURNO',
                    'CRITERIO DE DETERMINACION' => 'LIBERACION DE PRESION AL LLEGAR A LA PRESION DE TRABAJO',
                    'OBSERVACIONES' => 'Revisar calibracion adecuada'
                ],
                'categoria' => 'revision'
            ],
            [
                'items' => [
                    'ITEM DE CHEQUEO' => 'REVISION DE BANDAS',
                    'FRECUENCIA' => '1 VEZ POR TURNO',
                    'CRITERIO DE DETERMINACION' => 'VERIFICAR EL DESGASTE Y POSICION',
                    'OBSERVACIONES' => 'Tension de la banda'
                ],
                'categoria' => 'revision'
            ],
            [
                'items' => [
                    'ITEM DE CHEQUEO' => 'REVISION FILTRO DE AIRE',
                    'FRECUENCIA' => '1 VEZ POR SEMANA',
                    'CRITERIO DE DETERMINACION' => 'VERIFICAR LA SUCIEDAD DEL FILTRO',
                    'OBSERVACIONES' => 'Limpiar en contra del sentido'
                ],
                'categoria' => 'revision'
            ],
            [
                'items' => [
                    'ITEM DE CHEQUEO' => 'REVISIÓN NIVEL DE ACEITE',
                    'FRECUENCIA' => '1 VEZ POR TURNO',
                    'CRITERIO DE DETERMINACION' => 'REVISAR QUE EL NIVEL DEL ACEITE ESTE A 3/4 DE SU CAPACIDAD',
                    'OBSERVACIONES' => 'Lubricacion de los mecanismos'
                ],
                'categoria' => 'revision'
            ],
            [
                'items' => [
                    'ITEM DE CHEQUEO' => 'LIMPIEZA EXTERIOR DE EQUIPO',
                    'FRECUENCIA' => '1 VEZ POR TURNO',
                    'CRITERIO DE DETERMINACION' => 'LIBERACION DE PRESION AL LLEGAR A LA PRESION DE TRABAJO',
                    'OBSERVACIONES' => 'Exceso de pelusa y grasa'
                ],
                'categoria' => 'limpieza'
            ]
        ];

        foreach ($items as $item) {
            Item::create([
                'hoja_chequeo_id' => $hoja->id,
                'valores' => $item['items'],
                'categoria' => $item['categoria']
            ]);
        }
    }

    public function cmCal($tag) {
        $equipo = Equipo::create([
            'tag'           => 'CM-CAL-0'. $tag,
            'nombre'        => 'CALDERA ' . $tag,
            'area'          => 'CUARTO DE MAQUINAS',
            'foto'          => null,
            'numeroControl' => 'AC-ES-248-GV-391-2024',
            'revision'      => 'NOM-020-STPS-2011'
        ]);

        $hoja = HojaChequeo::create([
            'equipo_id'     => $equipo->id,
            'area'          => HojaChequeoArea::CUARTO_DE_MAQUINAS->value,
            'version'       => 1,
            'observaciones' => null
        ]);

        $items = [
            [
                'items'     => [
                    'ITEM'       => 'LIMPIEZA EXTERIOR DE EQUIPO',
                    'CRITERIO'   => 'REALIZARLO LOS LUNES DESPUES DEL APAGADO DE LA CALDERA',
                    'FRECUENCIA' => '1 VEZ POR QUINCENA'
                ],
                'categoria' => 'limpieza'
            ],
            [
                'items'     => [
                    'ITEM'       => 'LIMPIEZA DEL CRISTAL DEL NIVEL DEL AGUA',
                    'CRITERIO'   => 'REALIZAR DURANTE LAS PURGAS',
                    'FRECUENCIA' => '1 VEZ DURANTE EL TURNO'
                ],
                'categoria' => 'limpieza'
            ],
            [
                'items'     => [
                    'ITEM'       => 'COLOR DE HUMOS A LA SALIDA DE LA CHIMENEA',
                    'CRITERIO'   => 'TRANSPARENTE',
                    'FRECUENCIA' => '1 VEZ DURANTE EL TURNO'
                ],
                'categoria' => 'operacion'
            ],
            [
                'items'     => [
                    'ITEM'       => 'PURGAS DE COLUMNA DE CRISTAL DE NIVEL Y GRIFOS DE LA COLUMNA',
                    'CRITERIO'   => 'SI o No',
                    'FRECUENCIA' => '2 VECES POR TURNO'
                ],
                'categoria' => 'operacion'
            ],
            [
                'items'     => [
                    'ITEM'       => 'PURGA DE VALVULA DE PRUEBA',
                    'CRITERIO'   => 'SI o No',
                    'FRECUENCIA' => '1 VEZ DURANTE EL TURNO'
                ],
                'categoria' => 'operacion'
            ],
            [
                'items'     => [
                    'ITEM'       => 'PURGA DE FONDO',
                    'CRITERIO'   => 'HORA',
                    'FRECUENCIA' => '2 VECES POR TURNO'
                ],
                'categoria' => 'operacion'
            ],
            [
                'items'     => [
                    'ITEM'       => 'CONTROL DE PRESIÓN',
                    'CRITERIO'   => '5,2 KG',
                    'FRECUENCIA' => '1 VEZ DURANTE EL TURNO'
                ],
                'categoria' => 'operacion'
            ],
            [
                'items'     => [
                    'ITEM'       => 'DISPARO DE VALVULA DE SEGURIDAD',
                    'CRITERIO'   => 'ACCIONAMIENTO DE VALVULA MODO MANUAL',
                    'FRECUENCIA' => '1 VEZ AL MES'
                ],
                'categoria' => 'operacion'
            ],
            [
                'items'     => [
                    'ITEM'       => 'ALARMA DE NIVEL',
                    'CRITERIO'   => 'ENCENDIDO Y APAGADO DEL QUEMADOR',
                    'FRECUENCIA' => '1 VEZ DURANTE EL TURNO'
                ],
                'categoria' => 'operacion'
            ],
            [
                'items'     => [
                    'ITEM'       => 'DESINCRUSTANTE',
                    'CRITERIO'   => 'DESPUES DE LA PURGA 5 LT HORA',
                    'FRECUENCIA' => '1 VEZ DURANTE EL TURNO'
                ],
                'categoria' => 'operacion'
            ],
            [
                'items'     => [
                    'ITEM'       => 'TEMPERATURA TERMOMETRO CHIMENEA',
                    'CRITERIO'   => '200°C A 270°C',
                    'FRECUENCIA' => '1 VEZ DURANTE EL TURNO'
                ],
                'categoria' => 'revision'
            ],
            [
                'items'     => [
                    'ITEM'       => 'TEMPERATURAS DE LAS PARTES A PRESIÓN',
                    'CRITERIO'   => '140°C A 160°C',
                    'FRECUENCIA' => '1 VEZ DURANTE EL TURNO'
                ],
                'categoria' => 'revision'
            ],
            [
                'items'     => [
                    'ITEM'       => 'PRESIÓN DE VAPOR',
                    'CRITERIO'   => '4.4 A 5.2 KG/CM2',
                    'FRECUENCIA' => '1 VEZ DURANTE EL TURNO'
                ],
                'categoria' => 'revision'
            ],
            [
                'items'     => [
                    'ITEM'       => 'PRESION DEL COMBUSTIBLE',
                    'CRITERIO'   => null,
                    'FRECUENCIA' => '1 VEZ DURANTE EL TURNO'
                ],
                'categoria' => 'revision'
            ],
            [
                'items'     => [
                    'ITEM'       => 'TEMPERATURA ENTRADA DE AGUA',
                    'CRITERIO'   => '80°C A 90°C',
                    'FRECUENCIA' => '1 VEZ DURANTE EL TURNO'
                ],
                'categoria' => 'revision'
            ],
            [
                'items'     => [
                    'ITEM'       => 'FUNCIONAMIENTO DE LA BOMBA',
                    'CRITERIO'   => 'QUE ENCIENDA Y APAGUE AUTOMATICAMENTE',
                    'FRECUENCIA' => '1 VEZ DURANTE EL TURNO'
                ],
                'categoria' => 'revision'
            ],
            [
                'items'     => [
                    'ITEM'       => 'FUNCIONAMIENTO DEL QUEMADOR',
                    'CRITERIO'   => 'ENCENDIDO Y APAGADO EN MIRILLA',
                    'FRECUENCIA' => '1 VEZ DURANTE EL TURNO'
                ],
                'categoria' => 'revision'
            ],
            [
                'items'     => [
                    'ITEM'       => 'SUMINISTRO DE AGUA',
                    'CRITERIO'   => 'REVISAR NIVEL EN MANGUERA',
                    'FRECUENCIA' => '1 VEZ DURANTE EL TURNO'
                ],
                'categoria' => 'revision'
            ]
        ];

        foreach ($items as $item) {
            Item::create([
                'hoja_chequeo_id' => $hoja->id,
                'valores'         => $item['items'],
                'categoria'       => $item['categoria']
            ]);
        }
    }

    public function lavanderia() {
        $this->lvMgl01(1);
        $this->lvMgl01(2);

        $area = HojaChequeoArea::LAVANDERIA_INSTITUCIONAL->value;

        $this->tombolas($area);
        $this->crearLavadoraLavanderia("LA-UNI-01", "UNIMAC 01");
        $this->crearLavadoraLavanderia('LA02-UNI-01', 'UNIMAC 01 2');
        $this->crearLavadoraLavanderia('LA-PRI-01', 'PRIMUS 1');
        $this->crearLavadoraLavanderia('LA02-PRI-01', 'PRIMUS 1 2');
        $this->crearLavadoraLavanderia('LA-PRI-02', 'PRIMUS 2');
        $this->crearLavadoraLavanderia('LAVREN-03', 'LAVADORA RENZACCI 03');
        $this->crearLavadoraLavanderia('LAVREN-05', 'LAVADORA RENZACCI 05');
        $this->crearLavadoraLavanderia('STH-06', 'STAHL 06');
        $this->crearLavadoraLavanderia('STH-07', 'STAHL 07');
        $this->crearLavadoraLavanderia('STH-09', 'STAHL 09');
        $this->crearLavadoraLavanderia('STH-10', 'STAHL 10');
        $this->crearLavadoraLavanderia('STH-11', 'STAHL 11');
        $this->crearLavadoraLavanderia('LG-08', 'LG 08');
    }

    public function tintoreria(): void {
        $this->lsHdc02();
        $this->lsPer01();

        $area = HojaChequeoArea::TINTORERIA->value;
        $this->tombolas($area);
        $this->crearLavadoraTintoreria('LA-UNI-01', 'UNIMAC 01');
        $this->crearLavadoraTintoreria('LA02-UNI-01', 'UNIMAC 01 2');
        $this->crearLavadoraTintoreria('LA-PRI-01', 'PRIMUS 1');
        $this->crearLavadoraTintoreria('LA02-PRI-01', 'PRIMUS 1 2');
        $this->crearLavadoraTintoreria('LA-PRI-02', 'PRIMUS 2');
        $this->crearLavadoraTintoreria('LAVREN-03', 'LAVADORA RENZACCI 03');
        $this->crearLavadoraTintoreria('LAVREN-05', 'LAVADORA RENZACCI 05');
        $this->crearLavadoraTintoreria('STH-06', 'STAHL 06');
        $this->crearLavadoraTintoreria('STH-07', 'STAHL 07');
        $this->crearLavadoraTintoreria('STH-09', 'STAHL 09');
        $this->crearLavadoraTintoreria('STH-10', 'STAHL 10');
        $this->crearLavadoraTintoreria('STH-11', 'STAHL 11');
        $this->crearLavadoraTintoreria('LG-08', 'LG 08');
    }

    public function tombolas($area) {
        $this->crearSecadora('LA02-TOM-01', 'TOMBOLA 1', $area);
        $this->crearSecadora('LA-TOM-07', 'TOMBOLA 7', $area);
        $this->crearSecadora('LA-TOM-08', 'TOMBOLA 8', $area);
        $this->crearSecadora('TOM-09', 'TOMBOLA 9', $area);
        $this->crearSecadora('TOM-10', 'TOMBOLA 10', $area);
        $this->crearSecadora('TOM-11', 'TOMBOLA 11', $area);
        $this->crearSecadora('TOM-12', 'TOMBOLA 12', $area);
        $this->crearSecadora('TOM-13', 'TOMBOLA 13', $area);
    }

    public function crearLavadoraTintoreria($tag, $nombre) {
        $equipo = Equipo::where('tag', $tag)->first();
        if(!$equipo)
        $equipo = Equipo::create([
            'tag'           => $tag,
            'nombre'        => $nombre,
            'area'          => 'LAVADO EN AGUA',
            'foto'          => null,
            'numeroControl' => '001',
            'revision'      => 'N'
        ]);

        $hoja = HojaChequeo::create([
            'equipo_id'     => $equipo->id,
            'area'          => HojaChequeoArea::TINTORERIA->value,
            'version'       => 1,
            'observaciones' => "<ul><li>DESCARGUE LA MAQUINA RAPIDAMENTE DESPUES DE CADA CICLO COMPLETADO PARA EVITAR LA ACUMULACION DE HUMEDAD.</li><li>DEJE LA PUERTA DE CARGA ABIERTA DESPUES DE CADA CICLO PARA PERMITIR QUE LA HUMEDAD SE EVAPORE.</li><li>CERRAR LAS VALVULAS PRINCIPALES DE AGUA, VAPOR  AL FINAL DE LA JORNADA ASI COMO TAMBIEN DESENERGIZAR EL EQUIPO.</li><li>EN LA OPCION DEL BLOQUEO DE PUERTA SE REALIZARA LOS SIGUIENTES PASOS:</li><ol><li>Intentar correr un programa  con la puerta abierta  (no debe funcionar).</li><li>Cierre la puerta y arranque la maquina después intente abrir la puerta mientras el ciclo esta en proceso (no debe de abrir).</li></ol></ul>"
        ]);

        $items = [
            [
                'items'     => [
                    'ITEM'          => 'TUBERIA',
                    'FRECUENCIA'    => 'AL INICIO DEL TURNO',
                    'METODO'        => 'MANUAL',
                    'CRITERIO'      => 'EXCESO DE POLVO Y RESPECTIVO AISLANTE',
                    'OBSERVACIONES' => 'LIMPIEZA DE TUBERIA PARA EVITAR POLVO Y/O PELUSA'
                ],
                'categoria' => 'limpieza'
            ],
            [
                'items'     => [
                    'ITEM'          => 'LIMPIEZA EXTERIOR DE EQUIPO',
                    'FRECUENCIA'    => 'AL INICIO DEL TURNO',
                    'METODO'        => 'MANUAL',
                    'CRITERIO'      => 'EXCESO DE POLVO Y RESPECTIVO AISLANTE',
                    'OBSERVACIONES' => 'QUE NO TENGA SUCIEDAD EL GABINETE, LA JUNTA DE LA PUERTA Y RETIRAR LOS RESIDUOS DE DETERGENTE DEL DEPOSITO DE JABON'
                ],
                'categoria' => 'limpieza'
            ],
            [
                'items'     => [
                    'ITEM'          => 'LIMPIEZA PARTE TRASERA DE EQUIPO',
                    'FRECUENCIA'    => 'AL INICIO DEL TURNO',
                    'METODO'        => 'MANUAL',
                    'CRITERIO'      => 'SIN POLVO Y SIN GRASA',
                    'OBSERVACIONES' => 'REVISAR QUE EL CABLE ELECTRICO QUE ESTE BIEN SUJETADO Y QUITAR POLVO O PELUSA DE LA TAPA.'
                ],
                'categoria' => 'limpieza'
            ],
            [
                'items'     => [
                    'ITEM'          => 'LIMPIEZA DE PISOS',
                    'FRECUENCIA'    => 'AL INICIO DEL TURNO',
                    'METODO'        => 'MANUAL',
                    'CRITERIO'      => 'QUE NO TENGA POLVO O GRASA Y OBJETOS AJENOS AL LUGAR.',
                    'OBSERVACIONES' => 'NO DEBE HABER GRASA EN PISO Y/O OBJETOS EXTRAÑOS AL LUGAR'
                ],
                'categoria' => 'limpieza'
            ],
            [
                'items'     => [
                    'ITEM'          => 'SUMINISTRO DE AGUA, VAPOR Y ELECTRICIDAD',
                    'FRECUENCIA'    => 'AL INICIO DEL TURNO',
                    'METODO'        => 'VISUAL',
                    'CRITERIO'      => 'VERIFICAR QUE SE CUENTE  CON LOS SERVICIOS',
                    'OBSERVACIONES' => 'AL INICIO DE LA JORNADA ABRIR VALVULAS HACIA EL EQUIPO ASI COMO ENERGIZARLO. CERRAR VALVULAS Y DESENERGIZAR EL EQUIPO AL FINALIZAR LA JORNADA LABORAL'
                ],
                'categoria' => 'revision'
            ],
            [
                'items'     => [
                    'ITEM'          => 'CONTROL DE ENCENDIDO Y APAGADO',
                    'FRECUENCIA'    => 'AL INICIO DEL TURNO',
                    'METODO'        => 'MANUAL',
                    'CRITERIO'      => 'AL INICIAR LA JORNADA VERIFICAR QUE ESTE ENERGIZADO',
                    'OBSERVACIONES' => 'QUE ESTE EN MODO ENCENDIDO Y QUE EL DISPLAY ESTE ENCENDIDO'
                ],
                'categoria' => 'operacion'
            ],
            [
                'items'     => [
                    'ITEM'          => 'FUGAS DE AGUA',
                    'FRECUENCIA'    => 'AL INICIO Y DURANTE EL TURNO',
                    'METODO'        => 'REVISAR FISICAMENTE',
                    'CRITERIO'      => 'QUE NO GOTEEN LAS TUBERIAS',
                    'OBSERVACIONES' => 'GENERA ENCHARCAMIENTO DE AGUA Y DAÑA TUBERIAS'
                ],
                'categoria' => 'revision'
            ],
            [
                'items'     => [
                    'ITEM'          => 'FUGAS DE VAPOR',
                    'FRECUENCIA'    => 'AL INICIO Y DURANTE EL TURNO',
                    'METODO'        => 'REVISAR FISICAMENTE',
                    'CRITERIO'      => 'FUGAS DE VAPOR EN CONEXIONES',
                    'OBSERVACIONES' => 'NO DEBEN EXISTIR FUGAS EN LAS CONEXIONES'
                ],
                'categoria' => 'revision'
            ],
            [
                'items'     => [
                    'ITEM'          => 'PURGA',
                    'FRECUENCIA'    => 'AL INICIO DEL TURNO',
                    'METODO'        => 'MANUAL',
                    'CRITERIO'      => 'AL INICIAR LA JORNADA ABRIR LENTAMENTE LA VALVULA HASTA QUE SALGA VAPOR Y DESPUES CERRARLA.',
                    'OBSERVACIONES' => 'EVITAR CONDENSADOS DENTRO DEL EQUIPO Y TUBERIAS.'
                ],
                'categoria' => 'operacion'
            ],
            [
                'items'     => [
                    'ITEM'          => 'MOTOR',
                    'FRECUENCIA'    => '1 VEZ POR TURNO',
                    'METODO'        => 'REVISAR FISICAMENTE',
                    'CRITERIO'      => 'FUNCIONAMIENTO OPTIMO',
                    'OBSERVACIONES' => 'QUE GIRE LA CANASTA ADECUADAMENTE Y QUE NO EXISTA RUIDOS EXTRAÑOS'
                ],
                'categoria' => 'operacion'
            ],
            [
                'items'     => [
                    'ITEM'          => 'BLOQUEO DE PUERTA',
                    'FRECUENCIA'    => '1 VEZ POR TURNO',
                    'METODO'        => 'REVISAR FISICAMENTE',
                    'CRITERIO'      => 'CERRADURA Y ENCLAVAMIENTO DE LA PUERTA',
                    'OBSERVACIONES' => 'QUE CIERRE ADECUADAMENTE EN CUALQUIERA DE SUS ACTIVIDADES (VER NOTAS)'
                ],
                'categoria' => 'operacion'
            ],
            [
                'items'     => [
                    'ITEM'          => 'CICLOS',
                    'FRECUENCIA'    => 'AL FINAL DEL TURNO',
                    'METODO'        => 'MANUAL',
                    'CRITERIO'      => 'ANOTAR LOS CICLOS TRABAJADOS',
                    'OBSERVACIONES' => 'AL FINAL DE LA JORNADA ANOTAR SUS CICLOS'
                ],
                'categoria' => 'operacion'
            ],
            [
                'items'     => [
                    'ITEM'          => 'CANASTA',
                    'FRECUENCIA'    => '1 VEZ POR TURNO',
                    'METODO'        => 'REVISAR VISUALMENTE',
                    'CRITERIO'      => 'QUE GIRE LA CANASTA',
                    'OBSERVACIONES' => 'CUANDO COLOQUE LA CARGA SE CERCIORE QUE LA CANASTA GIRE'
                ],
                'categoria' => 'operacion'
            ]
        ];

        foreach ($items as $item) {
            Item::create([
                'hoja_chequeo_id' => $hoja->id,
                'valores'         => $item['items'],
                'categoria'       => $item['categoria']
            ]);
        }
    }

    public function crearLavadoraLavanderia($tag, $nombre) {
        $equipo = Equipo::where('tag', $tag)->first();
        if(!$equipo)
        $equipo = Equipo::create([
            'tag'           => $tag,
            'nombre'        => $nombre,
            'area'          => 'LAVANDERIA',
            'foto'          => null,
            'numeroControl' => '001',
            'revision'      => 'N'
        ]);

        $hoja = HojaChequeo::create([
            'equipo_id'     => $equipo->id,
            'area'          => HojaChequeoArea::LAVANDERIA_INSTITUCIONAL->value,
            'version'       => 1,
            'observaciones' => "<ul><li>DESCARGUE LA MAQUINA RAPIDAMENTE DESPUES DE CADA CICLO COMPLETADO PARA EVITAR LA ACUMULACION DE HUMEDAD.</li><li>DEJE LA PUERTA DE CARGA ABIERTA DESPUES DE CADA CICLO PARA PERMITIR QUE LA HUMEDAD SE EVAPORE.</li><li>CERRAR LAS VALVULAS PRINCIPALES DE AGUA, VAPOR  AL FINAL DE LA JORNADA ASI COMO TAMBIEN DESENERGIZAR EL EQUIPO.</li><li>EN LA OPCION DEL BLOQUEO DE PUERTA SE REALIZARA LOS SIGUIENTES PASOS:</li><ol><li>Intentar correr un programa  con la puerta abierta  (no debe funcionar).</li><li>Cierre la puerta y arranque la maquina después intente abrir la puerta mientras el ciclo esta en proceso (no debe de abrir).</li></ol></ul>"
        ]);

        $items = [
            [
                'items'     => [
                    'ITEM'          => 'TUBERIA',
                    'FRECUENCIA'    => 'AL INICIO DEL TURNO',
                    'METODO'        => 'MANUAL',
                    'CRITERIO'      => 'EXCESO DE POLVO Y RESPECTIVO AISLANTE',
                    'OBSERVACIONES' => 'LIMPIEZA DE TUBERIA PARA EVITAR POLVO Y/O PELUSA'
                ],
                'categoria' => 'limpieza'
            ],
            [
                'items'     => [
                    'ITEM'          => 'LIMPIEZA EXTERIOR DE EQUIPO',
                    'FRECUENCIA'    => 'AL INICIO DEL TURNO',
                    'METODO'        => 'MANUAL',
                    'CRITERIO'      => 'EXCESO DE POLVO Y RESPECTIVO AISLANTE',
                    'OBSERVACIONES' => 'QUE NO TENGA SUCIEDAD EL GABINETE, LA JUNTA DE LA PUERTA Y RETIRAR LOS RESIDUOS DE DETERGENTE DEL DEPOSITO DE JABON'
                ],
                'categoria' => 'limpieza'
            ],
            [
                'items'     => [
                    'ITEM'          => 'LIMPIEZA PARTE TRASERA DE EQUIPO',
                    'FRECUENCIA'    => 'AL INICIO DEL TURNO',
                    'METODO'        => 'MANUAL',
                    'CRITERIO'      => 'SIN POLVO Y SIN GRASA',
                    'OBSERVACIONES' => 'REVISAR QUE EL CABLE ELECTRICO QUE ESTE BIEN SUJETADO Y QUITAR POLVO O PELUSA DE LA TAPA.'
                ],
                'categoria' => 'limpieza'
            ],
            [
                'items'     => [
                    'ITEM'          => 'LIMPIEZA DE PISOS',
                    'FRECUENCIA'    => 'AL INICIO DEL TURNO',
                    'METODO'        => 'MANUAL',
                    'CRITERIO'      => 'QUE NO TENGA POLVO O GRASA Y OBJETOS AJENOS AL LUGAR.',
                    'OBSERVACIONES' => 'NO DEBE HABER GRASA EN PISO Y/O OBJETOS EXTRAÑOS AL LUGAR'
                ],
                'categoria' => 'limpieza'
            ],
            [
                'items'     => [
                    'ITEM'          => 'SUMINISTRO DE AGUA, VAPOR Y ELECTRICIDAD',
                    'FRECUENCIA'    => 'AL INICIO DEL TURNO',
                    'METODO'        => 'VISUAL',
                    'CRITERIO'      => 'VERIFICAR QUE SE CUENTE  CON LOS SERVICIOS',
                    'OBSERVACIONES' => 'AL INICIO DE LA JORNADA ABRIR VALVULAS HACIA EL EQUIPO ASI COMO ENERGIZARLO. CERRAR VALVULAS Y DESENERGIZAR EL EQUIPO AL FINALIZAR LA JORNADA LABORAL'
                ],
                'categoria' => 'revision'
            ],
            [
                'items'     => [
                    'ITEM'          => 'CONTROL DE ENCENDIDO Y APAGADO',
                    'FRECUENCIA'    => 'AL INICIO DEL TURNO',
                    'METODO'        => 'MANUAL',
                    'CRITERIO'      => 'AL INICIAR LA JORNADA VERIFICAR QUE ESTE ENERGIZADO',
                    'OBSERVACIONES' => 'QUE ESTE EN MODO ENCENDIDO Y QUE EL DISPLAY ESTE ENCENDIDO'
                ],
                'categoria' => 'operacion'
            ],
            [
                'items'     => [
                    'ITEM'          => 'FUGAS DE AGUA',
                    'FRECUENCIA'    => 'AL INICIO Y DURANTE EL TURNO',
                    'METODO'        => 'REVISAR FISICAMENTE',
                    'CRITERIO'      => 'QUE NO GOTEEN LAS TUBERIAS',
                    'OBSERVACIONES' => 'GENERA ENCHARCAMIENTO DE AGUA Y DAÑA TUBERIAS'
                ],
                'categoria' => 'revision'
            ],
            [
                'items'     => [
                    'ITEM'          => 'FUGAS DE VAPOR',
                    'FRECUENCIA'    => 'AL INICIO Y DURANTE EL TURNO',
                    'METODO'        => 'REVISAR FISICAMENTE',
                    'CRITERIO'      => 'FUGAS DE VAPOR EN CONEXIONES',
                    'OBSERVACIONES' => 'NO DEBEN EXISTIR FUGAS EN LAS CONEXIONES'
                ],
                'categoria' => 'revision'
            ],
            [
                'items'     => [
                    'ITEM'          => 'PURGA',
                    'FRECUENCIA'    => 'AL INICIO DEL TURNO',
                    'METODO'        => 'MANUAL',
                    'CRITERIO'      => 'AL INICIAR LA JORNADA ABRIR LENTAMENTE LA VALVULA HASTA QUE SALGA VAPOR Y DESPUES CERRARLA.',
                    'OBSERVACIONES' => 'EVITAR CONDENSADOS DENTRO DEL EQUIPO Y TUBERIAS.'
                ],
                'categoria' => 'operacion'
            ],
            [
                'items'     => [
                    'ITEM'          => 'MOTOR',
                    'FRECUENCIA'    => '1 VEZ POR TURNO',
                    'METODO'        => 'REVISAR FISICAMENTE',
                    'CRITERIO'      => 'FUNCIONAMIENTO OPTIMO',
                    'OBSERVACIONES' => 'QUE GIRE LA CANASTA ADECUADAMENTE Y QUE NO EXISTA RUIDOS EXTRAÑOS'
                ],
                'categoria' => 'operacion'
            ],
            [
                'items'     => [
                    'ITEM'          => 'BLOQUEO DE PUERTA',
                    'FRECUENCIA'    => '1 VEZ POR TURNO',
                    'METODO'        => 'REVISAR FISICAMENTE',
                    'CRITERIO'      => 'CERRADURA Y ENCLAVAMIENTO DE LA PUERTA',
                    'OBSERVACIONES' => 'QUE CIERRE ADECUADAMENTE EN CUALQUIERA DE SUS ACTIVIDADES (VER NOTAS)'
                ],
                'categoria' => 'operacion'
            ],
            [
                'items'     => [
                    'ITEM'          => 'DOSIFICADORES',
                    'FRECUENCIA'    => 'AL INICIO Y FINAL DE TURNO',
                    'METODO'        => 'REVISAR FISICAMENTE',
                    'CRITERIO'      => 'AL INICIO: ENCENDER DOSIFICADOR Y ABRIR LA VALVULA DE PASO DE AGUA AL FINAL: CERRAR VALVULA Y APAGAR DOSIFICADOR',
                    'OBSERVACIONES' => 'EVITAR FUGAS DE AGUA'
                ],
                'categoria' => 'revision'
            ],
            [
                'items'     => [
                    'ITEM'          => 'PRODUCTO',
                    'FRECUENCIA'    => '1 VEZ POR TURNO',
                    'METODO'        => 'REVISAR FISICAMENTE',
                    'CRITERIO'      => 'REVISAR QUE LOS BIDONES TENGAN PRODUCTO',
                    'OBSERVACIONES' => 'EVITAR PAROS DE PRODUCCIÒN'
                ],
                'categoria' => 'revision'
            ],
            [
                'items'     => [
                    'ITEM'          => 'CICLOS',
                    'FRECUENCIA'    => 'AL FINAL DEL TURNO',
                    'METODO'        => 'MANUAL',
                    'CRITERIO'      => 'ANOTAR LOS CICLOS TRABAJADOS',
                    'OBSERVACIONES' => 'AL FINAL DE LA JORNADA ANOTAR SUS CICLOS'
                ],
                'categoria' => 'operacion'
            ],
            [
                'items'     => [
                    'ITEM'          => 'CANASTA',
                    'FRECUENCIA'    => '1 VEZ POR TURNO',
                    'METODO'        => 'REVISAR VISUALMENTE',
                    'CRITERIO'      => 'QUE GIRE LA CANASTA',
                    'OBSERVACIONES' => 'CUANDO COLOQUE LA CARGA SE CERCIORE QUE LA CANASTA GIRE'
                ],
                'categoria' => 'operacion'
            ]
        ];

        foreach ($items as $item) {
            Item::create([
                'hoja_chequeo_id' => $hoja->id,
                'valores'         => $item['items'],
                'categoria'       => $item['categoria']
            ]);
        }
    }

    public function crearSecadora($tag, $nombre, $hojaArea) {
        $equipo = Equipo::where('tag', $tag)->first();
        if(!$equipo)
        $equipo = Equipo::create([
            'tag'           => $tag,
            'nombre'        => $nombre,
            'area'          => 'LAVADO EN AGUA',
            'foto'          => null,
            'numeroControl' => '009',
            'revision'      => 'N'
        ]);

        $hoja = HojaChequeo::create([
            'equipo_id'     => $equipo->id,
            'area'          => $hojaArea,
            'version'       => 1,
            'observaciones' => null
        ]);

        $items = [
            [
                'items'     => [
                    'ITEM'          => 'CONTROL DE ENCENDIDO Y APAGADO',
                    'FRECUENCIA'    => '1 VEZ AL INICIO DEL TURNO (CUANDO SE REQUIERA)',
                    'METODO'        => null,
                    'CRITERIO'      => 'FUNCIONAMIENTO OPTIMO',
                    'OBSERVACIONES' => null
                ],
                'categoria' => 'operacion'
            ],
            [
                'items'     => [
                    'ITEM'          => 'MOTOR',
                    'FRECUENCIA'    => '1 VEZ AL INICIO DE TURNO',
                    'METODO'        => null,
                    'CRITERIO'      => 'RUIDO EXTRAÑO',
                    'OBSERVACIONES' => null
                ],
                'categoria' => 'revision'
            ],
            [
                'items'     => [
                    'ITEM'          => 'CABLE DE ENERGIA ELECTRICA',
                    'FRECUENCIA'    => '1 VEZ POR TURNO',
                    'METODO'        => null,
                    'CRITERIO'      => 'SIN DAÑO',
                    'OBSERVACIONES' => null
                ],
                'categoria' => 'revision'
            ],
            [
                'items'     => [
                    'ITEM'          => 'CICLOS',
                    'FRECUENCIA'    => 'AL FINAL DEL DIA',
                    'METODO'        => null,
                    'CRITERIO'      => 'TOTALES',
                    'OBSERVACIONES' => null
                ],
                'categoria' => 'revision'
            ],
            [
                'items'     => [
                    'ITEM'          => 'FUGAS DE VAPOR',
                    'FRECUENCIA'    => 'AL INICIO DEL TURNO',
                    'METODO'        => 'REVISAR FISICAMENTE',
                    'CRITERIO'      => 'QUE NO GOTEEN LAS TUBERIAS',
                    'OBSERVACIONES' => 'GENERA ENCHARCAMIENTO DE AGUA Y DAÑA TUBERIAS'
                ],
                'categoria' => 'revision'
            ],
            [
                'items'     => [
                    'ITEM'          => 'LIMPIEZA DE PISOS',
                    'FRECUENCIA'    => '1 VEZ POR TURNO',
                    'METODO'        => 'MANUAL',
                    'CRITERIO'      => 'QUE NO TENGA POLVO O GRASA',
                    'OBSERVACIONES' => 'NO DEBE HABER GRASA EN PISO'
                ],
                'categoria' => 'limpieza'
            ],
            [
                'items'     => [
                    'ITEM'          => 'LIMPIEZA EXTERIOR DE EQUIPO',
                    'FRECUENCIA'    => '1 VEZ POR TURNO',
                    'METODO'        => 'MANUAL',
                    'CRITERIO'      => 'QUE NO TENGA POLVO',
                    'OBSERVACIONES' => 'QUITAR EL EXCESO DE PELUSA PARA EL MEJOR FUNCIONAMIENTO DEL EQUIPO'
                ],
                'categoria' => 'limpieza'
            ],
            [
                'items'     => [
                    'ITEM'          => 'TUBERIA',
                    'FRECUENCIA'    => '1 VEZ POR TURNO',
                    'METODO'        => 'VISUAL',
                    'CRITERIO'      => 'EXCESO DE POLVO',
                    'OBSERVACIONES' => 'NO DEBE EXISTIR FUGAS'
                ],
                'categoria' => 'limpieza'
            ],
            [
                'items'     => [
                    'ITEM'          => 'LIMPIEZA DE MOTOR',
                    'FRECUENCIA'    => '1 VEZ POR SEMANA',
                    'METODO'        => 'LIMPIEZA MANUAL',
                    'CRITERIO'      => 'QUE NO TENGA POLVO O GRASA',
                    'OBSERVACIONES' => 'QUITAR LA PELUSA Y POLVO, PARA QUE EL MOTOR NO SE FORCE'
                ],
                'categoria' => 'limpieza'
            ],
            [
                'items'     => [
                    'ITEM'          => 'LIMPIEZA PARTE TRASERA DE EQUIPO',
                    'FRECUENCIA'    => '1 VEZ POR SEMANA',
                    'METODO'        => 'LIMPIEZA MANUAL',
                    'CRITERIO'      => 'SIN POLVO Y SIN GRASA',
                    'OBSERVACIONES' => 'QUITAR EXCESO DE POLVO, GRASA U OTRO MATERIAL QUE AFECTE AL FUNCIONAMIENTO DEL EQUIPO.'
                ],
                'categoria' => 'limpieza'
            ],
            [
                'items'     => [
                    'ITEM'          => 'LIMPIEZA DE TUBO (CHIMENEA)',
                    'FRECUENCIA'    => '1 VEZ POR SEMANA',
                    'METODO'        => 'LIMPIEZA MANUAL',
                    'CRITERIO'      => 'SIN POLVO',
                    'OBSERVACIONES' => 'QUITAR EL EXCESO DE PELUSA PARA EL MEJOR FUNCIONAMIENTO DEL EQUIPO'
                ],
                'categoria' => 'limpieza'
            ],
            [
                'items'     => [
                    'ITEM'          => 'LIMPIEZA DE FILTROS',
                    'FRECUENCIA'    => 'AL INICIO DEL TURNO Y DESPUES DE COMIDA',
                    'METODO'        => 'LIMPIEZA MANUAL',
                    'CRITERIO'      => 'EXCESO DE PELUSA',
                    'OBSERVACIONES' => 'QUITAR EL EXCESO DE PELUSA PARA EL MEJOR FUNCIONAMIENTO DEL EQUIPO'
                ],
                'categoria' => 'limpieza'
            ]
        ];

        foreach ($items as $item) {
            Item::create([
                'hoja_chequeo_id' => $hoja->id,
                'valores'         => $item['items'],
                'categoria'       => $item['categoria']
            ]);
        }
    }

    public function lvMgl01($n) {
        $equipo = Equipo::create([
            'tag'           => 'LV-MGL-0' . $n,
            'nombre'        => 'MANGLE 0' . $n,
            'area'          => 'LAVANDERIA INSTITUCIONAL',
            'foto'          => null,
            'numeroControl' => '001',
            'revision'      => 'N'
        ]);

        $hoja = HojaChequeo::create([
            'equipo_id'     => $equipo->id,
            'area'          => HojaChequeoArea::LAVANDERIA_INSTITUCIONAL->value,
            'version'       => 1,
            'observaciones' => null
        ]);

        $items = [
            [
                'items'     => [
                    'ITEM'          => 'CONTROL DE ENCENDIDO Y APAGADO',
                    'FRECUENCIA'    => '1 VEZ AL INICIO DEL TURNO (CUANDO SE REQUIERA)',
                    'METODO'        => null,
                    'CRITERIO'      => 'FUNCIONAMIENTO OPTIMO',
                    'OBSERVACIONES' => null
                ],
                'categoria' => 'operacion'
            ],
            [
                'items'     => [
                    'ITEM'          => 'BOTON PARO DE EMERGENCIA',
                    'FRECUENCIA'    => '1 VEZ AL INICIO DEL TURNO (CUANDO SE REQUIERA)',
                    'METODO'        => null,
                    'CRITERIO'      => 'FUNCIONAMIENTO OPTIMO',
                    'OBSERVACIONES' => null
                ],
                'categoria' => 'operacion'
            ],
            [
                'items'     => [
                    'ITEM'          => 'BARRA PARO DE EMERGENCIA',
                    'FRECUENCIA'    => '1 VEZ AL INICIO DEL TURNO (CUANDO SE REQUIERA)',
                    'METODO'        => null,
                    'CRITERIO'      => 'FUNCIONAMIENTO OPTIMO',
                    'OBSERVACIONES' => null
                ],
                'categoria' => 'operacion'
            ],
            [
                'items'     => [
                    'ITEM'          => 'TERMOSTATO DE TEMPERATURA',
                    'FRECUENCIA'    => '1 VEZ AL INICIO DEL TURNO (CUANDO SE REQUIERA)',
                    'METODO'        => null,
                    'CRITERIO'      => 'FUNCIONAMIENTO OPTIMO',
                    'OBSERVACIONES' => null
                ],
                'categoria' => 'operacion'
            ],
            [
                'items'     => [
                    'ITEM'          => 'CABLE DE ENERGIA ELECTRICA',
                    'FRECUENCIA'    => '1 VEZ POR TURNO',
                    'METODO'        => null,
                    'CRITERIO'      => 'SIN DAÑO',
                    'OBSERVACIONES' => null
                ],
                'categoria' => 'revision'
            ],
            [
                'items'     => [
                    'ITEM'          => 'FUGAS DE GAS L.P.',
                    'FRECUENCIA'    => 'AL INICIO DEL TURNO',
                    'METODO'        => 'REVISAR FISICAMENTE',
                    'CRITERIO'      => 'FUGAS DE GAS EN CONEXIONES',
                    'OBSERVACIONES' => 'NO DEBEN EXISTIR FUGAS EN LAS CONEXIONES'
                ],
                'categoria' => 'revision'
            ],
            [
                'items'     => [
                    'ITEM'          => 'ZAPATAS DE CONTACTO',
                    'FRECUENCIA'    => '1 VEZ POR TURNO',
                    'METODO'        => 'MANUAL',
                    'CRITERIO'      => 'ELIMINAR EL EXCESO DE POLVO Y RESPECTIVO AISLANTE',
                    'OBSERVACIONES' => 'CUANDO ESTE APAGADA Y FRIA'
                ],
                'categoria' => 'limpieza'
            ],
            [
                'items'     => [
                    'ITEM'          => 'CINTAS GUIAS',
                    'FRECUENCIA'    => '1 VEZ POR TURNO',
                    'METODO'        => 'VISUAL',
                    'CRITERIO'      => 'QUE NO ESTEN ROTAS, DESGASTADAS, FUERA DE SU LUGAR Y ENGRAPADAS',
                    'OBSERVACIONES' => 'CUANDO ESTE APAGADA Y FRIA'
                ],
                'categoria' => 'revision'
            ],
            [
                'items'     => [
                    'ITEM'          => 'LIMPIEZA INTERIOR DEL EQUIPO',
                    'FRECUENCIA'    => '1 VEZ POR TURNO',
                    'METODO'        => 'MANUAL',
                    'CRITERIO'      => 'ELIMINAR POLVO, PELUSA Y MATERIALES EXTRAÑOS.',
                    'OBSERVACIONES' => 'CUANDO ESTE APAGADA Y FRIA'
                ],
                'categoria' => 'limpieza'
            ],
            [
                'items'     => [
                    'ITEM'          => 'LIMPIEZA EXTERIOR DEL EQUIPO',
                    'FRECUENCIA'    => '1 VEZ POR TURNO',
                    'METODO'        => 'MANUAL',
                    'CRITERIO'      => 'ELIMINAR POLVO, PELUSA Y MATERIALES EXTRAÑOS.',
                    'OBSERVACIONES' => 'CUANDO ESTE APAGADA Y FRIA'
                ],
                'categoria' => 'limpieza'
            ],
            [
                'items'     => [
                    'ITEM'          => 'LIMPIEZA DE PISOS',
                    'FRECUENCIA'    => '2 VEZ POR TURNO',
                    'METODO'        => 'MANUAL',
                    'CRITERIO'      => 'QUE NO TENGA POLVO O GRASA Y OBJETOS AJENOS AL LUGAR.',
                    'OBSERVACIONES' => 'NO DEBE HABER GRASA EN PISO'
                ],
                'categoria' => 'limpieza'
            ],
            [
                'items'     => [
                    'ITEM'          => 'PASAR SABANA CON CERA',
                    'FRECUENCIA'    => '1 VEZ POR TURNO',
                    'METODO'        => 'MANUAL',
                    'CRITERIO'      => 'AL INICIO DE LA JORNADA',
                    'OBSERVACIONES' => 'LUBRICA LOS RODILLOS'
                ],
                'categoria' => 'operacion'
            ],
            [
                'items'     => [
                    'ITEM'          => 'COLOCAR CERA A LA SABANA',
                    'FRECUENCIA'    => 'CADA 4 DÍAS',
                    'METODO'        => 'MANUAL',
                    'CRITERIO'      => 'AL INICIO DE LA JORNADA',
                    'OBSERVACIONES' => 'PARA MANTENER LA SABANA CON CERA'
                ],
                'categoria' => 'operacion'
            ]
        ];

        foreach ($items as $item) {
            Item::create([
                'hoja_chequeo_id' => $hoja->id,
                'valores'         => $item['items'],
                'categoria'       => $item['categoria']
            ]);
        }
    }

    public function lsPer01() {
        $equipo = Equipo::create([
            'tag'           => 'LS-PER-01',
            'nombre'        => 'LAVADORA PERCLORO',
            'area'          => 'LAVADO EN SECO',
            'foto'          => null,
            'numeroControl' => '001',
            'revision'      => 'N'
        ]);

        $hoja = HojaChequeo::create([
            'equipo_id'     => $equipo->id,
            'area'          => HojaChequeoArea::TINTORERIA->value,
            'version'       => 1,
            'observaciones' => null
        ]);

        $items = [
            [
                'items'     => [
                    'ITEM'        => 'LIMPIEZA DEL DESTILADOR',
                    'FRECUENCIA'  => 'CADA 2 DÍAS',
                    'METODO'      => null,
                    'CRITERIO'    => 'CADA 8- 10 CICLOS',
                    'RESPONSABLE' => 'OPERARIO'
                ],
                'categoria' => 'limpieza'
            ],
            [
                'items'     => [
                    'ITEM'        => 'TEMPERATURA DEL DESTILADOR',
                    'FRECUENCIA'  => '1 VEZ AL DIA',
                    'METODO'      => null,
                    'CRITERIO'    => 'DE 110º A 127º C',
                    'RESPONSABLE' => 'OPERARIO'
                ],
                'categoria' => 'revision'
            ],
            [
                'items'     => [
                    'ITEM'        => 'PRESIÒN DEL SISTEMA REFRIGERANTE',
                    'FRECUENCIA'  => '1 VEZ AL DIA',
                    'METODO'      => 'DURANTE EL SECADO',
                    'CRITERIO'    => 'PRESIÒN EN ALTA DE 3 A 5 BAR',
                    'RESPONSABLE' => 'OPERARIO'
                ],
                'categoria' => 'revision'
            ],
            [
                'items'     => [
                    'ITEM'        => 'PRESIÒN DEL SISTEMA REFRIGERANTE',
                    'FRECUENCIA'  => '1 VEZ AL DIA',
                    'METODO'      => 'DURANTE EL SECADO',
                    'CRITERIO'    => 'PRESIÒN EN BAJA DE 19 A 21 BAR',
                    'RESPONSABLE' => 'OPERARIO'
                ],
                'categoria' => 'revision'
            ],
            [
                'items'     => [
                    'ITEM'        => 'PRESIÒN DEL SISTEMA REFRIGERANTE',
                    'FRECUENCIA'  => '1 VEZ AL DIA',
                    'METODO'      => 'DURANTE EL ENFRIAMIENTO',
                    'CRITERIO'    => 'PRESIÒN EN ALTA DE 3 A 5 BAR',
                    'RESPONSABLE' => 'OPERARIO'
                ],
                'categoria' => 'revision'
            ],
            [
                'items'     => [
                    'ITEM'        => 'PRESIÒN DEL SISTEMA REFRIGERANTE',
                    'FRECUENCIA'  => '1 VEZ AL DIA',
                    'METODO'      => 'DURANTE EL ENFRIAMIENTO',
                    'CRITERIO'    => 'PRESIÒN EN BAJA DE 18 A 21 BAR',
                    'RESPONSABLE' => 'OPERARIO'
                ],
                'categoria' => 'revision'
            ],
            [
                'items'     => [
                    'ITEM'        => 'LIMPIEZA DEL FILTRO ROTATIVO',
                    'FRECUENCIA'  => '1 VEZ AL DÍA',
                    'METODO'      => null,
                    'CRITERIO'    => 'CADA 8-10 CICLOS',
                    'RESPONSABLE' => 'OPERARIO'
                ],
                'categoria' => 'limpieza'
            ],
            [
                'items'     => [
                    'ITEM'        => 'LIMPIEZA DEL FILTRO DE AIRE (Trampa de pelusa)',
                    'FRECUENCIA'  => '1 VEZ AL DÍA',
                    'METODO'      => null,
                    'CRITERIO'    => 'LIMPIEZA PROFUNDA',
                    'RESPONSABLE' => 'OPERARIO'
                ],
                'categoria' => 'limpieza'
            ],
            [
                'items'     => [
                    'ITEM'        => 'REGENERACIÓN MULTISORB',
                    'FRECUENCIA'  => 'CADA 35 CICLOS O UNA VEZ A LA SEMANA',
                    'METODO'      => null,
                    'CRITERIO'    => 'MANTENIMIENTO',
                    'RESPONSABLE' => 'OPERARIO'
                ],
                'categoria' => 'operacion'
            ],
            [
                'items'     => [
                    'ITEM'        => 'COMPRESOR',
                    'FRECUENCIA'  => '1 VEZ AL DÍA',
                    'METODO'      => null,
                    'CRITERIO'    => 'PURGA',
                    'RESPONSABLE' => 'OPERARIO'
                ],
                'categoria' => 'operacion'
            ],
            [
                'items'     => [
                    'ITEM'        => 'CICLOS AL FINAL DEL DÍA',
                    'FRECUENCIA'  => null,
                    'METODO'      => null,
                    'CRITERIO'    => 'TOTALES',
                    'RESPONSABLE' => 'OPERARIO'
                ],
                'categoria' => 'revision'
            ],
            [
                'items'     => [
                    'ITEM'        => 'SOLVENTE',
                    'FRECUENCIA'  => 'SEMANAL',
                    'METODO'      => null,
                    'CRITERIO'    => 'RELLENAR NIVELES',
                    'RESPONSABLE' => 'OPERARIO'
                ],
                'categoria' => 'revision'
            ],
            [
                'items'     => [
                    'ITEM'        => 'PROGRAMA 16',
                    'FRECUENCIA'  => '1 VEZ AL DÍA',
                    'METODO'      => null,
                    'CRITERIO'    => 'LIMPIEZA DE LA CANASTA',
                    'RESPONSABLE' => 'OPERARIO'
                ],
                'categoria' => 'operacion'
            ],
            [
                'items'     => [
                    'ITEM'        => 'MANTENIMIENTO CANASTA',
                    'FRECUENCIA'  => '1 VEZ CADA 15 DIAS',
                    'METODO'      => null,
                    'CRITERIO'    => 'LIMPIEZA DE LA CANASTA MANUAL',
                    'RESPONSABLE' => 'OPERARIO'
                ],
                'categoria' => 'operacion'
            ],
            [
                'items'     => [
                    'ITEM'        => 'LIMPIEZA GENERAL',
                    'FRECUENCIA'  => '1 VEZ POR TURNO',
                    'METODO'      => null,
                    'CRITERIO'    => 'LIMPIEZA PROFUNDA',
                    'RESPONSABLE' => 'OPERARIO'
                ],
                'categoria' => 'limpieza'
            ]
        ];

        foreach ($items as $item) {
            Item::create([
                'hoja_chequeo_id' => $hoja->id,
                'valores'         => $item['items'],
                'categoria'       => $item['categoria']
            ]);
        }
    }

    public function sua05(): void {
        $equipo = Equipo::create([
            'tag' => 'SUA-05',
            'nombre' => 'SUAVIZADOR 05',
            'numeroControl' => '005',
            'revision' => 'N',
            'area' => HojaChequeoArea::CUARTO_DE_MAQUINAS->value
        ]);

        $hoja = HojaChequeo::create([
            'equipo_id' => $equipo->id,
            'area' => HojaChequeoArea::CUARTO_DE_MAQUINAS->value
        ]);

        $items = [
            [
                'items' => [
                    'ITEM' => 'RETROLAVADO',
                    'FRECUENCIA' => 'CADA 36 HRS',
                    'METODO' => 'MANUAL',
                    'CRITERIO' => 'CUANDO SE PRESENTE DUREZA 0 A 80 PPM'
                ],
                'categoria' => 'operacion'
            ],
            [
                'items' => [
                    'ITEM' => 'LIMPIEZA DEL INYECTOR',
                    'FRECUENCIA' => '1 VEZ POR 15 DIAS',
                    'METODO' => '',
                    'CRITERIO' => ''
                ],
                'categoria' => 'limpieza'
            ],
            [
                'items' => [
                    'ITEM' => 'NIVEL DE SALMUERA',
                    'FRECUENCIA' => 'CADA 36 HRS',
                    'METODO' => '',
                    'CRITERIO' => '3/4 DE LLENADO'
                ],
                'categoria' => 'revision'
            ],
            [
                'items' => [
                    'ITEM' => 'COLOR DE AGUA EN LA PRUEBA',
                    'FRECUENCIA' => '1 VEZ POR DIA',
                    'METODO' => '',
                    'CRITERIO' => 'A/M'
                ],
                'categoria' => 'revision'
            ],
            [
                'items' => [
                    'ITEM' => '% DUREZA DEL AGUA (EDTA)',
                    'FRECUENCIA' => '1 VEZ POR DIA',
                    'METODO' => '',
                    'CRITERIO' => '0 PPM'
                ],
                'categoria' => 'revision'
            ],
            [
                'items'     => [
                    'ITEM'        => 'LIMPIEZA GENERAL',
                    'FRECUENCIA'  => '1 VEZ POR TURNO',
                    'METODO'      => '',
                    'CRITERIO'    => 'LIMPIEZA PROFUNDA',
                ],
                'categoria' => 'limpieza'
            ]
        ];

        foreach ($items as $item) {
            Item::create([
                'hoja_chequeo_id' => $hoja->id,
                'valores' => $item['items'],
                'categoria' => $item['categoria']
            ]);
        }
    }

    public function lsHdc02() {
        $equipo = Equipo::create([
            'tag'           => 'LS-HDC-02',
            'nombre'        => 'LAV. HIDROCARBURO',
            'area'          => 'Lavado en Seco',
            'foto'          => null,
            'numeroControl' => '002',
            'revision'      => 'N'
        ]);

        $hoja = HojaChequeo::create([
            'equipo_id'     => $equipo->id,
            'version'       => 1,
            'observaciones' => null,
            'area'          => HojaChequeoArea::TINTORERIA->value
        ]);

        $items = [
            [
                'items'     => [
                    'ITEM'       => 'LIMPIAR EL DESTILADOR',
                    'FRECUENCIA' => '1 VEZ AL DÍA',
                    'CRITERIO'   => 'CUANDO EL DESTILADOR ESTA FRIO'
                ],
                'categoria' => 'limpieza'
            ],
            [
                'items'     => [
                    'ITEM'       => 'LIMPIAR EL FILTRO DE TRAMPA BOTÓN',
                    'FRECUENCIA' => '1 VEZ AL DÍA',
                    'CRITERIO'   => 'CADA 3-4 CICLOS'
                ],
                'categoria' => 'limpieza'
            ],
            [
                'items'     => [
                    'ITEM'       => 'LIMPIAR EL FILTRO DE AIRE PRIMARIO Y SECUNDARIO',
                    'FRECUENCIA' => '1 VEZ AL DÍA',
                    'CRITERIO'   => 'MAQUINA EN PUNTO MUERTO'
                ],
                'categoria' => 'limpieza'
            ],
            [
                'items'     => [
                    'ITEM'       => 'LIMPIAR LOS FILTROS SPIN (1 y 2)',
                    'FRECUENCIA' => '1 VEZ AL DÍA',
                    'CRITERIO'   => 'CADA 8-10 CICLOS'
                ],
                'categoria' => 'limpieza'
            ],
            [
                'items'     => [
                    'ITEM'       => 'CORRER EL PROGRAMA DE “GOOD MORNING”',
                    'FRECUENCIA' => '1 VEZ AL DÍA',
                    'CRITERIO'   => 'DESPUES DE LA LIMPIEZA DEL DESTILADOR'
                ],
                'categoria' => 'operacion'
            ],
            [
                'items'     => [
                    'ITEM'       => 'LIMPIAR EL FILTRO DE LA BOMBA DE VACÍO',
                    'FRECUENCIA' => '2 VECES POR SEMANA',
                    'CRITERIO'   => 'MAQUINA EN PUNTO MUERTO'
                ],
                'categoria' => 'limpieza'
            ],
            [
                'items'     => [
                    'ITEM'       => 'LIMPIAR EL FILTRO DE AIRE PRIMARIO Y SECUNDARIO',
                    'FRECUENCIA' => '1 VEZ POR SEMANA',
                    'CRITERIO'   => 'LIMPIEZA PROFUNDA'
                ],
                'categoria' => 'limpieza'
            ],
            [
                'items'     => [
                    'ITEM'       => 'LIMPIAR EL FLOTADOR DE LA TRAMPA DE BOTONES',
                    'FRECUENCIA' => 'CADA 2 MESES',
                    'CRITERIO'   => 'LIMPIEZA PROFUNDA'
                ],
                'categoria' => 'limpieza'
            ],
            [
                'items'     => [
                    'ITEM'       => 'COMPRESOR',
                    'FRECUENCIA' => '1 VEZ AL DÍA',
                    'CRITERIO'   => 'PURGA'
                ],
                'categoria' => 'operacion' // Corregido operacon->operacion
            ],
            [
                'items'     => [
                    'ITEM'       => 'CICLOS',
                    'FRECUENCIA' => 'AL FINAL DEL DÍA',
                    'CRITERIO'   => 'TOTALES'
                ],
                'categoria' => 'revision'
            ],
            [
                'items'     => [
                    'ITEM'       => 'SOLVENTE',
                    'FRECUENCIA' => 'SEMANAL',
                    'CRITERIO'   => 'RELLENAR NIVELES'
                ],
                'categoria' => 'revision'
            ],
            [
                'items'     => [
                    'ITEM'       => 'LIMPIEZA DE SEPARADOR DE AGUA',
                    'FRECUENCIA' => '1 VEZ AL MES',
                    'CRITERIO'   => 'LIMPIEZA PROFUNDA'
                ],
                'categoria' => 'limpieza'
            ],
            [
                'items'     => [
                    'ITEM'       => 'LECTURA DEL MANOMETRO ST1',
                    'FRECUENCIA' => '1 VEZ AL DÍA',
                    'CRITERIO'   => 'ESTADO OPTIMO DE CONDENSADOR Y DESTILADOR'
                ],
                'categoria' => 'operacion'
            ],
            [
                'items'     => [
                    'ITEM'       => 'LECTURA DEL MANOMETRO ST3',
                    'FRECUENCIA' => '1 VEZ AL DÍA',
                    'CRITERIO'   => 'ESTADO OPTIMO DE CONDENSADOR Y DESTILADOR'
                ],
                'categoria' => 'operacion'
            ],
            [
                'items'     => [
                    'ITEM'       => 'LECTURA DEL MANOMETRO ST6',
                    'FRECUENCIA' => '1 VEZ AL DÍA',
                    'CRITERIO'   => 'ESTADO OPTIMO DE CONDENSADOR Y DESTILADOR'
                ],
                'categoria' => 'operacion'
            ],
            [
                'items'     => [
                    'ITEM'       => 'CICLOS DEL FILTRO ROTATIVO F1',
                    'FRECUENCIA' => '1 VEZ AL DÍA',
                    'CRITERIO'   => 'ESTADO OPTIMO DEl FILTRO SPIN 1'
                ],
                'categoria' => 'revision'
            ],
            [
                'items'     => [
                    'ITEM'       => 'CICLOS DEL FILTRO ROTATIVO F2',
                    'FRECUENCIA' => '1 VEZ AL DÍA',
                    'CRITERIO'   => 'ESTADO OPTIMO DEl FILTRO SPIN 2'
                ],
                'categoria' => 'revision' // Inferido de línea anterior
            ],
            [
                'items'     => [
                    'ITEM'       => 'limpieza general del equipo',
                    'FRECUENCIA' => '1 VEZ AL DÍA',
                    'CRITERIO'   => 'LIMPIEZA PROFUNDA'
                ],
                'categoria' => 'limpieza'
            ]
        ];

        foreach ($items as $item) {
            Item::create([
                'hoja_chequeo_id' => $hoja->id,
                'valores'         => $item['items'],
                'categoria'       => $item['categoria']
            ]);
        }
    }

    public function laHid02(): void {
        $equipo = Equipo::create([
            'tag'           => 'LA-HID-02',
            'nombre'        => 'HIDRONEUMATICO 02',
            'area'          => 'Lavado en Agua',
            'foto'          => null,
            'numeroControl' => '002',
            'revision'      => 'NOM-020-STPS-2011'
        ]);

        $hoja = HojaChequeo::create([
            'equipo_id'     => $equipo->id, 'version' => 1,
            'area'          => HojaChequeoArea::CUARTO_DE_MAQUINAS->value,
            'observaciones' => null
        ]);

        $items = [
            [
                'items'     => [
                    'ITEM'        => 'ENCENDIDO DE INTERRUPTOR PRINCIPAL',
                    'FRECUENCIA'  => '1 VEZ POR TURNO',
                    'METODO'      => 'VISUAL',
                    'CRITERIO'    => 'REVISAR QUE ESTE ENCENDIDO EL INTERRUPTOR DEL COMPRESOR DEL HIDRONEUMATICO',
                    'RESPONSABLE' => 'OPERARIO'
                ],
                'categoria' => 'operacion'
            ],
            [
                'items'     => [
                    'ITEM'        => 'ENCENDIDO DE INTERRUPTOR SECUNDARIO',
                    'FRECUENCIA'  => '1 VEZ POR TURNO',
                    'METODO'      => 'VISUAL Y AUDITIVO',
                    'CRITERIO'    => 'REVISAR QUE ESTE ENCENDIDO EL INTERRUPTOR DEL COMPRESOR DEL HIDRONEUMATICO',
                    'RESPONSABLE' => 'OPERARIO'
                ],
                'categoria' => 'operacion'
            ],
            [
                'items'     => [
                    'ITEM'        => 'LIMPIEZA EXTERIOR DE EQUIPO',
                    'FRECUENCIA'  => '1 VEZ POR TURNO',
                    'METODO'      => 'VISUAL',
                    'CRITERIO'    => 'REALIZAR AL INICIO DEL TURNO',
                    'RESPONSABLE' => 'OPERARIO'
                ],
                'categoria' => 'limpieza'
            ],
            [
                'items'     => [
                    'ITEM'        => 'REVISION DE TUBERIA PRINCIPAL',
                    'FRECUENCIA'  => '1 VEZ CADA SEMANA',
                    'METODO'      => 'VISUAL',
                    'CRITERIO'    => 'CHECAR SI HAY FUGAS EN UNIONES',
                    'RESPONSABLE' => 'OPERARIO'
                ],
                'categoria' => 'revision'
            ],
            [
                'items'     => [
                    'ITEM'        => 'REVISION DE LLENADO DE CISTERNA',
                    'FRECUENCIA'  => '1 VEZ POR TURNO',
                    'METODO'      => 'VISUAL',
                    'CRITERIO'    => 'VERIFICAR EL LLEANDO DE LA CISTERNA',
                    'RESPONSABLE' => 'OPERARIO'
                ],
                'categoria' => 'revision'
            ],
            [
                'items'     => [
                    'ITEM'        => 'LIMPIEZA GENERAL',
                    'FRECUENCIA'  => '1 AL DIA',
                    'METODO'      => 'MANUAL',
                    'CRITERIO'    => 'LIMPIEZA PROFUNDA',
                    'RESPONSABLE' => 'OPERARIO'
                ],
                'categoria' => 'limpieza'
            ],
            [
                'items'     => [
                    'ITEM'        => 'PRESION DE TRABAJO',
                    'FRECUENCIA'  => '1 VEZ DURANTE EL TURNO',
                    'METODO'      => 'MANUAL',
                    'CRITERIO'    => '40 PSI A 60 PSI',
                    'RESPONSABLE' => 'OPERARIO'
                ],
                'categoria' => 'revision'
            ],
            [
                'items'     => [
                    'ITEM'        => 'PRESION DEL AIRE',
                    'FRECUENCIA'  => '1 VEZ DURANTE EL TURNO',
                    'METODO'      => 'VISUAL',
                    'CRITERIO'    => '38 PSI',
                    'RESPONSABLE' => 'OPERARIO'
                ],
                'categoria' => 'revision'
            ]
        ];

        foreach ($items as $item) {
            Item::create([
                'hoja_chequeo_id' => $hoja->id,
                'valores'         => $item['items'],
                'categoria'       => $item['categoria']
            ]);
        }
    }
}
