<?php

namespace App\Filament\Resources\Reportes\Pages;

use App\Filament\Resources\Reportes\ReporteResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Tabs\Tab;
use Illuminate\Database\Eloquent\Builder;

class ListReportes extends ListRecords
{
    protected static string $resource = ReporteResource::class;

    public function getTabs(): array
    {
        $tabs = [
            'all' => Tab::make('Todos')
                ->icon('heroicon-m-list-bullet'),
        ];

        foreach (['pendiente', 'realizado'] as $cc) {
            $tabs['cc_'.$cc] = Tab::make(ucfirst($cc))
                ->modifyQueryUsing(fn (Builder $query) => $query->where('estado', $cc));
        }

        return $tabs;
    }

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
