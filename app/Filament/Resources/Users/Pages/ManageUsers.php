<?php

namespace App\Filament\Resources\Users\Pages;

use App\Filament\Resources\Users\UserResource;
use App\Models\Turno;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;
use Filament\Schemas\Components\Tabs\Tab;
use Illuminate\Database\Eloquent\Builder;

class ManageUsers extends ManageRecords
{
    protected static string $resource = UserResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->closeModalByClickingAway(false)
                ->modalHeading('Crear usuario'),
        ];
    }

    public function getTabs(): array
    {
        $tabs = [
            'all' => Tab::make('Todos'),
        ];

        $turnos = Turno::all();

        foreach ($turnos as $turno) {
            $tabs[$turno->id] = Tab::make($turno->nombre)
                ->modifyQueryUsing(fn (Builder $query) => $query->whereHas('turno', fn ($q) => $q->where('id', $turno->id)));
        }

        return $tabs;
    }
}
