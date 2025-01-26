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

        .icon {
            width: 1.5rem;
            height: 1.5rem;
        }

        svg {
            -dompdf-font-variant: normal; /* For Dompdf */
            overflow: visible !important;
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
                <td colspan="5" class="subheader">Hoja de chequeo</td>
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
                                @if(isset($tableData['checks'][$day][$index]['icon']))
                                    @php
                                        $icon = $tableData['checks'][$day][$index]['icon'];
                                        $color = $tableData['checks'][$day][$index]['color'] ?? '#000000';
                                    @endphp

                                    @switch($icon)
                                        {{-- Check Icon --}}
                                        @case('heroicon-c-check')
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

                                            {{-- Exclamation Triangle --}}
                                        @case('heroicon-o-exclamation-triangle')
                                            <img src="data:image/svg+xml;base64,{!! base64_encode('
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="'.$color.'">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z" />
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

                                            {{-- Eye Icon --}}
                                        @case('heroicon-o-eye')
                                            <img src="data:image/svg+xml;base64,{!! base64_encode('
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="'.$color.'">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z" />
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                </svg>
            ') !!}" width="12" height="12" style="vertical-align: middle;">
                                            @break

                                            {{-- Clock Icon --}}
                                        @case('heroicon-o-clock')
                                            <img src="data:image/svg+xml;base64,{!! base64_encode('
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="'.$color.'">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            ') !!}" width="12" height="12" style="vertical-align: middle;">
                                            @break

                                            {{-- Shield Exclamation --}}
                                        @case('heroicon-o-shield-exclamation')
                                            <img src="data:image/svg+xml;base64,{!! base64_encode('
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="'.$color.'">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m0-10.036A11.959 11.959 0 013.598 6 11.99 11.99 0 003 9.75c0 5.592 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.57-.598-3.75h-.152c-3.196 0-6.1-1.249-8.25-3.286zm0 13.036h.008v.008H12v-.008z" />
                </svg>
            ') !!}" width="12" height="12" style="vertical-align: middle;">
                                            @break

                                            {{-- Wrench Icon --}}
                                        @case('heroicon-o-wrench')
                                            <img src="data:image/svg+xml;base64,{!! base64_encode('
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="'.$color.'">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75a4.5 4.5 0 01-4.884 4.484c-1.076-.091-2.264.071-2.95.904l-7.152 8.684a2.548 2.548 0 11-3.586-3.586l8.684-7.152c.833-.686.995-1.874.904-2.95a4.5 4.5 0 016.336-4.486l-3.276 3.276a3.004 3.004 0 002.25 2.25l3.276-3.276c.256.565.398 1.192.398 1.852z" />
                    <path stroke-linecap="round" stroke-linejoin="round" d="M4.867 19.125h.008v.008h-.008v-.008z" />
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

