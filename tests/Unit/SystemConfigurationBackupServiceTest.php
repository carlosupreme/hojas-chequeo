<?php

namespace Tests\Unit;

use App\Services\SystemConfigurationBackupService;
use PharData;
use Tests\TestCase;
use ZipArchive;

class SystemConfigurationBackupServiceTest extends TestCase
{
    protected string $tempDir;

    protected function setUp(): void
    {
        parent::setUp();
        $this->tempDir = storage_path('app/backups/test_config_'.uniqid());
        mkdir($this->tempDir, 0755, true);
    }

    protected function tearDown(): void
    {
        if (is_dir($this->tempDir)) {
            $files = glob($this->tempDir.DIRECTORY_SEPARATOR.'*');
            if ($files) {
                foreach ($files as $file) {
                    @unlink($file);
                }
            }
            @rmdir($this->tempDir);
        }
        parent::tearDown();
    }

    public function test_generate_backup_creates_valid_tgz(): void
    {
        $service = new SystemConfigurationBackupService;
        $backup = $service->generateBackup('tgz');

        $this->assertFileExists($backup['path']);
        $this->assertStringEndsWith('.tgz', $backup['filename']);
        $this->assertGreaterThan(0, $backup['size']);

        // Verify it's a readable tar.gz
        $phar = new PharData($backup['path']);
        $this->assertTrue(isset($phar['MANIFEST.json']));
        $this->assertTrue(isset($phar['system_info.txt']));

        $manifestContent = json_decode($phar['MANIFEST.json']->getContent(), true);
        $this->assertIsArray($manifestContent);
        $this->assertArrayHasKey('server_info', $manifestContent);
        $this->assertArrayHasKey('nginx', $manifestContent);
        $this->assertArrayHasKey('php', $manifestContent);
        $this->assertArrayHasKey('postgresql', $manifestContent);

        // Cleanup
        @unlink($backup['path']);
    }

    public function test_generate_backup_creates_valid_zip(): void
    {
        $service = new SystemConfigurationBackupService;
        $backup = $service->generateBackup('zip');

        $this->assertFileExists($backup['path']);
        $this->assertStringEndsWith('.zip', $backup['filename']);
        $this->assertGreaterThan(0, $backup['size']);

        $zip = new ZipArchive;
        $this->assertTrue($zip->open($backup['path']));
        $this->assertNotFalse($zip->locateName('MANIFEST.json'));
        $this->assertNotFalse($zip->locateName('system_info.txt'));

        $manifestJson = $zip->getFromName('MANIFEST.json');
        $this->assertNotEmpty($manifestJson);
        $manifest = json_decode($manifestJson, true);
        $this->assertIsArray($manifest);
        $this->assertArrayHasKey('php', $manifest);

        $zip->close();

        // Cleanup
        @unlink($backup['path']);
    }

    public function test_cleanup_old_backups_removes_expired_files(): void
    {
        $backupDir = storage_path('app/backups');
        if (! is_dir($backupDir)) {
            mkdir($backupDir, 0755, true);
        }

        $oldFile = $backupDir.DIRECTORY_SEPARATOR.'configuracion_sistema_2020-01-01_000000.tgz';
        file_put_contents($oldFile, 'old backup');
        touch($oldFile, time() - 8000);

        $newFile = $backupDir.DIRECTORY_SEPARATOR.'configuracion_sistema_'.now()->format('Y-m-d_His').'_fresh.tgz';
        file_put_contents($newFile, 'new backup');

        $service = new SystemConfigurationBackupService;
        $service->cleanupOldBackups();

        $this->assertFileDoesNotExist($oldFile);
        $this->assertFileExists($newFile);

        @unlink($newFile);
    }
}
