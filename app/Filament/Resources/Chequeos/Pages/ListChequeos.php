<?php

namespace App\Filament\Resources\Chequeos\Pages;

use App\Filament\Pages\CreateChequeo;
use App\Filament\Resources\Chequeos\ChequeosResource;
use App\Models\HojaEjecucion;
use Filament\Actions\Action;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Tabs\Tab;
use Illuminate\Database\Eloquent\Builder;

class ListChequeos extends ListRecords
{
    protected static string $resource = ChequeosResource::class;

    protected static ?string $title = 'Chequeos';

    public function getTabs(): array
    {
        return [
            'all' => Tab::make('Todos')
                ->icon('heroicon-m-list-bullet'),

            'pending' => Tab::make('En Proceso')
                ->icon('heroicon-m-clock')
                ->modifyQueryUsing(fn (Builder $query) => $query->whereNull('finalizado_en'))
                ->badge(fn () => HojaEjecucion::whereNull('finalizado_en')->count())
                ->badgeColor('warning'),

            'today' => Tab::make('Finalizados Hoy')
                ->icon('heroicon-m-check-circle')
                ->modifyQueryUsing(fn (Builder $query) => $query->whereNotNull('finalizado_en')->whereDate('finalizado_en', today())),

            'week' => Tab::make('Esta Semana')
                ->modifyQueryUsing(fn (Builder $query) => $query->whereNotNull('finalizado_en')->whereBetween('finalizado_en', [now()->startOfWeek(), now()->endOfWeek()])),
        ];
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('create-chequeo')
                ->label('Crear chequeo diario')
                ->url(CreateChequeo::getUrl()),
        ];
    }
}
