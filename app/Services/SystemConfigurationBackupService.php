<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Process;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use RuntimeException;
use ZipArchive;

class SystemConfigurationBackupService
{
    /**
     * Generate a compressed backup package containing Nginx, PHP and PostgreSQL configurations.
     *
     * @param  string  $format  'tgz' or 'zip'
     * @return array{path: string, filename: string, size: int, files_count: int}
     *
     * @throws RuntimeException
     */
    public function generateBackup(string $format = 'tgz'): array
    {
        $this->cleanupOldBackups();

        $backupDir = storage_path('app/backups');
        if (! is_dir($backupDir)) {
            mkdir($backupDir, 0755, true);
        }

        $timestamp = now()->format('Y-m-d_His');
        $tempDirName = "config_collect_{$timestamp}_".bin2hex(random_bytes(4));
        $tempPath = $backupDir.DIRECTORY_SEPARATOR.$tempDirName;
        mkdir($tempPath, 0755, true);

        try {
            $manifest = [
                'generated_at' => now()->toIso8601String(),
                'server_info' => $this->collectSystemInfo($tempPath),
                'nginx' => $this->collectNginxConfig($tempPath),
                'php' => $this->collectPhpConfig($tempPath),
                'postgresql' => $this->collectPostgresConfig($tempPath),
            ];

            file_put_contents(
                $tempPath.DIRECTORY_SEPARATOR.'MANIFEST.json',
                json_encode($manifest, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)
            );

            // Compress
            $format = strtolower($format);
            if ($format === 'zip') {
                $archiveFilename = "configuracion_sistema_{$timestamp}.zip";
                $archivePath = $backupDir.DIRECTORY_SEPARATOR.$archiveFilename;
                $this->createZipArchive($tempPath, $archivePath);
            } else {
                $archiveFilename = "configuracion_sistema_{$timestamp}.tgz";
                $archivePath = $backupDir.DIRECTORY_SEPARATOR.$archiveFilename;
                $this->createTgzArchive($tempPath, $archivePath);
            }

            $size = filesize($archivePath) ?: 0;

            return [
                'path' => $archivePath,
                'filename' => $archiveFilename,
                'size' => $size,
                'files_count' => count(glob($tempPath.'/**/*', GLOB_BRACE) ?: []),
            ];
        } finally {
            $this->removeDirectory($tempPath);
        }
    }

