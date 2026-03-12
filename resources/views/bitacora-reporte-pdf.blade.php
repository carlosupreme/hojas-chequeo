<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bitácora de Operación - {{ $equipo->nombre }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            margin: 20px;
            line-height: 1.4;
            color: #333;
        }

        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #000;
            padding-bottom: 15px;
        }

        .header h1 {
            font-size: 18px;
            font-weight: bold;
            margin: 0 0 10px 0;
            text-transform: uppercase;
        }

        .header p {
            margin: 5px 0;
            font-size: 11px;
        }

        .info-line {
            font-weight: bold;
            font-size: 11px;
        }

        .company-info {
            text-align: center;
            margin-bottom: 20px;
            font-size: 10px;
            color: #666;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
            font-size: 10px;
        }

        th,
        td {
            border: 1px solid #000;
            padding: 6px;
            text-align: left;
            vertical-align: top;
        }

        th {
            background-color: #f0f0f0;
            font-weight: bold;
            text-align: center;
            font-size: 9px;
            text-transform: uppercase;
        }

        .fecha-col {
            width: 12%;
            text-align: center;
            font-weight: bold;
            vertical-align: middle;
        }

        .operacion-col {
            width: 18%;
            text-align: center;
            font-weight: bold;
        }

        .responsable-col {
            width: 30%;
        }

        .hora-col {
            width: 15%;
            text-align: center;
            font-family: monospace;
        }

        .tiempo-col {
            width: 15%;
            text-align: center;
            font-weight: bold;
            vertical-align: middle;
        }

        .encendido {
            background-color: #e8f5e8;
            color: #2d5a2d;
        }

        .apagado {
            background-color: #ffe8e8;
            color: #8b2635;
        }

        .observaciones {
            background-color: #f9f9f9;
            font-style: italic;
            font-size: 9px;
            color: #555;
        }

        .resumen {
            margin-top: 30px;
            border-top: 1px solid #ccc;
            padding-top: 15px;
        }

        .resumen h3 {
            font-size: 14px;
            margin-bottom: 10px;
            color: #333;
        }

        .resumen-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 10px;
            margin-top: 15px;
        }

        .resumen-item {
            background-color: #f8f9fa;
            padding: 10px;
            border-radius: 4px;
            border-left: 4px solid #007bff;
        }

        .resumen-item strong {
            display: block;
            font-size: 11px;
            color: #666;
            margin-bottom: 3px;
        }

        .resumen-item .value {
            font-size: 14px;
            font-weight: bold;
            color: #333;
        }

        .footer {
            position: fixed;
            bottom: 15px;
            width: 100%;
            text-align: center;
            font-size: 8px;
            color: #666;
            border-top: 1px solid #eee;
            padding-top: 5px;
        }

        .no-data {
            text-align: center;
            padding: 60px 20px;
            font-style: italic;
            color: #666;
        }

        .no-data h3 {
            font-size: 16px;
            margin-bottom: 10px;
        }

        .page-break {
            page-break-before: always;
        }

        .watermark {
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            font-size: 120px;
            color: rgba(0, 0, 0, 0.05);
            z-index: -1;
            font-weight: bold;
        }
    </style>
</head>

