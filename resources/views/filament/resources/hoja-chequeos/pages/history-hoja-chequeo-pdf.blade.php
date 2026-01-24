<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Historial Hoja de Chequeo</title>
    <style>
        @page {
            size: A4 landscape;
            margin: 0.7cm;
        }

        body {
            font-family: Arial, sans-serif;
            font-size: 7px;
            line-height: 1.2;
        }

        .page-break {
            page-break-after: always;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 10px;
        }

        th, td {
            border: 0.5px solid #000;
            padding: 2px 3px;
            text-align: left;
            vertical-align: middle;
            font-size: 6px;
        }

        .date-column {
            width: 22px;
            text-align: center;
            padding: 3px 1px;
            word-break: break-word;
            white-space: normal;
        }

        .header-column {
            width: auto;
        }

        .text-center {
            text-align: center;
        }

        .text-right {
            text-align: right;
        }

        .badge {
            display: inline-block;
            padding: 1px 3px;
            background: #f3f4f6;
            border-radius: 2px;
            margin-right: 3px;
        }

        .signature-img {
            max-width: 40px;
            max-height: 20px;
        }

        .empty-check {
            display: inline-block;
            width: 8px;
            height: 8px;
            border: 0.5px solid #000;
            border-radius: 50%;
        }

        .header {
            font-weight: bold;
            text-transform: uppercase;
        }

        .subheader {
            font-style: italic;
        }

        .icon {
            width: 1.5rem;
            height: 1.5rem;
        }

        svg {
            -dompdf-font-variant: normal;
            overflow: visible !important;
        }
    </style>
