<?php

namespace App\Observers;

use App\Models\Reporte;
use App\Models\User;
use Filament\Notifications\Notification;

class ReporteObserver
{
    public function created(Reporte $reporte): void
    {
        Notification::make()
            ->danger()
            ->icon('heroicon-o-exclamation-triangle')
            ->iconColor('danger')
            ->title('Reporte de falla')
            ->body($reporte->nombre.' ha reportado una falla en '.$reporte->equipo->tag.' Falla: '.$reporte->falla)
            ->sendToDatabase(User::role('Administrador')->get(), isEventDispatched: true);
    }
}
