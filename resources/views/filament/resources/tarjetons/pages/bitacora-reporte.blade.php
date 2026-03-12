<x-filament-panels::page>
    <div class="space-y-6">
        {{-- Formulario de filtros --}}
        <x-filament::card>
            <div class="space-y-6">
                <div>
                    <h2 class="text-xl font-semibold text-gray-900 dark:text-white">
                        Generar Bitácora de Operación
                    </h2>
                    <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                        Selecciona el equipo y el rango de fechas para generar la bitácora.
                    </p>
                </div>

                <form wire:submit="generarReporte">
                    {{ $this->form }}

                    <div class="flex gap-3 mt-6">
                        @foreach ($this->getFormActions() as $action)
                            {{ $action }}
                        @endforeach
                    </div>
                </form>
            </div>
        </x-filament::card>

        {{-- Vista previa del reporte --}}
        @if($mostrarReporte && $equipo)
            <x-filament::card>
                {{-- Encabezado del reporte --}}
                <div class="text-center mb-6 border-b border-gray-200 dark:border-gray-700 pb-4">
                    <h1 class="text-2xl font-bold text-gray-900 dark:text-white mb-2">
                        Bitácora de Operación del {{ $equipo->nombre }}
                    </h1>
                    <p class="text-gray-600 dark:text-gray-400">
                        Período: {{ \Carbon\Carbon::parse($data['fecha_inicio'])->locale('es')->translatedFormat('d/m/Y') }}
                        al {{ \Carbon\Carbon::parse($data['fecha_fin'])->locale('es')->translatedFormat('d/m/Y') }}
                    </p>
                    <p class="text-sm text-gray-500 dark:text-gray-400 mt-2">
                        No. De Control: {{ $equipo->numeroControl ?? 'N/A' }} | TAG: {{ $equipo->tag }}
                    </p>
                </div>

                {{-- Tabla de registros agrupada por día --}}
                @if($registros->isNotEmpty())
                    @php
                        $registrosPorDia = $registros->groupBy(
                            fn ($r) => $r->hora_encendido?->format('Y-m-d') ?? 'sin-fecha'
                        );
                    @endphp
                    <div class="overflow-x-auto">
                        <table class="min-w-full border-collapse border border-gray-300 dark:border-gray-600">
                            <thead class="bg-gray-50 dark:bg-gray-700">
                                <tr>
                                    <th class="border border-gray-300 dark:border-gray-600 px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                        Fecha
                                    </th>
                                    <th class="border border-gray-300 dark:border-gray-600 px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                        Operación
                                    </th>
                                    <th class="border border-gray-300 dark:border-gray-600 px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                        Responsable
                                    </th>
                                    <th class="border border-gray-300 dark:border-gray-600 px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                        Hora
                                    </th>
                                    <th class="border border-gray-300 dark:border-gray-600 px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                        Tiempo
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-600">
                                @foreach($registrosPorDia as $fechaKey => $turnosDia)
                                    @php
                                        $totalFilasDia = $turnosDia->count() * 2
                                            + $turnosDia->filter(fn ($r) => $r->observaciones)->count()
                                            + $turnosDia->filter(fn ($r) => $r->falla_vapor)->count();
                                        $tiempoDia = $turnosDia->sum('tiempo_operacion_minutos');
                                        $hDia = intdiv($tiempoDia, 60);
                                        $mDia = $tiempoDia % 60;
                                        $fechaCarbon = \Carbon\Carbon::parse($fechaKey);
                                    @endphp

                                    @foreach($turnosDia as $i => $registro)
                                        {{-- Row: Encendido --}}
                                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-700">
                                            @if($i === 0)
                                                <td rowspan="{{ $totalFilasDia }}"
                                                    class="border border-gray-300 dark:border-gray-600 px-4 py-3 text-sm text-gray-900 dark:text-white font-medium align-top bg-gray-50 dark:bg-gray-900/30">
                                                    <div class="font-bold">{{ $fechaCarbon->locale('es')->translatedFormat('d M Y') }}</div>
                                                    <div class="text-xs text-gray-500 dark:text-gray-400 capitalize">{{ $fechaCarbon->locale('es')->translatedFormat('l') }}</div>
                                                    @if($turnosDia->count() > 1)
                                                        <div class="mt-2">
                                                            <x-filament::badge color="gray" size="sm">
                                                                {{ $turnosDia->count() }} turnos
                                                            </x-filament::badge>
                                                        </div>
                                                    @endif
                                                    <div class="mt-1 text-xs font-semibold text-purple-600 dark:text-purple-400">
                                                        Total: {{ $hDia }}h {{ $mDia }}m
                                                    </div>
                                                </td>
                                            @endif
                                            <td class="border border-gray-300 dark:border-gray-600 px-4 py-2 text-sm bg-green-50 dark:bg-green-900/20">
                                                <x-filament::badge color="success" size="sm">Encendido</x-filament::badge>
                                            </td>
                                            <td class="border border-gray-300 dark:border-gray-600 px-4 py-2 text-sm text-gray-900 dark:text-white">
                                                {{ $registro->encendido_por ?? 'N/A' }}
                                            </td>
                                            <td class="border border-gray-300 dark:border-gray-600 px-4 py-2 text-sm text-gray-900 dark:text-white font-mono">
                                                {{ $registro->hora_encendido?->format('H:i') ?? 'N/A' }}
                                                @if($registro->hora_encendido)
                                                    <span class="text-xs text-gray-400">{{ $registro->hora_encendido->format('d/m') }}</span>
                                                @endif
                                            </td>
                                            <td rowspan="2" class="border border-gray-300 dark:border-gray-600 px-4 py-3 text-sm font-medium text-center align-middle">
                                                <x-filament::badge color="info">
                                                    {{ $registro->tiempo_operacion_formateado }}
                                                </x-filament::badge>
                                            </td>
                                        </tr>
                                        {{-- Row: Apagado --}}
                                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-700">
                                            <td class="border border-gray-300 dark:border-gray-600 px-4 py-2 text-sm bg-red-50 dark:bg-red-900/20">
                                                <x-filament::badge color="danger" size="sm">Apagado</x-filament::badge>
                                            </td>
                                            <td class="border border-gray-300 dark:border-gray-600 px-4 py-2 text-sm text-gray-900 dark:text-white">
                                                {{ $registro->apagado_por ?? 'N/A' }}
                                            </td>
                                            <td class="border border-gray-300 dark:border-gray-600 px-4 py-2 text-sm text-gray-900 dark:text-white font-mono">
                                                {{ $registro->hora_apagado?->format('H:i') ?? 'N/A' }}
                                                @if($registro->hora_apagado)
                                                    <span class="text-xs {{ $registro->hora_apagado->toDateString() !== $registro->hora_encendido?->toDateString() ? 'text-amber-500 dark:text-amber-400 font-semibold' : 'text-gray-400' }}">
                                                        {{ $registro->hora_apagado->format('d/m') }}
                                                    </span>
                                                @endif
                                            </td>
                                        </tr>
                                        @if($registro->observaciones)
                                            <tr>
                                                <td colspan="4"
                                                    class="border border-gray-300 dark:border-gray-600 px-4 py-2 text-sm text-gray-600 dark:text-gray-400 italic bg-gray-50 dark:bg-gray-800">
                                                    <div class="flex items-start gap-2">
                                                        <x-heroicon-o-chat-bubble-left-ellipsis class="w-4 h-4 mt-0.5 flex-shrink-0" />
                                                        <div><strong>Obs:</strong> {{ $registro->observaciones }}</div>
                                                    </div>
                                                </td>
                                            </tr>
                                        @endif
                                        @if($registro->falla_vapor)
                                            <tr>
                                                <td colspan="4"
                                                    class="border border-gray-300 dark:border-gray-600 px-4 py-2 text-sm text-red-700 dark:text-red-400 bg-red-50 dark:bg-red-900/20">
                                                    <div class="flex items-start gap-2">
                                                        <x-heroicon-s-fire class="w-4 h-4 mt-0.5 flex-shrink-0 text-red-500" />
                                                        <div>
                                                            <strong>Falla de vapor:</strong>
                                                            {{ $registro->falla_vapor_descripcion ?? 'Sin descripción' }}
                                                        </div>
                                                    </div>
                                                </td>
                                            </tr>
                                        @endif
                                    @endforeach
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    {{-- Resumen estadístico --}}
                    @php
                        $todasLasFechas = $registros->flatMap(function ($r) {
                            $fechas = [];
                            if ($r->hora_encendido) $fechas[] = $r->hora_encendido->toDateString();
                            if ($r->hora_apagado) $fechas[] = $r->hora_apagado->toDateString();
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
                    <div class="mt-6 grid grid-cols-1 md:grid-cols-5 gap-4 pt-6 border-t border-gray-200 dark:border-gray-700">
                        <x-filament::card>
                            <div class="text-center">
                                <div class="text-2xl font-bold text-blue-600 dark:text-blue-400">
                                    {{ $diasUnicos }}
                                </div>
                                <div class="text-sm text-gray-500 dark:text-gray-400">
                                    Días con Operación
                                </div>
                            </div>
                        </x-filament::card>

                        <x-filament::card>
                            <div class="text-center">
                                <div class="text-2xl font-bold text-green-600 dark:text-green-400">
                                    {{ $completados }} / {{ $totalTurnos }}
                                </div>
                                <div class="text-sm text-gray-500 dark:text-gray-400">
                                    Turnos Completos
                                </div>
                            </div>
                        </x-filament::card>

                        <x-filament::card>
                            <div class="text-center">
                                <div class="text-2xl font-bold text-purple-600 dark:text-purple-400">
                                    {{ $horasTotal }}h {{ $minutosTotal }}m
                                </div>
                                <div class="text-sm text-gray-500 dark:text-gray-400">
                                    Tiempo Total
                                </div>
                            </div>
                        </x-filament::card>

                        <x-filament::card>
                            <div class="text-center">
                                <div class="text-2xl font-bold text-orange-600 dark:text-orange-400">
                                    {{ $horasPromedio }}h {{ $minutosPromedio }}m
                                </div>
                                <div class="text-sm text-gray-500 dark:text-gray-400">
                                    Promedio por Día
                                </div>
                            </div>
                        </x-filament::card>

                        <x-filament::card>
                            <div class="text-center">
                                <div class="text-2xl font-bold {{ $fallasCount > 0 ? 'text-red-600 dark:text-red-400' : 'text-gray-400 dark:text-gray-500' }}">
                                    {{ $fallasCount }}
                                </div>
                                <div class="text-sm text-gray-500 dark:text-gray-400">
                                    Fallas de Vapor
                                </div>
                            </div>
                        </x-filament::card>
                    </div>
                @else
                    <div class="text-center py-12">
                        <x-heroicon-o-document-text class="w-16 h-16 text-gray-400 mx-auto mb-4" />
                        <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-2">
                            Sin registros
                        </h3>
                        <p class="text-gray-500 dark:text-gray-400">
                            No hay registros para mostrar en el rango de fechas seleccionado.
                        </p>
                    </div>
                @endif
            </x-filament::card>
        @endif

    </div>
</x-filament-panels::page>