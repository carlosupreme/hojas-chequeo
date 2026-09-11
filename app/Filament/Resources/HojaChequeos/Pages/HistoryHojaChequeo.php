<?php

namespace App\Filament\Resources\HojaChequeos\Pages;

use App\Filament\Resources\HojaChequeos\HojaChequeoResource;
use App\Models\HojaChequeo;
use App\Models\Turno;
use App\Services\HistoryExcelExporter;
use App\Services\HistoryPdfExporter;
use App\Services\HojaChequeoHistoryService;
use Carbon\CarbonPeriod;
use Exception;
use Filament\Actions\Action;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Resources\Pages\Concerns\InteractsWithRecord;
use Filament\Resources\Pages\Page;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Schema;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;

class HistoryHojaChequeo extends Page
{
    use InteractsWithRecord;

    protected static string $resource = HojaChequeoResource::class;

    protected string $view = 'filament.resources.hoja-chequeos.pages.history-hoja-chequeo';

    public ?string $startDate = null;

    public ?string $endDate = null;

    public string $activeTab = 'compare';

    protected array $queryString = ['startDate', 'endDate', 'activeTab'];

    public static function canAccess(array $parameters = []): bool
    {
        return auth()->user()?->hasAnyRole(['Administrador', 'Supervisor']) ?? false;
    }

    public function getTitle(): string
    {
        return 'Historial de '.$this->record->equipo->tag.' (v'.$this->record->version.')';
    }

    protected ?Collection $ejecucionesCache = null;

    protected ?Collection $turnosCache = null;

    protected ?array $ejecucionesByDateAndTurnoCache = null;

    public function updatedStartDate(): void
    {
        $this->clearCache();
    }

    public function updatedEndDate(): void
    {
        $this->clearCache();
    }

    protected function clearCache(): void
    {
        $this->ejecucionesCache = null;
        $this->turnosCache = null;
        $this->ejecucionesByDateAndTurnoCache = null;
    }

    public function mount(int|string $record): void
    {
        $this->record = HojaChequeo::with(['equipo', 'filas.answerType', 'columnas'])->findOrFail($record);
        $this->startDate = $this->startDate ?? now()->subWeeks(2)->format('Y-m-d');
        $this->endDate = $this->endDate ?? now()->format('Y-m-d');
    }

    public function getDateRange(): array
    {
        $period = CarbonPeriod::create($this->startDate, $this->endDate);

        return collect($period)->map(fn ($date) => $date->format('Y-m-d'))->toArray();
    }

    public function getEjecuciones(): Collection
    {
        return $this->ejecucionesCache ??= app(HojaChequeoHistoryService::class)->getEjecuciones($this->record, $this->startDate, $this->endDate);
    }

    public function getTurnos(): Collection
    {
        if ($this->turnosCache !== null) {
            return $this->turnosCache;
        }

        $ejecucionTurnos = $this->getEjecuciones()->pluck('turno_id')->unique();

        return $this->turnosCache = Turno::whereIn('id', $ejecucionTurnos)->get();
    }

    public function getEjecucionesByDateAndTurno(): array
    {
        if ($this->ejecucionesByDateAndTurnoCache !== null) {
            return $this->ejecucionesByDateAndTurnoCache;
        }

        $ejecuciones = $this->getEjecuciones();
        $grouped = [];

        foreach ($ejecuciones as $ejecucion) {
            $date = $ejecucion->finalizado_en->format('Y-m-d');
            $turnoId = $ejecucion->turno_id;

            $grouped[$date][$turnoId] = $ejecucion;
        }

        return $this->ejecucionesByDateAndTurnoCache = $grouped;
    }

    public function getShiftColors(): array
    {
        $turnos = $this->getTurnos();
        $colors = ['blue', 'green', 'red', 'yellow'];

        return $turnos->mapWithKeys(function ($turno, $index) use ($colors) {
            return [$turno->id => $colors[$index % count($colors)]];
        })->toArray();
    }

    /**
     * Calculate sum and average of numeric values per fila using SQL aggregation
     * Returns array indexed by fila_id with ['suma' => float, 'promedio' => float]
     */
    public function getFilaAggregates(): array
    {
        return app(HojaChequeoHistoryService::class)->getFilaAggregates($this->record, $this->startDate, $this->endDate, $this->activeTab);
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Grid::make()->schema([
                    DatePicker::make('startDate')
                        ->label('Fecha de inicio')
                        ->live()
                        ->native(false)
                        ->displayFormat('D d/m/Y')
                        ->maxDate(now())
                        ->required(),
                    DatePicker::make('endDate')
                        ->label('Fecha de fin')
                        ->live()
                        ->native(false)
                        ->displayFormat('D d/m/Y')
                        ->afterOrEqual('startDate')
                        ->maxDate(now())
                        ->required(),
                ]),
            ]);
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('exportPdf')
                ->label('Exportar a PDF')
                ->icon('heroicon-o-document-arrow-down')
                ->schema([
                    Select::make('turno_id')
                        ->label('Seleccionar Turno')
                        ->options(function () {
                            $turnos = $this->getTurnos();
                            $options = ['all' => 'Todos los Turnos'];

                            foreach ($turnos as $turno) {
                                $options[$turno->id] = $turno->nombre;
                            }

                            return $options;
                        })
                        ->default('all')
                        ->required()
                        ->native(false),
                ])
                ->action(function (array $data) {
                    return $this->exportPdf($data['turno_id']);
                }),
            Action::make('exportExcel')
                ->extraAttributes(['class' => 'text-white bg-green-600'])
                ->label('Exportar a Excel')
                ->icon('heroicon-o-document-arrow-down')
                ->schema([
                    Select::make('turno_id')
                        ->label('Seleccionar Turno')
                        ->options(function () {
                            $turnos = $this->getTurnos();
                            $options = ['all' => 'Todos los Turnos'];

                            foreach ($turnos as $turno) {
                                $options[$turno->id] = $turno->nombre;
                            }

                            return $options;
                        })
                        ->default('all')
                        ->required()
                        ->native(false),
                ])
                ->action(function (array $data) {
                    return $this->exportExcel($data['turno_id']);
                }),
        ];
    }

    public function exportPdf($turnoId = 'all')
    {
        return app(HistoryPdfExporter::class)->exportPdf(
            $this->record,
            $this->getTurnos(),
            $this->getEjecuciones(),
            $this->getDateRange(),
            $turnoId
        );
    }

    /**
     * @throws Exception
     */
    public function exportExcel($turnoId = 'all')
    {
        try {
            return app(HistoryExcelExporter::class)->exportExcel(
                $this->record,
                $this->getTurnos(),
                $this->getEjecuciones(),
                $this->getDateRange(),
                $turnoId
            );
        } catch (Exception $e) {
            Log::error('Error exportando Excel: '.$e->getMessage());
            throw new Exception('Error al generar el archivo Excel: '.$e->getMessage());
        }
    }
}
