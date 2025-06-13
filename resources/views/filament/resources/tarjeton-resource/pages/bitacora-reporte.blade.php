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
                        Período: Del {{ \Carbon\Carbon::parse($data['fecha_inicio'])->format('d') }} 
                        al {{ \Carbon\Carbon::parse($data['fecha_fin'])->format('d') }} de {{ \Carbon\Carbon::parse($data['fecha_fin'])->locale('es')->translatedFormat('F Y') }}
                    </p>
                    <p class="text-sm text-gray-500 dark:text-gray-400 mt-2">
                        No. De Control: {{ $equipo->numeroControl ?? 'N/A' }} | TAG: {{ $equipo->tag }}
                    </p>
                </div>

                {{-- Tabla de registros --}}
                @if($registros->isNotEmpty())
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
                                        Tiempo Total
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-600">
                                @foreach($registros as $registro)
                                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-700">
                                        <td rowspan="2" class="border border-gray-300 dark:border-gray-600 px-4 py-3 text-sm text-gray-900 dark:text-white font-medium align-center">
                                            {{ $registro->fecha->locale('es')->translatedFormat('d F Y') }}
                                        </td>
                                        <td class="border border-gray-300 dark:border-gray-600 px-4 py-2 text-sm text-gray-900 dark:text-white bg-green-50 dark:bg-green-900/20">
                                            <x-filament::badge color="success" size="sm">
                                                Encendido
                                            </x-filament::badge>
                                        </td>
                                        <td class="border border-gray-300 dark:border-gray-600 px-4 py-2 text-sm text-gray-900 dark:text-white">
                                            {{ $registro->encendido_por ?? 'N/A' }}
                                        </td>
                                        <td class="border border-gray-300 dark:border-gray-600 px-4 py-2 text-sm text-gray-900 dark:text-white font-mono">
                                            {{ $registro->hora_encendido ?? 'N/A' }}
                                        </td>
                                        <td rowspan="2" class="border border-gray-300 dark:border-gray-600 px-4 py-3 text-sm text-gray-900 dark:text-white font-medium text-center align-middle">
                                            <x-filament::badge color="info">
                                                {{ $registro->tiempo_operacion_formateado }}
                                            </x-filament::badge>
                                        </td>
                                    </tr>
                                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-700">
                                        <td class="border border-gray-300 dark:border-gray-600 px-4 py-2 text-sm text-gray-900 dark:text-white bg-red-50 dark:bg-red-900/20">
                                            <x-filament::badge color="danger" size="sm">
                                                Apagado
                                            </x-filament::badge>
                                        </td>
                                        <td class="border border-gray-300 dark:border-gray-600 px-4 py-2 text-sm text-gray-900 dark:text-white">
                                            {{ $registro->apagado_por ?? 'N/A' }}
                                        </td>
                                        <td class="border border-gray-300 dark:border-gray-600 px-4 py-2 text-sm text-gray-900 dark:text-white font-mono">
                                            {{ $registro->hora_apagado ?? 'N/A' }}
                                        </td>
                                    </tr>
                                    @if($registro->observaciones)
                                        <tr>
                                            <td colspan="5" class="border border-gray-300 dark:border-gray-600 px-4 py-2 text-sm text-gray-600 dark:text-gray-400 italic bg-gray-50 dark:bg-gray-800">
                                                <div class="flex items-start gap-2">
                                                    <x-heroicon-o-chat-bubble-left-ellipsis class="w-4 h-4 mt-0.5 flex-shrink-0" />
                                                    <div>
                                                        <strong>Observaciones:</strong> {{ $registro->observaciones }}
                                                    </div>
                                                </div>
                                            </td>
                                        </tr>
                                    @endif
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    {{-- Resumen estadístico --}}
                    <div class="mt-6 grid grid-cols-1 md:grid-cols-4 gap-4 pt-6 border-t border-gray-200 dark:border-gray-700">
                        <x-filament::card>
                            <div class="text-center">
                                <div class="text-2xl font-bold text-blue-600 dark:text-blue-400">
                                    {{ $registros->count() }}
                                </div>
                                <div class="text-sm text-gray-500 dark:text-gray-400">
                                    Días Registrados
                                </div>
                            </div>
                        </x-filament::card>

                        <x-filament::card>
                            <div class="text-center">
                                <div class="text-2xl font-bold text-green-600 dark:text-green-400">
                                    {{ $registros->whereNotNull('hora_encendido')->whereNotNull('hora_apagado')->count() }}
                                </div>
                                <div class="text-sm text-gray-500 dark:text-gray-400">
                                    Operaciones Completas
                                </div>
                            </div>
                        </x-filament::card>

                        <x-filament::card>
                            <div class="text-center">
                                @php
                                    $tiempoTotal = $registros->sum('tiempo_operacion_minutos');
                                    $horasTotal = intval($tiempoTotal / 60);
                                    $minutosTotal = $tiempoTotal % 60;
                                @endphp
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
                                @php
                                    $promedioDiario = $registros->count() > 0 ? $tiempoTotal / $registros->count() : 0;
                                    $horasPromedio = intval($promedioDiario / 60);
                                    $minutosPromedio = intval($promedioDiario % 60);
                                @endphp
                                <div class="text-2xl font-bold text-orange-600 dark:text-orange-400">
                                    {{ $horasPromedio }}h {{ $minutosPromedio }}m
                                </div>
                                <div class="text-sm text-gray-500 dark:text-gray-400">
                                    Promedio Diario
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