    /**
     * Collect Nginx configurations.
     *
     * @return array<string, mixed>
     */
    protected function collectNginxConfig(string $targetBase): array
    {
        $nginxDir = $targetBase.DIRECTORY_SEPARATOR.'nginx';
        mkdir($nginxDir, 0755, true);

        $collected = [];

        // 1. Try dumping full active config via 'nginx -T'
        try {
            $dumpProc = Process::timeout(10)->run('nginx -T');
            if ($dumpProc->successful() && ! empty(trim($dumpProc->output()))) {
                file_put_contents($nginxDir.DIRECTORY_SEPARATOR.'nginx_active_dump.conf', $dumpProc->output());
                $collected[] = 'nginx_active_dump.conf (desde nginx -T)';
            }
        } catch (\Throwable $e) {
            // Ignore if nginx binary not in PATH or no permission
        }

        // 2. If Nginx is running in Docker (e.g. sandbox-nginx or custom container)
        try {
            $dockerPs = Process::timeout(5)->run("docker ps --format '{{.Names}}'");
            if ($dockerPs->successful()) {
                $containers = array_filter(explode("\n", trim($dockerPs->output())));
                foreach ($containers as $c) {
                    if (str_contains(strtolower($c), 'nginx') || str_contains(strtolower($c), 'web')) {
                        // Attempt nginx -T inside container
                        $proc = Process::timeout(10)->run("docker exec {$c} nginx -T");
                        if ($proc->successful() && ! empty(trim($proc->output()))) {
                            file_put_contents($nginxDir.DIRECTORY_SEPARATOR."{$c}_nginx_dump.conf", $proc->output());
                            $collected[] = "{$c}_nginx_dump.conf (docker exec)";
                            break;
                        }
                    }
                }
            }
        } catch (\Throwable $e) {
            // Ignore
        }

        // 3. System directories on Linux / Ubuntu
        $systemPaths = [
            '/etc/nginx/nginx.conf',
            '/usr/local/etc/nginx/nginx.conf',
            '/etc/nginx/conf.d',
            '/etc/nginx/sites-available',
            '/etc/nginx/sites-enabled',
            base_path('.sandbox/nginx.conf'),
        ];

        foreach ($systemPaths as $path) {
            if (is_file($path) && is_readable($path)) {
                $destName = basename($path);
                if (file_exists($nginxDir.DIRECTORY_SEPARATOR.$destName)) {
                    $destName = basename(dirname($path)).'_'.$destName;
                }
                copy($path, $nginxDir.DIRECTORY_SEPARATOR.$destName);
                $collected[] = $destName;
            } elseif (is_dir($path) && is_readable($path)) {
                $dirName = basename($path);
                $destSubDir = $nginxDir.DIRECTORY_SEPARATOR.$dirName;
                mkdir($destSubDir, 0755, true);
                $files = glob($path.'/*.{conf,site}', GLOB_BRACE) ?: [];
                foreach ($files as $f) {
                    if (is_file($f) && is_readable($f)) {
                        copy($f, $destSubDir.DIRECTORY_SEPARATOR.basename($f));
                        $collected[] = "{$dirName}/".basename($f);
                    }
                }
            }
        }

        if (empty($collected)) {
            file_put_contents(
                $nginxDir.DIRECTORY_SEPARATOR.'README.txt',
                "No se encontraron archivos en /etc/nginx ni binario 'nginx' ejecutable por el usuario web.\n"
            );
        }

        return [
            'status' => ! empty($collected) ? 'success' : 'partial',
            'files' => $collected,
        ];
    }

