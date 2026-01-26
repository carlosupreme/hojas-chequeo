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
                        x-data="chartComponent({
                            type: 'line',
                            series: @js($this->recorridosHistory['series']),
                            labels: @js($this->recorridosHistory['labels'])
                        })"
                        class="h-80 w-full"
                    ></div>
                </div>
            </div>
        @endif

        {{-- ================= TAB: MANTENIMIENTO ================= --}}
        @if($activeTab === 'mantenimiento')
            <div class="space-y-6">

                {{-- REGISTROS COMPARISON --}}
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    <div
                        class="bg-white dark:bg-gray-900 p-6 rounded-xl shadow-sm border border-gray-200 dark:border-gray-800">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Total Registros
                            Realizados</h3>
                        <!-- Simple Bars Visualization -->
                        <div class="space-y-4">
                            @foreach($this->registrosStats['realizados'] as $area => $count)
                                <div>
                                    <div class="flex justify-between text-sm mb-1">
                                        <span class="capitalize">{{ $area }}</span>
                                        <span class="font-bold">{{ $count }}</span>
                                    </div>
                                    <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-2.5">
                                        <div class="bg-blue-600 h-2.5 rounded-full"
                                             style="width: {{ min($count * 2, 100) }}%"></div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    <div
                        class="bg-white dark:bg-gray-900 p-6 rounded-xl shadow-sm border border-gray-200 dark:border-gray-800">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Registros Completos
                            (100%)</h3>
                        <div class="space-y-4">
                            @foreach($this->registrosStats['completos'] as $area => $count)
                                <div>
                                    <div class="flex justify-between text-sm mb-1">
                                        <span class="capitalize">{{ $area }}</span>
                                        <span class="font-bold text-green-600">{{ $count }}</span>
                                    </div>
                                    <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-2.5">
                                        <div class="bg-green-500 h-2.5 rounded-full"
                                             style="width: {{ min($count * 2, 100) }}%"></div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>

                {{-- CALDERAS STATS GRID --}}
                <div
                    class="bg-white dark:bg-gray-900 rounded-xl shadow-sm border border-gray-200 dark:border-gray-800 overflow-hidden">
                    <div class="p-4 border-b border-gray-200 dark:border-gray-800 bg-gray-50 dark:bg-gray-800">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Análisis de Calderas
                            (Promedios)</h3>
                    </div>
                    <div
                        class="grid grid-cols-1 md:grid-cols-2 divide-y md:divide-y-0 md:divide-x divide-gray-200 dark:divide-gray-800">
                        @foreach(['caldera_1' => 'Caldera 1', 'caldera_2' => 'Caldera 2'] as $key => $title)
                            <div class="p-6">
                                <h4 class="font-bold text-gray-700 dark:text-gray-300 mb-4">{{ $title }}</h4>
                                <div class="grid grid-cols-2 gap-4">
                                    @foreach($this->calderasStats[$key] as $metric => $data)
                                        <div
                                            class="p-3 rounded-lg border {{ $data['warning'] ? 'bg-red-50 border-red-200 dark:bg-red-900/20 dark:border-red-800' : 'bg-gray-50 border-gray-100 dark:bg-gray-800 dark:border-gray-700' }}">
                                            <p class="text-xs text-gray-500 uppercase">{{ str_replace('_', ' ', $metric) }}</p>
                                            <p class="text-xl font-bold {{ $data['warning'] ? 'text-red-600 dark:text-red-400' : 'text-gray-900 dark:text-white' }}">
                                                {{ $data['val'] }}
                                                @if($data['warning'])
                                                    <span
                                                        class="ml-1 inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-medium bg-red-100 text-red-800">
                                                        Check
                                                    </span>
                                                @endif
                                            </p>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

                {{-- CHART: HORAS DE TRABAJO --}}
                <div
                    class="bg-white dark:bg-gray-900 p-6 rounded-xl shadow-sm border border-gray-200 dark:border-gray-800">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Horas de Trabajo
                        Acumuladas</h3>
                    <div
                        x-data="chartComponent({
                            type: 'bar',
                            series: [{ name: 'Horas', data: @js($this->horasTrabajo['data']) }],
                            labels: @js($this->horasTrabajo['labels']),
                            colors: ['#6366f1']
                        })"
                        class="h-80 w-full"
                    ></div>
                </div>

            </div>
        @endif

        {{-- ================= TAB: REPORTES ================= --}}
        @if($activeTab === 'reportes')
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">

                {{-- Chart: Solicitudes History --}}
                <div
                    class="md:col-span-2 bg-white dark:bg-gray-900 p-6 rounded-xl shadow-sm border border-gray-200 dark:border-gray-800">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Historial de Solicitudes
                        ({{ ucfirst($period) }})</h3>
                    <div
                        x-data="chartComponent({
                            type: 'area',
                            series: [{ name: 'Solicitudes', data: @js($this->solicitudesStats['history_values']) }],
                            labels: @js($this->solicitudesStats['history_labels']),
                            colors: ['#f59e0b']
                        })"
                        class="h-80 w-full"
                    ></div>
                </div>

                {{-- Chart: Solicitudes Status --}}
                <div
                    class="bg-white dark:bg-gray-900 p-6 rounded-xl shadow-sm border border-gray-200 dark:border-gray-800">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Estado de Solicitudes</h3>
                    <div
                        x-data="chartComponent({
                            type: 'donut',
                            series: @js(array_values($this->solicitudesStats['status'])),
                            labels: @js(array_keys($this->solicitudesStats['status'])),
                            colors: ['#ef4444', '#10b981']
                        })"
                        class="h-64 w-full flex justify-center"
                    ></div>
                    <div class="mt-4 text-center">
                        <div class="grid grid-cols-2 gap-4">
                            <div class="text-center">
                                    <span
                                        class="block text-2xl font-bold text-red-500">{{ $this->solicitudesStats['status']['pendientes'] }}</span>
                                <span class="text-xs text-gray-500">Pendientes</span>
                            </div>
                            <div class="text-center">
                                    <span
                                        class="block text-2xl font-bold text-green-500">{{ $this->solicitudesStats['status']['realizadas'] }}</span>
                                <span class="text-xs text-gray-500">Realizadas</span>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        @endif
    </div>

    {{-- SCRIPTS (ApexCharts) --}}
    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('chartComponent', (config) => ({
                chart: null,
                init() {
                    const options = {
                        series: config.series,
                        chart: {
                            type: config.type,
                            height: '100%',
                            fontFamily: 'inherit',
                            toolbar: {show: false},
                            background: 'transparent'
                        },
                        labels: config.labels || [],
                        xaxis: {
                            categories: config.labels || [],
                            labels: {style: {colors: '#9ca3af'}},
                            axisBorder: {show: false},
                            axisTicks: {show: false}
                        },
                        yaxis: {
                            labels: {style: {colors: '#9ca3af'}}
                        },
                        colors: config.colors || ['#3b82f6', '#10b981', '#f59e0b'],
                        grid: {
                            borderColor: '#374151',
                            strokeDashArray: 4,
                        },
                        theme: {mode: document.documentElement.classList.contains('dark') ? 'dark' : 'light'},
                        dataLabels: {enabled: false},
                        stroke: {curve: 'smooth', width: 3},
                        plotOptions: {
                            bar: {borderRadius: 4}
                        }
                    };

                    this.chart = new ApexCharts(this.$el, options);
                    this.chart.render();

                    // Listen for Livewire updates to refresh charts
                    Livewire.on('update-charts', () => {
                        // This assumes the component re-renders entirely.
                        // If using wire:ignore, you'd need to fetch new data here.
                        // Since this component uses standard Blade rendering for data injection,
                        // simple re-render handles it.
                    });
                }
            }));
        });
    </script>
</x-filament-panels::page>
