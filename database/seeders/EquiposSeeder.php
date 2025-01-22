<?php

namespace Database\Seeders;

use App\Models\Equipo;
use App\Models\HojaChequeo;
use App\Models\Item;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class EquiposSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void {
        $this->cuartoDeMaquinas();
    }

    public function cuartoDeMaquinas() {
        $this->hid02();
        $this->cal02();
        $this->cal01();
        $this->hid04();
        $this->sua05();
        $this->fze03();
    }

    public function fze03() {
        $equipo = Equipo::create([
            'tag'    => 'CM-FZE-03',
            'nombre' => 'FILTRO DE ZEOLITA',
            'area'   => 'CUARTO DE MAQUINAS',
            'foto'   => null
        ]);

        $hoja = HojaChequeo::create(['equipo_id' => $equipo->id, 'version' => 1, 'observaciones' => null]);

        $items = [
            [
                'ITEM' => 'TURBIDEZ',
                'FRECUENCIA' => '1 VEZ POR DIA',
                'METODO' => '',
                'CRITERIO' => '<5 NTU',
                'RESPONSABLE' => 'OPERARIO'
            ],
            [
                'ITEM' => 'COLOR DE AGUA EN EL ROTAMETRO',
                'FRECUENCIA' => '1 VEZ POR DIA',
                'METODO' => '',
                'CRITERIO' => 'CLARA',
                'RESPONSABLE' => 'OPERARIO'
            ],
            [
                'ITEM' => 'RETROLAVADO',
                'FRECUENCIA' => 'CADA SEMANA',
                'METODO' => '',
                'CRITERIO' => 'CON TURBIDEZ >5NTU',
                'RESPONSABLE' => 'OPERARIO'
            ],
            [
                'ITEM' => 'ANALISIS DE CLORO RESIDUAL',
                'FRECUENCIA' => '1 VEZ POR DIA',
                'METODO' => '',
                'CRITERIO' => 'ENTRADA',
                'RESPONSABLE' => 'OPERARIO'
            ],
            [
                'ITEM' => 'ANALISIS DE CLORO RESIDUAL',
                'FRECUENCIA' => '1 VEZ POR DIA',
                'METODO' => '',
                'CRITERIO' => 'SALIDA',
                'RESPONSABLE' => 'OPERARIO'
            ],
            [
                'ITEM' => 'RETROLAVADO',
                'FRECUENCIA' => 'CADA SEMANA',
                'METODO' => '',
                'CRITERIO' => 'CON CLORO >1 PPM',
                'RESPONSABLE' => 'OPERARIO'
            ]
        ];

        foreach ($items as $item) {
            Item::create([
                'hoja_chequeo_id' => $hoja->id,
                'valores' => $item
            ]);
        }
    }

    public function sua05() {
        $equipo = Equipo::create([
            'tag'    => 'CM-SUA-05',
            'nombre' => 'SUAVIZADOR 5',
            'area'   => 'CUARTO DE MAQUINAS',
            'foto'   => null
        ]);

        $hoja = HojaChequeo::create(['equipo_id' => $equipo->id, 'version' => 1, 'observaciones' => null]);

        $items = [
            [
                'ITEM' => 'RETROLAVADO',
                'FRECUENCIA' => 'CADA 36 HRS',
                'METODO' => 'MANUAL',
                'CRITERIO' => 'CUANDO SE PRESENTE DUREZA 0 A 80 PPM',
                'RESPONSABLE' => 'OPERARIO'
            ],
            [
                'ITEM' => 'REVISAR SUMINISTRO DE AGUA',
                'FRECUENCIA' => '1 VEZ POR DIA',
                'METODO' => 'VISUAL',
                'CRITERIO' => 'REVISAR LA APERTURA DE LA VALVULA DE PASO',
                'RESPONSABLE' => 'OPERARIO'
            ],
            [
                'ITEM' => 'NIVEL DE SALMUERA',
                'FRECUENCIA' => 'CADA 36HRS',
                'METODO' => '',
                'CRITERIO' => '3/4 DE LLENADO',
                'RESPONSABLE' => 'OPERARIO'
            ],
            [
                'ITEM' => 'COLOR DE AGUA EN LA PRUEBA',
                'FRECUENCIA' => '1 VEZ POR DIA',
                'METODO' => '',
                'CRITERIO' => 'M / A',
                'RESPONSABLE' => 'OPERARIO'
            ],
            [
                'ITEM' => '% DUREZA DEL AGUA',
                'FRECUENCIA' => '1 VEZ POR DIA',
                'METODO' => '',
                'CRITERIO' => '0 ppm',
                'RESPONSABLE' => 'OPERARIO'
            ],
            [
                'ITEM' => 'LIMPIEZA DEL INYECTOR',
                'FRECUENCIA' => '1 VEZ POR 16 DIAS',
                'METODO' => '',
                'CRITERIO' => '',
                'RESPONSABLE' => 'OPERARIO'
            ]
        ];

        foreach ($items as $item) {
            Item::create([
                'hoja_chequeo_id' => $hoja->id,
                'valores' => $item
            ]);
        }
    }


    public function hid04() {
        $equipo = Equipo::create([
            'tag'    => 'CM-HID-04',
            'nombre' => 'Hidroneumatico 4',
            'area'   => 'CUARTO DE MAQUINAS',
            'foto'   => null
        ]);

        $hoja = HojaChequeo::create(['equipo_id' => $equipo->id, 'version' => 1, 'observaciones' => null]);

        $items = [
            [
                'ITEM' => 'ENCENDIDO DE INTERRUPTOR PRINCIPAL',
                'FRECUENCIA' => '1 VEZ POR TURNO',
                'METODO' => 'VISUAL',
                'CRITERIO' => 'REVISAR QUE ESTE ENCENDIDO EL INTERRUPTOR DEL COMPRESOR DEL HIDRONEUMATICO',
                'RESPONSABLE' => 'OPERARIO'
            ],
            [
                'ITEM' => 'ENCENDIDO DE INTERRUPTOR SECUNDARIO',
                'FRECUENCIA' => '1 VEZ POR TURNO',
                'METODO' => 'VISUAL Y AUDITIVO',
                'CRITERIO' => 'REVISAR QUE ESTE ENCENDIDO EL INTERRUPTOR DEL COMPRESOR DEL HIDRONEUMATICO',
                'RESPONSABLE' => 'OPERARIO'
            ],
            [
                'ITEM' => 'LIMPIEZA EXTERIOR DE EQUIPO',
                'FRECUENCIA' => '1 VEZ POR TURNO',
                'METODO' => 'VISUAL',
                'CRITERIO' => 'REALIZAR AL INICIO DEL TURNO',
                'RESPONSABLE' => 'OPERARIO'
            ],
            [
                'ITEM' => 'REVISION DE TUBERIA PRINCIPAL',
                'FRECUENCIA' => '1 VEZ CADA SEMANA',
                'METODO' => 'VISUAL',
                'CRITERIO' => 'CHECAR SI HAY FUGAS EN UNIONES',
                'RESPONSABLE' => 'OPERARIO'
            ],
            [
                'ITEM' => 'REVISION DE LLENADO DE CISTERNA',
                'FRECUENCIA' => '1 VEZ POR TURNO',
                'METODO' => 'VISUAL',
                'CRITERIO' => 'VERIFICAR EL LLEANDO DE LA CISTERNA',
                'RESPONSABLE' => 'OPERARIO'
            ],
            [
                'ITEM' => 'PRESION DE TRABAJO MANOMETRO',
                'FRECUENCIA' => '1 VEZ POR TURNO',
                'METODO' => '',
                'CRITERIO' => '60PSI A 80PSI',
                'RESPONSABLE' => 'OPERARIO'
            ],
            [
                'ITEM' => 'PRESION DEL AIRE',
                'FRECUENCIA' => '1 VEZ POR TURNO',
                'METODO' => '',
                'CRITERIO' => '58PSI',
                'RESPONSABLE' => 'OPERARIO'
            ]

        ];

        foreach ($items as $item) {
            Item::create([
                'hoja_chequeo_id' => $hoja->id,
                'valores' => $item
            ]);
        }
    }

    public function hid02() {
        $equipo = Equipo::create([
            'nombre' => 'Hidroneumatico 1',
            'tag'    => 'CM02-HID-01',
            'area'   => 'Cuarto de maquinas',
            'foto'   => null
        ]);

        $hoja = HojaChequeo::create([
            'equipo_id'     => $equipo->id,
            'version'       => 1,
            'observaciones' => null
        ]);

        $items = [
            [
                'valores' => [
                    'ITEM'        => 'ENCENDIDO DE INTERRUPTOR PRINCIPAL',
                    'FRECUENCIA'  => '1 VEZ POR TURNO',
                    'METODO'      => 'VISUAL',
                    'CRITERIO'    => 'REVISAR QUE ESTE ENCENDIDO EL INTERRUPTOR DEL COMPRESOR DEL HIDRONEUMATICO',
                    'RESPONSABLE' => 'OPERARIO'
                ]
            ],
            [
                'valores' => [
                    'ITEM'        => 'ENCENDIDO DE INTERRUPTOR SECUNDARIO',
                    'FRECUENCIA'  => '1 VEZ POR TURNO',
                    'METODO'      => 'VISUAL Y AUDITIVO',
                    'CRITERIO'    => 'REVISAR QUE ESTE ENCENDIDO EL INTERRUPTOR DEL COMPRESOR DEL HIDRONEUMATICO',
                    'RESPONSABLE' => 'OPERARIO'
                ]
            ],
            [
                'valores' => [
                    'ITEM'        => 'LIMPIEZA  EXTERIOR DE  EQUIPO',
                    'FRECUENCIA'  => '1 VEZ POR TURNO',
                    'METODO'      => 'VISUAL',
                    'CRITERIO'    => 'REALIZAR AL INICIO DEL TURNO',
                    'RESPONSABLE' => 'OPERARIO'
                ]
            ],
            [
                'valores' => [
                    'ITEM'        => 'REVISION DE TUBERIA PRINCIPAL',
                    'FRECUENCIA'  => '1 VEZ CADA SEMANA',
                    'METODO'      => 'VISUAL',
                    'CRITERIO'    => 'CHECAR SI HAY FUGAS EN UNIONES',
                    'RESPONSABLE' => 'OPERARIO'
                ]
            ],
            [
                'valores' => [
                    'ITEM'        => 'REVISION DE LLENADO DE CISTERNA',
                    'FRECUENCIA'  => '1 VEZ POR TURNO',
                    'METODO'      => 'VISUAL',
                    'CRITERIO'    => 'VERIFICAR EL LLEANDO DE LA CISTERNA',
                    'RESPONSABLE' => 'OPERARIO'
                ]
            ]
        ];

        foreach ($items as $itemData) {
            Item::create([
                'hoja_chequeo_id' => $hoja->id,
                'valores'         => $itemData['valores']
            ]);
        }
    }

    public function cal02() {
        $equipo = Equipo::create([
            'tag'    => 'CM-CAL-02',
            'nombre' => 'CALDERA-02',
            'area'   => 'CUARTO DE MAQUINAS',
            'foto'   => null
        ]);

        $hoja = HojaChequeo::create(['equipo_id' => $equipo->id, 'version' => 1, 'observaciones' => null]);

        $items = [
            ['ITEM' => 'TEMPERATURA INTERIOR', 'NORMA' => '140°C a 160°C', 'FRECUENCIA' => '3 VECES DURANTE EL TURNO'],
            ['ITEM' => 'PRESION DE VAPOR', 'NORMA' => '3 KG A 6 KG. 5KG OPTIMO', 'FRECUENCIA' => '3 VECES DURANTE EL TURNO'],
            ['ITEM' => 'TEMPERATURA ENTRADA AGUA', 'NORMA' => '90°C a 100°C', 'FRECUENCIA' => '3 VECES DURANTE EL TURNO'],
            ['ITEM' => 'PURGA DE FONDO', 'NORMA' => 'HORA', 'FRECUENCIA' => '1 VEZ CADA DOS DIAS'],
            ['ITEM' => 'PRESION INICIAL DE PURGA', 'NORMA' => '4.5 KG A 5 KG', 'FRECUENCIA' => '1 VEZ CADA DOS DIAS'],
            ['ITEM' => 'APERTURA VALVULA 1', 'NORMA' => '100%', 'FRECUENCIA' => '1 VEZ CADA DOS DIAS'],
            ['ITEM' => 'APERTURA VALVULA 2', 'NORMA' => '100%', 'FRECUENCIA' => '1 VEZ CADA DOS DIAS'],
            ['ITEM' => 'APERTURA VALVULA NIVEL', 'NORMA' => '100%', 'FRECUENCIA' => '1 VEZ CADA DOS DIAS'],
            ['ITEM' => 'COLOR DE AGUA EN CRISTAL', 'NORMA' => 'B, C, F', 'FRECUENCIA' => '1 VEZ CADA DOS DIAS'],
            ['ITEM' => 'PRECION FINAL DE PURGA', 'NORMA' => '3.5 KG A 4.5 KG', 'FRECUENCIA' => '1 VEZ CADA DOS DIAS'],
            ['ITEM' => 'DISPARO VALVULA DE SEGURIDAD', 'NORMA' => 'ACCIONAMIENTO DE VALVULA', 'FRECUENCIA' => '1 VEZ CADA TRES DIAS']
        ];

        foreach ($items as $item) {
            Item::create([
                'hoja_chequeo_id' => $hoja->id,
                'valores'         => $item
            ]);
        }
    }

    public function cal01() {
        $equipo = Equipo::create([
            'tag'    => 'CM-CAL-01',
            'nombre' => 'CALDERA-01',
            'area'   => 'CUARTO DE MAQUINAS',
            'foto'   => null
        ]);

        $hoja = HojaChequeo::create(['equipo_id' => $equipo->id, 'version' => 1, 'observaciones' => null]);

        $items = [
            [
                'ITEM' => 'FUNCIONAMIENTO DE BOMBA',
                'FRECUENCIA' => '3 VECES POR TURNO',
                'METODO' => 'VISUAL Y AUDIITIVO',
                'CRITERIO' => 'QUE ENCIENDA Y APAGE AUTOMATICAMENTE',
                'RESPONSABLE' => 'OPERARIO'
            ],
            [
                'ITEM' => 'FUNCIONAMIENTO DE QUEMADOR',
                'FRECUENCIA' => '3 VECES POR TURNO',
                'METODO' => 'VISUAL Y AUDITIVO',
                'CRITERIO' => 'ENCENDIDO Y APAGADO DE FLAMA EN MIRILLA',
                'RESPONSABLE' => 'OPERARIO'
            ],
            [
                'ITEM' => 'LIMPIEZA EXTERIOR DE EQUIPO',
                'FRECUENCIA' => '1 VEZ POR TURNO',
                'METODO' => 'VISUAL',
                'CRITERIO' => 'REALIZAR AL INICIO DEL TURNO',
                'RESPONSABLE' => 'OPERARIO'
            ],
            [
                'ITEM' => 'LIMPIEZA TUBERIA',
                'FRECUENCIA' => '1 VEZ POR TURNO',
                'METODO' => 'VISUAL',
                'CRITERIO' => 'EXCESO DE POLVO',
                'RESPONSABLE' => 'OPERARIO'
            ],
            [
                'ITEM' => 'REVISION ENCENDIDO DE FOCOS',
                'FRECUENCIA' => '1 VEZ POR TURNO',
                'METODO' => 'VISUAL',
                'CRITERIO' => 'ENCENDIDO AL ACTIVAR INTERRUPTOR',
                'RESPONSABLE' => 'OPERARIO'
            ],
            [
                'ITEM' => 'LIMPIEZA MANOMETROS',
                'FRECUENCIA' => 'DIARIO',
                'METODO' => 'LIMPIEZA MANUAL',
                'CRITERIO' => 'REALIZAR AL INICIO DEL TURNO',
                'RESPONSABLE' => 'OPERARIO'
            ],
            [
                'ITEM' => 'CONTENIDO DE AGUA EN TANQUE DE CONDENSADOS',
                'FRECUENCIA' => '1 VEZ POR TURNO',
                'METODO' => 'MANUAL Y VISUAL',
                'CRITERIO' => 'REVISION EN MANGUERA TRANSPARENTE NIVEL Y APERTURA DE VALVULA EN EL INFERIOR',
                'RESPONSABLE' => 'OPERARIO'
            ],
            [
                'ITEM' => 'REVISION DE SUAVIZADOR',
                'FRECUENCIA' => '1 VEZ POR TURNO',
                'METODO' => 'MANUAL Y VISUAL',
                'CRITERIO' => 'QUE EL DEPOSITO DE SALMUERA NO SE QUEDE SIN SAL Y AGUA',
                'RESPONSABLE' => 'OPERARIO'
            ],
            [
                'ITEM' => 'TEMPERATURA INTERIOR',
                'FRECUENCIA' => '3 VECES DURANTE EL TURNO',
                'METODO' => '',
                'CRITERIO' => '140 °C a 160°C',
                'RESPONSABLE' => 'OPERARIO'
            ],
            [
                'ITEM' => 'PRESION DE VAPOR',
                'FRECUENCIA' => '1 VEZ POR TURNO',
                'METODO' => '',
                'CRITERIO' => '4 Kg Hora de comida',
                'RESPONSABLE' => 'OPERARIO'
            ],
            [
                'ITEM' => 'PRESION DE VAPOR',
                'FRECUENCIA' => '3 VECES DURANTE EL TURNO',
                'METODO' => '',
                'CRITERIO' => '5 KG optimo',
                'RESPONSABLE' => 'OPERARIO'
            ],
            [
                'ITEM' => 'PRESION DE VAPOR',
                'FRECUENCIA' => 'AL ARRANQUE DEL TURNO',
                'METODO' => '',
                'CRITERIO' => '6 KG (SOLO EN TEMPORADA ALTA)',
                'RESPONSABLE' => 'OPERARIO'
            ],
            [
                'ITEM' => 'TEMPERATURA ENTRADA AGUA',
                'FRECUENCIA' => '3 VECES DURANTE EL TURNO',
                'METODO' => '',
                'CRITERIO' => '90 °C a 100°C',
                'RESPONSABLE' => 'OPERARIO'
            ],
            [
                'ITEM' => 'PURGA DE FONDO',
                'FRECUENCIA' => '1 VEZ CADA DOS DIAS',
                'METODO' => '',
                'CRITERIO' => 'APERTURA DE VALVULAS DE PURGA',
                'RESPONSABLE' => 'OPERARIO'
            ],
            [
                'ITEM' => 'DISPARO VALVULA DE SEGURIDAD',
                'FRECUENCIA' => '1 VEZ CADA TRES DIAS',
                'METODO' => '',
                'CRITERIO' => 'ACCIONAMIENTO DE VALVULA',
                'RESPONSABLE' => 'OPERARIO'
            ]
        ];

        foreach ($items as $item) {
            Item::create([
                'hoja_chequeo_id' => $hoja->id,
                'valores' => $item
            ]);
        }
    }
}