    /**
     * Collect PHP and PHP-FPM configurations.
     *
     * @return array<string, mixed>
     */
    protected function collectPhpConfig(string $targetBase): array
    {
        $phpDir = $targetBase.DIRECTORY_SEPARATOR.'php';
        mkdir($phpDir, 0755, true);

        $collected = [];

        // 1. Loaded php.ini
        $loadedIni = php_ini_loaded_file();
        if ($loadedIni && is_file($loadedIni) && is_readable($loadedIni)) {
            copy($loadedIni, $phpDir.DIRECTORY_SEPARATOR.'php.ini');
            $collected[] = 'php.ini ('.basename($loadedIni).')';
        }

        // 2. Scanned additional .ini files (e.g. /etc/php.d/ or /etc/php/8.x/conf.d/)
        $scannedInis = php_ini_scanned_files();
        if ($scannedInis) {
            $confD = $phpDir.DIRECTORY_SEPARATOR.'conf.d';
            mkdir($confD, 0755, true);
            foreach (explode(',', $scannedInis) as $iniPath) {
                $iniPath = trim($iniPath);
                if (is_file($iniPath) && is_readable($iniPath)) {
                    copy($iniPath, $confD.DIRECTORY_SEPARATOR.basename($iniPath));
                    $collected[] = 'conf.d/'.basename($iniPath);
                }
            }
        }

        // 3. PHP-FPM configuration pools (Fedora, Debian, Ubuntu, CentOS, sandbox)
        $fpmCandidates = [
            '/etc/php-fpm.conf',
            '/etc/php-fpm.d/www.conf',
            base_path('.sandbox/php-fpm.conf'),
        ];
        // Check versioned paths /etc/php/<version>/fpm/
        $versionedFpm = glob('/etc/php/*/fpm/php-fpm.conf') ?: [];
        $versionedPools = glob('/etc/php/*/fpm/pool.d/*.conf') ?: [];
        $fpmCandidates = array_merge($fpmCandidates, $versionedFpm, $versionedPools);

        foreach ($fpmCandidates as $fpmPath) {
            if (is_file($fpmPath) && is_readable($fpmPath)) {
                $fpmDest = $phpDir.DIRECTORY_SEPARATOR.'fpm';
                if (! is_dir($fpmDest)) {
                    mkdir($fpmDest, 0755, true);
                }
                $filename = basename($fpmPath);
                if (file_exists($fpmDest.DIRECTORY_SEPARATOR.$filename)) {
                    $filename = basename(dirname($fpmPath)).'_'.$filename;
                }
                copy($fpmPath, $fpmDest.DIRECTORY_SEPARATOR.$filename);
                $collected[] = "fpm/{$filename}";
            }
        }

        // 4. Export full active runtime php.ini directives
        $activeSettings = ini_get_all(null, false);
        if ($activeSettings) {
            $iniContent = "; ================================================================\n";
            $iniContent .= '; PHP Runtime Active Configuration Values ('.php_uname('s').' PHP '.PHP_VERSION.")\n";
            $iniContent .= '; Exported: '.now()->toIso8601String()."\n";
            $iniContent .= "; ================================================================\n\n";
            foreach ($activeSettings as $key => $val) {
                if (is_bool($val)) {
                    $valStr = $val ? 'On' : 'Off';
                } elseif (is_null($val) || $val === '') {
                    $valStr = '""';
                } else {
                    $valStr = is_numeric($val) ? $val : '"'.str_replace('"', '\"', (string) $val).'"';
                }
                $iniContent .= "{$key} = {$valStr}\n";
            }
            file_put_contents($phpDir.DIRECTORY_SEPARATOR.'php_runtime_directives.ini', $iniContent);
            $collected[] = 'php_runtime_directives.ini';
        }

        return [
            'status' => ! empty($collected) ? 'success' : 'partial',
            'files' => $collected,
            'php_version' => PHP_VERSION,
            'loaded_ini' => $loadedIni ?: 'none',
        ];
    }

