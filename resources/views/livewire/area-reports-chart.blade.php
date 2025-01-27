<div class="p-6 bg-white rounded-lg shadow-lg">
    <h1 class="mb-4 text-center text-gray-900 dark:text-white">Numero de equipos reportados por mes</h1>
    <div class="mb-6">
        <!-- Selector de Año -->
        <div>
            <label for="year" class="block text-sm font-medium text-gray-700">Año</label>
            <select id="year" wire:model="selectedYear"
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
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
                                precision: 0
                            }
                        }
                    },
                    plugins: {
                        legend: {
                            position: 'top',
                        },
                        tooltip: {
                            mode: 'index',
                            intersect: false,
                        },
                    },
                    interaction: {
                        mode: 'nearest',
                        axis: 'x',
                        intersect: false
                    }
                }
            });
        }

        function updateAreaChart(chartData) {
            if (areaChart) {
                areaChart.data = chartData;
                areaChart.update();
            }
        }
    </script>
    @endscript
</div>
