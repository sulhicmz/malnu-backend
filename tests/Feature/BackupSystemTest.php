<?php

declare(strict_types=1);

namespace Tests\Feature;

use Tests\TestCase;
use App\Services\BackupService;

class BackupSystemTest extends TestCase
{
    private BackupService $backupService;
    protected string $backupPath;

    protected function setUp(): void
    {
        parent::setUp();

        $this->backupService = new BackupService();
        $this->backupPath = BASE_PATH . '/storage/testing/backups';
        
        if (! is_dir($this->backupPath)) {
            mkdir($this->backupPath, 0755, true);
        }
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        
        $this->removeDirectory($this->backupPath);
    }

    public function test_backup_service_can_create_database_backup()
    {
        $result = $this->backupService->createBackup('database', [
            'path' => $this->backupPath . '/database',
        ]);

        $this->assertIsArray($result);
        $this->assertArrayHasKey('success', $result);
        $this->assertArrayHasKey('timestamp', $result);
        $this->assertArrayHasKey('type', $result);
        $this->assertArrayHasKey('details', $result);
        $this->assertEquals('database', $result['type']);
    }

    public function test_backup_service_can_create_filesystem_backup()
    {
        $result = $this->backupService->createBackup('filesystem', [
            'path' => $this->backupPath . '/filesystem',
        ]);

        $this->assertIsArray($result);
        $this->assertArrayHasKey('success', $result);
        $this->assertArrayHasKey('timestamp', $result);
        $this->assertEquals('filesystem', $result['type']);
    }

    public function test_backup_service_can_create_config_backup()
    {
        $result = $this->backupService->createBackup('config', [
            'path' => $this->backupPath . '/config',
        ]);

        $this->assertIsArray($result);
        $this->assertArrayHasKey('success', $result);
        $this->assertEquals('config', $result['type']);
    }

    public function test_backup_service_can_create_comprehensive_backup()
    {
        $result = $this->backupService->createBackup('all', [
            'path' => $this->backupPath,
        ]);

        $this->assertIsArray($result);
        $this->assertArrayHasKey('success', $result);
        $this->assertEquals('all', $result['type']);
        $this->assertArrayHasKey('database', $result['details']);
        $this->assertArrayHasKey('filesystem', $result['details']);
        $this->assertArrayHasKey('config', $result['details']);
    }

    public function test_backup_service_can_get_backup_status()
    {
        $status = $this->backupService->getBackupStatus();

        $this->assertIsArray($status);
        $this->assertArrayHasKey('timestamp', $status);
        $this->assertArrayHasKey('backup_locations', $status);
        $this->assertArrayHasKey('statistics', $status);
        $this->assertArrayHasKey('latest_backups', $status);
        
        $this->assertArrayHasKey('database', $status['backup_locations']);
        $this->assertArrayHasKey('filesystem', $status['backup_locations']);
        $this->assertArrayHasKey('config', $status['backup_locations']);
        $this->assertArrayHasKey('comprehensive', $status['backup_locations']);
        
        $this->assertArrayHasKey('database_backups', $status['statistics']);
        $this->assertArrayHasKey('filesystem_backups', $status['statistics']);
        $this->assertArrayHasKey('config_backups', $status['statistics']);
        $this->assertArrayHasKey('comprehensive_backups', $status['statistics']);
    }

    public function test_backup_verification_with_valid_backup()
    {
        $backupFile = $this->createTestBackup();

        $result = $this->backupService->verifyBackup($backupFile, 'all');

        $this->assertIsArray($result);
        $this->assertArrayHasKey('success', $result);
        $this->assertArrayHasKey('timestamp', $result);
        $this->assertArrayHasKey('backup_path', $result);
        $this->assertArrayHasKey('verification_results', $result);
        $this->assertEquals($backupFile, $result['backup_path']);
    }

    public function test_backup_verification_with_nonexistent_file()
    {
        $result = $this->backupService->verifyBackup('/nonexistent/backup.tar.gz', 'all');

        $this->assertIsArray($result);
        $this->assertArrayHasKey('success', $result);
        $this->assertFalse($result['success']);
        $this->assertArrayHasKey('error', $result);
    }

