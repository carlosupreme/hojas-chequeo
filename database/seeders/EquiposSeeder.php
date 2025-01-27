<?php

namespace Database\Seeders;

use App\Models\Equipo;
use App\Models\HojaChequeo;
use App\Models\Item;
use Illuminate\Database\Seeder;

class EquiposSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void {
        $this->cuartoDeMaquinas();
    }

    public function cuartoDeMaquinas(): void {
        $this->laHid02();
        $this->sth06();
        $this->lsHdc02();
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
            'area'          => 'Tintoreria' // Inferido de Lavado en Seco
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

    public function sth06() {
        $equipo = Equipo::create([
            'tag'           => 'STH-06',
            'nombre'        => 'STAHL 06',
            'area'          => 'Lavado',
            'foto'          => null,
            'numeroControl' => '006',
            'revision'      => 'N'
        ]);

        $hoja = HojaChequeo::create(['equipo_id'     => $equipo->id,
                                     'version'       => 1,
                                     'area'          => 'Lavanderia Institucional',
                                     'observaciones' => null]);

        $items = [
            [
                'items'     => [
                    'ITEM'          => 'TUBERIA',
                    'FRECUENCIA'    => 'AL INICIO DEL TURNO',
                    'METODO'        => 'MANUAL',
                    'CRITERIO'      => 'EXCESO DE POLVO Y RESPECTIVO AISLANTE',
                    'RESPONSABLE'   => 'OPERARIO',
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
                    'RESPONSABLE'   => 'OPERARIO',
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
                    'RESPONSABLE'   => 'OPERARIO',
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
                    'RESPONSABLE'   => 'OPERARIO',
                    'OBSERVACIONES' => 'NO DEBE HABER GRASA EN PISO Y/O OBJETOS EXTRAÑOS AL LUGAR'
                ],
                'categoria' => 'limpieza'
            ],
            [
                'items'     => [
                    'ITEM'          => 'SUMINISTRO DE AGUA, VAPOR Y ELECTRICIDAD',
                    'FRECUENCIA'    => 'AL INICIO DEL TURNO',
                    'METODO'        => 'VISUAL',
                    'CRITERIO'      => 'VERIFICAR QUE SE CUENTE CON LOS SERVICIOS',
                    'RESPONSABLE'   => 'OPERARIO',
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
                    'RESPONSABLE'   => 'OPERARIO',
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
                    'RESPONSABLE'   => 'OPERARIO',
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
                    'RESPONSABLE'   => 'OPERARIO',
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
                    'RESPONSABLE'   => 'OPERARIO',
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
                    'RESPONSABLE'   => 'OPERARIO',
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
                    'RESPONSABLE'   => 'OPERARIO',
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
                    'RESPONSABLE'   => 'OPERARIO',
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
                    'RESPONSABLE'   => 'OPERARIO',
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
                    'RESPONSABLE'   => 'OPERARIO',
                    'OBSERVACIONES' => 'AL FINAL DE LA JORNADA ANOTAR SUS CICLOS'
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
            'area'          => 'Lavanderia Institucional',
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
