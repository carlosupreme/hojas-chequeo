<?php

namespace App\Providers\Filament;

use App\Filament\Pages\Analisis;
use App\Filament\Pages\CreateChequeo;
use App\Filament\Pages\CreateRecorrido;
use App\Filament\Resources\Chequeos\ChequeosResource;
use App\Filament\Resources\EntregaTurnos\EntregaTurnoResource;
use App\Filament\Resources\Recorridos\RecorridoResource;
use App\Filament\Resources\Reportes\ReporteResource;
use App\Filament\Resources\Tarjetons\TarjetonResource;
use Filament\Enums\GlobalSearchPosition;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use App\Filament\Pages\Dashboard;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\View\PanelsRenderHook;
use Filament\Widgets\AccountWidget;
use Filament\Widgets\FilamentInfoWidget;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\Support\Facades\Blade;
use Illuminate\View\Middleware\ShareErrorsFromSession;
use Illuminate\View\View;

class SupervisorPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->id('supervisor')
            ->path('supervisor')
            ->login()
            ->colors([
                'primary' => Color::Blue,
            ])
            ->databaseNotifications()
            ->renderHook(
                PanelsRenderHook::HEAD_END,
                fn (): string => Blade::render("<script src='https://cdn.jsdelivr.net/npm/apexcharts'></script>")
            )
            ->renderHook(
                PanelsRenderHook::HEAD_END,
                fn (): string => Blade::render("
                <script>
                document.addEventListener('alpine:init', () => {
                    // History Chart Component - Global Definition
                    Alpine.data('reportesHistoryChart', () => ({
                        chart: null,

                        initChart(config) {
                            const options = {
                                series: [{
                                    name: 'Reportes',
                                    data: config.data || []
                                }],
                                chart: {
                                    type: 'area',
                                    height: '100%',
                                    fontFamily: 'inherit',
                                    toolbar: { show: false },
                                    background: 'transparent',
                                    animations: {
                                        enabled: true,
                                        easing: 'easeinout',
                                        speed: 800,
                                        animateGradually: {
                                            enabled: true,
                                            delay: 150
                                        },
                                        dynamicAnimation: {
                                            enabled: true,
                                            speed: 350
                                        }
                                    }
                                },
                                xaxis: {
                                    categories: config.labels || [],
                                    labels: {
                                        style: {
                                            colors: document.documentElement.classList.contains('dark') ? '#9ca3af' : '#6b7280'
                                        }
                                    },
                                    axisBorder: { show: false },
                                    axisTicks: { show: false }
                                },
                                yaxis: {
                                    labels: {
                                        style: {
                                            colors: document.documentElement.classList.contains('dark') ? '#9ca3af' : '#6b7280'
                                        }
                                    }
                                },
                                colors: ['#3b82f6'],
                                fill: {
                                    type: 'gradient',
                                    gradient: {
                                        shadeIntensity: 1,
                                        opacityFrom: 0.7,
                                        opacityTo: 0.3,
                                        stops: [0, 90, 100]
                                    }
                                },
                                grid: {
                                    borderColor: document.documentElement.classList.contains('dark') ? '#374151' : '#e5e7eb',
                                    strokeDashArray: 4,
                                },
                                theme: {
                                    mode: document.documentElement.classList.contains('dark') ? 'dark' : 'light'
                                },
                                dataLabels: { enabled: false },
                                stroke: {
                                    curve: 'smooth',
                                    width: 2
                                },
                                tooltip: {
                                    theme: document.documentElement.classList.contains('dark') ? 'dark' : 'light'
                                }
                            };

                            this.chart = new ApexCharts(this.\$el, options);
                            this.chart.render();
                        },

                        updateChart(config) {
                            if (this.chart) {
                                this.chart.updateSeries([{
                                    name: 'Reportes',
                                    data: config.data || []
                                }], true);

                                this.chart.updateOptions({
                                    xaxis: {
                                        categories: config.labels || []
                                    }
                                }, false, true);
                            }
                        }
                    }));

                    // Priority Donut Chart Component - Global Definition
                    Alpine.data('priorityDonutChart', () => ({
                        chart: null,

                        initChart(config) {
                            const options = {
                                series: config.data || [],
                                chart: {
                                    type: 'donut',
                                    height: '100%',
                                    fontFamily: 'inherit',
                                    background: 'transparent',
                                    animations: {
                                        enabled: true,
                                        easing: 'easeinout',
                                        speed: 800,
                                        animateGradually: {
                                            enabled: true,
                                            delay: 150
                                        },
                                        dynamicAnimation: {
                                            enabled: true,
                                            speed: 350
                                        }
                                    }
                                },
                                labels: config.labels || [],
                                colors: ['#ef4444', '#f59e0b', '#10b981'],
                                plotOptions: {
                                    pie: {
                                        donut: {
                                            size: '70%',
                                            labels: {
                                                show: true,
                                                total: {
                                                    show: true,
                                                    showAlways: true,
                                                    label: 'Total',
                                                    fontSize: '14px',
                                                    fontWeight: 600,
                                                    color: document.documentElement.classList.contains('dark') ? '#9ca3af' : '#6b7280',
                                                    formatter: function (w) {
                                                        return w.globals.seriesTotals.reduce((a, b) => {
                                                            return a + b;
                                                        }, 0);
                                                    }
                                                }
                                            }
                                        }
                                    }
                                },
                                legend: {
                                    show: false
                                },
                                dataLabels: {
                                    enabled: true,
                                    formatter: function(val, opts) {
                                        return opts.w.config.series[opts.seriesIndex];
                                    },
                                    style: {
                                        fontSize: '12px',
                                        fontWeight: 'bold',
                                        colors: ['#fff']
                                    }
                                },
                                tooltip: {
                                    theme: document.documentElement.classList.contains('dark') ? 'dark' : 'light',
                                    y: {
                                        formatter: function(val) {
                                            return val + ' reportes';
                                        }
                                    }
                                }
                            };

                            this.chart = new ApexCharts(this.\$el, options);
                            this.chart.render();
                        },

                        updateChart(config) {
                            if (this.chart) {
                                this.chart.updateSeries(config.data || [], true);
                            }
                        }
                    }));

                    // Recorridos Estado Chart (Stacked Bar) - Global Definition
                    Alpine.data('recorridosEstadoChart', () => ({
                        chart: null,

                        initChart(config) {
                            const options = {
                                series: config.series || [],
                                chart: {
                                    type: 'bar',
                                    height: '100%',
                                    stacked: true,
                                    fontFamily: 'inherit',
                                    toolbar: { show: false },
                                    background: 'transparent',
                                    animations: {
                                        enabled: true,
                                        easing: 'easeinout',
                                        speed: 800
                                    }
                                },
                                plotOptions: {
                                    bar: {
                                        horizontal: false,
                                        borderRadius: 4,
                                        columnWidth: '60%'
                                    }
                                },
                                colors: ['#10b981', '#ef4444', '#3b82f6', '#f59e0b'],
                                xaxis: {
                                    categories: config.labels || [],
                                    labels: {
                                        style: {
                                            colors: document.documentElement.classList.contains('dark') ? '#9ca3af' : '#6b7280'
                                        }
                                    }
                                },
                                yaxis: {
                                    labels: {
                                        style: {
                                            colors: document.documentElement.classList.contains('dark') ? '#9ca3af' : '#6b7280'
                                        }
                                    }
                                },
                                grid: {
                                    borderColor: document.documentElement.classList.contains('dark') ? '#374151' : '#e5e7eb',
                                    strokeDashArray: 4,
                                },
                                dataLabels: { enabled: false },
                                legend: {
                                    position: 'top',
                                    horizontalAlign: 'center',
                                    labels: {
                                        colors: document.documentElement.classList.contains('dark') ? '#9ca3af' : '#6b7280'
                                    }
                                },
                                theme: {
                                    mode: document.documentElement.classList.contains('dark') ? 'dark' : 'light'
                                },
                                tooltip: {
                                    theme: document.documentElement.classList.contains('dark') ? 'dark' : 'light'
                                }
                            };

                            this.chart = new ApexCharts(this.\$el, options);
                            this.chart.render();
                        },

                        updateChart(config) {
                            if (this.chart) {
                                this.chart.updateSeries(config.series || [], true);
                                this.chart.updateOptions({
                                    xaxis: {
                                        categories: config.labels || []
                                    }
                                }, false, true);
                            }
                        }
                    }));

                    // Recorridos Total by Turno Chart (Vertical Bar) - Global Definition
                    Alpine.data('recorridosTotalChart', () => ({
                        chart: null,

                        initChart(config) {
                            const options = {
                                series: [{
                                    name: 'Total Recorridos',
                                    data: config.data || []
                                }],
                                chart: {
                                    type: 'bar',
                                    height: '100%',
                                    fontFamily: 'inherit',
                                    toolbar: { show: false },
                                    background: 'transparent',
                                    animations: {
                                        enabled: true,
                                        easing: 'easeinout',
                                        speed: 800
                                    }
                                },
                                plotOptions: {
                                    bar: {
                                        horizontal: false,
                                        borderRadius: 4,
                                        distributed: true,
                                        columnWidth: '60%',
                                        dataLabels: {
                                            position: 'top'
                                        }
                                    }
                                },
                                colors: ['#10b981', '#3b82f6', '#f59e0b', '#ef4444', '#8b5cf6', '#ec4899'],
                                xaxis: {
                                    categories: config.labels || [],
                                    labels: {
                                        style: {
                                            colors: document.documentElement.classList.contains('dark') ? '#9ca3af' : '#6b7280'
                                        }
                                    }
                                },
                                yaxis: {
                                    labels: {
                                        style: {
                                            colors: document.documentElement.classList.contains('dark') ? '#9ca3af' : '#6b7280'
                                        }
                                    }
                                },
                                grid: {
                                    borderColor: document.documentElement.classList.contains('dark') ? '#374151' : '#e5e7eb',
                                    strokeDashArray: 4,
                                },
                                dataLabels: {
                                    enabled: true,
                                    formatter: function(val) {
                                        return val;
                                    },
                                    offsetY: -20,
                                    style: {
                                        fontSize: '12px',
                                        colors: [document.documentElement.classList.contains('dark') ? '#fff' : '#333']
                                    }
                                },
                                legend: { show: false },
                                theme: {
                                    mode: document.documentElement.classList.contains('dark') ? 'dark' : 'light'
                                },
                                tooltip: {
                                    theme: document.documentElement.classList.contains('dark') ? 'dark' : 'light'
                                }
                            };

                            this.chart = new ApexCharts(this.\$el, options);
                            this.chart.render();
                        },

                        updateChart(config) {
                            if (this.chart) {
                                this.chart.updateSeries([{
                                    name: 'Total Recorridos',
                                    data: config.data || []
                                }], true);
                                this.chart.updateOptions({
                                    xaxis: {
                                        categories: config.labels || []
                                    }
                                }, false, true);
                            }
                        }
                    }));

                    // Recorridos Chart Component (Line) - Global Definition
                    Alpine.data('recorridosChart', () => ({
                        chart: null,

                        initChart(config) {
                            const options = {
                                series: config.series || [],
                                chart: {
                                    type: 'line',
                                    height: '100%',
                                    fontFamily: 'inherit',
                                    toolbar: { show: false },
                                    background: 'transparent',
                                    animations: {
                                        enabled: true,
                                        easing: 'easeinout',
                                        speed: 800,
                                        animateGradually: {
                                            enabled: true,
                                            delay: 150
                                        },
                                        dynamicAnimation: {
                                            enabled: true,
                                            speed: 350
                                        }
                                    }
                                },
                                xaxis: {
                                    categories: config.labels || [],
                                    labels: {
                                        style: {
                                            colors: document.documentElement.classList.contains('dark') ? '#9ca3af' : '#6b7280'
                                        }
                                    },
                                    axisBorder: { show: false },
                                    axisTicks: { show: false }
                                },
                                yaxis: {
                                    labels: {
                                        style: {
                                            colors: document.documentElement.classList.contains('dark') ? '#9ca3af' : '#6b7280'
                                        }
                                    }
                                },
                                colors: ['#3b82f6', '#10b981', '#f59e0b'],
                                grid: {
                                    borderColor: document.documentElement.classList.contains('dark') ? '#374151' : '#e5e7eb',
                                    strokeDashArray: 4,
                                },
                                theme: {
                                    mode: document.documentElement.classList.contains('dark') ? 'dark' : 'light'
                                },
                                dataLabels: { enabled: false },
                                stroke: {
                                    curve: 'smooth',
                                    width: 3
                                },
                                markers: {
                                    size: 5,
                                    strokeWidth: 2,
                                    strokeColors: document.documentElement.classList.contains('dark') ? '#1f2937' : '#ffffff',
                                    hover: {
                                        size: 7
                                    }
                                },
                                tooltip: {
                                    theme: document.documentElement.classList.contains('dark') ? 'dark' : 'light'
                                },
                                legend: {
                                    position: 'top',
                                    horizontalAlign: 'right',
                                    floating: true,
                                    offsetY: -25,
                                    offsetX: -5,
                                    labels: {
                                        colors: document.documentElement.classList.contains('dark') ? '#9ca3af' : '#6b7280'
                                    }
                                }
                            };

                            this.chart = new ApexCharts(this.\$el, options);
                            this.chart.render();
                        },

                        updateChart(config) {
                            if (this.chart) {
                                this.chart.updateSeries(config.series || [], true);

                                this.chart.updateOptions({
                                    xaxis: {
                                        categories: config.labels || []
                                    }
                                }, false, true);
                            }
                        }
                    }));

                    // Turno Completion Bar Chart (Horizontal Bar for %) - Global Definition
                    Alpine.data('turnoCompletionBarChart', () => ({
                        chart: null,

                        initChart(config) {
                            const options = {
                                series: [{
                                    name: '% Realizados',
                                    data: config.data || []
                                }],
                                chart: {
                                    type: 'bar',
                                    height: '100%',
                                    fontFamily: 'inherit',
                                    toolbar: { show: false },
                                    background: 'transparent',
                                    animations: {
                                        enabled: true,
                                        easing: 'easeinout',
                                        speed: 800
                                    }
                                },
                                plotOptions: {
                                    bar: {
                                        horizontal: true,
                                        borderRadius: 4,
                                        distributed: true,
                                        dataLabels: {
                                            position: 'center'
                                        }
                                    }
                                },
                                colors: ['#10b981', '#3b82f6', '#f59e0b', '#ef4444', '#8b5cf6', '#ec4899'],
                                xaxis: {
                                    categories: config.labels || [],
                                    max: 100,
                                    labels: {
                                        style: {
                                            colors: document.documentElement.classList.contains('dark') ? '#9ca3af' : '#6b7280'
                                        },
                                        formatter: function(val) {
                                            return val + '%';
                                        }
                                    }
                                },
                                yaxis: {
                                    labels: {
                                        style: {
                                            colors: document.documentElement.classList.contains('dark') ? '#9ca3af' : '#6b7280'
                                        }
                                    }
                                },
                                grid: {
                                    borderColor: document.documentElement.classList.contains('dark') ? '#374151' : '#e5e7eb',
                                    strokeDashArray: 4,
                                },
                                dataLabels: {
                                    enabled: true,
                                    formatter: function(val) {
                                        return val + '%';
                                    },
                                    style: {
                                        fontSize: '12px',
                                        fontWeight: 'bold',
                                        colors: ['#fff']
                                    }
                                },
                                legend: { show: false },
                                theme: {
                                    mode: document.documentElement.classList.contains('dark') ? 'dark' : 'light'
                                },
                                tooltip: {
                                    theme: document.documentElement.classList.contains('dark') ? 'dark' : 'light',
                                    y: {
                                        formatter: function(val) {
                                            return val + '%';
                                        }
                                    }
                                }
                            };

                            this.chart = new ApexCharts(this.\$el, options);
                            this.chart.render();
                        },

                        updateChart(config) {
                            if (this.chart) {
                                this.chart.updateSeries([{
                                    name: '% Realizados',
                                    data: config.data || []
                                }], true);
                                this.chart.updateOptions({
                                    xaxis: {
                                        categories: config.labels || []
                                    }
                                }, false, true);
                            }
                        }
                    }));

                    // Turno Ejecucion Count Chart (Bar) - Global Definition
                    Alpine.data('turnoEjecucionChart', () => ({
                        chart: null,

                        initChart(config) {
                            const options = {
                                series: [{
                                    name: 'Ejecuciones',
                                    data: config.data || []
                                }],
                                chart: {
                                    type: 'bar',
                                    height: '100%',
                                    fontFamily: 'inherit',
                                    toolbar: { show: false },
                                    background: 'transparent',
                                    animations: {
                                        enabled: true,
                                        easing: 'easeinout',
                                        speed: 800
                                    }
                                },
                                plotOptions: {
                                    bar: {
                                        horizontal: false,
                                        borderRadius: 4,
                                        distributed: true,
                                        columnWidth: '60%',
                                        dataLabels: {
                                            position: 'top'
                                        }
                                    }
                                },
                                colors: ['#10b981', '#3b82f6', '#f59e0b', '#ef4444', '#8b5cf6', '#ec4899'],
                                xaxis: {
                                    categories: config.labels || [],
                                    labels: {
                                        style: {
                                            colors: document.documentElement.classList.contains('dark') ? '#9ca3af' : '#6b7280'
                                        }
                                    }
                                },
                                yaxis: {
                                    labels: {
                                        style: {
                                            colors: document.documentElement.classList.contains('dark') ? '#9ca3af' : '#6b7280'
                                        }
                                    }
                                },
                                grid: {
                                    borderColor: document.documentElement.classList.contains('dark') ? '#374151' : '#e5e7eb',
                                    strokeDashArray: 4,
                                },
                                dataLabels: {
                                    enabled: true,
                                    formatter: function(val) {
                                        return val;
                                    },
                                    offsetY: -20,
                                    style: {
                                        fontSize: '12px',
                                        colors: [document.documentElement.classList.contains('dark') ? '#fff' : '#333']
                                    }
                                },
                                legend: { show: false },
                                theme: {
                                    mode: document.documentElement.classList.contains('dark') ? 'dark' : 'light'
                                },
                                tooltip: {
                                    theme: document.documentElement.classList.contains('dark') ? 'dark' : 'light'
                                }
                            };

                            this.chart = new ApexCharts(this.\$el, options);
                            this.chart.render();
                        },

                        updateChart(config) {
                            if (this.chart) {
                                this.chart.updateSeries([{
                                    name: 'Ejecuciones',
                                    data: config.data || []
                                }], true);
                                this.chart.updateOptions({
                                    xaxis: {
                                        categories: config.labels || []
                                    }
                                }, false, true);
                            }
                        }
                    }));
                });

                // Listen for Livewire events
                document.addEventListener('livewire:initialized', () => {
                    Livewire.on('chartDataUpdated', () => {
                        // Dispatch custom event for Alpine.js components
                        window.dispatchEvent(new CustomEvent('chart-data-updated'));
                    });

                    Livewire.on('update-charts', () => {
                        // Charts will be updated via Alpine.js reactivity
                        console.log('Charts update triggered');
                    });
                });
                </script>
                ")
            )
            ->renderHook(
                PanelsRenderHook::HEAD_END,
                fn (): string => Blade::render("@vite('resources/js/app.js')")
            )
            ->viteTheme('resources/css/filament/supervisor/theme.css')
            ->favicon(asset('/logo.png'))
            ->brandLogo(asset('logo.png'))
            ->darkModeBrandLogo(asset('dark.png'))
            ->brandName('Tacuba')
            ->brandLogoHeight('35px')
            ->sidebarCollapsibleOnDesktop()
            ->globalSearch()
            ->topbar(false)
            ->unsavedChangesAlerts()
            ->globalSearch(position: GlobalSearchPosition::Sidebar)
            ->globalSearchKeyBindings(['command+k', 'ctrl+k'])
            ->sidebarWidth('300px')
            ->renderHook(PanelsRenderHook::AUTH_LOGIN_FORM_AFTER,
                fn (): View => view('operador-login-button'))
            ->discoverResources(in: app_path('Filament/Supervisor/Resources'), for: 'App\Filament\Supervisor\Resources')
            ->discoverPages(in: app_path('Filament/Supervisor/Pages'), for: 'App\Filament\Supervisor\Pages')
            ->resources([
                ChequeosResource::class,
                RecorridoResource::class,
                EntregaTurnoResource::class,
                TarjetonResource::class,
                ReporteResource::class,
            ])
            ->pages([
                Dashboard::class,
                CreateChequeo::class,
                CreateRecorrido::class,
                Analisis::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\Filament\Widgets')
            ->widgets([
                AccountWidget::class,
                FilamentInfoWidget::class,
            ])
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                VerifyCsrfToken::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->authMiddleware([
                Authenticate::class,
            ]);
    }
}