    public function test_backup_service_can_clean_old_backups()
    {
        $this->createMultipleTestBackups(7);

        $result = $this->backupService->cleanOldBackups('database', 3);

        $this->assertIsArray($result);
        $this->assertArrayHasKey('success', $result);
        $this->assertArrayHasKey('timestamp', $result);
        $this->assertArrayHasKey('cleaned', $result);
        $this->assertArrayHasKey('keep', $result);
        $this->assertEquals(3, $result['keep']);
    }

    public function test_restore_backup_returns_valid_structure()
    {
        $backupFile = $this->createTestBackup();

        $result = $this->backupService->restoreBackup($backupFile, 'all', []);

        $this->assertIsArray($result);
        $this->assertArrayHasKey('success', $result);
        $this->assertArrayHasKey('timestamp', $result);
        $this->assertArrayHasKey('type', $result);
        $this->assertArrayHasKey('backup_path', $result);
        $this->assertArrayHasKey('details', $result);
        $this->assertEquals($backupFile, $result['backup_path']);
        $this->assertEquals('all', $result['type']);
    }

    public function test_backup_file_integrity_check()
    {
        $backupFile = $this->createTestBackup();

        $command = 'tar -tzf ' . escapeshellarg($backupFile) . ' 2>/dev/null';
        $exitCode = 0;
        $output = [];
        exec($command, $output, $exitCode);

        $this->assertEquals(0, $exitCode, 'Backup file integrity check failed');
        $this->assertNotEmpty($output, 'Backup file appears to be empty');
    }

    public function test_backup_file_has_correct_structure()
    {
        $backupFile = $this->createTestBackup();

        $command = 'tar -tzf ' . escapeshellarg($backupFile) . ' 2>/dev/null';
        $exitCode = 0;
        $output = [];
        exec($command, $output, $exitCode);

        $this->assertEquals(0, $exitCode);
        $this->assertContains('test.txt', $output);
    }

    public function test_backup_checksums_can_be_calculated()
    {
        $backupFile = $this->createTestBackup();

        $md5Hash = md5_file($backupFile);
        $sha256Hash = hash_file('sha256', $backupFile);

        $this->assertIsString($md5Hash);
        $this->assertIsString($sha256Hash);
        $this->assertEquals(32, strlen($md5Hash));
        $this->assertEquals(64, strlen($sha256Hash));
    }

    public function test_backup_file_size_is_valid()
    {
        $backupFile = $this->createTestBackup();

        $fileSize = filesize($backupFile);

        $this->assertIsInt($fileSize);
        $this->assertGreaterThan(100, $fileSize, 'Backup file size is too small');
        $this->assertLessThan(10 * 1024 * 1024, $fileSize, 'Backup file is suspiciously large');
    }

    protected function createTestBackup(): string
    {
        $testDir = $this->backupPath . '/test_content_' . uniqid();
        mkdir($testDir, 0755, true);
        file_put_contents($testDir . '/test.txt', 'Test content for backup');

        $backupFile = $this->backupPath . '/test_backup_' . uniqid() . '.tar.gz';
        $command = 'tar -czf ' . escapeshellarg($backupFile) . ' -C ' . escapeshellarg($testDir) . ' .';
        
        $exitCode = 0;
        exec($command, $output, $exitCode);

        if ($exitCode !== 0) {
            throw new \RuntimeException('Failed to create test backup');
        }

        $this->removeDirectory($testDir);

        return $backupFile;
    }

    protected function createMultipleTestBackups(int $count): void
    {
        $backupDir = $this->backupPath . '/multiple_backups';
        mkdir($backupDir, 0755, true);

        for ($i = 0; $i < $count; $i++) {
            $testFile = $backupDir . '/test_' . $i . '.txt';
            file_put_contents($testFile, 'Test content ' . $i);
            
            $backupFile = $backupDir . '/db_backup_test_' . $i . '_' . date('Y-m-d-H-i-s') . '.tar.gz';
            $command = 'tar -czf ' . escapeshellarg($backupFile) . ' -C ' . escapeshellarg($backupDir) . ' ' . escapeshellarg('test_' . $i . '.txt');
            
            $exitCode = 0;
            exec($command, $output, $exitCode);
            
            sleep(1);
        }
    }

    protected function removeDirectory(string $dir): void
    {
        if (! is_dir($dir)) {
            return;
        }

        $files = array_diff(scandir($dir), ['.', '..']);
        foreach ($files as $file) {
            $path = $dir . '/' . $file;
            if (is_dir($path)) {
                $this->removeDirectory($path);
            } else {
                unlink($path);
            }
        }
        rmdir($dir);
    }
}
