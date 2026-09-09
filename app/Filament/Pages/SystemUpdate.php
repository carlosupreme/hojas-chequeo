<?php

namespace App\Filament\Pages;

use App\Services\DatabaseBackupService;
use Filament\Actions\Action;
use Filament\Forms\Components\Radio;
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

        if (session()->has('backup_error')) {
            Notification::make()
                ->title('Error al generar la copia de seguridad')
                ->body(session('backup_error'))
                ->danger()
                ->send();
        }
    }

    public static function canAccess(): bool
    {
        return Auth::user()?->hasRole('Administrador') ?? false;
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('downloadDatabase')
                ->label('Descargar Base de Datos')
                ->icon(Heroicon::OutlinedArrowDownTray)
                ->color('success')
                ->modalHeading('Descargar Base de Datos (PostgreSQL)')
                ->modalDescription('Se generará una copia de seguridad completa de la base de datos PostgreSQL del servidor y se descargará comprimida.')
                ->form([
                    Radio::make('format')
                        ->label('Formato de descarga')
                        ->options([
                            'tgz' => 'Archivo TGZ (.tar.gz) — Recomendado para Linux / PostgreSQL',
                            'zip' => 'Archivo ZIP (.zip) — Formato estándar compatible',
                        ])
                        ->default('tgz')
                        ->required(),
                ])
                ->modalSubmitActionLabel('Descargar Respaldo')
                ->action(function (array $data, DatabaseBackupService $backupService) {
                    $format = $data['format'] ?? 'tgz';

                    try {
                        $backup = $backupService->generateBackup($format);

                        Notification::make()
                            ->title('Copia de seguridad generada')
                            ->body("Se descargó {$backup['filename']} correctamente.")
                            ->success()
                            ->send();

                        $contentType = $format === 'zip' ? 'application/zip' : 'application/gzip';

                        return response()->download($backup['path'], $backup['filename'], [
                            'Content-Type' => $contentType,
                        ])->deleteFileAfterSend(true);
                    } catch (\Throwable $e) {
                        Notification::make()
                            ->title('Error al generar la copia de seguridad')
                            ->body($e->getMessage())
                            ->danger()
                            ->send();

                        return null;
                    }
                }),

            Action::make('update')
                ->label('Actualizar Sistema')
                ->icon(Heroicon::OutlinedArrowPath)
                ->color('warning')
                ->requiresConfirmation()
                ->modalHeading('¿Actualizar el sistema?')
                ->modalDescription('Esto descargará los últimos cambios de GitHub y reiniciará la aplicación. Se recomienda descargar una copia de seguridad antes de continuar.')
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
