<?php

namespace App\Filament\Pages;

use Auth;
use Filament\Pages\Page;

class ChequeoDaily extends Page
{
    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-pencil-square';

    protected string $view = 'filament.pages.chequeo-daily';

    protected static ?string $title = 'Chequeo diario';

    public static function canAccess(): bool {
        return Auth::user()->hasRole(['Operador', 'Supervisor']);
    }

    public static function getNavigationGroup(): ?string {
        return 'Mantenimiento';
    }
}
