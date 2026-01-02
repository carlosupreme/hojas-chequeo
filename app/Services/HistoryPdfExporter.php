<?php

namespace App\Services;

use App\Models\HojaChequeo;
use App\Models\Turno;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Collection;
use Symfony\Component\HttpFoundation\StreamedResponse;

class HistoryPdfExporter
{
    public function exportPdf(
        HojaChequeo $record,
        Collection $turnos,
        Collection $ejecuciones,
        array $dates,
        $turnoId = 'all'
    ): StreamedResponse {
        $filas = $record->filas()->with('answerType', 'valores')->get();
        $columnas = $record->columnas()->get();

        $chunks = [];
        $dateChunks = array_chunk($dates, 15);

        if ($turnoId === 'all') {
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

        $turnoName = $turnoId === 'all' ? 'todos' : (Turno::find($turnoId)?->nombre ?? 'turno');

        $pdf = Pdf::loadView('filament.resources.hoja-chequeos.pages.history-hoja-chequeo-pdf', compact('filas', 'columnas', 'chunks', 'record'))
            ->setPaper('a4', 'landscape')
            ->setOption('isHtml5ParserEnabled', true)
            ->setOption('isPhpEnabled', true);

        $filename = 'historial_'.$record->equipo->tag.'_'.$turnoName.'_'.now()->format('Y-m-d').'.pdf';

        return response()->streamDownload(
            fn () => print ($pdf->output()),
            $filename
        );
    }
}
