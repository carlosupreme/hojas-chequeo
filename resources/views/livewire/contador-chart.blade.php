<div class="p-6 bg-white dark:bg-gray-800 rounded-lg shadow-lg">
    <h1 class="mb-4 text-center text-gray-900 dark:text-white">Numero de veces reportado por mes</h1>
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
        <!-- Selector de Equipo -->
        <div>
            <label for="equipment" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Equipo</label>
            <select id="equipment" wire:model.live="selectedEquipment"
                    class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600
                           bg-white dark:bg-gray-700
                           text-gray-900 dark:text-gray-100
                           shadow-sm focus:border-indigo-500 dark:focus:border-indigo-400
                           focus:ring-indigo-500 dark:focus:ring-indigo-400">
                @foreach($equipos as $id => $tag)
                    <option value="{{ $id }}">{{ $tag }}</option>
                @endforeach
            </select>
        </div>

        <!-- Selector de Año -->
        <div>
            <label for="year" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Año</label>
            <select id="year" wire:model.live="selectedYear"
                    class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600
                           bg-white dark:bg-gray-700
                           text-gray-900 dark:text-gray-100
                           shadow-sm focus:border-indigo-500 dark:focus:border-indigo-400
                           focus:ring-indigo-500 dark:focus:ring-indigo-400">
                @foreach($years as $year)
                    <option value="{{ $year }}">{{ $year }}</option>
                @endforeach
            </select>
        </div>
    </div>

    <!-- Canvas para la gráfica -->
    <div wire:ignore class="w-full h-96 bg-white dark:bg-gray-800">
        <canvas id="equipmentReportsChart"></canvas>
    </div>


    @script
    <script>
        let equipmentChart = null;

        document.addEventListener('livewire:initialized', () => {
            initEquipmentChart($wire.chartData);
        });

        $wire.on('equipmentChartDataUpdated', function (chartData) {
            console.log(chartData)
            updateEquipmentChart(chartData[0]);
        });

        function initEquipmentChart(chartData) {
            const ctx = document.getElementById('equipmentReportsChart').getContext('2d');
            const isDarkMode = document.documentElement.classList.contains('dark');

            if (equipmentChart) {
                equipmentChart.destroy();
            }

            equipmentChart = new Chart(ctx, {
                type: 'bar',
                data: chartData,
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                stepSize: 1,
                                precision: 0,
                                color: isDarkMode ? '#e5e7eb' : '#374151'
                            },
                            grid: {
                                color: isDarkMode ? 'rgba(255, 255, 255, 0.1)' : 'rgba(0, 0, 0, 0.1)'
                            }
                        },
                        x: {
                            ticks: {
                                color: isDarkMode ? '#e5e7eb' : '#374151'
                            },
                            grid: {
                                color: isDarkMode ? 'rgba(255, 255, 255, 0.1)' : 'rgba(0, 0, 0, 0.1)'
                            }
                        }
                    },
                    plugins: {
                        legend: {
                            position: 'top',
                            labels: {
                                color: isDarkMode ? '#e5e7eb' : '#374151'
                            }
                        },
                        tooltip: {
                            mode: 'index',
                            intersect: false,
                            backgroundColor: isDarkMode ? 'rgba(17, 24, 39, 0.8)' : 'rgba(255, 255, 255, 0.8)',
                            titleColor: isDarkMode ? '#e5e7eb' : '#374151',
                            bodyColor: isDarkMode ? '#e5e7eb' : '#374151',
                            borderColor: isDarkMode ? 'rgba(255, 255, 255, 0.1)' : 'rgba(0, 0, 0, 0.1)',
                            borderWidth: 1
                        }
                    }
                }
            });
        }

        const darkModeObserver = new MutationObserver((mutations) => {
            mutations.forEach((mutation) => {
                if (mutation.attributeName === 'class') {
                    initEquipmentChart($wire.chartData);
                }
            });
        });

        darkModeObserver.observe(document.documentElement, {
            attributes: true,
            attributeFilter: ['class']
        });

        function updateEquipmentChart(chartData) {
            if (equipmentChart) {
                equipmentChart.data = chartData;
                equipmentChart.update();
            }
        }
    </script>
    @endscript
</div>
