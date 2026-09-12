<?php

namespace App\Http\Controllers;

use App\Services\DatabaseBackupService;
use App\Services\SystemConfigurationBackupService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class DatabaseBackupController extends Controller
{
    /**
     * Generate and download a compressed database backup.
     */
    public function download(Request $request, DatabaseBackupService $backupService): BinaryFileResponse|RedirectResponse
    {
        $user = Auth::user();
        if (! $user || ! $user->hasRole('Administrador')) {
            abort(403, 'No tienes permisos para descargar la base de datos.');
        }

        $format = strtolower($request->query('format', 'tgz'));
        if (! in_array($format, ['tgz', 'zip'], true)) {
            $format = 'tgz';
        }

        try {
            $backup = $backupService->generateBackup($format);

            $contentType = $format === 'zip' ? 'application/zip' : 'application/gzip';

            return response()->download($backup['path'], $backup['filename'], [
                'Content-Type' => $contentType,
                'Cache-Control' => 'no-cache, no-store, must-revalidate',
            ])->deleteFileAfterSend(true);
        } catch (\Throwable $e) {
            Log::error('Error al generar backup de base de datos para descarga: '.$e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);

            return redirect()->route('filament.admin.pages.system-update')
                ->with('backup_error', $e->getMessage());
        }
    }

    /**
     * Generate and download a compressed package of active server configurations (Nginx, PHP, PostgreSQL).
     */
    public function downloadConfiguration(Request $request, SystemConfigurationBackupService $configService): BinaryFileResponse|RedirectResponse
    {
        $user = Auth::user();
        if (! $user || ! $user->hasRole('Administrador')) {
            abort(403, 'No tienes permisos para descargar la configuración del sistema.');
        }

        $format = strtolower($request->query('format', 'tgz'));
        if (! in_array($format, ['tgz', 'zip'], true)) {
            $format = 'tgz';
        }

        try {
            $backup = $configService->generateBackup($format);

            $contentType = $format === 'zip' ? 'application/zip' : 'application/gzip';

            return response()->download($backup['path'], $backup['filename'], [
                'Content-Type' => $contentType,
                'Cache-Control' => 'no-cache, no-store, must-revalidate',
            ])->deleteFileAfterSend(true);
        } catch (\Throwable $e) {
            Log::error('Error al generar paquete de configuración para descarga: '.$e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);

            return redirect()->route('filament.admin.pages.system-update')
                ->with('backup_error', $e->getMessage());
        }
    }
}
