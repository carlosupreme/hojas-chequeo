<?php

namespace App\Filament\Resources\HojaChequeoResource\Pages;

use App\Filament\Resources\HojaChequeoResource;
use App\Models\HojaChequeo;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Filament\Actions\Action;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Grid;
use Filament\Forms\Form;
use Filament\Resources\Pages\Page;
use Illuminate\Support\Arr;
use Livewire\Attributes\Computed;

class HistoryHojaChequeo extends Page
{
    protected static string $resource = HojaChequeoResource::class;

    protected static string $view = 'filament.resources.hoja-chequeo-resource.pages.history-hoja-chequeo';

    public HojaChequeo $record;

    public ?string $startDate = null;
    public ?string $endDate   = null;

    protected array $queryString = ['startDate', 'endDate'];

    public function getTitle(): string {
        return 'Historial de ' . $this->record->equipo->tag;
    }

    public function mount(): void {
        $this->startDate = $this->startDate ?? now()->subWeek()->format('Y-m-d');
        $this->endDate = $this->endDate ?? now()->format('Y-m-d');
    }

    public function form(Form $form): Form {
        return $form
            ->schema([
                Grid::make()->schema([
                    DatePicker::make('startDate')
                              ->label('Fecha de inicio')
                              ->live()
                              ->native(false)
                              ->displayFormat('D d/m/Y')
                              ->maxDate(now())
                              ->required(),
                    DatePicker::make('endDate')
                              ->live()
                              ->label('Fecha de fin')
                              ->native(false)
                              ->displayFormat('D d/m/Y')
                              ->afterOrEqual('startDate')
                              ->maxDate(now())
                              ->required(),
                ])
            ]);
    }


    #[Computed]
    public function dateRange(): array {
        return collect(CarbonPeriod::create(Carbon::parse($this->startDate), Carbon::parse($this->endDate)))
            ->map(fn($date) => $date->format('Y-m-d'))
            ->toArray();
    }


    private function normalizeArrayKeys(array $items, int $limit): array {
        if (empty($items)) {
            return [];
        }

        $allKeys = [];
        foreach ($items as $item) {
            $allKeys = array_unique(array_merge($allKeys, array_keys($item)));
        }

        $normalizedItems = array_map(function ($item) use ($allKeys) {
            return Arr::only(array_merge(array_fill_keys($allKeys, ''), $item), $allKeys);
        }, $items);

        if (count($normalizedItems) > $limit) {
            $normalizedItems = array_slice($normalizedItems, count($normalizedItems) - $limit, $limit);
        }

        return $normalizedItems;
    }

    #[Computed]
    public function tableData(): array {
        $data = [
            'items'                => [],
            'operatorSignatures'   => [],
            'supervisorSignatures' => [],
            'checks'               => [],
            'operatorNames'        => []
        ];

        $endDate = Carbon::parse($this->endDate)->addDay()->format('Y-m-d');
        $items = $this->record->chequeosDiarios()
                              ->whereBetween('created_at', [$this->startDate, $endDate])
                              ->with('itemsChequeoDiario.simbologia')
                              ->get();

        foreach ($items as $item) {
            $dayOfCheck = Carbon::parse($item->created_at)->format('Y-m-d');
            $data['checks'][$dayOfCheck] = [];
            $data['operatorSignatures'][$dayOfCheck] = $item->firma_operador;
            $data['supervisorSignatures'][$dayOfCheck] = $item->firma_supervisor;
            $data['operatorNames'][$dayOfCheck] = $item->nombre_operador;

            foreach ($item->itemsChequeoDiario as $checkItem) {
                $data['items'][] = $checkItem->item->valores;

                $value = [
                    'icon'  => $checkItem->simbologia?->icono,
                    'color' => $checkItem->simbologia?->color,
                    'text'  => $checkItem->valor
                ];

                $data['checks'][$dayOfCheck][] = $value;
            }
        }

        $data['items'] = $this->normalizeArrayKeys(
            $data['items'],
            collect($data['checks'])->first() ? count(collect($data['checks'])->first()): 0
        );

        return $data;
    }

    #[Computed]
    public function headers(): array {
        return array_keys($this->tableData['items'][0] ?? []);
    }

    #[Computed]
    public function availableDates(): array {
        return array_filter($this->dateRange, fn($date) => isset($this->tableData['checks'][$date]) ||
            isset($this->tableData['operatorSignatures'][$date])
        );
    }

    protected function getActions(): array {
        return [
            Action::make('exportPdf')
                  ->label('Exportar a PDF')
                  ->icon('heroicon-o-document-arrow-down')
                  ->action(fn() => $this->exportPdf()),
        ];
    }

    public function exportPdf() {}
}
