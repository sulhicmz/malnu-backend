<?php

declare(strict_types=1);

namespace App\Console\Commands;

use Hyperf\Command\Command;
use Hyperf\Contract\ConfigInterface;
use Psr\Container\ContainerInterface;
use Hyperf\Logger\LoggerFactory;
use Psr\Log\LoggerInterface;

class RestoreCommand extends Command
{
    protected ?string $signature = 'restore:backup {--type=} {--backup-file=} {--force}';

    protected string $description = 'Restore system from backup';

    protected ContainerInterface $container;

    protected ConfigInterface $config;

    protected LoggerInterface $logger;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
        parent::__construct();
        $this->config = $container->get(ConfigInterface::class);
        $this->logger = $container->get(LoggerFactory::class)->get('backup');
    }

    public function handle()
    {
        $type = $this->option('type') ?: 'all';
        $backupFile = $this->option('backup-file');
        $force = $this->option('force', false);

        if (!$force) {
            $confirmed = $this->confirm('This will overwrite existing data. Are you sure you want to proceed?', false);
            if (!$confirmed) {
                $this->info('Restore operation cancelled.');
                return 0;
            }
        }

        $this->info("Starting restore operation for type: {$type}...");

        try {
            switch ($type) {
                case 'database':
                    $result = $this->restoreDatabase($backupFile);
                    break;
                case 'filesystem':
                    $result = $this->restoreFilesystem($backupFile);
                    break;
                case 'config':
                    $result = $this->restoreConfig($backupFile);
                    break;
                case 'all':
                    $result = $this->restoreAll($backupFile);
                    break;
                default:
                    $this->error("Invalid restore type: {$type}. Supported types: database, filesystem, config, all");
                    return 1;
            }

            if ($result) {
                $this->info("Restore operation completed successfully for type: {$type}");
                $this->logger->info("Restore completed", [
                    'type' => $type,
                    'backup_file' => $backupFile
                ]);
                return 0;
            } else {
                $this->error("Restore operation failed for type: {$type}");
                $this->logger->error("Restore failed", [
                    'type' => $type,
                    'backup_file' => $backupFile
                ]);
                return 1;
            }
        } catch (\Exception $e) {
            $this->error("Restore operation failed with error: " . $e->getMessage());
            $this->logger->error("Restore failed", [
                'type' => $type,
                'error' => $e->getMessage(),
                'backup_file' => $backupFile
            ]);
            return 1;
        }
    }

    protected function restoreDatabase(?string $backupFile): bool
    {
        if (!$backupFile) {
            // Find the latest database backup
            $backupPath = $this->getStoragePath('backups/database');
            $files = glob($backupPath . '/database-backup-*.sql*');
            if (empty($files)) {
                $this->error("No database backup files found in {$backupPath}");
                return false;
            }
            $backupFile = end($files);
        }

        if (!file_exists($backupFile)) {
            $this->error("Backup file does not exist: {$backupFile}");
            return false;
        }

        $database = $this->config->get('database.default', 'mysql');

        if ($database === 'mysql') {
            return $this->restoreMysql($backupFile);
        } elseif ($database === 'sqlite') {
            return $this->restoreSqlite($backupFile);
        } else {
            $this->error("Unsupported database type: {$database}");
            return false;
        }
    }

    protected function restoreMysql(string $backupFile): bool
    {
        $host = $this->config->get('database.connections.mysql.host', 'localhost');
        $databaseName = $this->config->get('database.connections.mysql.database');
        $username = $this->config->get('database.connections.mysql.username');
        $password = $this->config->get('database.connections.mysql.password');
        $port = $this->config->get('database.connections.mysql.port', 3306);

        // Handle compressed files
        $tempFile = $backupFile;
        if (substr($backupFile, -3) === '.gz') {
            $tempFile = tempnam(sys_get_temp_dir(), 'restore_');
            $this->decompressFile($backupFile, $tempFile);
        }

        $cmd = "mysql --host={$host} --port={$port} --user={$username} --password={$password} {$databaseName} < {$tempFile}";

        $output = shell_exec($cmd . ' 2>&1');
        
        // Clean up temp file if we created one
        if ($tempFile !== $backupFile) {
            unlink($tempFile);
        }

        if ($output !== null && strpos($output, 'Error') !== false) {
            $this->error("MySQL restore failed: " . $output);
            return false;
        }

        $this->info("MySQL database restored successfully from: {$backupFile}");
        return true;
    }

    protected function restoreSqlite(string $backupFile): bool
    {
        $databasePath = $this->config->get('database.connections.sqlite.database');
        
        // Handle compressed files
        if (substr($backupFile, -3) === '.gz') {
            $this->decompressFile($backupFile, $databasePath);
        } else {
            $success = copy($backupFile, $databasePath);
            if (!$success) {
                $this->error("Failed to copy SQLite database: {$backupFile} to {$databasePath}");
                return false;
            }
        }

        $this->info("SQLite database restored successfully to: {$databasePath}");
        return true;
    }

    protected function restoreFilesystem(?string $backupFile): bool
    {
        if (!$backupFile) {
            // Find the latest filesystem backup
            $backupPath = $this->getStoragePath('backups/filesystem');
            $files = glob($backupPath . '/filesystem-backup-*.tar*');
            if (empty($files)) {
                $this->error("No filesystem backup files found in {$backupPath}");
                return false;
            }
            $backupFile = end($files);
        }

        if (!file_exists($backupFile)) {
            $this->error("Backup file does not exist: {$backupFile}");
            return false;
        }

        // Create temporary directory for extraction
        $tempDir = $this->getStoragePath('temp/restore_' . date('Y-m-d-H-i-s'));
        mkdir($tempDir, 0755, true);

        // Extract the backup
        $cmd = "tar -xzf {$backupFile} -C {$tempDir}";
        $output = shell_exec($cmd . ' 2>&1');

        if ($output !== null && strpos($output, 'Error') !== false) {
            $this->error("File system extraction failed: " . $output);
            $this->removeDirectory($tempDir);
            return false;
        }

        // Find the extracted directory (it will be named with timestamp)
        $extractedDir = null;
        $tempFiles = scandir($tempDir);
        foreach ($tempFiles as $file) {
            if ($file !== '.' && $file !== '..') {
                $extractedDir = $tempDir . '/' . $file;
                break;
            }
        }

        if (!$extractedDir || !is_dir($extractedDir)) {
            $this->error("Could not find extracted directory in {$tempDir}");
            $this->removeDirectory($tempDir);
            return false;
        }

        // Copy files back to the application (excluding some directories)
        $this->copyDirectory($extractedDir, BASE_PATH, ['storage', 'vendor']);

        // Clean up
        $this->removeDirectory($tempDir);

        $this->info("File system restored successfully from: {$backupFile}");
        return true;
    }

    protected function restoreConfig(?string $backupFile): bool
    {
        if (!$backupFile) {
            // Find the latest config backup
            $backupPath = $this->getStoragePath('backups/config');
            $files = glob($backupPath . '/config-backup-*.tar*');
            if (empty($files)) {
                $this->error("No config backup files found in {$backupPath}");
                return false;
            }
            $backupFile = end($files);
        }

        if (!file_exists($backupFile)) {
            $this->error("Backup file does not exist: {$backupFile}");
            return false;
        }

        // Create temporary directory for extraction
        $tempDir = $this->getStoragePath('temp/config_restore_' . date('Y-m-d-H-i-s'));
        mkdir($tempDir, 0755, true);

        // Extract the backup
        $cmd = "tar -xzf {$backupFile} -C {$tempDir}";
        $output = shell_exec($cmd . ' 2>&1');

        if ($output !== null && strpos($output, 'Error') !== false) {
            $this->error("Config extraction failed: " . $output);
            $this->removeDirectory($tempDir);
            return false;
        }

        // Find the extracted directory
        $extractedDir = null;
        $tempFiles = scandir($tempDir);
        foreach ($tempFiles as $file) {
            if ($file !== '.' && $file !== '..') {
                $extractedDir = $tempDir . '/' . $file;
                break;
            }
        }

        if (!$extractedDir || !is_dir($extractedDir)) {
            $this->error("Could not find extracted directory in {$tempDir}");
            $this->removeDirectory($tempDir);
            return false;
        }

        // Restore .env file if it exists in backup
        if (file_exists($extractedDir . '/.env')) {
            copy($extractedDir . '/.env', BASE_PATH . '/.env');
            $this->info("Restored .env file");
        }

        // Restore config directory
        $this->copyDirectory($extractedDir . '/config', BASE_PATH . '/config');

        // Clean up
        $this->removeDirectory($tempDir);

        $this->info("Configuration restored successfully from: {$backupFile}");
        return true;
    }

    protected function restoreAll(?string $backupFile): bool
    {
        // For 'all' type, we expect a specific naming convention or we restore latest of each type
        $this->info("Restoring all components...");
        
        // First restore config
        if (!$this->restoreConfig(null)) {
            $this->error("Config restore failed, aborting full restore");
            return false;
        }

        // Then restore database
        if (!$this->restoreDatabase(null)) {
            $this->error("Database restore failed, aborting full restore");
            return false;
        }

        // Finally restore filesystem
        if (!$this->restoreFilesystem(null)) {
            $this->error("File system restore failed");
            return false;
        }

        return true;
    }

    protected function decompressFile(string $compressedFile, string $outputFile): bool
    {
        $gzFile = gzopen($compressedFile, 'rb');
        if (!$gzFile) {
            return false;
        }

        $file = fopen($outputFile, 'wb');
        if (!$file) {
            gzclose($gzFile);
            return false;
        }

        while (!gzeof($gzFile)) {
            fwrite($file, gzread($gzFile, 4096));
        }

        fclose($file);
        gzclose($gzFile);

        return true;
    }

    protected function copyDirectory(string $src, string $dst, array $excludeDirs = []): void
    {
        if (!is_dir($src)) {
            return;
        }

        if (!is_dir($dst)) {
            mkdir($dst, 0755, true);
        }

        $files = scandir($src);
        foreach ($files as $file) {
            if ($file !== '.' && $file !== '..') {
                // Skip excluded directories
                if (in_array($file, $excludeDirs)) {
                    continue;
                }
                
                $srcPath = $src . '/' . $file;
                $dstPath = $dst . '/' . $file;

                if (is_dir($srcPath)) {
                    $this->copyDirectory($srcPath, $dstPath, $excludeDirs);
                } else {
                    copy($srcPath, $dstPath);
                }
            }
        }
    }

    protected function removeDirectory(string $dir): void
    {
        if (!is_dir($dir)) {
            return;
        }

        $files = array_diff(scandir($dir), ['.', '..']);
        foreach ($files as $file) {
            $path = $dir . '/' . $file;
            is_dir($path) ? $this->removeDirectory($path) : unlink($path);
        }
        rmdir($dir);
    }

    private function getStoragePath(string $subPath = ''): string
    {
        $storagePath = BASE_PATH . '/storage';
        if ($subPath) {
            $storagePath .= '/' . ltrim($subPath, '/');
        }
        return $storagePath;
    }
}