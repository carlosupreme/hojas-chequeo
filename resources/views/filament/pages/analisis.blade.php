<x-filament-panels::page class="min-h-screen space-y-6">

    {{-- HEADER & FILTERS --}}
    <div
        class="flex flex-col md:flex-row justify-between items-center gap-4 bg-white dark:bg-gray-900 p-4 rounded-xl shadow-sm border border-gray-200 dark:border-gray-800">
        <div>
            <h2 class="text-xl font-bold text-gray-900 dark:text-white">Análisis de Información</h2>
            <p class="text-sm text-gray-500">Métricas clave y rendimiento operativo</p>
        </div>

        {{-- Period Selector --}}
        <div class="flex items-center gap-2">
            {{$this->dateRangeForm}}
        </div>
    </div>

    {{-- TABS NAVIGATION --}}
    <div class="border-b border-gray-200 dark:border-gray-800">
        <nav class="-mb-px flex space-x-8" aria-label="Tabs">
            @foreach(['recorridos' => 'Recorridos', 'mantenimiento' => 'Mantenimiento', 'reportes' => 'Reportes'] as $key => $label)
                <button
                    wire:click="setTab('{{ $key }}')"
                    class="{{ $activeTab === $key
                        ? 'border-blue-500 text-blue-600 dark:text-blue-400'
                        : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 dark:text-gray-400 dark:hover:text-gray-300'
                    }} whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm transition-colors">
                    {{ $label }}
                </button>
            @endforeach
        </nav>
    </div>

    {{-- CONTENT AREA --}}
    <div class="animate-in fade-in duration-300">

        {{-- ================= TAB: RECORRIDOS ================= --}}
        @if($activeTab === 'recorridos')
            <div class="space-y-6">
                {{-- KPI CARDS BY TURNO --}}
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                    {{-- Funcionando --}}
                    <div class="bg-white dark:bg-gray-900 p-6 rounded-xl shadow-sm border border-gray-200 dark:border-gray-800">
                        <div class="flex items-center mb-4">
                            <div class="p-3 rounded-full bg-green-100 dark:bg-green-900">
                                <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                </svg>
                            </div>
                            <h3 class="ml-3 text-lg font-semibold text-gray-900 dark:text-white">Funcionando (✓)</h3>
                        </div>
                        <div class="space-y-2">
                            @foreach($this->recorridosKpis['funcionando'] as $turno => $count)
                                <div class="flex justify-between items-center">
                                    <span class="text-sm text-gray-500">{{ $turno }}</span>
                                    <span class="text-xl font-bold text-green-600">{{ $count }}</span>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    {{-- Falla --}}
                    <div class="bg-white dark:bg-gray-900 p-6 rounded-xl shadow-sm border border-gray-200 dark:border-gray-800">
                        <div class="flex items-center mb-4">
                            <div class="p-3 rounded-full bg-red-100 dark:bg-red-900">
                                <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                </svg>
                            </div>
                            <h3 class="ml-3 text-lg font-semibold text-gray-900 dark:text-white">Falla (X)</h3>
                        </div>
                        <div class="space-y-2">
                            @foreach($this->recorridosKpis['falla'] as $turno => $count)
                                <div class="flex justify-between items-center">
                                    <span class="text-sm text-gray-500">{{ $turno }}</span>
                                    <span class="text-xl font-bold text-red-600">{{ $count }}</span>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    {{-- PPM --}}
                    <div class="bg-white dark:bg-gray-900 p-6 rounded-xl shadow-sm border border-gray-200 dark:border-gray-800">
                        <div class="flex items-center mb-4">
                            <div class="p-3 rounded-full bg-blue-100 dark:bg-blue-900">
                                <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                </svg>
                            </div>
                            <h3 class="ml-3 text-lg font-semibold text-gray-900 dark:text-white">P. Mantenimiento</h3>
                        </div>
                        <div class="space-y-2">
                            @foreach($this->recorridosKpis['parados_ppm'] as $turno => $count)
                                <div class="flex justify-between items-center">
                                    <span class="text-sm text-gray-500">{{ $turno }}</span>
                                    <span class="text-xl font-bold text-blue-600">{{ $count }}</span>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    {{-- PPP --}}
                    <div class="bg-white dark:bg-gray-900 p-6 rounded-xl shadow-sm border border-gray-200 dark:border-gray-800">
                        <div class="flex items-center mb-4">
                            <div class="p-3 rounded-full bg-amber-100 dark:bg-amber-900">
                                <svg class="w-6 h-6 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 9v6m4-6v6m7-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                            </div>
                            <h3 class="ml-3 text-lg font-semibold text-gray-900 dark:text-white">P. Producción</h3>
                        </div>
                        <div class="space-y-2">
                            @foreach($this->recorridosKpis['parados_ppp'] as $turno => $count)
                                <div class="flex justify-between items-center">
                                    <span class="text-sm text-gray-500">{{ $turno }}</span>
                                    <span class="text-xl font-bold text-amber-600">{{ $count }}</span>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>

                {{-- CHARTS ROW --}}
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    {{-- CHART: Estado de Equipos por Turno --}}
                    <div class="bg-white dark:bg-gray-900 p-6 rounded-xl shadow-sm border border-gray-200 dark:border-gray-800">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Estado de Equipos por Turno</h3>
                        <div
                            wire:key="estado-chart-{{ md5(json_encode($this->dateRange)) }}"
                            x-data="recorridosEstadoChart"
                            x-init="initChart({
                                series: @js($this->recorridosEstadoChart['series']),
                                labels: @js($this->recorridosEstadoChart['labels'])
                            })"
                            class="h-80 w-full"
                        ></div>
                    </div>

                    {{-- CHART: Total Recorridos por Turno --}}
                    <div class="bg-white dark:bg-gray-900 p-6 rounded-xl shadow-sm border border-gray-200 dark:border-gray-800">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Total Recorridos por Turno</h3>
                        <div
                            wire:key="total-chart-{{ md5(json_encode($this->dateRange)) }}"
                            x-data="recorridosTotalChart"
                            x-init="initChart({
                                data: @js($this->recorridosTotalByTurno['data']),
                                labels: @js($this->recorridosTotalByTurno['labels'])
                            })"
                            class="h-80 w-full"
                        ></div>
                    </div>
                </div>
            </div>
        @endif

        {{-- ================= TAB: MANTENIMIENTO ================= --}}
        @if($activeTab === 'mantenimiento')
            <livewire:analisis.analisis-hoja-chequeo :start-date="$this->dateRange['inicio']"
                                                     :end-date="$this->dateRange['final']"
                                                     :key="'hojachequeo-'.md5($this->dateRange['inicio'].$this->dateRange['final'])"/>
        @endif

        {{-- ================= TAB: REPORTES ================= --}}
        @if($activeTab === 'reportes')
            <livewire:analisis.analisis-reportes :start-date="$this->dateRange['inicio']"
                                                 :end-date="$this->dateRange['final']"
                                                 :key="'reportes-'.md5($this->dateRange['inicio'].$this->dateRange['final'])"/>
        @endif
    </div>
</x-filament-panels::page>
