<?php

namespace Database\Seeders;

use App\Models\CategoriaRecorrido;
use App\Models\FormularioRecorrido;
use App\Models\ItemRecorrido;
use Illuminate\Database\Seeder;

class RecorridoLavanderiaSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Crear el Formulario Principal
        $formulario = FormularioRecorrido::create([
            'nombre' => 'RECORRIDO GENERAL PLANTA SANTA ROSA-LAVANDERÍA',
            'descripcion' => 'Supervisión de áreas de lavandería al inicio y final de la jornada',
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
                ['nombre' => 'Funcionamiento de las Prensas (En caso de utilizar alguna Prensa los días domingos o días inhabiles.)', 'tipo' => 'estado'],
                ['nombre' => '>Apertura de válvulas', 'tipo' => 'estado'],
                ['nombre' => '>Limpieza', 'tipo' => 'estado'],
                ['nombre' => '>Revisión de Hojas de chequeo', 'tipo' => 'estado'],
                ['nombre' => 'Funcionamiento de la bomba de vacío', 'tipo' => 'estado'],
                ['nombre' => '>Equipo energizado', 'tipo' => 'estado'],
                ['nombre' => '>Revisión de Hojas de chequeo', 'tipo' => 'estado'],
                ['nombre' => 'Funcionamiento de los Mangles 1 y/o 2*', 'tipo' => 'estado'],
                ['nombre' => '>Apertura de válvula de gas', 'tipo' => 'estado'],
                ['nombre' => '>Revisión Hoja de chequeo', 'tipo' => 'estado'],
            ],

            // ========== AL FINAL DE LA JORNADA ==========
            'AL FINAL DE LA JORNADA - CUARTO DE MAQUINAS' => [
                ['nombre' => 'Funcionamiento de la Caldera', 'tipo' => 'estado'],
                ['nombre' => '>Equipo Desenergizado', 'tipo' => 'estado'],
                ['nombre' => '>Cierre de válvulas', 'tipo' => 'estado'],
                ['nombre' => '>Registro Tarjetón Calderas', 'tipo' => 'estado'],
                ['nombre' => 'Funcionamiento de la Cisterna (Cuando la producción se termine temprano, los días domingos o días inhabiles.)', 'tipo' => 'estado'],
                ['nombre' => '>Apagar Bomba', 'tipo' => 'estado'],
                ['nombre' => '>Desprezurizar líneas de agua', 'tipo' => 'estado'],
            ],
            'AL FINAL DE LA JORNADA - CUBO DE LUZ' => [
                ['nombre' => 'Funcionamiento de la Cisterna', 'tipo' => 'estado'],
                ['nombre' => '>Apagar Bomba', 'tipo' => 'estado'],
            ],
            'AL FINAL DE LA JORNADA - LAVADO EN AGUA' => [
                ['nombre' => 'Funcionamiento de las Lavadoras', 'tipo' => 'estado'],
                ['nombre' => '>Equipo Desenergizado', 'tipo' => 'estado'],
                ['nombre' => '>Revisión de Hojas de chequeo', 'tipo' => 'estado'],
                ['nombre' => 'Funcionamiento de las Tómbolas', 'tipo' => 'estado'],
                ['nombre' => '>Equipo Desenergizado', 'tipo' => 'estado'],
                ['nombre' => '>Revisión de Hojas de chequeo', 'tipo' => 'estado'],
            ],
            'AL FINAL DE LA JORNADA - PLANCHADO' => [
                ['nombre' => 'Funcionamiento de las Prensas (En caso de utilizar alguna Prensa los días domingos o días inhabiles.)', 'tipo' => 'estado'],
                ['nombre' => '>Cierre de válvulas', 'tipo' => 'estado'],
                ['nombre' => '>Limpieza', 'tipo' => 'estado'],
                ['nombre' => 'Funcionamiento de la bomba de vacío', 'tipo' => 'estado'],
                ['nombre' => '>Equipo Desenergizado', 'tipo' => 'estado'],
                ['nombre' => '>Revisión de Hojas de chequeo', 'tipo' => 'estado'],
                ['nombre' => 'Funcionamiento de los Mangles 1 y/o 2', 'tipo' => 'estado'],
                ['nombre' => '>Equipo Desenergizado', 'tipo' => 'estado'],
                ['nombre' => '>Cierre de válvulas', 'tipo' => 'estado'],
            ],
        ];

        $ordenCat = 1;
        foreach ($estructura as $nombreCat => $items) {
            $categoria = CategoriaRecorrido::create([
                'formulario_recorrido_id' => $formulario->id,
                'nombre' => $nombreCat,
                'orden' => $ordenCat++,
            ]);

            $ordenItem = 1;
            foreach ($items as $itemData) {
                ItemRecorrido::create([
                    'categoria_recorrido_id' => $categoria->id,
                    'nombre' => $itemData['nombre'],
                    'tipo_entrada' => $itemData['tipo'],
                    'orden' => $ordenItem++,
                ]);
            }
        }
    }
}
