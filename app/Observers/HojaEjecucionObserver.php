<?php

namespace App\Observers;

use App\Models\HojaEjecucion;
use App\Models\User;
use Filament\Notifications\Notification;

class HojaEjecucionObserver
{
    public function saved(HojaEjecucion $hojaEjecucion): void
    {
        if (is_null($hojaEjecucion->finalizado_en)) {
            return;
        }

        Notification::make()
            ->success()
            ->icon('heroicon-o-document-text')
            ->iconColor('success')
            ->title('Chequeo diario finalizado')
            ->body($hojaEjecucion->nombre_operador.' ha finalizado el chequeo de '.$hojaEjecucion->hojaChequeo->equipo->tag)
            ->sendToDatabase(User::role('Administrador')->get(), isEventDispatched: true);
    }
}