</head>
<body>
@foreach($chunks as $index => $chunk)
    <div @if(!$loop->last) class="page-break" @endif>
        <div class="w-full" style="position: relative;">
            <img src="{{ public_path('lg.png') }}" style="float: left" alt="Tacuba" height="20px">
            <h4 style="text-align: center">TACUBA DRY CLEAN</h4>
        </div>
        <table>
            <tr>
                <td class="header">AREA</td>
                <td class="header">TAG</td>
                <td class="header text-center" rowspan="2">
                    HOJA DE CHEQUEO EQUIPO {{$record->equipo->nombre}}
                    @if(isset($chunk['turno']))
                        ({{ $chunk['turno']->nombre }})
                    @endif
                </td>
                <td class="header">No DE CONTROL DE EQUIPO</td>
                <td class="header">REVISION</td>
                <td class="header">RAZON</td>
            </tr>
            <tr>
                <td class="subheader">{{ $record->equipo->area }}</td>
                <td class="subheader">{{ $record->equipo->tag }}</td>
                <td class="subheader">{{ $record->equipo->numeroControl }}</td>
                <td class="subheader">{{ $record->equipo->revision }}</td>
                <td class="subheader">EMISION</td>
            </tr>
        </table>

        <table>
            <thead>
            <tr>
                @foreach($columnas as $columna)
                    <th class="header-column">{{ strtoupper($columna->label) }}</th>
                @endforeach
                @foreach($chunk['dates'] as $day)
                    <th class="date-column">{{ \Carbon\Carbon::parse($day)->format('d/m') }}</th>
                @endforeach
            </tr>
            </thead>
            <tbody>
            @foreach($filas as $fila)
                <tr>
                    @foreach($columnas as $columna)
                        @php
                            $valor = $fila->valores->where('hoja_columna_id', $columna->id)->first();
                        @endphp
                        <td>{{ $valor?->valor ?? '' }}</td>
                    @endforeach

                    @foreach($chunk['dates'] as $day)
                        <td class="date-column">
                            @php
                                $ejecucion = $chunk['ejecuciones'][$day] ?? null;
                                $respuesta = $ejecucion?->respuestas->where('hoja_fila_id', $fila->id)->first();
                            @endphp

                            @if($respuesta)
                                @if($respuesta->answer_option_id && $respuesta->answerOption)
                                    @php
                                        $icon = $respuesta->answerOption->icon;
                                        $color = $respuesta->answerOption->color ?? '#000000';
                                    @endphp

                                    @switch($icon)
                                        {{-- Check Icon --}}
                                        @case('heroicon-o-check')
                                            <img src="data:image/svg+xml;base64,{!! base64_encode('
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="'.$color.'">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" />
                </svg>
            ') !!}" width="12" height="12" style="vertical-align: middle;">
                                            @break

                                        {{-- X Mark Icon --}}
                                        @case('heroicon-o-x-mark')
                                            <img src="data:image/svg+xml;base64,{!! base64_encode('
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="'.$color.'">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                </svg>
            ') !!}" width="12" height="12" style="vertical-align: middle;">
                                            @break

                                        {{-- Minus Circle --}}
                                        @case('heroicon-o-minus-circle')
                                            <img src="data:image/svg+xml;base64,{!! base64_encode('
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="'.$color.'">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 12H9m12 0a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            ') !!}" width="12" height="12" style="vertical-align: middle;">
                                            @break

                                        {{-- No Symbol --}}
                                        @case('heroicon-o-no-symbol')
                                            <img src="data:image/svg+xml;base64,{!! base64_encode('
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="'.$color.'">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636" />
                </svg>
            ') !!}" width="12" height="12" style="vertical-align: middle;">
                                            @break

                                        {{-- Question Mark Circle --}}
                                        @case('heroicon-o-question-mark-circle')
                                            <img src="data:image/svg+xml;base64,{!! base64_encode('
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="'.$color.'">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9.879 7.519c1.171-1.025 3.071-1.025 4.242 0 1.172 1.025 1.172 2.687 0 3.712-.203.179-.43.326-.67.442-.745.361-1.45.999-1.45 1.827v.75M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-9 5.25h.008v.008H12v-.008z" />
                </svg>
            ') !!}" width="12" height="12" style="vertical-align: middle;">
                                            @break
                                    @endswitch

                                @elseif($respuesta->numeric_value !== null)
                                    <span class="text-sm">{{ $respuesta->numeric_value }}</span>
                                @elseif($respuesta->text_value)
                                    <span class="text-sm">{{ \Illuminate\Support\Str::limit($respuesta->text_value, 8) }}</span>
                                @elseif($respuesta->boolean_value !== null)
                                    <span class="text-sm">{{ $respuesta->boolean_value ? 'Sí' : 'No' }}</span>
                                @endif
                            @else
                                <span></span>
                            @endif
                        </td>
                    @endforeach
                </tr>
            @endforeach

            <!-- Operator Name Row -->
            <tr>
                <td colspan="{{ count($columnas) }}" class="text-right">NOMBRE DE OPERADOR:</td>
                @foreach($chunk['dates'] as $day)
                    <td class="date-column">
                        @php
                            $ejecucion = $chunk['ejecuciones'][$day] ?? null;
                        @endphp
                        {{ $ejecucion?->nombre_operador ?? '' }}
                    </td>
                @endforeach
            </tr>

            <!-- Operator Signature Row -->
            <tr>
                <td colspan="{{ count($columnas) }}" class="text-right">FIRMA DEL OPERADOR</td>
                @foreach($chunk['dates'] as $day)
                    <td class="date-column">
                        @php
                            $ejecucion = $chunk['ejecuciones'][$day] ?? null;
                        @endphp
                        @if($ejecucion?->firma_operador)
                            <img src="{{ app(\App\Services\ImageService::class)->getAsBase64($ejecucion->firma_operador) }}"
                                 alt="Firma del Operador"
                                 class="signature-img">
                        @else
                            <span></span>
                        @endif
                    </td>
                @endforeach
            </tr>

            <!-- Supervisor Signature Row -->
            <tr>
                <td colspan="{{ count($columnas) }}" class="text-right">FIRMA DEL SUPERVISOR</td>
                @foreach($chunk['dates'] as $day)
                    <td class="date-column">
                        @php
                            $ejecucion = $chunk['ejecuciones'][$day] ?? null;
                        @endphp
                        @if($ejecucion?->firma_supervisor)
                            <img src="{{ app(\App\Services\ImageService::class)->getAsBase64($ejecucion->firma_supervisor) }}"
                                 alt="Firma del Supervisor"
                                 class="signature-img">
                        @endif
                    </td>
                @endforeach
            </tr>
            </tbody>
        </table>
    </div>
@endforeach
</body>
</html>
