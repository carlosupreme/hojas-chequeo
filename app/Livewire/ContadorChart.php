<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Equipo;
use App\Models\Reporte;
use Carbon\Carbon;


class ContadorChart extends Component
{
    public $selectedEquipment = null;
    public $selectedYear      = null;
    public $equipos           = [];
    public $years             = [];
    public $chartData         = [];

    public function mount() {
        // Cargar equipos disponibles
        $this->equipos = Equipo::pluck('tag', 'id')->toArray();

        // Obtener el año más antiguo desde Reportes
        $oldestYear = Reporte::min('created_at');
        $currentYear = now()->year;

        // Crear array de años desde el más antiguo hasta el actual
        $startYear = $oldestYear ? Carbon::parse($oldestYear)->year : $currentYear;
        for ($year = $startYear; $year <= $currentYear; $year++) {
            $this->years[$year] = $year;
        }

        // Establecer valores por defecto
        $this->selectedEquipment = array_key_first($this->equipos);
        $this->selectedYear = $currentYear;

        $this->loadChartData();
    }

    public function loadChartData() {
        if (!$this->selectedEquipment || !$this->selectedYear) {
            return;
        }

        $monthlyData = [];
        $months = range(1, 12);

        foreach ($months as $month) {
            $count = Reporte::where('equipo_id', $this->selectedEquipment)
                            ->whereYear('created_at', $this->selectedYear)
                            ->whereMonth('created_at', $month)
                            ->count();

            $monthlyData[] = $count;
        }

        $equipo = Equipo::find($this->selectedEquipment);

        $this->chartData = [
            'labels'   => ['Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun', 'Jul', 'Ago', 'Sep', 'Oct', 'Nov', 'Dic'],
            'datasets' => [
                [
                    'label'           => "Reportes del equipo {$equipo->tag}",
                    'data'            => $monthlyData,
                    'backgroundColor' => 'rgba(54, 162, 235, 0.8)',
                    'borderColor'     => 'rgba(54, 162, 235, 1)',
                    'borderWidth'     => 1,
                    'borderRadius'    => 5,
                ]
            ]
        ];

        $this->dispatch('equipmentChartDataUpdated', $this->chartData);
    }

    public function updated($property) {
        if (in_array($property, ['selectedEquipment', 'selectedYear'])) {
            $this->loadChartData();
        }
    }

    public function render() {
        return view('livewire.contador-chart');
    }
}