<body>
    {{-- Encabezado --}}
    <div class="header">
        <h1>Bitácora de Operación del {{ $equipo->nombre }}</h1>
        <p>
            <strong>Período:</strong> {{ \Carbon\Carbon::parse($fecha_inicio)->format('d/m/Y') }}
            al {{ \Carbon\Carbon::parse($fecha_fin)->format('d/m/Y') }}
        </p>
        <p class="info-line">
            <strong>No. De Control:</strong> {{ $equipo->numeroControl ?? 'N/A' }} |
            <strong>TAG:</strong> {{ $equipo->tag }}
        </p>
    </div>

    {{-- Tabla de registros agrupada por día --}}
    @if($registros->isNotEmpty())
        <table>
            <thead>
                <tr>
                    <th class="fecha-col">Fecha</th>
                    <th class="operacion-col">Operación</th>
                    <th class="responsable-col">Responsable</th>
                    <th class="hora-col">Hora</th>
                    <th class="tiempo-col">Tiempo</th>
                </tr>
            </thead>
            <tbody>
                @foreach($registrosPorDia as $fechaKey => $turnosDia)
                    @php
                        $totalFilasDia = $turnosDia->count() * 2
                            + $turnosDia->filter(fn($r) => $r->observaciones)->count();
                        $tiempoDia = $turnosDia->sum('tiempo_operacion_minutos');
                        $hDia = intdiv($tiempoDia, 60);
                        $mDia = $tiempoDia % 60;
                        $fechaCarbon = \Carbon\Carbon::parse($fechaKey);
                    @endphp

                    @foreach($turnosDia as $i => $registro)
                        <tr>
                            @if($i === 0)
                                <td rowspan="{{ $totalFilasDia }}" class="fecha-col">
                                    {{ $fechaCarbon->format('d/m/Y') }}
                                    <br>
                                    <small style="font-size: 8px; color: #666;">
                                        {{ $fechaCarbon->locale('es')->translatedFormat('l') }}
                                    </small>
                                    @if($turnosDia->count() > 1)
                                        <br><small style="font-size: 8px;">({{ $turnosDia->count() }} turnos)</small>
                                    @endif
                                    <br><small style="font-size: 8px; font-weight: bold;">Total: {{ $hDia }}h {{ $mDia }}m</small>
                                </td>
                            @endif
                            <td class="operacion-col encendido">ENCENDIDO</td>
                            <td class="responsable-col">{{ $registro->encendido_por ?? 'N/A' }}</td>
                            <td class="hora-col">
                                {{ $registro->hora_encendido?->format('H:i') ?? 'N/A' }}
                                @if($registro->hora_encendido)
                                    <br><small
                                        style="font-size: 8px; color: #888;">{{ $registro->hora_encendido->format('d/m') }}</small>
                                @endif
                            </td>
                            <td rowspan="2" class="tiempo-col">
                                {{ $registro->tiempo_operacion_formateado }}
                            </td>
                        </tr>
                        <tr>
                            <td class="operacion-col apagado">APAGADO</td>
                            <td class="responsable-col">{{ $registro->apagado_por ?? 'N/A' }}</td>
                            <td class="hora-col">
                                {{ $registro->hora_apagado?->format('H:i') ?? 'N/A' }}
                                @if($registro->hora_apagado)
                                    <br><small
                                        style="font-size: 8px; {{ $registro->hora_apagado->toDateString() !== $registro->hora_encendido?->toDateString() ? 'color: #d35400; font-weight: bold;' : 'color: #888;' }}">
                                        {{ $registro->hora_apagado->format('d/m') }}
                                    </small>
                                @endif
                            </td>
                        </tr>
                        @if($registro->observaciones)
                            <tr>
                                <td colspan="4" class="observaciones">
                                    <strong>OBS:</strong> {{ $registro->observaciones }}
                                </td>
                            </tr>
                        @endif
                    @endforeach
                @endforeach
            </tbody>
        </table>

        {{-- Resumen --}}
        @php
            $todasLasFechas = $registros->flatMap(function ($r) {
                $fechas = [];
                if ($r->hora_encendido)
                    $fechas[] = $r->hora_encendido->toDateString();
                if ($r->hora_apagado)
                    $fechas[] = $r->hora_apagado->toDateString();
                return $fechas;
            })->unique();
            $diasUnicos = $todasLasFechas->count();
            $totalTurnos = $registros->count();
            $completados = $registros->whereNotNull('hora_encendido')->whereNotNull('hora_apagado')->count();
            $tiempoTotal = $registros->sum('tiempo_operacion_minutos');
            $horasTotal = intdiv($tiempoTotal, 60);
            $minutosTotal = $tiempoTotal % 60;
            $promedioDiario = $diasUnicos > 0 ? $tiempoTotal / $diasUnicos : 0;
            $horasPromedio = intdiv((int) $promedioDiario, 60);
            $minutosPromedio = (int) $promedioDiario % 60;
            $fallasCount = $registros->where('falla_vapor', true)->count();
        @endphp
        <div class="resumen">
            <h3>Resumen del Período</h3>
            <table style="width: 100%; font-size: 11px;">
                <tr>
                    <td style="border: none; width: 20%; text-align: center; padding: 8px;">
                        <strong style="display: block; color: #666; font-size: 9px;">DÍAS CON OPERACIÓN</strong>
                        <span style="font-size: 16px; font-weight: bold;">{{ $diasUnicos }}</span>
                    </td>
                    <td style="border: none; width: 20%; text-align: center; padding: 8px;">
                        <strong style="display: block; color: #666; font-size: 9px;">TURNOS COMPLETOS</strong>
                        <span style="font-size: 16px; font-weight: bold;">{{ $completados }} / {{ $totalTurnos }}</span>
                    </td>
                    <td style="border: none; width: 20%; text-align: center; padding: 8px;">
                        <strong style="display: block; color: #666; font-size: 9px;">TIEMPO TOTAL</strong>
                        <span style="font-size: 16px; font-weight: bold;">{{ $horasTotal }}h {{ $minutosTotal }}m</span>
                    </td>
                    <td style="border: none; width: 20%; text-align: center; padding: 8px;">
                        <strong style="display: block; color: #666; font-size: 9px;">PROMEDIO/DÍA</strong>
                        <span style="font-size: 16px; font-weight: bold;">{{ $horasPromedio }}h
                            {{ $minutosPromedio }}m</span>
                    </td>
                    <td style="border: none; width: 20%; text-align: center; padding: 8px;">
                        <strong style="display: block; color: #666; font-size: 9px;">FALLAS VAPOR</strong>
                        <span
                            style="font-size: 16px; font-weight: bold; {{ $fallasCount > 0 ? 'color: #c0392b;' : '' }}">{{ $fallasCount }}</span>
                    </td>
                </tr>
            </table>
        </div>


    @else
        <div class="no-data">
            <h3>SIN REGISTROS</h3>
            <p>No se encontraron registros para el período seleccionado.</p>
            <p><strong>Equipo:</strong> {{ $equipo->nombre }} ({{ $equipo->tag }})</p>
            <p><strong>Período:</strong> {{ \Carbon\Carbon::parse($fecha_inicio)->format('d/m/Y') }} al
                {{ \Carbon\Carbon::parse($fecha_fin)->format('d/m/Y') }}
            </p>
        </div>
    @endif

    {{-- Pie de página --}}
    <div class="footer">
        <p>
            <strong>Generado el:</strong> {{ now()->format('d/m/Y H:i') }} |
            <strong>Usuario:</strong> {{ auth()->user()->name }}
        </p>
    </div>
</body>

</html>