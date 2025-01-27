<div class="p-6 bg-white dark:bg-gray-800 rounded-lg shadow-lg">
    <h1 class="mb-4 text-center text-gray-900 dark:text-white">Numero de equipos reportados por mes</h1>
    <div class="mb-6">
        <!-- Selector de Año -->
        <div>
            <label for="year" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Año</label>
            <select id="year" wire:model="selectedYear"
                    class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 shadow-sm focus:border-indigo-500 dark:focus:border-indigo-400 focus:ring-indigo-500 dark:focus:ring-indigo-400">
                @foreach($years as $year)
                    <option value="{{ $year }}">{{ $year }}</option>
                @endforeach
            </select>
        </div>
    </div>
    <!-- Canvas para la gráfica -->
    <div wire:ignore class="w-full h-96">
        <canvas id="areaReportsChart"></canvas>
    </div>
    @assets
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    @endassets
    @script
    <script>
        let areaChart = null;
        document.addEventListener('livewire:initialized', () => {
            initAreaChart($wire.chartData);
        });
        $wire.on('areaChartDataUpdated', function (chartData) {
            updateAreaChart(chartData);
        });

        function initAreaChart(chartData) {
            const ctx = document.getElementById('areaReportsChart').getContext('2d');
            const isDarkMode = document.documentElement.classList.contains('dark');
            if (areaChart) {
                areaChart.destroy();
            }
            areaChart = new Chart(ctx, {
                type: 'line',
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
                    },
                    interaction: {
                        mode: 'nearest',
                        axis: 'x',
                        intersect: false
                    }
                }
            });
        }

        // Add dark mode change listener
            const darkModeObserver = new MutationObserver((mutations) => {
                mutations.forEach((mutation) => {
                    if (mutation.attributeName === 'class') {
                        initAreaChart($wire.chartData);
                    }
                });
            });
            darkModeObserver.observe(document.documentElement, {
                attributes: true,
                attributeFilter: ['class']
            });

        function updateAreaChart(chartData) {
            if (areaChart) {
                areaChart.data = chartData;
                areaChart.update();
            }
        }
    </script>
    @endscript
</div>
