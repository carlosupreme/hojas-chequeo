<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Check Sheet History</title>
    <style>
        @page {
            size: A4 landscape;
            margin: 1cm;
        }

        body {
            font-family: Arial, sans-serif;
            font-size: 8px;
            line-height: 1.3;
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
            padding: 3px 4px;
            text-align: left;
            vertical-align: middle;
        }

        .date-column {
            width: 22px;
            text-align: center;
            padding: 3px 1px;
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
    </style>
</head>
<body>
@foreach($chunks as $index => $chunk)
    <div @if(!$loop->last) class="page-break" @endif>
        <table>
            <tr>
                <td colspan="7" class="header">{{ $record->equipo->nombre }}</td>
                <td colspan="8" class="text-right">Página {{ $index + 1 }}</td>
            </tr>
            <tr>
                <td colspan="5" class="subheader">HOJA DE CHEQUEO DE EQUIPO</td>
                <td colspan="5" class="subheader">AREA: {{ $record->equipo->area  }}</td>
                <td colspan="5" class="subheader">TAG: {{ $record->equipo->tag  }}</td>
            </tr>
        </table>

        <table>
            <thead>
            <tr>
                @foreach($headers as $header)
                    <th class="header-column">{{ $header }}</th>
                @endforeach
                @foreach($chunk['dates'] as $day)
                    <th class="date-column">{{ \Carbon\Carbon::parse($day)->format('d/m') }}</th>
                @endforeach
            </tr>
            </thead>
            <tbody>
            @foreach($tableData['items'] as $index => $item)
                <tr>
                    @foreach($headers as $header)
                        <td>{{ $item[$header] }}</td>
                    @endforeach

                    @foreach($chunk['dates'] as $day)
                        <td class="date-column">
                            @if(isset($tableData['checks'][$day][$index]))
                                @if($tableData['checks'][$day][$index]['icon'])
                                    <x-dynamic-component
                                            :component="$tableData['checks'][$day][$index]['icon']"
                                            class="w-5 h-5"
                                            style="color: {{ $tableData['checks'][$day][$index]['color'] }}"
                                    />
                                @else
                                    <span class="text-sm"> {{$tableData['checks'][$day][$index]['text']}}</span>
                                @endif
                            @else
                                <span class="empty-check"></span>
                            @endif
                        </td>
                    @endforeach
                </tr>
            @endforeach

            <!-- Operator Name Row -->
            <tr>
                <td colspan="{{ count($headers) }}" class="text-right">NOMBRE DE OPERADOR:</td>
                @foreach($chunk['dates'] as $day)
                    <td class="date-column">
                        {{ $tableData['operatorNames'][$day] ?? '' }}
                    </td>
                @endforeach
            </tr>

            <!-- Operator Signature Row -->
            <tr>
                <td colspan="{{ count($headers) }}" class="text-right">FIRMA DEL OPERADOR</td>
                @foreach($chunk['dates'] as $day)
                    <td class="date-column">
                        @if(isset($tableData['operatorSignatures'][$day]))
                            <img src="{{ $tableData['operatorSignatures'][$day] }}"
                                 alt="Firma del Operador"
                                 class="signature-img">
                        @else
                            <span class="empty-check"></span>
                        @endif
                    </td>
                @endforeach
            </tr>

            <!-- Supervisor Signature Row -->
            <tr>
                <td colspan="{{ count($headers) }}" class="text-right">FIRMA DEL SUPERVISOR</td>
                @foreach($chunk['dates'] as $day)
                    <td class="date-column">
                        @if(isset($tableData['supervisorSignatures'][$day]))
                            <img src="{{ $tableData['supervisorSignatures'][$day] }}"
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

