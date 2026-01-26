<?php

namespace App\Observers;

use App\Models\LogRecorrido;
use App\Models\User;
use Filament\Notifications\Notification;

class LogRecorridoObserver
{
    public function created(LogRecorrido $logRecorrido): void
    {
        Notification::make()
            ->success()
            ->icon('heroicon-o-building-office-2')
            ->iconColor('success')
            ->title('Recorrido finalizado')
            ->body($logRecorrido->operador->name.' ha finalizado el recorrido '.$logRecorrido->formularioRecorrido->nombre)
            ->sendToDatabase(User::role('Administrador')->get(), isEventDispatched: true);
    }
}
