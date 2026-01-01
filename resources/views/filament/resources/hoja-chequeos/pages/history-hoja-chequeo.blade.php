@php
    use Carbon\Carbon;

    $dates = $this->getDateRange();
    $turnos = $this->getTurnos();
    $ejecucionesByDateAndTurno = $this->getEjecucionesByDateAndTurno();
    $shiftColors = $this->getShiftColors();
    $filaAggregates = $this->getFilaAggregates(); // SQL aggregation instead of runtime calculation

    $filas = $record->filas()->with('answerType')->get();
    $columnas = $record->columnas()->get();
@endphp

<x-filament-panels::page>
    <div class="flex items-center gap-4 mb-6">
        <x-filament::badge color="warning">Version: {{ $record->version }}</x-filament::badge>
        <x-filament::badge>Area: {{ $record->equipo->area }}</x-filament::badge>
        <x-filament::badge>Tag: {{ $record->equipo->tag }}</x-filament::badge>
        <x-filament::badge>Equipo: {{ $record->equipo->nombre }}</x-filament::badge>
    </div>

    <div class="bg-white dark:bg-gray-900 p-4 rounded-lg flex flex-col gap-6 w-full">
        <form wire:submit.prevent="$refresh">
            {{ $this->form }}
        </form>

        {{-- Tabs Navigation --}}
        <div class="border-b border-gray-200 dark:border-gray-700">
            <nav class="-mb-px flex gap-6" aria-label="Tabs">
                @foreach($turnos as $turno)
                    <button
                        wire:click="$set('activeTab', '{{ $turno->id }}')"
                        type="button"
                        class="whitespace-nowrap border-b-2 px-1 py-4 text-sm font-medium transition-colors
                            {{ $activeTab == $turno->id
                                ? 'border-' . $shiftColors[$turno->id] . '-500 text-' . $shiftColors[$turno->id] . '-600 dark:text-' . $shiftColors[$turno->id] . '-400'
                                : 'border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300'
                            }}"
                    >
                        <span class="flex items-center gap-2">
                            <span class="w-3 h-3 rounded-full bg-{{ $shiftColors[$turno->id] }}-500"></span>
                            {{ $turno->nombre }}
                        </span>
                    </button>
                @endforeach

                <button
                    wire:click="$set('activeTab', 'compare')"
                    type="button"
                    class="whitespace-nowrap border-b-2 px-1 py-4 text-sm font-medium transition-colors
                        {{ $activeTab == 'compare'
                            ? 'border-gray-900 text-gray-900 dark:border-gray-100 dark:text-gray-100'
                            : 'border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300'
                        }}"
                >
                    <span class="flex items-center gap-2">
                        @svg('heroicon-o-chart-bar', 'w-4 h-4')
                        Todos
                    </span>
                </button>
            </nav>
        </div>

        {{-- Table Container --}}
        <div class="bg-white dark:bg-gray-900 rounded-lg overflow-hidden">
            <div class="overflow-x-auto relative">
                <table class="min-w-full border-collapse border border-gray-200 dark:border-gray-700 text-sm">
                    <thead class="bg-gray-50 dark:bg-gray-800">
                    <tr>
                        {{-- Left Headers --}}
                        <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider border-r border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800">
                            #
                        </th>
                        <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider border-r border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800">
                            Categoría
                        </th>
                        @foreach($columnas as $columna)
                            <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider border-r border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800 min-w-30">
                                {{ $columna->label }}
                            </th>
                        @endforeach

                        {{-- Date Columns --}}
                        @if($activeTab == 'compare')
                            {{-- Compare Mode: Group by Date, then by Shift --}}
                            @foreach($dates as $date)
                                @foreach($turnos as $turno)
                                    <th class="px-3 py-2 text-center text-xs font-medium uppercase tracking-wider border-r   border-gray-200 dark:border-gray-700 bg-{{ $shiftColors[$turno->id] }}-50 dark:bg-{{ $shiftColors[$turno->id] }}-900/20 min-w-25">
                                        <div class="font-semibold text-gray-900 dark:text-gray-100">
                                            {{ Carbon::parse($date)->format('d/m') }}
                                        </div>
                                        <div class="text-xs mt-1">
                                            {{ $turno->nombre }}
                                        </div>
                                    </th>
                                @endforeach
                            @endforeach
                        @else
                            {{-- Single Shift Mode --}}
                            @foreach($dates as $date)
                                @php
                                    $turnoColor = $shiftColors[$activeTab] ?? 'gray';
                                @endphp
                                <th class="px-3 py-2 text-center text-xs font-medium uppercase tracking-wider  border-r border-gray-200 dark:border-gray-700 bg-{{ $turnoColor }}-50 dark:bg-{{ $turnoColor }}-900/20 min-w-25">
                                    <div class="font-semibold text-gray-900 dark:text-gray-100">
                                        {{ Carbon::parse($date)->format('D') }}
                                    </div>
                                    <div class="text-xs mt-1">
                                        {{ Carbon::parse($date)->format('d/m/Y') }}
                                    </div>
                                </th>
                            @endforeach
                        @endif

                        {{-- Fixed Aggregate Columns --}}
                        <th class="px-3 py-2 text-center text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider border-l border-r border-gray-200 dark:border-gray-700 bg-amber-50 dark:bg-amber-900/20 ">
                            Suma
                        </th>
                        <th class="px-3 py-2 text-center text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider border-r border-gray-200 dark:border-gray-700 bg-amber-50 dark:bg-amber-900/20 ">
                            Promedio
                        </th>
                    </tr>
                    </thead>

                    <tbody class="bg-white dark:bg-gray-900 divide-y divide-gray-200 dark:divide-gray-700">
                    {{-- Data Rows --}}
                    @foreach($filas as $filaIndex => $fila)
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/50">
                            {{-- Left Cells --}}
                            <td class="px-3 py-2 text-xs font-medium text-gray-500 dark:text-gray-400 border-r border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900">
                                {{ $filaIndex + 1 }}
                            </td>
                            <td class="px-3 py-2 border-r border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900">
                                <span @class([
    'capitalize inline-flex items-center px-2 py-1 rounded-full text-xs font-medium',
    'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400' => $fila->categoria === 'limpieza',
    'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400' => $fila->categoria === 'operacion',
    'bg-yellow-100 text-yellow-700 dark:bg-yellow-900/30 dark:text-yellow-400' => $fila->categoria === 'revision',
    'bg-gray-100 text-gray-700 dark:bg-gray-800 dark:text-gray-400' => ! in_array($fila->categoria, ['limpieza', 'operacion', 'revision']),
])>
    {{ $fila->categoria }}
