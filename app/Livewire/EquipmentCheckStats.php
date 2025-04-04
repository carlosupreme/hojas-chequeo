<?php

namespace App\Livewire;

use App\Models\Equipo;
use Carbon\Carbon;
use Livewire\Component;

class EquipmentCheckStats extends Component
{
    public $selectedEquipment = null;
    public $selectedMonth = null;
    public $chartData = [];
    public $equipos = [];
    public $months = [];

    public function mount() {
        $this->equipos = Equipo::pluck('tag', 'id')->toArray();
        $this->selectedEquipment = array_key_first($this->equipos);
        $this->generateMonthsBasedOnEquipment(); // Cambiar a nuevo método
        $this->selectedMonth = now()->format('Y-m');
        $this->loadChartData();
    }

    private function generateMonthsBasedOnEquipment(): void
    {
        $this->months = [];

        if (!$this->selectedEquipment) return;

        $equipo = Equipo::find($this->selectedEquipment);

        if (!$equipo || !$equipo->created_at) return;

        $startDate = $equipo->created_at->copy()->startOfMonth();
        $endDate = now()->startOfMonth();

        while ($startDate <= $endDate) {
            $key = $startDate->format('Y-m');
            $this->months[$key] = ucfirst($startDate->translatedFormat('F Y'));
            $startDate->addMonth();
        }

        // Ordenar de más reciente a más antiguo
        krsort($this->months);
    }

    public function updatedSelectedEquipment($value) {
        $this->generateMonthsBasedOnEquipment();
        $this->selectedMonth = now()->format('Y-m');
    }

    public function loadChartData(): void {
        if (!$this->selectedEquipment || !$this->selectedMonth) {
            return;
        }

        $date = Carbon::createFromFormat('Y-m', $this->selectedMonth);
        $startOfMonth = $date->copy()->startOfMonth();
        $endOfMonth = $date->copy()->endOfMonth();

        $equipo = Equipo::find($this->selectedEquipment);
        $resultados = $this->calculateCompliance($equipo, $startOfMonth, $endOfMonth);

        $this->updateChartData($resultados);
        $this->dispatch('chartDataUpdated', $this->chartData);
    }

    private function calculateCompliance($equipo, $start, $end): array {
        $categorias = ['limpieza', 'operacion', 'revision'];
        $resultados = [];

        foreach ($categorias as $categoria) {
            $itemIds = $equipo->hojasChequeo()
                              ->join('items', 'hoja_chequeos.id', '=', 'items.hoja_chequeo_id')
                              ->where('items.categoria', $categoria)
                              ->pluck('items.id');

            if ($itemIds->isEmpty()) {
                $resultados[$categoria] = 0;
                continue;
            }

            $diasConChequeos = $equipo->hojasChequeo()
                                      ->join('chequeo_diarios', 'hoja_chequeos.id', '=', 'chequeo_diarios.hoja_chequeo_id')
                                      ->whereBetween('chequeo_diarios.created_at', [$start, $end])
                                      ->distinct('chequeo_diarios.created_at')
                                      ->count('chequeo_diarios.created_at');

            $totalChequeos = $itemIds->count() * $diasConChequeos;

            if ($totalChequeos === 0) {
                $resultados[$categoria] = 0;
                continue;
            }

            $chequeosCorrectos = $equipo->hojasChequeo()
                                        ->join('chequeo_diarios', 'hoja_chequeos.id', '=', 'chequeo_diarios.hoja_chequeo_id')
                                        ->join('item_chequeo_diarios', 'chequeo_diarios.id', '=', 'item_chequeo_diarios.chequeo_diario_id')
                                        ->whereIn('item_chequeo_diarios.item_id', $itemIds)
                                        ->where('item_chequeo_diarios.simbologia_id', 1) // Solo funciona si la simbologia es id=1
                                        ->whereBetween('chequeo_diarios.created_at', [$start, $end])
                                        ->count();

            $resultados[$categoria] = round(($chequeosCorrectos / $totalChequeos) * 100, 2);
        }

        return $resultados;
    }

    private function updateChartData(array $resultados): void {
        $this->chartData = [
            'labels' => ['Limpieza', 'Operación', 'Revisión'],
            'datasets' => [
                [
                    'label' => 'Porcentaje de Cumplimiento',
                    'data' => array_values($resultados),
                    'backgroundColor' => [
                        'rgba(75, 192, 192, 0.8)',
                        'rgba(54, 162, 235, 0.8)',
                        'rgba(153, 102, 255, 0.8)',
                    ],
                ]
            ]
        ];
    }

    public function updated($property) {
        if (in_array($property, ['selectedEquipment', 'selectedMonth'])) {
            $this->loadChartData();
        }
    }

    public function render() {
        return view('livewire.equipment-check-stats');
    }
}
