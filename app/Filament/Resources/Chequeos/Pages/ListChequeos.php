<?php

namespace App\Filament\Resources\Chequeos\Pages;

use App\Filament\Pages\CreateChequeo;
use App\Filament\Resources\Chequeos\ChequeosResource;
use App\Models\CentroCosto;
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
        $tabs = [
            'all' => Tab::make('Todos')
                ->icon('heroicon-m-list-bullet'),
        ];

        foreach (CentroCosto::orderBy('nombre')->get() as $cc) {
            $tabs['cc_'.$cc->id] = Tab::make($cc->nombre)
                ->icon('heroicon-m-building-office')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('centro_costo_id', $cc->id));
        }

        return $tabs;
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