    /**
     * Collect PostgreSQL configuration.
     *
     * @return array<string, mixed>
     */
    protected function collectPostgresConfig(string $targetBase): array
    {
        $pgDir = $targetBase.DIRECTORY_SEPARATOR.'postgresql';
        mkdir($pgDir, 0755, true);

        $collected = [];

        // 1. Query database for config files locations
        $configFile = null;
        $hbaFile = null;
        try {
            $configFile = DB::selectOne('SHOW config_file')->config_file ?? null;
            $hbaFile = DB::selectOne('SHOW hba_file')->hba_file ?? null;
        } catch (\Throwable $e) {
            Log::warning('Could not query PostgreSQL SHOW config_file: '.$e->getMessage());
        }

        // 2. If PostgreSQL is accessible via local filesystem (Ubuntu native service)
        if ($configFile && is_file($configFile) && is_readable($configFile)) {
            copy($configFile, $pgDir.DIRECTORY_SEPARATOR.'postgresql.conf');
            $collected[] = 'postgresql.conf';
        }
        if ($hbaFile && is_file($hbaFile) && is_readable($hbaFile)) {
            copy($hbaFile, $pgDir.DIRECTORY_SEPARATOR.'pg_hba.conf');
            $collected[] = 'pg_hba.conf';
        }

        // Check standard Debian/Ubuntu paths /etc/postgresql/<version>/main/
        if (empty($collected)) {
            $confCandidates = glob('/etc/postgresql/*/*/postgresql.conf') ?: [];
            $hbaCandidates = glob('/etc/postgresql/*/*/pg_hba.conf') ?: [];
            foreach ($confCandidates as $c) {
                if (is_file($c) && is_readable($c)) {
                    copy($c, $pgDir.DIRECTORY_SEPARATOR.'postgresql.conf');
                    $collected[] = 'postgresql.conf (desde /etc/postgresql)';
                    break;
                }
            }
            foreach ($hbaCandidates as $h) {
                if (is_file($h) && is_readable($h)) {
                    copy($h, $pgDir.DIRECTORY_SEPARATOR.'pg_hba.conf');
                    $collected[] = 'pg_hba.conf (desde /etc/postgresql)';
                    break;
                }
            }
        }

        // 3. If PostgreSQL is running inside a Docker container (laravel-postgres or postgres)
        if (empty($collected)) {
            try {
                $dockerPs = Process::timeout(5)->run("docker ps --format '{{.Names}}'");
                if ($dockerPs->successful()) {
                    $containers = array_filter(explode("\n", trim($dockerPs->output())));
                    $pgContainer = null;
                    if (in_array('laravel-postgres', $containers, true)) {
                        $pgContainer = 'laravel-postgres';
                    } else {
                        foreach ($containers as $c) {
                            if (str_contains(strtolower($c), 'postgres')) {
                                $pgContainer = $c;
                                break;
                            }
                        }
                    }

                    if ($pgContainer) {
                        $targetConf = $configFile ?: '/var/lib/postgresql/data/postgresql.conf';
                        $targetHba = $hbaFile ?: '/var/lib/postgresql/data/pg_hba.conf';

                        $confProc = Process::timeout(10)->run("docker exec {$pgContainer} cat {$targetConf}");
                        if ($confProc->successful() && ! empty(trim($confProc->output()))) {
                            file_put_contents($pgDir.DIRECTORY_SEPARATOR.'postgresql.conf', $confProc->output());
                            $collected[] = "postgresql.conf (desde contenedor {$pgContainer})";
                        }

                        $hbaProc = Process::timeout(10)->run("docker exec {$pgContainer} cat {$targetHba}");
                        if ($hbaProc->successful() && ! empty(trim($hbaProc->output()))) {
                            file_put_contents($pgDir.DIRECTORY_SEPARATOR.'pg_hba.conf', $hbaProc->output());
                            $collected[] = "pg_hba.conf (desde contenedor {$pgContainer})";
                        }
                    }
                }
            } catch (\Throwable $e) {
                // Ignore
            }
        }

        // 4. Query full active settings from pg_settings and write cleanly formatted conf
        try {
            $settings = DB::select('SELECT name, setting, unit, category, short_desc FROM pg_settings ORDER BY category, name');
            if (! empty($settings)) {
                $pgSettingsContent = "# ================================================================\n";
                $pgSettingsContent .= "# PostgreSQL Active Configuration Settings (pg_settings)\n";
                $pgSettingsContent .= '# Exported: '.now()->toIso8601String()."\n";
                $pgSettingsContent .= '# Total Settings: '.count($settings)."\n";
                $pgSettingsContent .= "# ================================================================\n\n";

                $currentCategory = null;
                foreach ($settings as $s) {
                    if ($s->category !== $currentCategory) {
                        $currentCategory = $s->category;
                        $pgSettingsContent .= "\n# ------------------------------------------------------------\n";
                        $pgSettingsContent .= "# Category: {$currentCategory}\n";
                        $pgSettingsContent .= "# ------------------------------------------------------------\n";
                    }
                    $unitStr = ! empty($s->unit) ? " ({$s->unit})" : '';
                    $descStr = ! empty($s->short_desc) ? " # {$s->short_desc}{$unitStr}" : '';
                    $pgSettingsContent .= sprintf("%-35s = '%s'%s\n", $s->name, addslashes((string) $s->setting), $descStr);
                }

                file_put_contents($pgDir.DIRECTORY_SEPARATOR.'postgresql_active_settings.conf', $pgSettingsContent);
                $collected[] = 'postgresql_active_settings.conf';
            }
        } catch (\Throwable $e) {
            Log::warning('Could not export pg_settings: '.$e->getMessage());
        }

        return [
            'status' => ! empty($collected) ? 'success' : 'partial',
            'files' => $collected,
            'config_file' => $configFile,
            'hba_file' => $hbaFile,
        ];
    }

