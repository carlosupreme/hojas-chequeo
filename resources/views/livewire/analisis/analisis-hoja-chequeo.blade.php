<div class="space-y-6">

    {{-- FILTERS --}}
    <div class="bg-white dark:bg-gray-900 p-6 rounded-xl shadow-sm border border-gray-200 dark:border-gray-800">
        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Filtros</h3>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            {{-- HojaChequeo Filter --}}
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Hoja de Chequeo</label>
            <select
                wire:model.live="hojaChequeoId"
                class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-800 dark:text-white"
            >
                <option value="">Todas las hojas</option>
                @foreach($this->hojaChequeos as $id => $nombre)
                    <option value="{{ $id }}">{{ $nombre }}</option>
                @endforeach
            </select>

        </div>
    </div>

    {{-- CHARTS ROW --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

        {{-- Chart: % Completion by Turno --}}
        <div class="bg-white dark:bg-gray-900 p-6 rounded-xl shadow-sm border border-gray-200 dark:border-gray-800">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">
                % de Ítems Realizados por Turno
                <span class="text-sm font-normal text-gray-500">(Marcados con = ✓)</span>
            </h3>
            <div
                x-data="turnoCompletionBarChart"
                x-init="initChart({
                    labels: @js($this->turnoCompletionStats['labels']),
                    data: @js($this->turnoCompletionStats['data'])
                })"
                @chart-data-updated.window="updateChart({
                    labels: @js($this->turnoCompletionStats['labels']),
                    data: @js($this->turnoCompletionStats['data'])
                })"
                class="h-72 w-full"
                wire:ignore
            ></div>
        </div>

        {{-- Chart: Total Ejecuciones by Turno --}}
        <div class="bg-white dark:bg-gray-900 p-6 rounded-xl shadow-sm border border-gray-200 dark:border-gray-800">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">
                Total de Chequeos por Turno
            </h3>
            <div
                x-data="turnoEjecucionChart"
                x-init="initChart({
                    labels: @js($this->turnoEjecucionCount['labels']),
                    data: @js($this->turnoEjecucionCount['data'])
                })"
                @chart-data-updated.window="updateChart({
                    labels: @js($this->turnoEjecucionCount['labels']),
                    data: @js($this->turnoEjecucionCount['data'])
                })"
                class="h-72 w-full"
                wire:ignore
            ></div>
        </div>
    </div>

    {{-- DETAILED STATS TABLE --}}
    <div class="bg-white dark:bg-gray-900 rounded-xl shadow-sm border border-gray-200 dark:border-gray-800">
        <div class="p-6 border-b border-gray-200 dark:border-gray-700">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Detalle por Turno</h3>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm text-left text-gray-500 dark:text-gray-400">
                <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-800 dark:text-gray-400">
                <tr>
                    <th scope="col" class="px-6 py-3">Turno</th>
                    <th scope="col" class="px-6 py-3 text-center">Chequeos</th>
                    <th scope="col" class="px-6 py-3 text-center">
                            <span class="inline-flex items-center text-green-600">
                                <x-heroicon-o-check class="w-4 h-4 mr-1"/> Realizados bien
                            </span>
                    </th>
                    <th scope="col" class="px-6 py-3 text-center">
                            <span class="inline-flex items-center text-red-600">
                                <x-heroicon-o-x-mark class="w-4 h-4 mr-1"/> Mal
                            </span>
                    </th>
                    <th scope="col" class="px-6 py-3 text-center">
                            <span class="inline-flex items-center text-yellow-600">
                                <x-heroicon-o-no-symbol class="w-4 h-4 mr-1"/> No Realizado
                            </span>
                    </th>
                    <th scope="col" class="px-6 py-3 text-center">
                            <span class="inline-flex items-center text-gray-500">
                                <x-heroicon-o-minus-circle class="w-4 h-4 mr-1"/> N/A
                            </span>
                    </th>
                    <th scope="col" class="px-6 py-3 text-center">% OK</th>
                </tr>
                </thead>
                <tbody>
                @forelse($this->turnoDetailedStats as $stat)
                    <tr class="bg-white border-b dark:bg-gray-900 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-800">
                        <td class="px-6 py-4 font-medium text-gray-900 dark:text-white">
                            {{ $stat['turno'] }}
                        </td>
                        <td class="px-6 py-4 text-center font-semibold">
                            {{ $stat['total_ejecuciones'] }}
                        </td>
                        <td class="px-6 py-4 text-center">
                            <span class="px-2 py-1 bg-green-100 text-green-800 rounded-full text-xs font-medium">
                                {{ $stat['realizados'] }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-center">
                            <span class="px-2 py-1 bg-red-100 text-red-800 rounded-full text-xs font-medium">
                                {{ $stat['realizados_mal'] }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-center">
                            <span class="px-2 py-1 bg-yellow-100 text-yellow-800 rounded-full text-xs font-medium">
                                {{ $stat['no_realizados'] }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-center">
                            <span class="px-2 py-1 bg-gray-100 text-gray-800 rounded-full text-xs font-medium">
                                {{ $stat['no_aplica'] }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-center">
                            @php
                                $pct = $stat['porcentaje_ok'];
                                $colorClass = $pct >= 80 ? 'text-green-600' : ($pct >= 50 ? 'text-yellow-600' : 'text-red-600');
                            @endphp
                            <span class="font-bold {{ $colorClass }}">
                                {{ $pct }}%
                            </span>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="px-6 py-8 text-center text-gray-500">
                            No hay datos para el período seleccionado.
                        </td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- CALDERAS SECTION --}}
    <div class="bg-white dark:bg-gray-900 rounded-xl shadow-sm border border-gray-200 dark:border-gray-800">
        <div class="p-6 border-b border-gray-200 dark:border-gray-700">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white flex items-center gap-2">
                <svg class="w-6 h-6 text-orange-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 18.657A8 8 0 016.343 7.343S7 9 9 10c0-2 .5-5 2.986-7C14 5 16.09 5.777 17.656 7.343A7.975 7.975 0 0120 13a7.975 7.975 0 01-2.343 5.657z"></path>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.879 16.121A3 3 0 1012.015 11L11 14H9c0 .768.293 1.536.879 2.121z"></path>
                </svg>
                Análisis de Calderas
            </h3>
        </div>

        <div class="p-6">
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                @foreach($this->calderasStats as $caldera)
                    <div class="space-y-4">
                        {{-- Caldera Header --}}
                        <div class="flex items-center justify-between">
                            <h4 class="text-lg font-bold text-gray-900 dark:text-white">
                                {{ $caldera['nombre'] }}
                                <span class="text-sm font-normal text-gray-500">({{ $caldera['tag'] }})</span>
                            </h4>
                            <span class="px-3 py-1 bg-orange-100 text-orange-800 dark:bg-orange-900/30 dark:text-orange-400 rounded-full text-sm font-medium">
                                {{ $caldera['tarjetones_count'] }} tarjetones
                            </span>
                        </div>

                        {{-- Stats Cards Grid --}}
                        <div class="grid grid-cols-2 gap-4">
                            {{-- TOTALES Card --}}
                            <div class="bg-gradient-to-br from-blue-50 to-blue-100 dark:from-blue-900/20 dark:to-blue-800/20 rounded-xl p-4 border border-blue-200 dark:border-blue-800">
                                <h5 class="text-sm font-semibold text-blue-800 dark:text-blue-400 mb-3 flex items-center gap-1">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                                    </svg>
                                    TOTALES
                                </h5>
                                <div class="space-y-2">
                                    <div class="flex justify-between items-center">
                                        <span class="text-xs text-gray-600 dark:text-gray-400">Horas Trabajo</span>
                                        <span class="font-bold text-gray-900 dark:text-white">{{ $caldera['totals']['horas_trabajo'] }} hrs</span>
                                    </div>
                                    <div class="flex justify-between items-center">
                                        <span class="text-xs text-gray-600 dark:text-gray-400">Efectividad Vapor</span>
                                        <span @class([
                                            'font-bold',
                                            'text-green-600' => $caldera['totals']['efectividad_vapor'] >= 90,
                                            'text-yellow-600' => $caldera['totals']['efectividad_vapor'] >= 70 && $caldera['totals']['efectividad_vapor'] < 90,
                                            'text-red-600' => $caldera['totals']['efectividad_vapor'] < 70,
                                        ])>{{ $caldera['totals']['efectividad_vapor'] }}%</span>
                                    </div>
                                    <div class="flex justify-between items-center">
                                        <span class="text-xs text-gray-600 dark:text-gray-400">Temperatura</span>
                                        <span class="font-bold text-gray-900 dark:text-white">{{ $caldera['totals']['temperatura'] }}°</span>
                                    </div>
                                    <div class="flex justify-between items-center">
                                        <span class="text-xs text-gray-600 dark:text-gray-400">Presión</span>
                                        <span class="font-bold text-gray-900 dark:text-white">{{ $caldera['totals']['presion'] }} PSI</span>
                                    </div>
                                </div>
                            </div>

                            {{-- PROMEDIOS Card --}}
                            <div class="bg-gradient-to-br from-green-50 to-green-100 dark:from-green-900/20 dark:to-green-800/20 rounded-xl p-4 border border-green-200 dark:border-green-800">
                                <h5 class="text-sm font-semibold text-green-800 dark:text-green-400 mb-3 flex items-center gap-1">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 12l3-3 3 3 4-4M8 21l4-4 4 4M3 4h18M4 4h16v12a1 1 0 01-1 1H5a1 1 0 01-1-1V4z"></path>
                                    </svg>
                                    PROMEDIOS
                                </h5>
                                <div class="space-y-2">
                                    <div class="flex justify-between items-center">
                                        <span class="text-xs text-gray-600 dark:text-gray-400">Horas Trabajo</span>
                                        <span class="font-bold text-gray-900 dark:text-white">{{ $caldera['averages']['horas_trabajo'] }} hrs</span>
                                    </div>
                                    <div class="flex justify-between items-center">
                                        <span class="text-xs text-gray-600 dark:text-gray-400">Efectividad Vapor</span>
                                        <span @class([
                                            'font-bold',
                                            'text-green-600' => $caldera['averages']['efectividad_vapor'] >= 90,
                                            'text-yellow-600' => $caldera['averages']['efectividad_vapor'] >= 70 && $caldera['averages']['efectividad_vapor'] < 90,
                                            'text-red-600' => $caldera['averages']['efectividad_vapor'] < 70,
                                        ])>{{ $caldera['averages']['efectividad_vapor'] }}%</span>
                                    </div>
                                    <div class="flex justify-between items-center">
                                        <span class="text-xs text-gray-600 dark:text-gray-400">Temperatura</span>
                                        <span class="font-bold text-gray-900 dark:text-white">{{ $caldera['averages']['temperatura'] }}°</span>
                                    </div>
                                    <div class="flex justify-between items-center">
                                        <span class="text-xs text-gray-600 dark:text-gray-400">Presión</span>
                                        <span class="font-bold text-gray-900 dark:text-white">{{ $caldera['averages']['presion'] }} PSI</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

</div>
