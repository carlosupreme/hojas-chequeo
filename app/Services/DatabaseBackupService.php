<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Process;
use RuntimeException;
use ZipArchive;

class DatabaseBackupService
{
    /**
     * Look for the pg_dump binary on the system.
     */
    public function findPgDump(): ?string
    {
        // 1. Check custom path from environment variable
        $custom = env('PG_DUMP_PATH');
        if (! empty($custom) && file_exists($custom) && is_executable($custom)) {
            return $custom;
        }

        // 2. Check system PATH via 'which'
        try {
            $which = Process::run('which pg_dump');
            if ($which->successful()) {
                $path = trim($which->output());
                if (! empty($path) && file_exists($path) && is_executable($path)) {
                    return $path;
                }
            }
        } catch (\Throwable $e) {
            // Ignore and try fallback paths
        }

        // 3. Standard Linux / UNIX binaries
        $candidates = [
            '/usr/bin/pg_dump',
            '/usr/local/bin/pg_dump',
            '/usr/local/pgsql/bin/pg_dump',
        ];

        foreach ($candidates as $candidate) {
            if (file_exists($candidate) && is_executable($candidate)) {
                return $candidate;
            }
        }

        // 4. Debian / Ubuntu versioned paths: /usr/lib/postgresql/<version>/bin/pg_dump
        $versioned = glob('/usr/lib/postgresql/*/bin/pg_dump');
        if (! empty($versioned)) {
            rsort($versioned, SORT_NATURAL);

            return $versioned[0];
        }

        return null;
    }

    /**
     * Generate a database backup for PostgreSQL and compress it into .tgz or .zip.
     *
     * @param  string  $format  'tgz' or 'zip'
     * @param  array<string, mixed>  $options
     * @return array{path: string, filename: string, size: int}
     *
     * @throws RuntimeException
     */
    public function generateBackup(string $format = 'tgz', array $options = []): array
    {
        $this->cleanupOldBackups();

        $pgDump = $this->findPgDump();
        if (! $pgDump) {
            throw new RuntimeException(
                "No se encontró la herramienta 'pg_dump' en el servidor. Por favor verifica que 'postgresql-client' esté instalado."
            );
        }

        $defaultConn = config('database.default');
        $connConfig = config("database.connections.{$defaultConn}", []);

        if (($connConfig['driver'] ?? '') !== 'pgsql') {
            $connConfig = config('database.connections.pgsql', []);
        }

        $database = $connConfig['database'] ?? null;
        if (! $database) {
            throw new RuntimeException('No se encontró el nombre de la base de datos en la configuración.');
        }

        $host = $connConfig['host'] ?? '127.0.0.1';
        $port = (string) ($connConfig['port'] ?? '5432');
        $username = $connConfig['username'] ?? 'postgres';
        $password = (string) ($connConfig['password'] ?? '');

        $backupDir = storage_path('app/backups');
        if (! is_dir($backupDir)) {
            mkdir($backupDir, 0755, true);
        }

        $timestamp = now()->format('Y-m-d_His');
        $safeDbName = preg_replace('/[^a-zA-Z0-9_-]/', '_', $database);
        $sqlFilename = "backup_{$safeDbName}_{$timestamp}.sql";
        $sqlPath = $backupDir.DIRECTORY_SEPARATOR.$sqlFilename;

        // Build pg_dump arguments
        $cmd = [
            $pgDump,
            '-h', $host,
            '-p', $port,
            '-U', $username,
            '--no-owner',
            '--clean',
            '--if-exists',
            $database,
        ];

        // Execute pg_dump and stream output directly to file
        $fileHandle = fopen($sqlPath, 'w');
        if (! $fileHandle) {
            throw new RuntimeException("No se pudo crear el archivo temporal de volcado en {$sqlPath}");
        }

        $process = Process::timeout(300)
            ->env([
                'PGPASSWORD' => $password,
            ])
            ->run($cmd, function ($type, $buffer) use ($fileHandle) {
                if ($type === 'out') {
                    fwrite($fileHandle, $buffer);
                }
            });

        fclose($fileHandle);

        if (! $process->successful() || ! file_exists($sqlPath) || filesize($sqlPath) === 0) {
            if (file_exists($sqlPath)) {
                @unlink($sqlPath);
            }
            $err = trim($process->errorOutput() ?: $process->output());
            Log::error("Fallo al ejecutar pg_dump: {$err}", ['command' => $cmd]);
            throw new RuntimeException('Error al generar el volcado de PostgreSQL: '.($err ?: 'código de salida '.$process->exitCode()));
        }

        // Compress file
        $format = strtolower($format);
        if ($format === 'zip') {
            $archiveFilename = "backup_{$safeDbName}_{$timestamp}.zip";
            $archivePath = $backupDir.DIRECTORY_SEPARATOR.$archiveFilename;
            $this->createZipArchive($sqlPath, $sqlFilename, $archivePath);
        } else {
            // Default to tgz
            $archiveFilename = "backup_{$safeDbName}_{$timestamp}.tgz";
            $archivePath = $backupDir.DIRECTORY_SEPARATOR.$archiveFilename;
            $this->createTgzArchive($backupDir, $sqlFilename, $archivePath);
        }

        // Remove uncompressed .sql file
        @unlink($sqlPath);

        return [
            'path' => $archivePath,
            'filename' => $archiveFilename,
            'size' => filesize($archivePath) ?: 0,
        ];
    }

