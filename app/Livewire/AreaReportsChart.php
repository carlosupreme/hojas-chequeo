<?php

namespace App\Livewire;

use App\Models\HojaChequeo;
use App\Models\Reporte;
use Carbon\Carbon;
use Livewire\Component;

class AreaReportsChart extends Component
{
    public $selectedYear = null;
    public $years = [];
    public $chartData = [];

    public function mount()
    {
        // Obtener el año más antiguo desde HojaChequeo
        $oldestYear = HojaChequeo::min('created_at');
        $currentYear = now()->year;

        // Crear array de años desde el más antiguo hasta el actual
        $startYear = $oldestYear ? Carbon::parse($oldestYear)->year : $currentYear;
        for ($year = $startYear; $year <= $currentYear; $year++) {
            $this->years[$year] = $year;
        }

        // Establecer año actual por defecto
        $this->selectedYear = $currentYear;

        $this->loadChartData();
    }

    public function loadChartData()
    {
        if (!$this->selectedYear) {
            return;
        }

        // Definir las áreas y meses
        $areas = ['Cuarto de maquinas', 'Tintoreria', 'Lavanderia Institucional'];
        $months = range(1, 12);

        // Preparar datos para cada área
        $datasets = [];
        $colors = [
            'rgba(75, 192, 192, 1)',  // Verde azulado
            'rgba(54, 162, 235, 1)',   // Azul
            'rgba(153, 102, 255, 1)'   // Púrpura
        ];

        foreach ($areas as $index => $area) {
            $monthlyData = [];

            foreach ($months as $month) {
                $count = Reporte::join('hoja_chequeos', 'reportes.hoja_chequeo_id', '=', 'hoja_chequeos.id')
                                ->where('hoja_chequeos.area', $area)
                                ->whereYear('reportes.fecha', $this->selectedYear)
                                ->whereMonth('reportes.fecha', $month)
                                ->count();

                $monthlyData[] = $count;
            }

            $datasets[] = [
                'label' => $area,
                'data' => $monthlyData,
                'borderColor' => $colors[$index],
                'backgroundColor' => $colors[$index],
                'tension' => 0.4,
                'fill' => false
            ];
        }

        $this->chartData = [
            'labels' => ['Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun', 'Jul', 'Ago', 'Sep', 'Oct', 'Nov', 'Dic'],
            'datasets' => $datasets
        ];

        $this->dispatch('areaChartDataUpdated', $this->chartData);
    }

    public function updated($property)
    {
        if ($property === 'selectedYear') {
            $this->loadChartData();
        }
    }

    public function render()
    {
        return view('livewire.area-reports-chart');
    }
}
