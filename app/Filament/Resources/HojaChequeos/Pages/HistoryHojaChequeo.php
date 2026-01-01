<?php

namespace App\Filament\Resources\HojaChequeos\Pages;

use App\Filament\Resources\HojaChequeos\HojaChequeoResource;
use App\Models\HojaChequeo;
use App\Models\Turno;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Filament\Actions\Action;
use Filament\Forms\Components\DatePicker;
use Filament\Resources\Pages\Page;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Schema;
use Illuminate\Support\Collection;

class HistoryHojaChequeo extends Page
{
    protected static string $resource = HojaChequeoResource::class;

    protected string $view = 'filament.resources.hoja-chequeos.pages.history-hoja-chequeo';

    public HojaChequeo $record;

    public ?string $startDate = null;

    public ?string $endDate = null;

    public string $activeTab = 'compare';

    protected array $queryString = ['startDate', 'endDate', 'activeTab'];

    public function getTitle(): string
    {
        return 'Historial de '.$this->record->equipo->tag.' (v'.$this->record->version.')';
    }

    public function mount(): void
    {
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
        $endDate = Carbon::parse($this->endDate)->addDay()->format('Y-m-d');
        return $this->record->chequeos()
            ->with(['turno', 'user', 'respuestas.hojaFila', 'respuestas.answerOption'])
            ->whereBetween('finalizado_en', [$this->startDate, $endDate])
            ->whereNotNull('finalizado_en')
            ->orderBy('finalizado_en')
            ->get();
    }

    public function getTurnos(): Collection
    {
        $ejecucionTurnos = $this->getEjecuciones()->pluck('turno_id')->unique();

        return Turno::whereIn('id', $ejecucionTurnos)->get();
    }

    public function getEjecucionesByDateAndTurno(): array
    {
        $ejecuciones = $this->getEjecuciones();
        $grouped = [];

        foreach ($ejecuciones as $ejecucion) {
            $date = $ejecucion->finalizado_en->format('Y-m-d');
            $turnoId = $ejecucion->turno_id;

            $grouped[$date][$turnoId] = $ejecucion;
        }

        return $grouped;
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
        // Build query based on active tab
        $query = \DB::table('hoja_ejecucions as he')
            ->join('hoja_fila_respuestas as hfr', 'he.id', '=', 'hfr.hoja_ejecucion_id')
            ->where('he.hoja_chequeo_id', $this->record->id)
            ->whereBetween('he.finalizado_en', [$this->startDate, $this->endDate])
            ->whereNotNull('he.finalizado_en')
            ->whereNotNull('hfr.numeric_value');

        // Filter by turno if not in compare mode
        if ($this->activeTab !== 'compare') {
            $query->where('he.turno_id', $this->activeTab);
        }

        // Aggregate by fila_id
        $results = $query
            ->select('hfr.hoja_fila_id')
            ->selectRaw('SUM(hfr.numeric_value) as suma')
            ->selectRaw('AVG(hfr.numeric_value) as promedio')
            ->selectRaw('COUNT(hfr.numeric_value) as count')
            ->groupBy('hfr.hoja_fila_id')
            ->get();

        // Convert to array indexed by fila_id
        return $results->mapWithKeys(function ($result) {
            return [
                $result->hoja_fila_id => [
                    'suma' => (float) $result->suma,
                    'promedio' => (float) $result->promedio,
                    'count' => (int) $result->count,
                ],
            ];
        })->toArray();
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
                ->form([
                    \Filament\Forms\Components\Select::make('turno_id')
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
                ->label('Exportar a Excel')
                ->icon('heroicon-o-document-arrow-down')
                ->action(fn () => $this->exportExcel()),
        ];
    }

    public function exportPdf($turnoId = 'all')
    {
        $dates = $this->getDateRange();
        $filas = $this->record->filas()->with('answerType', 'valores')->get();
        $columnas = $this->record->columnas()->get();
        $ejecuciones = $this->getEjecuciones();

        $chunks = [];
        $dateChunks = array_chunk($dates, 15);

        if ($turnoId === 'all') {
            // For "all" mode, create separate chunks for each turno
            $turnos = $this->getTurnos();

            foreach ($turnos as $turno) {
                foreach ($dateChunks as $chunkDates) {
                    $chunkEjecuciones = [];

                    foreach ($chunkDates as $date) {
                        $ejecucion = $ejecuciones->first(function ($ej) use ($date, $turno) {
                            return $ej->finalizado_en->format('Y-m-d') === $date && $ej->turno_id === $turno->id;
                        });

                        if ($ejecucion) {
                            $chunkEjecuciones[$date] = $ejecucion;
                        }
                    }

                    $chunks[] = [
                        'dates' => $chunkDates,
                        'ejecuciones' => $chunkEjecuciones,
                        'turno' => $turno,
                    ];
                }
            }
        } else {
            // For single turno mode
            $turno = Turno::find($turnoId);
            $filteredEjecuciones = $ejecuciones->where('turno_id', $turnoId);

            foreach ($dateChunks as $chunkDates) {
                $chunkEjecuciones = [];

                foreach ($chunkDates as $date) {
                    $ejecucion = $filteredEjecuciones->first(function ($ej) use ($date) {
                        return $ej->finalizado_en->format('Y-m-d') === $date;
                    });

                    if ($ejecucion) {
                        $chunkEjecuciones[$date] = $ejecucion;
                    }
                }

                $chunks[] = [
                    'dates' => $chunkDates,
                    'ejecuciones' => $chunkEjecuciones,
                    'turno' => $turno,
                ];
            }
        }

        // Get turno info for filename
        $turnoName = $turnoId === 'all' ? 'todos' : (Turno::find($turnoId)?->nombre ?? 'turno');

        $pdf = Pdf::loadView('filament.resources.hoja-chequeos.pages.history-hoja-chequeo-pdf', [
            'record' => $this->record,
            'chunks' => $chunks,
            'filas' => $filas,
            'columnas' => $columnas,
        ])
            ->setPaper('a4', 'landscape')
            ->setOption('isHtml5ParserEnabled', true)
            ->setOption('isPhpEnabled', true);

        $filename = 'historial_'.$this->record->equipo->tag.'_'.$turnoName.'_'.now()->format('Y-m-d').'.pdf';

        return response()->streamDownload(
            fn () => print ($pdf->output()),
            $filename
        );
    }

    public function exportExcel() {}
}