    /**
     * Compress using TGZ (tar -czf or PharData fallback).
     */
    protected function createTgzArchive(string $dir, string $filename, string $targetPath): void
    {
        $tarProcess = Process::timeout(180)->run([
            'tar',
            '-czf',
            $targetPath,
            '-C',
            $dir,
            $filename,
        ]);

        if ($tarProcess->successful() && file_exists($targetPath) && filesize($targetPath) > 0) {
            return;
        }

        // Fallback using PharData if tar binary failed
        try {
            $tarPath = preg_replace('/\.tgz$/', '.tar', $targetPath);
            $phar = new \PharData($tarPath);
            $phar->addFile($dir.DIRECTORY_SEPARATOR.$filename, $filename);
            $phar->compress(\Phar::GZ);
            @unlink($tarPath);
            $tarGzPath = $tarPath.'.gz';
            if (file_exists($tarGzPath)) {
                rename($tarGzPath, $targetPath);

                return;
            }
        } catch (\Throwable $e) {
            Log::warning('Fallback PharData failed: '.$e->getMessage());
        }

        throw new RuntimeException('No se pudo comprimir el archivo a formato TGZ: '.$tarProcess->errorOutput());
    }

    /**
     * Compress using ZipArchive.
     */
    protected function createZipArchive(string $sourcePath, string $filename, string $targetPath): void
    {
        if (! class_exists(ZipArchive::class)) {
            throw new RuntimeException('La extensión PHP-ZIP no está disponible en el servidor.');
        }

        $zip = new ZipArchive;
        $status = $zip->open($targetPath, ZipArchive::CREATE | ZipArchive::OVERWRITE);

        if ($status !== true) {
            throw new RuntimeException("No se pudo crear el archivo ZIP. Código de estado: {$status}");
        }

        $zip->addFile($sourcePath, $filename);
        $zip->close();

        if (! file_exists($targetPath) || filesize($targetPath) === 0) {
            throw new RuntimeException('El archivo ZIP generado está vacío o no se guardó correctamente.');
        }
    }

    /**
     * Delete backup files older than $maxAgeHours.
     */
    public function cleanupOldBackups(int $maxAgeHours = 2): void
    {
        $backupDir = storage_path('app/backups');
        if (! is_dir($backupDir)) {
            return;
        }

        $threshold = now()->subHours($maxAgeHours)->timestamp;

        $files = glob($backupDir.DIRECTORY_SEPARATOR.'backup_*');
        if (! $files) {
            return;
        }

        foreach ($files as $file) {
            if (is_file($file) && filemtime($file) < $threshold) {
                @unlink($file);
            }
        }
    }
}
