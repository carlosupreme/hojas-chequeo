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
                {{-- KPI CARDS --}}
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <x-kpi-card title="Equipos Funcionando" color="green">
                        <div class="flex justify-between items-end mt-2">
                            <div>
                                <span class="text-xs text-gray-400 uppercase">Tintorería</span>
                                <div
                                    class="text-2xl font-bold text-gray-900 dark:text-white">{{ $this->recorridosKpis['funcionando']['tintoreria'] }}</div>
                            </div>
                            <div class="text-right">
                                <span class="text-xs text-gray-400 uppercase">Lavandería</span>
                                <div
                                    class="text-2xl font-bold text-gray-900 dark:text-white">{{ $this->recorridosKpis['funcionando']['lavanderia'] }}</div>
                            </div>
                        </div>
                    </x-kpi-card>

                    <x-kpi-card title="Paros Mantenimiento (PPM)" color="amber">
                        <div class="flex justify-between items-end mt-2">
                            <div>
                                <span class="text-xs text-gray-400 uppercase">Tintorería</span>
                                <div
                                    class="text-2xl font-bold text-gray-900 dark:text-white">{{ $this->recorridosKpis['parados_ppm']['tintoreria'] }}</div>
                            </div>
                            <div class="text-right">
                                <span class="text-xs text-gray-400 uppercase">Lavandería</span>
                                <div
                                    class="text-2xl font-bold text-gray-900 dark:text-white">{{ $this->recorridosKpis['parados_ppm']['lavanderia'] }}</div>
                            </div>
                        </div>
                    </x-kpi-card>

                    <x-kpi-card title="Paros Producción (PPP)" color="red">
                        <div class="flex justify-between items-end mt-2">
                            <div>
                                <span class="text-xs text-gray-400 uppercase">Tintorería</span>
                                <div
                                    class="text-2xl font-bold text-gray-900 dark:text-white">{{ $this->recorridosKpis['parados_ppp']['tintoreria'] }}</div>
                            </div>
                            <div class="text-right">
                                <span class="text-xs text-gray-400 uppercase">Lavandería</span>
                                <div
                                    class="text-2xl font-bold text-gray-900 dark:text-white">{{ $this->recorridosKpis['parados_ppp']['lavanderia'] }}</div>
                            </div>
                        </div>
                    </x-kpi-card>
                </div>

                {{-- CHART: Recorridos Semanales --}}
                <div
                    class="bg-white dark:bg-gray-900 p-6 rounded-xl shadow-sm border border-gray-200 dark:border-gray-800">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Recorridos Realizados</h3>
                    <div
                        x-data="recorridosChart"
                        x-init="initChart({
                            series: @js($this->recorridosHistory['series']),
                            labels: @js($this->recorridosHistory['labels'])
                        })"
                        @update-charts.window="updateChart({
                            series: @js($this->recorridosHistory['series']),
                            labels: @js($this->recorridosHistory['labels'])
                        })"
                        class="h-80 w-full"
                        wire:ignore
                    ></div>
                </div>
            </div>
        @endif

        {{-- ================= TAB: MANTENIMIENTO ================= --}}
        @if($activeTab === 'mantenimiento')
            <livewire:analisis.analisis-hoja-chequeo/>
        @endif

        {{-- ================= TAB: REPORTES ================= --}}
        @if($activeTab === 'reportes')
            <livewire:analisis.analisis-reportes :start-date="$this->dateRange['inicio']"
                                                 :end-date="$this->dateRange['final']"
                                                 :key="'reportes-'.md5($this->dateRange['inicio'].$this->dateRange['final'])"/>
        @endif
    </div>
</x-filament-panels::page>
