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
