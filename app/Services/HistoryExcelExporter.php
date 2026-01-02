<?php

namespace App\Services;

use App\Models\AnswerOption;
use App\Models\HojaChequeo;
use App\Models\Turno;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use Symfony\Component\HttpFoundation\StreamedResponse;

class HistoryExcelExporter
{
    public function exportExcel(
        HojaChequeo $record,
        Collection $turnos,
        Collection $ejecuciones,
        array $dates,
        $turnoId = 'all'
    ): StreamedResponse {
        $spreadsheet = new Spreadsheet;

        $filas = $record->filas()->with('answerType', 'valores')->get();
        $columnas = $record->columnas()->get();

        $ejecMap = [];
        foreach ($ejecuciones as $ej) {
            $date = $ej->finalizado_en->format('Y-m-d');
            $ejecMap[$ej->turno_id][$date] = $ej;
        }

        if ($turnoId !== 'all') {
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
            $sheet->setCellValue('C3', 'HOJA DE CHEQUEO EQUIPO '.$record->equipo->nombre);
            $sheet->setCellValue('D3', 'No DE CONTROL');
            $sheet->setCellValue('E3', 'REVISION');

            $sheet->setCellValue('A4', $record->equipo->area);
            $sheet->setCellValue('B4', $record->equipo->tag);
            $sheet->setCellValue('D4', $record->equipo->numeroControl);
            $sheet->setCellValue('E4', $record->equipo->revision);

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
        $fileName = 'historial-'.$record->equipo->tag.'_'.$turnoName.'-'.now()->format('Y-m-d').'.xlsx';

        return response()->streamDownload(function () use ($spreadsheet) {
            $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
            $writer->save('php://output');
        }, $fileName, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }
}
