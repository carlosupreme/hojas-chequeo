<?php

namespace App\Filament\Resources\HojaChequeoResource\Pages;

use App\Filament\Resources\HojaChequeoResource;
use App\Models\HojaChequeo;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Filament\Actions\Action;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Grid;
use Filament\Forms\Form;
use Filament\Resources\Pages\Page;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Livewire\Attributes\Computed;

class HistoryHojaChequeo extends Page
{
    protected static string $resource = HojaChequeoResource::class;

    protected static string $view = 'filament.resources.hoja-chequeo-resource.pages.history-hoja-chequeo';

    public HojaChequeo $record;

    public ?string $startDate = null;
    public ?string $endDate = null;

    protected array $queryString = ['startDate', 'endDate'];

    public function getTitle(): string
    {
        return 'Historial de ' . $this->record->equipo->tag;
    }

    public function mount(): void
    {
        $this->startDate = $this->startDate ?? now()->subWeek()->format('Y-m-d');
        $this->endDate = $this->endDate ?? now()->format('Y-m-d');
    }

    public function form(Form $form): Form
    {
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
    public function dateRange(): array
    {
        return collect(CarbonPeriod::create(Carbon::parse($this->startDate), Carbon::parse($this->endDate)))
            ->map(fn($date) => $date->format('Y-m-d'))
            ->toArray();
    }

    private function normalizeArrayKeys(array $items, int $limit): array
    {
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
    public function tableData(): array
    {
        $data = [
            'items' => [],
            'operatorSignatures' => [],
            'supervisorSignatures' => [],
            'checks' => [],
            'operatorNames' => []
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
                    'icon' => $checkItem->simbologia?->icono,
                    'color' => $checkItem->simbologia?->color,
                    'text' => $checkItem->valor
                ];

                $data['checks'][$dayOfCheck][] = $value;
            }
        }

        foreach ($this->dateRange as $date) {
            if (!isset($data['checks'][$date])) {
                $data['checks'][$date] = [];
                $data['operatorSignatures'][$date] = null;
                $data['supervisorSignatures'][$date] = null;
                $data['operatorNames'][$date] = null;
            }
        }

        $data['items'] = $this->normalizeArrayKeys(
            $data['items'],
            collect($data['checks'])->first() ? count(collect($data['checks'])->first()) : 0
        );

        debug($data);

        return $data;
    }

    #[Computed]
    public function headers(): array
    {
        return array_keys($this->tableData['items'][0] ?? []);
    }

    #[Computed]
    public function availableDates(): array
    {
        return $this->dateRange;
    }

