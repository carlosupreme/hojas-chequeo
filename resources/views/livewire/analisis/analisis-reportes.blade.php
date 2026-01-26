<div>
    {{-- Filters Section --}}
    <div class="mb-6 bg-white dark:bg-gray-900 p-6 rounded-xl shadow-sm border border-gray-200 dark:border-gray-800">
        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Filtros</h3>
        <div class="grid grid-cols-2 gap-4">

            {{-- Equipment Filter --}}
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Equipo</label>
                <select
                    wire:model.live="equipoId"
                    class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-800 dark:text-white"
                >
                    <option value="">Todos los equipos</option>
                    @foreach($this->equipos as $equipo)
                        <option value="{{ $equipo->id }}">{{ $equipo->nombre }} ({{ $equipo->tag }})</option>
                    @endforeach
                </select>
            </div>

            {{-- Area Filter --}}
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Área</label>
                <select
                    wire:model.live="area"
                    class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-800 dark:text-white"
                >
                    <option value="">Todas las áreas</option>
                    @foreach($this->areas as $value => $label)
                        <option value="{{ $value }}">{{ $label }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        {{-- Clear Filters Button --}}
        <div class="mt-4 flex justify-end">
            <button
                wire:click="clearFilters"
                class="px-4 py-2 bg-gray-500 text-white rounded-md hover:bg-gray-600 transition-colors duration-200 flex items-center space-x-2"
            >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                </svg>
                <span>Limpiar Filtros</span>
            </button>
        </div>
    </div>

    {{-- Summary Cards --}}
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
        {{-- Total Reports --}}
        <div class="bg-white dark:bg-gray-900 p-6 rounded-xl shadow-sm border border-gray-200 dark:border-gray-800">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-blue-100 dark:bg-blue-900">
                    <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    </svg>
                </div>
                <div class="ml-4">
                    <h2 class="text-sm font-medium text-gray-500 dark:text-gray-400">Total Reportes</h2>
                    <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ $this->solicitudesStats['total_reports'] }}</p>
                </div>
            </div>
        </div>

        {{-- Pending Reports --}}
        <div class="bg-white dark:bg-gray-900 p-6 rounded-xl shadow-sm border border-gray-200 dark:border-gray-800">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-yellow-100 dark:bg-yellow-900">
                    <svg class="w-6 h-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
                <div class="ml-4">
                    <h2 class="text-sm font-medium text-gray-500 dark:text-gray-400">Pendientes</h2>
                    <p class="text-2xl font-bold text-yellow-600">{{ $this->solicitudesStats['status']['pendientes'] }}</p>
                </div>
            </div>
        </div>

        {{-- Completed Reports --}}
        <div class="bg-white dark:bg-gray-900 p-6 rounded-xl shadow-sm border border-gray-200 dark:border-gray-800">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-green-100 dark:bg-green-900">
                    <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
                <div class="ml-4">
                    <h2 class="text-sm font-medium text-gray-500 dark:text-gray-400">Realizadas</h2>
                    <p class="text-2xl font-bold text-green-600">{{ $this->solicitudesStats['status']['realizadas'] }}</p>
                </div>
            </div>
        </div>

        {{-- High Priority Reports --}}
        <div class="bg-white dark:bg-gray-900 p-6 rounded-xl shadow-sm border border-gray-200 dark:border-gray-800">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-red-100 dark:bg-red-900">
                    <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                    </svg>
                </div>
                <div class="ml-4">
                    <h2 class="text-sm font-medium text-gray-500 dark:text-gray-400">Alta Prioridad</h2>
                    <p class="text-2xl font-bold text-red-600">{{ $this->priorityStats['alta'] }}</p>
                </div>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">

        {{-- Chart: Solicitudes History --}}
        <div
            class="lg:col-span-2 bg-white dark:bg-gray-900 p-6 rounded-xl shadow-sm border border-gray-200 dark:border-gray-800">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Historial de Reportes</h3>
            <div
                x-data="reportesHistoryChart"
                x-init="initChart({
                    data: @js($this->solicitudesStats['history_values']),
                    labels: @js($this->solicitudesStats['history_labels'])
                })"
                @chart-data-updated.window="updateChart({
                    data: @js($this->solicitudesStats['history_values']),
                    labels: @js($this->solicitudesStats['history_labels'])
                })"
                class="h-80 w-full"
                wire:ignore
            ></div>
        </div>

        {{-- Chart: Priority Distribution --}}
        <div
            class="bg-white dark:bg-gray-900 p-6 rounded-xl shadow-sm border border-gray-200 dark:border-gray-800">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Distribución por Prioridad</h3>
            <div
                x-data="priorityDonutChart"
                x-init="initChart({
                    data: @js(array_values($this->priorityStats)),
                    labels: @js(['Alta', 'Media', 'Baja'])
                })"
                @chart-data-updated.window="updateChart({
                    data: @js(array_values($this->priorityStats)),
                    labels: @js(['Alta', 'Media', 'Baja'])
                })"
                class="h-64 w-full"
                wire:ignore
            ></div>
            <div class="mt-4 space-y-2">
                <div class="flex justify-between text-sm">
                    <span class="flex items-center"><span class="w-3 h-3 bg-red-500 rounded-full mr-2"></span>Alta</span>
                    <span class="font-medium">{{ $this->priorityStats['alta'] }}</span>
                </div>
                <div class="flex justify-between text-sm">
                    <span class="flex items-center"><span class="w-3 h-3 bg-yellow-500 rounded-full mr-2"></span>Media</span>
                    <span class="font-medium">{{ $this->priorityStats['media'] }}</span>
                </div>
                <div class="flex justify-between text-sm">
                    <span class="flex items-center"><span class="w-3 h-3 bg-green-500 rounded-full mr-2"></span>Baja</span>
                    <span class="font-medium">{{ $this->priorityStats['baja'] }}</span>
                </div>
            </div>
        </div>

    </div>

    {{-- Equipment Stats (if filtering by equipment) --}}
    @if($this->equipoStats)
    <div class="mb-6">
        <div class="bg-white dark:bg-gray-900 p-6 rounded-xl shadow-sm border border-gray-200 dark:border-gray-800">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                    Estadísticas del Equipo: {{ $this->equipoStats['equipo']->nombre }}
                </h3>
                <span class="px-3 py-1 bg-blue-100 text-blue-800 text-sm rounded-full">
                    {{ $this->equipoStats['equipo']->tag }}
                </span>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div class="text-center">
                    <p class="text-2xl font-bold text-blue-600">{{ $this->equipoStats['total_reportes'] }}</p>
                    <p class="text-sm text-gray-500">Total Reportes</p>
                </div>
                <div class="text-center">
                    <p class="text-2xl font-bold text-yellow-600">{{ $this->equipoStats['reportes_pendientes'] }}</p>
                    <p class="text-sm text-gray-500">Pendientes</p>
                </div>
                <div class="text-center">
                    <p class="text-2xl font-bold text-green-600">{{ $this->equipoStats['reportes_realizadas'] }}</p>
                    <p class="text-sm text-gray-500">Realizadas</p>
                </div>
            </div>
        </div>
    </div>
    @endif

    {{-- Area Stats (if filtering by area) --}}
    @if($this->areaStats)
    <div class="mb-6">
        <div class="bg-white dark:bg-gray-900 p-6 rounded-xl shadow-sm border border-gray-200 dark:border-gray-800">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                    Estadísticas del Área: {{ $this->areaStats['area_name'] }}
                </h3>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div class="text-center">
                    <p class="text-2xl font-bold text-blue-600">{{ $this->areaStats['total_reportes'] }}</p>
                    <p class="text-sm text-gray-500">Total Reportes</p>
                </div>
                <div class="text-center">
                    <p class="text-2xl font-bold text-purple-600">{{ $this->areaStats['equipos_involucrados'] }}</p>
                    <p class="text-sm text-gray-500">Equipos Involucrados</p>
                </div>
                <div class="text-center">
                    <p class="text-2xl font-bold text-red-600">{{ $this->areaStats['reportes_by_priority']['alta'] }}</p>
                    <p class="text-sm text-gray-500">Prioridad Alta</p>
                </div>
                <div class="text-center">
                    <p class="text-2xl font-bold text-yellow-600">{{ $this->areaStats['reportes_by_priority']['media'] }}</p>
                    <p class="text-sm text-gray-500">Prioridad Media</p>
                </div>
            </div>
        </div>
    </div>
    @endif

    {{-- Recent Reports Table --}}
    <div class="bg-white dark:bg-gray-900 rounded-xl shadow-sm border border-gray-200 dark:border-gray-800">
        <div class="p-6 border-b border-gray-200 dark:border-gray-700">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Reportes Recientes</h3>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm text-left text-gray-500 dark:text-gray-400">
                <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-800 dark:text-gray-400">
                    <tr>
                        <th scope="col" class="px-6 py-3">Fecha</th>
                        <th scope="col" class="px-6 py-3">Equipo</th>
                        <th scope="col" class="px-6 py-3">Área</th>
                        <th scope="col" class="px-6 py-3">Prioridad</th>
                        <th scope="col" class="px-6 py-3">Estado</th>
                        <th scope="col" class="px-6 py-3">Usuario</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($this->filteredReportes->take(10) as $reporte)
                    <tr class="bg-white border-b dark:bg-gray-900 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-800">
                        <td class="px-6 py-4 font-medium text-gray-900 dark:text-white">
                            {{ \Carbon\Carbon::parse($reporte->fecha)->format('d/m/Y H:i') }}
                        </td>
                        <td class="px-6 py-4">
                            {{ $reporte->equipo?->nombre ?? 'N/A' }}
                            @if($reporte->equipo?->tag)
                                <span class="ml-1 text-xs text-gray-400">({{ $reporte->equipo->tag }})</span>
                            @endif
                        </td>
                        <td class="px-6 py-4">{{ $reporte->area ?? 'N/A' }}</td>
                        <td class="px-6 py-4">
                            <span class="px-2 py-1 text-xs rounded-full
                                @if($reporte->prioridad === 'alta') bg-red-100 text-red-800
                                @elseif($reporte->prioridad === 'media') bg-yellow-100 text-yellow-800
                                @else bg-green-100 text-green-800
                                @endif">
                                {{ ucfirst($reporte->prioridad) }}
                            </span>
                        </td>
                        <td class="px-6 py-4">
                            <span class="px-2 py-1 text-xs rounded-full
                                @if($reporte->estado === 'pendiente') bg-yellow-100 text-yellow-800
                                @else bg-green-100 text-green-800
                                @endif">
                                {{ ucfirst($reporte->estado) }}
                            </span>
                        </td>
                        <td class="px-6 py-4">{{ $reporte->user?->name ?? 'N/A' }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-6 py-8 text-center text-gray-500">
                            No se encontraron reportes con los filtros aplicados.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
