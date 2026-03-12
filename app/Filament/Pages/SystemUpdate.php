<?php

namespace App\Filament\Pages;

use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;

class SystemUpdate extends Page
{
    protected string $view = 'filament.pages.system-update';

    protected static ?string $title = 'Actualizar Sistema';

    protected static bool $shouldRegisterNavigation = false;

    protected static string $logFile = '/tmp/deploy.log';

    protected static string $lockFile = '/tmp/deploy.lock';

    public bool $isRunning = false;

    public function mount(): void
    {
        $this->isRunning = $this->checkIfRunning();
    }

    public static function canAccess(): bool
    {
        return Auth::user()?->hasRole('Administrador') ?? false;
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('update')
                ->label('Actualizar Sistema')
                ->icon(Heroicon::OutlinedArrowPath)
                ->color('warning')
                ->requiresConfirmation()
                ->modalHeading('¿Actualizar el sistema?')
                ->modalDescription('Esto descargará los últimos cambios de GitHub y reiniciará la aplicación. El proceso toma 2-5 minutos.')
                ->modalSubmitActionLabel('Sí, actualizar')
                ->disabled(fn () => $this->isRunning)
                ->action('runDeploy'),

            Action::make('refresh_log')
                ->label('Actualizar Log')
                ->icon(Heroicon::OutlinedArrowPath)
                ->color('gray')
                ->action('refreshLog'),
        ];
    }

    public function runDeploy(): void
    {
        if ($this->checkIfRunning()) {
            Notification::make()
                ->title('Ya hay una actualización en curso')
                ->warning()
                ->send();

            return;
        }

        $deployScript = base_path('deploy.sh');
        $logFile = static::$logFile;

        file_put_contents($logFile, '['.now()->format('H:i:s').'] Iniciando actualización...'.PHP_EOL);

        // Create lock before launching — shell removes it when script finishes (pass or fail)
        touch(static::$lockFile);
        $lockFile = static::$lockFile;
        exec("nohup bash -c 'bash {$deployScript} >> {$logFile} 2>&1; rm -f {$lockFile}' &");

        $this->isRunning = true;

        Notification::make()
            ->title('Actualización iniciada')
            ->body('El proceso corre en segundo plano. Refresca el log para ver el progreso.')
            ->success()
            ->send();
    }

    public function refreshLog(): void
    {
        $wasRunning = $this->isRunning;
        $this->isRunning = $this->checkIfRunning();

        if ($wasRunning && ! $this->isRunning) {
            Notification::make()
                ->title('Sistema actualizado')
                ->body('La actualización finalizó correctamente.')
                ->success()
                ->send();
        }
    }

    #[Computed]
    public function logContent(): string
    {
        if (! file_exists(static::$logFile)) {
            return 'No hay log disponible todavía.';
        }

        $content = file_get_contents(static::$logFile) ?: 'Log vacío.';

        // Strip ANSI escape codes
        return preg_replace('/\x1B\[[0-9;]*[a-zA-Z]/', '', $content);
    }

    private function checkIfRunning(): bool
    {
        return file_exists(static::$lockFile);
    }
}