    public function calculateItemStats($index)
    {
        $sum = 0;
        $count = 0;

        foreach ($this->availableDates as $day) {
            if (isset($this->tableData['checks'][$day][$index]['text'])) {
                $value = filter_var(
                    $this->tableData['checks'][$day][$index]['text'],
                    FILTER_SANITIZE_NUMBER_FLOAT,
                    FILTER_FLAG_ALLOW_FRACTION
                );

                if ($value !== false) {
                    $sum += (float) $value;
                    $count++;
                }
            }
        }

        return [
            'sum' => $sum,
            'average' => $count > 0 ? round($sum / $count, 2) : 0,
            'count' => $count
        ];
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('exportPdf')
                ->label('Exportar a PDF')
                ->icon('heroicon-o-document-arrow-down')
                ->action(fn() => $this->exportPdf()),
            Action::make('exportExcel')
                ->label('Exportar a Excel')
                ->icon('heroicon-o-document-arrow-down')
                ->action(fn() => $this->exportExcel()),
        ];
    }

    public function exportExcel()
    {
        try {
            $headers = $this->headers;
            $tableData = $this->tableData;
            $availableDates = $this->availableDates;

            $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();

            // Estilo para bordes
            $borderStyle = [
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                    ],
                ],
            ];

            // Estilo para encabezados con bordes
            $headerStyle = [
                'font' => ['bold' => true],
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                    ],
                ],
            ];

            // Establecer título
            $sheet->mergeCells('A1:E1');
            $sheet->setCellValue('A1', 'TACUBA DRY CLEAN');
            $sheet->getStyle('A1:E1')->applyFromArray($headerStyle);

            // Información del equipo
            $sheet->setCellValue('A3', 'AREA');
            $sheet->setCellValue('B3', 'TAG');
            $sheet->setCellValue('C3', 'HOJA DE CHEQUEO EQUIPO ' . $this->record->equipo->nombre);
            $sheet->setCellValue('D3', 'No DE CONTROL');
            $sheet->setCellValue('E3', 'REVISION');

            $sheet->setCellValue('A4', $this->record->equipo->area);
            $sheet->setCellValue('B4', $this->record->equipo->tag);
            $sheet->setCellValue('D4', $this->record->equipo->numeroControl);
            $sheet->setCellValue('E4', $this->record->equipo->revision);

            // Aplicar bordes a la información del equipo
            $sheet->getStyle('A3:E4')->applyFromArray($borderStyle);

            // Preparar encabezados de la tabla
            $tableStartRow = 6;
            $col = 0;
            foreach ($headers as $header) {
                $col++;
                $cellCoordinate = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($col) . $tableStartRow;
                $sheet->setCellValue($cellCoordinate, $header);
            }
            foreach ($availableDates as $date) {
                $col++;
                $cellCoordinate = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($col) . $tableStartRow;
                $sheet->setCellValue($cellCoordinate, Carbon::parse($date)->format('d/m'));
            }

            // Aplicar estilo a los encabezados de la tabla
            $lastCol = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($col);
            $sheet->getStyle("A{$tableStartRow}:{$lastCol}{$tableStartRow}")->applyFromArray($headerStyle);

            // Agregar datos
            $currentRow = $tableStartRow + 1;
            foreach ($tableData['items'] as $index => $item) {
                $col = 0;
                foreach ($headers as $header) {
                    $col++;
                    $cellCoordinate = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($col) . $currentRow;
                    $sheet->setCellValue($cellCoordinate, $item[$header]);
                }

                foreach ($availableDates as $day) {
                    $cellValue = '';
                    if (isset($tableData['checks'][$day][$index])) {
                        $check = $tableData['checks'][$day][$index];
                        if (isset($check['text'])) {
                            $cellValue = $check['text'];
                        } elseif (isset($check['icon'])) {
                            $cellValue = $this->getIconText($check['icon']);
                        }
                    }
                    $col++;
                    $cellCoordinate = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($col) . $currentRow;
                    $sheet->setCellValue($cellCoordinate, $cellValue);
                }
                $currentRow++;
            }

            // Agregar firmas
            $col = count($headers);
            $cellCoordinate = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($col) . $currentRow;
            $sheet->setCellValue($cellCoordinate, 'NOMBRE DE OPERADOR:');

            $currentRow++;
            $cellCoordinate = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($col) . $currentRow;
            $sheet->setCellValue($cellCoordinate, 'FIRMA DEL OPERADOR:');

            $currentRow++;
            $cellCoordinate = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($col) . $currentRow;
            $sheet->setCellValue($cellCoordinate, 'FIRMA DEL SUPERVISOR:');

            // Aplicar bordes a toda la tabla
            $lastRow = $currentRow;
            $sheet->getStyle("A{$tableStartRow}:{$lastCol}{$lastRow}")->applyFromArray($borderStyle);

            // Ajustar columnas
            foreach (range('A', $lastCol) as $column) {
                $sheet->getColumnDimension($column)->setAutoSize(true);
            }

            // Generar archivo
            $fileName = 'hoja-chequeo-' . $this->record->equipo->tag . '-' . now()->format('Y-m-d') . '.xlsx';

            return response()->streamDownload(function () use ($spreadsheet) {
                $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
                $writer->save('php://output');
            }, $fileName, [
                'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            ]);

        } catch (\Exception $e) {
            \Log::error('Error exportando Excel: ' . $e->getMessage());
            throw new \Exception('Error al generar el archivo Excel: ' . $e->getMessage());
        }
    }

    private function getIconText(string $icon): string
    {
        return match ($icon) {
            'heroicon-c-check' => '✓',
            'heroicon-o-x-mark' => '✗',
            'heroicon-o-exclamation-triangle' => '⚠',
            'heroicon-o-minus-circle' => '⊖',
            'heroicon-o-eye' => '👁',
            'heroicon-o-clock' => '⏰',
            'heroicon-o-shield-exclamation' => '🛡',
            'heroicon-o-wrench' => '🔧',
            'heroicon-o-no-symbol' => '⛔',
            'heroicon-o-question-mark-circle' => '❓',
            default => ''
        };
    }


    public function exportPdf()
    {
        $headers = $this->headers;
        $tableData = $this->tableData;
        $availableDates = $this->availableDates;
        $record = $this->record;

        $dateChunks = array_chunk($availableDates, 15);
        $chunks = array_map(function ($dates) {
            return ['dates' => $dates];
        }, $dateChunks);

        $pdf = Pdf::loadView('pdf-view', compact('chunks', 'headers', 'tableData', 'record'))
            ->setPaper('a4', 'landscape')
            ->setOption('isHtml5ParserEnabled', true)
            ->setOption('isPhpEnabled', true)
            ->setOption('isRemoteEnabled', true);

        $fileName = 'checksheet-history-' . Str::random(10) . '.pdf';

        return response()->streamDownload(
            fn() => print ($pdf->output()),
            $fileName
        );
    }
}
