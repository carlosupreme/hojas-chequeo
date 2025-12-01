<?php

namespace App\Livewire;

use App\HojaChequeoArea;
use App\Models\HojaChequeo;
use App\Models\Reporte;
use Carbon\Carbon;
use Livewire\Component;

class AreaReportsChart extends Component
{
    public $selectedYear = null;

    public $years = [];

    public $chartData = [];

    public function mount(): void
    {
        $oldestYear = HojaChequeo::min('created_at');
        $currentYear = now()->year;

        $startYear = $oldestYear ? Carbon::parse($oldestYear)->year : $currentYear;
        for ($year = $startYear; $year <= $currentYear; $year++) {
            $this->years[$year] = $year;
        }

        $this->selectedYear = $currentYear;

        $this->loadChartData();
    }

    public function loadChartData(): void
    {
        if (! $this->selectedYear) {
            return;
        }

        $areas = array_map(fn (HojaChequeoArea $area) => $area->value, HojaChequeoArea::cases());

        $reportCounts = Reporte::query()
            ->selectRaw('area, EXTRACT(MONTH FROM fecha) as month, COUNT(*) as count')
            ->whereYear('fecha', $this->selectedYear)
            ->whereIn('area', $areas)
            ->groupBy('area')
            ->groupByRaw('EXTRACT(MONTH FROM fecha)')
            ->get()
            ->groupBy('area')
            ->map(fn ($items) => $items->pluck('count', 'month')->toArray());

        $colors = [
            'rgba(75, 192, 192, 1)',
            'rgba(54, 162, 235, 1)',
            'rgba(153, 102, 255, 1)',
        ];

        $datasets = [];
        foreach ($areas as $index => $area) {
            $areaCounts = $reportCounts->get($area, []);
            $monthlyData = [];

            for ($month = 1; $month <= 12; $month++) {
                $monthlyData[] = (int) ($areaCounts[$month] ?? 0);
            }

            $datasets[] = [
                'label' => $area,
                'data' => $monthlyData,
                'borderColor' => $colors[$index] ?? 'rgba(128, 128, 128, 1)',
                'backgroundColor' => $colors[$index] ?? 'rgba(128, 128, 128, 1)',
                'tension' => 0.4,
                'fill' => false,
            ];
        }

        $this->chartData = [
            'labels' => ['Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun', 'Jul', 'Ago', 'Sep', 'Oct', 'Nov', 'Dic'],
            'datasets' => $datasets,
        ];

        $this->dispatch('areaChartDataUpdated', $this->chartData);
    }

    public function updated($property): void
    {
        if ($property === 'selectedYear') {
            $this->loadChartData();
        }
    }

    public function render(): \Illuminate\Contracts\View\View
    {
        return view('livewire.area-reports-chart');
    }
}
