<?php

namespace Database\Seeders;

use App\Models\CategoriaRecorrido;
use App\Models\FormularioRecorrido;
use App\Models\ItemRecorrido;
use Illuminate\Database\Seeder;

class RecorridoTintoreriaSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Crear el Formulario Principal
        $formulario = FormularioRecorrido::firstOrCreate([
            'nombre' => 'RECORRIDO GENERAL PLANTA SANTA ROSA - TINTORERÍA',
        ], [
            'descripcion' => 'Supervisión de áreas de tintorería al inicio y final de la jornada',
        ]);

        // 2. Definir la Estructura (Categorías e Items)
        $estructura = [
            // ========== AL INICIO DE LA JORNADA ==========
            'AL INICIO DE LA JORNADA - CUARTO DE MAQUINAS' => [
                ['nombre' => 'Funcionamiento de la Caldera', 'tipo' => 'estado'],
                ['nombre' => '>Equipo energizado', 'tipo' => 'estado'],
                ['nombre' => '>Apertura de válvulas', 'tipo' => 'estado'],
                ['nombre' => '>Registro Tarjetón Calderas', 'tipo' => 'estado'],
            ],
            'AL INICIO DE LA JORNADA - CUBO DE LUZ' => [
                ['nombre' => 'Funcionamiento de la Cisterna', 'tipo' => 'estado'],
                ['nombre' => '>Energizar Bomba', 'tipo' => 'estado'],
                ['nombre' => '>Revisión de niveles', 'tipo' => 'estado'],
            ],
            'AL INICIO DE LA JORNADA - LAVADO EN AGUA' => [
                ['nombre' => 'Funcionamiento de las Lavadoras*', 'tipo' => 'estado'],
                ['nombre' => '>Equipo energizado', 'tipo' => 'estado'],
                ['nombre' => '>Revisión de Hojas de chequeo', 'tipo' => 'estado'],
                ['nombre' => 'Funcionamiento de las Tómbolas*', 'tipo' => 'estado'],
                ['nombre' => '>Equipo energizado', 'tipo' => 'estado'],
                ['nombre' => '>Revisión de Hojas de chequeo', 'tipo' => 'estado'],
            ],
            'AL INICIO DE LA JORNADA - PLANCHADO' => [
                ['nombre' => 'Funcionamiento de las Prensas*', 'tipo' => 'estado'],
                ['nombre' => '>Apertura de válvulas', 'tipo' => 'estado'],
                ['nombre' => '>Limpieza', 'tipo' => 'estado'],
                ['nombre' => '>Revisión de Hojas de chequeo', 'tipo' => 'estado'],
                ['nombre' => 'Funcionamiento de la bomba de vacío', 'tipo' => 'estado'],
                ['nombre' => '>Equipo energizado', 'tipo' => 'estado'],
                ['nombre' => '>Revisión de Hojas de chequeo', 'tipo' => 'estado'],
            ],
            'AL INICIO DE LA JORNADA - LAVADO EN SECO' => [
                ['nombre' => 'Funcionamiento de las Lavadoras*', 'tipo' => 'estado'],
                ['nombre' => '>Equipo energizado', 'tipo' => 'estado'],
                ['nombre' => '>Revisión de Hojas de chequeo', 'tipo' => 'estado'],
            ],

            // ========== AL FINAL DE LA JORNADA ==========
            'AL FINAL DE LA JORNADA - CUARTO DE MAQUINAS' => [
                ['nombre' => 'Funcionamiento de la Caldera', 'tipo' => 'estado'],
                ['nombre' => '>Equipo Desenergizado', 'tipo' => 'estado'],
                ['nombre' => '>Cierre de válvulas', 'tipo' => 'estado'],
                ['nombre' => '>Registro Tarjetón Calderas', 'tipo' => 'estado'],
            ],
            'AL FINAL DE LA JORNADA - LAVADO EN AGUA' => [
                ['nombre' => 'Funcionamiento de las Lavadoras', 'tipo' => 'estado'],
                ['nombre' => '>Equipo Desenergizado', 'tipo' => 'estado'],
                ['nombre' => '>Revisión de Hojas de chequeo', 'tipo' => 'estado'],
                ['nombre' => 'Funcionamiento de las Tómbolas', 'tipo' => 'estado'],
                ['nombre' => '>Equipo Desenergizado', 'tipo' => 'estado'],
                ['nombre' => '>Revisión de Hojas de chequeo', 'tipo' => 'estado'],
            ],
            'AL FINAL DE LA JORNADA - LAVADO EN SECO' => [
                ['nombre' => 'Funcionamiento de las Lavadoras*', 'tipo' => 'estado'],
                ['nombre' => '>Equipo Desenergizado', 'tipo' => 'estado'],
                ['nombre' => '>Revisión de Hojas de chequeo', 'tipo' => 'estado'],
            ],
            'AL FINAL DE LA JORNADA - PLANCHADO' => [
                ['nombre' => 'Funcionamiento de las Prensas', 'tipo' => 'estado'],
                ['nombre' => '>Cierre de válvulas', 'tipo' => 'estado'],
                ['nombre' => '>Limpieza', 'tipo' => 'estado'],
                ['nombre' => 'Funcionamiento de la bomba de vacío', 'tipo' => 'estado'],
                ['nombre' => '>Equipo Desenergizado', 'tipo' => 'estado'],
                ['nombre' => '>Revisión de Hojas de chequeo', 'tipo' => 'estado'],
            ],
        ];

        $ordenCat = 1;
        foreach ($estructura as $nombreCat => $items) {
            $categoria = CategoriaRecorrido::firstOrCreate([
                'formulario_recorrido_id' => $formulario->id,
                'nombre' => $nombreCat,
            ], [
                'orden' => $ordenCat++,
            ]);

            $ordenItem = 1;
            foreach ($items as $itemData) {
                ItemRecorrido::firstOrCreate([
                    'categoria_recorrido_id' => $categoria->id,
                    'nombre' => $itemData['nombre'],
                ], [
                    'tipo_entrada' => $itemData['tipo'],
                    'orden' => $ordenItem++,
                ]);
            }
        }
    }
}
