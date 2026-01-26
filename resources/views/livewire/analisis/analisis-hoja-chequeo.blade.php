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

</div>
