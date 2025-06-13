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
        
        th, td {
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
            color: rgba(0,0,0,0.05);
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
            <strong>Período:</strong> Del {{ \Carbon\Carbon::parse($fecha_inicio)->format('d') }} 
            al {{ \Carbon\Carbon::parse($fecha_fin)->format('d') }} de {{ \Carbon\Carbon::parse($fecha_fin)->locale('es')->translatedFormat('F Y') }}
        </p>
        <p class="info-line">
            <strong>No. De Control:</strong> {{ $equipo->numeroControl ?? 'N/A' }} | 
            <strong>TAG:</strong> {{ $equipo->tag }}
        </p>
    </div>

    {{-- Tabla de registros --}}
    @if($registros->isNotEmpty())
        <table>
            <thead>
                <tr>
                    <th class="fecha-col">Fecha</th>
                    <th class="operacion-col">Operación</th>
                    <th class="responsable-col">Responsable</th>
                    <th class="hora-col">Hora</th>
                    <th class="tiempo-col">Tiempo Total</th>
                </tr>
            </thead>
            <tbody>
                @foreach($registros as $registro)
                    <tr>
                        <td rowspan="2" class="fecha-col">
                            {{ $registro->fecha->format('d/m/Y') }}
                            <br>
                            <small style="font-size: 8px; color: #666;">
                                 {{ $registro->fecha->locale('es')->translatedFormat('D') }}
                            </small>
                        </td>
                        <td class="operacion-col encendido">ENCENDIDO</td>
                        <td class="responsable-col">{{ $registro->encendido_por ?? 'N/A' }}</td>
                        <td class="hora-col">{{ $registro->hora_encendido ?? 'N/A' }}</td>
                        <td rowspan="2" class="tiempo-col">
                            {{ $registro->tiempo_operacion_formateado }}
                        </td>
                    </tr>
                    <tr>
                        <td class="operacion-col apagado">APAGADO</td>
                        <td class="responsable-col">{{ $registro->apagado_por ?? 'N/A' }}</td>
                        <td class="hora-col">{{ $registro->hora_apagado ?? 'N/A' }}</td>
                    </tr>
                    @if($registro->observaciones)
                        <tr>
                            <td colspan="5" class="observaciones">
                                <strong>OBSERVACIONES:</strong> {{ $registro->observaciones }}
                            </td>
                        </tr>
                    @endif
                @endforeach
            </tbody>
        </table>
        
       
    @else
        <div class="no-data">
            <h3>SIN REGISTROS</h3>
            <p>No se encontraron registros para el período seleccionado.</p>
            <p><strong>Equipo:</strong> {{ $equipo->nombre }} ({{ $equipo->tag }})</p>
            <p><strong>Período:</strong> {{ \Carbon\Carbon::parse($fecha_inicio)->format('d/m/Y') }} al {{ \Carbon\Carbon::parse($fecha_fin)->format('d/m/Y') }}</p>
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