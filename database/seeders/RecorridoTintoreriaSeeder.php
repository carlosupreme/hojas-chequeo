<?php

namespace Database\Seeders;

use App\Models\CategoriaRecorrido;
use App\Models\FormularioRecorrido;
use App\Models\ItemRecorrido;
use App\Models\LogRecorrido;
use App\Models\Turno;
use App\Models\User;
use App\Models\ValorRecorrido;
use Illuminate\Database\Seeder;

class RecorridoTintoreriaSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Crear el Formulario Principal
        $formulario = FormularioRecorrido::create([
            'nombre' => 'RECORRIDO GENERAL DE LA PLANTA SANTA ROSA',
            'descripcion' => 'Chequeo diario de equipos y áreas de producción',
        ]);

        // 2. Definir la Estructura (Categorías e Items)
        $estructura = [
            'CUARTO DE MAQUINAS' => [
                ['nombre' => 'Generador de Vapor 1 o 2', 'tipo' => 'estado'],
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

        // 3. Crear Datos de Prueba (Simular los primeros 3 días de Diciembre)
        $usuario = User::first();
        $turno = Turno::first();

        if ($usuario && $turno) {
            for ($dia = 1; $dia <= 3; $dia++) {
                $log = LogRecorrido::create([
                    'formulario_recorrido_id' => $formulario->id,
                    'user_id' => $usuario->id,
                    'turno_id' => $turno->id,
                    'fecha' => "2025-12-0$dia",
                ]);

                // Llenar valores para este log
                $items = ItemRecorrido::whereHas('categoriaRecorrido', function ($q) use ($formulario) {
                    $q->where('formulario_recorrido_id', $formulario->id);
                })->get();

                foreach ($items as $item) {
                    ValorRecorrido::create([
                        'log_recorrido_id' => $log->id,
                        'item_recorrido_id' => $item->id,
                        'estado' => $item->tipo_entrada == 'estado' ? (rand(0, 5) > 0 ? '√' : 'PP') : null,
                        'valor_numerico' => $item->tipo_entrada == 'numero' ? rand(100, 999) : null,
                        'observaciones' => rand(0, 10) > 8 ? 'Todo bien' : null,
                    ]);
                }
            }
        }
    }
}
