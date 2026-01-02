<?php

namespace App\Filament\Resources\HojaChequeos\Pages;

use App\Filament\Resources\HojaChequeos\HojaChequeoResource;
use App\Models\AnswerOption;
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
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Spreadsheet;

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
                ->extraAttributes(['class' => 'text-white bg-green-600'])
                ->label('Exportar a Excel')
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
                    return $this->exportExcel($data['turno_id']);
                }),
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

    public function exportExcel($turnoId = 'all')
    {
        try {
            $spreadsheet = new Spreadsheet;

            $dates = $this->getDateRange();
            $filas = $this->record->filas()->with('answerType', 'valores')->get();
            $columnas = $this->record->columnas()->get();
            $ejecuciones = $this->getEjecuciones();

            // Build map: turno_id => date (Y-m-d) => ejecucion
            $ejecMap = [];
            foreach ($ejecuciones as $ej) {
                $date = $ej->finalizado_en->format('Y-m-d');
                $ejecMap[$ej->turno_id][$date] = $ej;
            }

            // Determine which turnos to export based on user selection
            if ($turnoId === 'all') {
                $turnos = $this->getTurnos();
            } else {
                $turnos = Turno::where('id', $turnoId)->get();
            }

            // Build dynamic mapping from existing AnswerOption records
            // 1) get all options
            $answerOptions = AnswerOption::orderBy('id')->get();
            // 2) extract unique icons in deterministic order
            $icons = $answerOptions->pluck('icon')->filter()->unique()->values();
            // 3) map icons -> numeric values (1..N)
            $iconMap = [];
            foreach ($icons as $idx => $icon) {
                $iconMap[$icon] = $idx + 1;
            }
            // 4) build label -> value map using the icon mapping (if option has no icon, leave empty)
            $labelMap = [];
            foreach ($answerOptions as $opt) {
                $labelMap[$opt->label] = $opt->icon ? ($iconMap[$opt->icon] ?? '') : '';
            }

            // Friendly turno name for filename
            $turnoName = $turnoId === 'all' ? 'todos' : (Turno::find($turnoId)?->nombre ?? 'turno');

            $sheetIndex = 0;
            foreach ($turnos as $turno) {
                if ($sheetIndex === 0) {
                    $sheet = $spreadsheet->getActiveSheet();
                    $sheet->setTitle(substr($turno->nombre ?? 'Turno', 0, 31));
                } else {
                    $sheet = $spreadsheet->createSheet();
                    $sheet->setTitle(substr($turno->nombre ?? ('Turno-'.$sheetIndex), 0, 31));
                }

                // Styles
                $borderStyle = [
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                        ],
                    ],
                ];

                $headerStyle = [
                    'font' => ['bold' => true],
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                        ],
                    ],
                ];

                // Title (merge first 5 columns like original pdf)
                $sheet->mergeCells('A1:E1');
                $sheet->setCellValue('A1', 'TACUBA DRY CLEAN');
                $sheet->getStyle('A1:E1')->applyFromArray($headerStyle);

                // Equipo info
                $sheet->setCellValue('A3', 'AREA');
                $sheet->setCellValue('B3', 'TAG');
                $sheet->setCellValue('C3', 'HOJA DE CHEQUEO EQUIPO '.$this->record->equipo->nombre);
                $sheet->setCellValue('D3', 'No DE CONTROL');
                $sheet->setCellValue('E3', 'REVISION');

                $sheet->setCellValue('A4', $this->record->equipo->area);
                $sheet->setCellValue('B4', $this->record->equipo->tag);
                $sheet->setCellValue('D4', $this->record->equipo->numeroControl);
                $sheet->setCellValue('E4', $this->record->equipo->revision);

                $sheet->getStyle('A3:E4')->applyFromArray($borderStyle);

                // Table header start at row 6
                $headerRow = 6;
                $colIndex = 1; // 1-based for Coordinate

                // Write columna headers
                foreach ($columnas as $columna) {
                    $cell = Coordinate::stringFromColumnIndex($colIndex).$headerRow;
                    $sheet->setCellValue($cell, strtoupper($columna->label));
                    $colIndex++;
                }

                // Write date headers
                foreach ($dates as $day) {
                    $cell = Coordinate::stringFromColumnIndex($colIndex).$headerRow;
                    $sheet->setCellValue($cell, Carbon::parse($day)->format('d/m'));
                    $colIndex++;
                }

                // Keep track of last column for styling
                $lastColumnIndex = $colIndex - 1;

                // Apply header style to header row
                $sheet->getStyle(Coordinate::stringFromColumnIndex(1).$headerRow.':'.Coordinate::stringFromColumnIndex($lastColumnIndex).$headerRow)
                    ->applyFromArray($headerStyle);

                // Fill filas
                $currentRow = $headerRow + 1;
                foreach ($filas as $fila) {
                    $colIndex = 1;
                    // columna valores
                    foreach ($columnas as $columna) {
                        $valor = $fila->valores->where('hoja_columna_id', $columna->id)->first();
                        $cell = Coordinate::stringFromColumnIndex($colIndex).$currentRow;
                        $sheet->setCellValue($cell, $valor?->valor ?? '');
                        $colIndex++;
                    }

                    // date cells: find ejecucion for this turno and date
                    foreach ($dates as $day) {
                        $cell = Coordinate::stringFromColumnIndex($colIndex).$currentRow;
                        $ejec = $ejecMap[$turno->id][$day] ?? null;
                        $respuesta = $ejec?->respuestas->where('hoja_fila_id', $fila->id)->first();

                        if ($respuesta) {
                            if ($respuesta->answer_option_id && $respuesta->answerOption) {
                                $icon = $respuesta->answerOption->icon ?? null;
                                $mapped = $icon ? ($iconMap[$icon] ?? 3) : 3;
                                $sheet->setCellValueExplicit($cell, $mapped, \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_NUMERIC);
                            } elseif ($respuesta->numeric_value !== null) {
                                $sheet->setCellValueExplicit($cell, $respuesta->numeric_value, \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_NUMERIC);
                            } elseif ($respuesta->text_value) {
                                $sheet->setCellValue($cell, $respuesta->text_value);
                            } elseif ($respuesta->boolean_value !== null) {
                                // store as 1/0 for easier aggregation
                                $sheet->setCellValueExplicit($cell, $respuesta->boolean_value ? 1 : 0, \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_NUMERIC);
                            }
                        }

                        $colIndex++;
                    }

                    $currentRow++;
                }

                // Operator name row
                $labelColEnd = count($columnas);
                $labelCell = Coordinate::stringFromColumnIndex(1).$currentRow;
                $sheet->setCellValue($labelCell, 'NOMBRE DE OPERADOR:');
                // Fill operator names into date columns
                $colIndex = count($columnas) + 1;
                foreach ($dates as $day) {
                    $cell = Coordinate::stringFromColumnIndex($colIndex).$currentRow;
                    $ejec = $ejecMap[$turno->id][$day] ?? null;
                    $sheet->setCellValue($cell, $ejec?->nombre_operador ?? '');
                    $colIndex++;
                }
                $currentRow++;

                // Operator signature row -> replace image with [FIRMADO]
                $labelCell = Coordinate::stringFromColumnIndex(1).$currentRow;
                $sheet->setCellValue($labelCell, 'FIRMA DEL OPERADOR');
                $colIndex = count($columnas) + 1;
                foreach ($dates as $day) {
                    $cell = Coordinate::stringFromColumnIndex($colIndex).$currentRow;
                    $ejec = $ejecMap[$turno->id][$day] ?? null;
                    if ($ejec?->firma_operador) {
                        $sheet->setCellValue($cell, '[FIRMADO]');
                    }
                    $colIndex++;
                }
                $currentRow++;

                // Supervisor signature row -> replace image with [FIRMADO]
                $labelCell = Coordinate::stringFromColumnIndex(1).$currentRow;
                $sheet->setCellValue($labelCell, 'FIRMA DEL SUPERVISOR');
                $colIndex = count($columnas) + 1;
                foreach ($dates as $day) {
                    $cell = Coordinate::stringFromColumnIndex($colIndex).$currentRow;
                    $ejec = $ejecMap[$turno->id][$day] ?? null;
                    if ($ejec?->firma_supervisor) {
                        $sheet->setCellValue($cell, '[FIRMADO]');
                    }
                    $colIndex++;
                }
                $currentRow++;

                // Add mapping legend under the table: option label -> numeric value
                // Leave one empty row for spacing
                $currentRow++;
                $sheet->setCellValue(Coordinate::stringFromColumnIndex(1).$currentRow, 'MAPA DE OPCIONES (Etiqueta -> Valor)');
                $sheet->getStyle(Coordinate::stringFromColumnIndex(1).$currentRow)->getFont()->setBold(true);
                $currentRow++;

                // Write label -> value mappings
                foreach ($labelMap as $label => $value) {
                    $text = $label.' -> '.($value === '' ? '-' : $value);
                    $sheet->setCellValue(Coordinate::stringFromColumnIndex(1).$currentRow, $text);
                    $currentRow++;
                }

                // Apply border style to used area
                $firstCell = Coordinate::stringFromColumnIndex(1).$headerRow;
                $lastCell = Coordinate::stringFromColumnIndex($lastColumnIndex).($currentRow - 1);
                $sheet->getStyle($firstCell.':'.$lastCell)->applyFromArray($borderStyle);

                // Auto size columns for readability (limited to first 50 columns to avoid huge loops)
                $maxAuto = min($lastColumnIndex, 50);
                for ($i = 1; $i <= $maxAuto; $i++) {
                    $sheet->getColumnDimension(Coordinate::stringFromColumnIndex($i))->setAutoSize(true);
                }

                $sheetIndex++;
            }

            // Prepare filename
            $fileName = 'historial-'.$this->record->equipo->tag.'_'.$turnoName.'-'.now()->format('Y-m-d').'.xlsx';

            return response()->streamDownload(function () use ($spreadsheet) {
                $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
                $writer->save('php://output');
            }, $fileName, [
                'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            ]);

        } catch (\Exception $e) {
            \Log::error('Error exportando Excel: '.$e->getMessage());
            throw new \Exception('Error al generar el archivo Excel: '.$e->getMessage());
        }
    }
}
