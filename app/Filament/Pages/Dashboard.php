<?php

namespace App\Filament\Pages;

use App\Filament\Widgets\RecorridosEstadoChart;
use App\Filament\Widgets\ReportesChart;
use App\Filament\Widgets\StatsOverview;
use App\Filament\Widgets\TopUsersWidget;
use App\Filament\Widgets\TurnoEjecucionesChart;
use BackedEnum;
use Filament\Forms\Components\DatePicker;
use Filament\Pages\Dashboard as BaseDashboard;
use Filament\Pages\Dashboard\Concerns\HasFiltersForm;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;

class Dashboard extends BaseDashboard
{
    use HasFiltersForm;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedComputerDesktop;

    protected static ?string $title = 'Dashboard';

    protected static ?int $navigationSort = -2;

    public function filtersForm(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make()
                    ->schema([
                        DatePicker::make('startDate')
                            ->label('Fecha Inicio')
                            ->default(now()->startOfMonth())
                            ->native(false)
                            ->displayFormat('d/m/Y')
                            ->locale('es'),
                        DatePicker::make('endDate')
                            ->label('Fecha Fin')
                            ->default(now())
                            ->native(false)
                            ->displayFormat('d/m/Y')
                            ->locale('es'),
                    ])
                    ->columns(2),
            ]);
    }

    public function getWidgets(): array
    {
        return [
            StatsOverview::class,
            TopUsersWidget::class,
            RecorridosEstadoChart::class,
            TurnoEjecucionesChart::class,
            ReportesChart::class,
        ];
    }

    public function getColumns(): int
    {
        return 2;
    }
}
