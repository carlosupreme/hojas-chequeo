<?php

namespace App\Livewire;

use App\Models\Equipo;
use App\Models\Reporte;
use Carbon\Carbon;
use Livewire\Component;

class ContadorChart extends Component
{
    public $selectedEquipment = null;

    public $selectedYear = null;

    public $equipos = [];

    public $years = [];

    public $chartData = [];

    public function mount(): void
    {
        $this->equipos = Equipo::pluck('tag', 'id')->toArray();

        $oldestYear = Reporte::min('created_at');
        $currentYear = now()->year;

        $startYear = $oldestYear ? Carbon::parse($oldestYear)->year : $currentYear;
        for ($year = $startYear; $year <= $currentYear; $year++) {
            $this->years[$year] = $year;
        }

        $this->selectedEquipment = array_key_first($this->equipos);
        $this->selectedYear = $currentYear;

        $this->loadChartData();
    }

    public function loadChartData(): void
    {
        if (! $this->selectedEquipment || ! $this->selectedYear) {
            return;
        }

        $reportCounts = Reporte::query()
            ->selectRaw('EXTRACT(MONTH FROM created_at) as month, COUNT(*) as count')
            ->where('equipo_id', $this->selectedEquipment)
            ->whereYear('created_at', $this->selectedYear)
            ->groupByRaw('EXTRACT(MONTH FROM created_at)')
            ->pluck('count', 'month')
            ->toArray();

        $monthlyData = [];
        for ($month = 1; $month <= 12; $month++) {
            $monthlyData[] = (int) ($reportCounts[$month] ?? 0);
        }

        $equipoTag = $this->equipos[$this->selectedEquipment] ?? 'Desconocido';

        $this->chartData = [
            'labels' => ['Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun', 'Jul', 'Ago', 'Sep', 'Oct', 'Nov', 'Dic'],
            'datasets' => [
                [
                    'label' => "Reportes del equipo {$equipoTag}",
                    'data' => $monthlyData,
                    'backgroundColor' => 'rgba(54, 162, 235, 0.8)',
                    'borderColor' => 'rgba(54, 162, 235, 1)',
                    'borderWidth' => 1,
                    'borderRadius' => 5,
                ],
            ],
        ];

        $this->dispatch('equipmentChartDataUpdated', $this->chartData);
    }

    public function updated($property): void
    {
        if (in_array($property, ['selectedEquipment', 'selectedYear'])) {
            $this->loadChartData();
        }
    }

    public function render(): \Illuminate\Contracts\View\View
    {
        return view('livewire.contador-chart');
    }
}
