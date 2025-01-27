<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;

class Panel extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-home';
    protected static ?int $navigationSort = -2;


    protected static string $view = 'filament.pages.panel';
}
