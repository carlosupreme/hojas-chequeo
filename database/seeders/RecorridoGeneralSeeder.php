<?php

namespace Database\Seeders;

use App\Models\CategoriaRecorrido;
use App\Models\FormularioRecorrido;
use App\Models\ItemRecorrido;
use Illuminate\Database\Seeder;

class RecorridoGeneralSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Crear el Formulario Principal
        $formulario = FormularioRecorrido::create([
            'nombre' => 'RECORRIDO GENERAL DE LA PLANTA SANTA ROSA - MANTENIMEINTO',
            'descripcion' => 'Chequeo diario de equipos y áreas de producción',
        ]);

        // 2. Definir la Estructura (Categorías e Items)
        $estructura = [
            'CUARTO DE MAQUINAS' => [
                ['nombre' => 'Generador de Vapor 1', 'tipo' => 'estado'],
                ['nombre' => 'Generador de Vapor 2', 'tipo' => 'estado'],
                ['nombre' => 'Nivel de agua en la mirilla', 'tipo' => 'estado'],
                ['nombre' => 'Presión de vapor', 'tipo' => 'estado'],
                ['nombre' => 'Verificar válvulas abiertas de Gas L.P., Agua', 'tipo' => 'estado'],
                ['nombre' => 'Suavizada y Salida de Vapor', 'tipo' => 'estado'],
            ],
            'Compresor de Aire 1' => [
                ['nombre' => 'Presión de aire', 'tipo' => 'estado'],
                ['nombre' => 'Verificar válvula de salida de aire', 'tipo' => 'estado'],
            ],
            'Tanque de condensados' => [
                ['nombre' => 'Nivel de agua que sea visible en la mirilla su capacidad', 'tipo' => 'estado'],
                ['nombre' => 'Verificar válvula de salida abierta', 'tipo' => 'estado'],
                ['nombre' => 'Temperatura del agua', 'tipo' => 'estado'],
            ],
            'Cisterna- Cubo de luz' => [
                ['nombre' => 'Nivel de agua', 'tipo' => 'estado'],
            ],
            'Bomba sumergible (Cuarto de Maquinas y Cubo de luz)' => [
                ['nombre' => 'Funcionamiento de las bombas', 'tipo' => 'estado'],
            ],
            'Líneas de agua' => [
                ['nombre' => 'Válvulas que estén abiertas hacia lavado en agua y tanque de condensados', 'tipo' => 'estado'],
            ],
            'Líneas de Vapor' => [
                ['nombre' => 'Válvulas que estén abiertas hacia planchado, lavado en agua y lavado en seco', 'tipo' => 'estado'],
            ],
            'LAVADO EN AGUA' => [
                ['nombre' => '*Centrifuga', 'tipo' => 'estado'],
                ['nombre' => '*Lavadoras', 'tipo' => 'estado'],
                ['nombre' => '*Tómbolas', 'tipo' => 'estado'],
                ['nombre' => 'LA-HID-02- Presiones de trabajo', 'tipo' => 'estado'],
            ],
            'MEDIDOR DE AGUA' => [
                ['nombre' => 'Lectura del medidor', 'tipo' => 'numero'],
            ],
            'MEDIDOR DE LUZ' => [
                ['nombre' => 'Lectura del medidor kWh kW kVARh Y', 'tipo' => 'texto'],
            ],
            'AZOTEA' => [
                ['nombre' => 'Limpieza (Llevar artículos de limpieza)', 'tipo' => 'estado'],
                ['nombre' => 'Nivel Tanque estacionario TES-01', 'tipo' => 'numero'], // Para el %
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
