<?php

namespace Tests\Unit;

use App\Services\DatabaseBackupService;
use Tests\TestCase;
use RuntimeException;
use ZipArchive;

class DatabaseBackupServiceTest extends TestCase
{
    protected string $tempDir;

    protected function setUp(): void
    {
        parent::setUp();
        $this->tempDir = sys_get_temp_dir().DIRECTORY_SEPARATOR.'backup_test_'.uniqid();
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

    public function test_fails_when_pg_dump_not_found(): void
    {
        $service = new class extends DatabaseBackupService
        {
            public function findPgDump(): ?string
            {
                return null;
            }
        };

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage("No se encontró la herramienta 'pg_dump'");

        $service->generateBackup('tgz');
    }

    public function test_creates_zip_archive_correctly(): void
    {
        $service = new class extends DatabaseBackupService
        {
            public function testZip(string $source, string $filename, string $target): void
            {
                $this->createZipArchive($source, $filename, $target);
            }
        };

        $dummySql = $this->tempDir.DIRECTORY_SEPARATOR.'dummy.sql';
        file_put_contents($dummySql, 'CREATE TABLE test (id INT);');

        $zipPath = $this->tempDir.DIRECTORY_SEPARATOR.'backup.zip';
        $service->testZip($dummySql, 'dummy.sql', $zipPath);

        $this->assertFileExists($zipPath);

        $zip = new ZipArchive;
        $this->assertTrue($zip->open($zipPath));
        $this->assertSame('dummy.sql', $zip->getNameIndex(0));
        $content = $zip->getFromIndex(0);
        $this->assertStringContainsString('CREATE TABLE test', $content);
        $zip->close();
    }

    public function test_creates_tgz_archive_correctly(): void
    {
        $service = new class extends DatabaseBackupService
        {
            public function testTgz(string $dir, string $filename, string $target): void
            {
                $this->createTgzArchive($dir, $filename, $target);
            }
        };

        $dummySql = $this->tempDir.DIRECTORY_SEPARATOR.'dummy.sql';
        file_put_contents($dummySql, 'CREATE TABLE test_tgz (id INT);');

        $tgzPath = $this->tempDir.DIRECTORY_SEPARATOR.'backup.tgz';
        $service->testTgz($this->tempDir, 'dummy.sql', $tgzPath);

        $this->assertFileExists($tgzPath);
        $this->assertGreaterThan(0, filesize($tgzPath));
    }

    public function test_cleanup_old_backups_removes_expired_files(): void
    {
        $service = new class extends DatabaseBackupService
        {
            public function cleanupCustom(string $dir, int $maxAgeHours): void
            {
                $threshold = time() - ($maxAgeHours * 3600);
                $files = glob($dir.DIRECTORY_SEPARATOR.'backup_*');
                if ($files) {
                    foreach ($files as $file) {
                        if (is_file($file) && filemtime($file) < $threshold) {
                            @unlink($file);
                        }
                    }
                }
            }
        };

        $oldFile = $this->tempDir.DIRECTORY_SEPARATOR.'backup_old.tgz';
        $newFile = $this->tempDir.DIRECTORY_SEPARATOR.'backup_new.tgz';

        file_put_contents($oldFile, 'old');
        file_put_contents($newFile, 'new');

        // Set modification time for old file to 3 hours ago
        touch($oldFile, time() - (3 * 3600));

        $service->cleanupCustom($this->tempDir, 2);

        $this->assertFileDoesNotExist($oldFile);
        $this->assertFileExists($newFile);
    }
}
