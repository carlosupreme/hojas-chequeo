<?php

namespace Database\Seeders;

use App\Models\CategoriaRecorrido;
use App\Models\FormularioRecorrido;
use App\Models\ItemRecorrido;
use Illuminate\Database\Seeder;

class RecorridoGerentesSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Crear el Formulario Principal
        $formulario = FormularioRecorrido::create([
            'nombre' => 'RECORRIDO GENERAL PLANTA SANTA ROSA - GERENCIA',
            'descripcion' => 'Supervisión gerencial quincenal de áreas de producción',
        ]);

        // 2. Definir la Estructura (Categorías e Items)
        $estructura = [
            'CUARTO DE MAQUINAS' => [
                ['nombre' => 'Funcionamiento de los equipos', 'tipo' => 'estado'],
            ],
            'CUBO DE LUZ' => [
                ['nombre' => 'Funcionamiento de la cisterna', 'tipo' => 'estado'],
            ],
            'LAVADO EN AGUA' => [
                ['nombre' => 'Funcionamiento de las Lavadoras*', 'tipo' => 'estado'],
                ['nombre' => 'Funcionamiento de las Tómbolas*', 'tipo' => 'estado'],
            ],
            'PLANCHADO' => [
                ['nombre' => 'Funcionamiento de las Prensas*', 'tipo' => 'estado'],
                ['nombre' => 'Funcionamiento de la bomba de vacío', 'tipo' => 'estado'],
            ],
            'LAVADO EN SECO' => [
                ['nombre' => 'Funcionamiento de las Lavadoras*', 'tipo' => 'estado'],
            ],
            'RECORRIDOS SUPERVISORES' => [
                ['nombre' => 'Recorrido Lavandería', 'tipo' => 'estado'],
                ['nombre' => 'Recorrido Tintorería', 'tipo' => 'estado'],
            ],
            'HOJAS DE CHEQUEO' => [
                ['nombre' => 'Lavandería', 'tipo' => 'estado'],
                ['nombre' => 'Tintorería', 'tipo' => 'estado'],
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
