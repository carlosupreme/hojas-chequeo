<div class="p-6 bg-white dark:bg-gray-800 rounded-lg shadow-lg">
    <h1 class="mb-4 text-center text-gray-900 dark:text-white">Porcentaje de cumplimiento</h1>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
        <!-- Equipment Selector -->
        <div>
            <label for="equipment" class="block text-sm font-medium text-gray-700 dark:text-gray-200">Equipo</label>
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

        <!-- Month Selector -->
        <div>
            <label for="month" class="block text-sm font-medium text-gray-700 dark:text-gray-200">Mes</label>
            <select id="month" wire:model.live="selectedMonth"
                    class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600
                           bg-white dark:bg-gray-700
                           text-gray-900 dark:text-gray-100
                           shadow-sm focus:border-indigo-500 dark:focus:border-indigo-400
                           focus:ring-indigo-500 dark:focus:ring-indigo-400">
                @foreach($months as $key => $label)
                    <option value="{{ $key }}">{{ $label }}</option>
                @endforeach
            </select>
        </div>
    </div>

    <!-- Chart Canvas with wire:ignore -->
    <div wire:ignore class="w-full h-96 bg-white dark:bg-gray-800">
        <canvas id="checkStatsChart"></canvas>
    </div>

    @assets
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    @endassets

    @script
    <script>
        let chart = null;

        // Initialize or update chart when Livewire is ready
        document.addEventListener('livewire:initialized', () => {
            // Initial chart setup
            updateChart($wire.chartData);

            // Listen for chart data updates
            $wire.on('chartDataUpdated', (chartData) => {
                console.log(chartData[0])
                updateChart(chartData[0]);
            });
        });

        // Handle chart updates
        function updateChart(chartData) {
            const ctx = document.getElementById('checkStatsChart').getContext('2d');
            const isDarkMode = document.documentElement.classList.contains('dark');

            // Destroy existing chart if it exists
            if (chart) {
                chart.destroy();
            }

            // Create new chart instance with dark mode considerations
            chart = new Chart(ctx, {
                type: 'bar',
                data: chartData,
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: {
                            beginAtZero: true,
                            max: 100,
                            ticks: {
                                callback: (value) => `${value}%`,
                                color: isDarkMode ? '#e5e7eb' : '#343434'
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
                        tooltip: {
                            callbacks: {
                                label: (context) => `${context.parsed.y}%`
                            },
                            backgroundColor: isDarkMode ? 'rgba(0, 0, 0, 0.8)' : 'rgba(255, 255, 255, 0.8)',
                            titleColor: isDarkMode ? '#fff' : '#000',
                            bodyColor: isDarkMode ? '#fff' : '#000'
                        },
                        legend: {
                            labels: {
                                color: isDarkMode ? '#e5e7eb' : '#374151'
                            }
                        }
                    }
                }
            });
        }
    </script>
    @endscript
</div>