</span>
                            </td>

                            {{-- Columna Values (from HojaFilaValor) --}}
                            @foreach($columnas as $columna)
                                @php
                                    $valor = $fila->valores->where('hoja_columna_id', $columna->id)->first();
                                @endphp
                                <td class="px-3 py-2 text-xs border-r border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800/50">
                                    {{ $valor?->valor ?? '' }}
                                </td>
                            @endforeach

                            {{-- Date Columns with Respuestas --}}
                            @if($activeTab == 'compare')
                                {{-- Compare Mode --}}
                                @foreach($dates as $date)
                                    @foreach($turnos as $turno)
                                        @php
                                            $ejecucion = $ejecucionesByDateAndTurno[$date][$turno->id] ?? null;
                                            $respuesta = $ejecucion?->respuestas->where('hoja_fila_id', $fila->id)->first();
                                            $turnoColor = $shiftColors[$turno->id];
                                        @endphp
                                        <td class="px-3 py-2 text-center text-xs border-l border-r border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900">
                                            @if($respuesta)
                                                @if($respuesta->answer_option_id)
                                                    @php
                                                        $iconColor = $respuesta->answerOption->color ?? 'gray';
                                                    @endphp
                                                    @svg($respuesta->answerOption->icon ?? 'heroicon-o-question-mark-circle', 'w-5 h-5 mx-auto text-' . $iconColor . '-600 dark:text-' . $iconColor . '-400')
                                                @elseif($respuesta->numeric_value !== null)
                                                    <span class="font-medium">{{ $respuesta->numeric_value }}</span>
                                                @elseif($respuesta->text_value)
                                                    <span class="truncate block max-w-25"
                                                          title="{{ $respuesta->text_value }}">
                                                        {{ $respuesta->text_value }}
                                                    </span>
                                                @elseif($respuesta->boolean_value !== null)
                                                    <span class="truncate block max-w-25">
                                                        {{$respuesta->boolean_value ? "Si": "No"}}
                                                    </span>
                                                @endif
                                            @endif
                                        </td>
                                    @endforeach
                                @endforeach
                            @else
                                {{-- Single Shift Mode --}}
                                @foreach($dates as $date)
                                    @php
                                        $ejecucion = $ejecucionesByDateAndTurno[$date][$activeTab] ?? null;
                                        $respuesta = $ejecucion?->respuestas->where('hoja_fila_id', $fila->id)->first();
                                        $turnoColor = $shiftColors[$activeTab] ?? 'gray';
                                    @endphp
                                    <td class="px-3 py-2 text-center text-xs border-l border-r border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900">
                                        @if($respuesta)
                                            @if($respuesta->answer_option_id)
                                                @php
                                                    $iconColor = $respuesta->answerOption->color ?? 'gray';
                                                @endphp
                                                @svg($respuesta->answerOption->icon ?? 'heroicon-o-question-mark-circle', 'w-5 h-5 mx-auto text-' . $iconColor . '-600 dark:text-' . $iconColor . '-400')
                                            @elseif($respuesta->numeric_value !== null)
                                                <span class="font-medium">{{ $respuesta->numeric_value }}</span>
                                            @elseif($respuesta->text_value)
                                                <span class="truncate block max-w-25"
                                                      title="{{ $respuesta->text_value }}">
                                                    {{ $respuesta->text_value }}
                                                </span>
                                            @elseif($respuesta->boolean_value !== null)
                                                <span class="truncate block max-w-25">
                                                    {{$respuesta->boolean_value ? "Si": "No"}}
                                                </span>
                                            @endif
                                        @endif
                                    </td>
                                @endforeach
                            @endif

                            {{-- Aggregate Columns (from SQL aggregation) --}}
                            @php
                                $aggregate = $filaAggregates[$fila->id] ?? null;
                            @endphp
                            <td class="px-3 py-2 text-center text-xs border-l border-r border-gray-200 dark:border-gray-700 bg-amber-50 dark:bg-amber-900/20 font-semibold ">
                                @if($aggregate && isset($aggregate['suma']))
                                    {{ number_format($aggregate['suma'], 2) }}
                                @endif
                            </td>
                            <td class="px-3 py-2 text-center text-xs border-r border-gray-200 dark:border-gray-700 bg-amber-50 dark:bg-amber-900/20 font-semibold ">
                                @if($aggregate && isset($aggregate['promedio']))
                                    {{ number_format($aggregate['promedio'], 2) }}
                                @endif
                            </td>
                        </tr>
                    @endforeach

                    {{-- Operator Name Row --}}
                    <tr class="border-t border-gray-200 dark:border-gray-700">
                        <td colspan="{{ 2 + $columnas->count() }}"
                            class="px-3 py-3 text-xs text-end font-semibold text-gray-900 dark:text-gray-100 border-r border-gray-200 dark:border-gray-700">
                            Nombre del Operador
                        </td>
                        @if($activeTab == 'compare')
                            @foreach($dates as $date)
                                @foreach($turnos as $turno)
                                    @php
                                        $ejecucion = $ejecucionesByDateAndTurno[$date][$turno->id] ?? null;
                                    @endphp
                                    <td class="px-3 py-3 text-center text-xs border-l border-r border-gray-200 dark:border-gray-700">
                                        {{ $ejecucion?->nombre_operador ?? '' }}
                                    </td>
                                @endforeach
                            @endforeach
                        @else
                            @foreach($dates as $date)
                                @php
                                    $ejecucion = $ejecucionesByDateAndTurno[$date][$activeTab] ?? null;
                                @endphp
                                <td class="px-3 py-3 text-center text-xs border-l border-r border-gray-200 dark:border-gray-700">
                                    {{ $ejecucion?->nombre_operador ?? '' }}
                                </td>
                            @endforeach
                        @endif
                        {{-- Empty cells for aggregate columns --}}
                        <td class="px-3 py-3 border-l border-r border-gray-200 dark:border-gray-700 bg-amber-50 dark:bg-amber-900/20 "></td>
                        <td class="px-3 py-3 border-r border-gray-200 dark:border-gray-700 bg-amber-50 dark:bg-amber-900/20 "></td>
                    </tr>

                    {{-- Operator Signature Row --}}
                    <tr class="border-t border-gray-200 dark:border-gray-700">
                        <td colspan="{{ 2 + $columnas->count() }}"
                            class="px-3 py-3 text-xs text-end font-semibold text-gray-900 dark:text-gray-100 border-r border-gray-200 dark:border-gray-700">
                            Firma del Operador
                        </td>
                        @if($activeTab == 'compare')
                            @foreach($dates as $date)
                                @foreach($turnos as $turno)
                                    @php
                                        $ejecucion = $ejecucionesByDateAndTurno[$date][$turno->id] ?? null;
                                    @endphp
                                    <td class="px-3 py-3 text-center border-l border-r border-gray-200 dark:border-gray-700">
                                        @if($ejecucion?->firma_operador)
                                            <img src="{{ asset('storage/' . $ejecucion->firma_operador) }}"
                                                 alt="Firma Operador"
                                                 class="h-12 mx-auto">
                                        @endif
                                    </td>
                                @endforeach
                            @endforeach
                        @else
                            @foreach($dates as $date)
                                @php
                                    $ejecucion = $ejecucionesByDateAndTurno[$date][$activeTab] ?? null;
                                @endphp
                                <td class="px-3 py-3 text-center border-l border-r border-gray-200 dark:border-gray-700">
                                    @if($ejecucion?->firma_operador)
                                        <img src="{{ asset('storage/' . $ejecucion->firma_operador) }}"
                                             alt="Firma Operador"
                                             class="h-12 mx-auto">
                                    @endif
                                </td>
                            @endforeach
                        @endif
                        {{-- Empty cells for aggregate columns --}}
                        <td class="px-3 py-3 border-l border-r border-gray-200 dark:border-gray-700 bg-amber-50 dark:bg-amber-900/20 "></td>
                        <td class="px-3 py-3 border-r border-gray-200 dark:border-gray-700 bg-amber-50 dark:bg-amber-900/20 "></td>
                    </tr>

                    {{-- Supervisor Signature Row --}}
                    <tr class="border-t border-gray-200 dark:border-gray-700">
                        <td colspan="{{ 2 + $columnas->count() }}"
                            class="px-3 py-3 text-xs text-end font-semibold text-gray-900 dark:text-gray-100 border-r border-gray-200 dark:border-gray-700">
                            Firma del Supervisor
                        </td>
                        @if($activeTab == 'compare')
                            @foreach($dates as $date)
                                @foreach($turnos as $turno)
                                    @php
                                        $ejecucion = $ejecucionesByDateAndTurno[$date][$turno->id] ?? null;
                                    @endphp
                                    <td class="px-3 py-3 text-center border-l border-r border-gray-200 dark:border-gray-700">
                                        @if($ejecucion?->firma_supervisor)
                                            <img src="{{ asset('storage/' . $ejecucion->firma_supervisor) }}"
                                                 alt="Firma Supervisor"
                                                 class="h-12 mx-auto">
                                        @endif
                                    </td>
                                @endforeach
                            @endforeach
                        @else
                            @foreach($dates as $date)
                                @php
                                    $ejecucion = $ejecucionesByDateAndTurno[$date][$activeTab] ?? null;
                                @endphp
                                <td class="px-3 py-3 text-center border-l border-r border-gray-200 dark:border-gray-700">
                                    @if($ejecucion?->firma_supervisor)
                                        <img src="{{ asset('storage/' . $ejecucion->firma_supervisor) }}"
                                             alt="Firma Supervisor"
                                             class="h-12 mx-auto">
                                    @endif
                                </td>
                            @endforeach
                        @endif
                        {{-- Empty cells for aggregate columns --}}
                        <td class="px-3 py-3 border-l border-r border-gray-200 dark:border-gray-700 bg-amber-50 dark:bg-amber-900/20 "></td>
                        <td class="px-3 py-3 border-r border-gray-200 dark:border-gray-700 bg-amber-50 dark:bg-amber-900/20 "></td>
                    </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <x-filament-actions::modals/>

</x-filament-panels::page>