    /**
     * Collect system info and write to system_info.txt.
     *
     * @return array<string, mixed>
     */
    protected function collectSystemInfo(string $targetBase): array
    {
        $dbVersion = 'Unknown';
        try {
            $dbVersion = DB::selectOne('SELECT version()')->version ?? 'Unknown';
        } catch (\Throwable $e) {
            //
        }

        $info = [
            'os' => php_uname(),
            'php_version' => PHP_VERSION,
            'php_sapi' => php_sapi_name(),
            'database' => config('database.default'),
            'database_version' => $dbVersion,
            'laravel_version' => app()->version(),
            'app_env' => app()->environment(),
            'app_url' => config('app.url'),
            'timestamp' => now()->toIso8601String(),
        ];

        $txt = "================================================================\n";
        $txt .= "INFORMACIÓN DEL SISTEMA Y SERVIDOR\n";
        $txt .= "================================================================\n";
        foreach ($info as $k => $v) {
            $txt .= sprintf("%-20s: %s\n", strtoupper($k), $v);
        }

        file_put_contents($targetBase.DIRECTORY_SEPARATOR.'system_info.txt', $txt);

        return $info;
    }

    /**
     * Compress using TGZ.
     */
    protected function createTgzArchive(string $dir, string $targetPath): void
    {
        $items = array_values(array_diff(scandir($dir) ?: [], ['.', '..']));
        if (empty($items)) {
            $items = ['.'];
        }

        $tarProcess = Process::timeout(180)->run(array_merge([
            'tar',
            '-czf',
            $targetPath,
            '-C',
            $dir,
        ], $items));

        if ($tarProcess->successful() && file_exists($targetPath) && filesize($targetPath) > 0) {
            return;
        }

        // Fallback using PharData
        try {
            $tarPath = preg_replace('/\.tgz$/', '.tar', $targetPath);
            $phar = new \PharData($tarPath);
            $phar->buildFromDirectory($dir);
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

        throw new RuntimeException('No se pudo comprimir la configuración a formato TGZ: '.$tarProcess->errorOutput());
    }

    /**
     * Compress using ZipArchive.
     */
    protected function createZipArchive(string $sourceDir, string $targetPath): void
    {
        if (! class_exists(ZipArchive::class)) {
            throw new RuntimeException('La extensión PHP-ZIP no está disponible en el servidor.');
        }

        $zip = new ZipArchive;
        $status = $zip->open($targetPath, ZipArchive::CREATE | ZipArchive::OVERWRITE);

        if ($status !== true) {
            throw new RuntimeException("No se pudo crear el archivo ZIP de configuración. Código: {$status}");
        }

        $files = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($sourceDir, RecursiveDirectoryIterator::SKIP_DOTS),
            RecursiveIteratorIterator::LEAVES_ONLY
        );

        foreach ($files as $file) {
            if (! $file->isDir()) {
                $filePath = $file->getRealPath();
                $relativePath = substr($filePath, strlen($sourceDir) + 1);
                $zip->addFile($filePath, $relativePath);
            }
        }

        $zip->close();

        if (! file_exists($targetPath) || filesize($targetPath) === 0) {
            throw new RuntimeException('El archivo ZIP de configuración generado está vacío o no se guardó correctamente.');
        }
    }

    /**
     * Recursively remove a directory and its contents.
     */
    protected function removeDirectory(string $dir): void
    {
        if (! is_dir($dir)) {
            return;
        }

        $files = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($dir, RecursiveDirectoryIterator::SKIP_DOTS),
            RecursiveIteratorIterator::CHILD_FIRST
        );

        foreach ($files as $fileinfo) {
            $todo = ($fileinfo->isDir() ? 'rmdir' : 'unlink');
            @$todo($fileinfo->getRealPath());
        }

        @rmdir($dir);
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

        $files = glob($backupDir.DIRECTORY_SEPARATOR.'configuracion_sistema_*');
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
