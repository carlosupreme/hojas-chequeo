<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;

class ChequeoDaily extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-pencil-square';

    protected static string $view = 'filament.pages.chequeo-daily';

    protected static ?string $title = 'Chequeo diario';

    public static function canAccess(): bool {
        return \Auth::user()->hasRole('Operador');
    }
}
