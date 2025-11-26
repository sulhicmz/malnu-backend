<?php

declare(strict_types=1);

namespace App\Console\Commands;

use Hyperf\Command\Command;
use Hyperf\Contract\ConfigInterface;
use Psr\Container\ContainerInterface;
use Hyperf\Logger\LoggerFactory;
use Psr\Log\LoggerInterface;

class ConfigBackupCommand extends Command
{
    protected ?string $signature = 'backup:config {--path=} {--compress} {--clean-old}';

    protected string $description = 'Create configuration backup of environment and settings';

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
        $backupPath = $this->option('path') ?: $this->getStoragePath('backups/config');
        $compress = $this->option('compress', false);
        $cleanOld = $this->option('clean-old', false);

        // Ensure backup directory exists
        if (!is_dir($backupPath)) {
            mkdir($backupPath, 0755, true);
        }

        $timestamp = date('Y-m-d-H-i-s');
        $filename = "config-backup-{$timestamp}.tar";
        $fullPath = $backupPath . '/' . $filename;

        $this->info("Starting configuration backup...");
        
        try {
            // Create a temporary directory to collect configuration files
            $tempDir = $this->getStoragePath('temp/config_backup_' . $timestamp);
            mkdir($tempDir, 0755, true);

            // Copy .env file if it exists
            if (file_exists(BASE_PATH . '/.env')) {
                copy(BASE_PATH . '/.env', $tempDir . '/.env');
            }

            // Copy config directory
            $this->copyDirectory(BASE_PATH . '/config', $tempDir . '/config');

            // Create a backup of important configuration files
            $configFiles = [
                'composer.json',
                'composer.lock',
                'artisan',
                'opencode.json'
            ];

            foreach ($configFiles as $file) {
                if (file_exists(BASE_PATH . '/' . $file)) {
                    copy(BASE_PATH . '/' . $file, $tempDir . '/' . $file);
                }
            }

            // Create tar archive
            $cmd = "tar -czf {$fullPath} -C " . dirname($tempDir) . " " . basename($tempDir);

            $output = shell_exec($cmd . ' 2>&1');
            
            if ($output !== null && strpos($output, 'Error') !== false) {
                $this->error("Configuration backup failed: " . $output);
                return 1;
            }

            // Clean up temporary directory
            $this->removeDirectory($tempDir);

            $this->info("Configuration backup completed successfully: {$fullPath}");
            $this->logger->info("Configuration backup completed", [
                'path' => $fullPath,
                'size' => filesize($fullPath)
            ]);

            // Clean old backups if requested
            if ($cleanOld) {
                $this->cleanOldBackups($backupPath);
            }

            return 0;
        } catch (\Exception $e) {
            $this->error("Configuration backup failed with error: " . $e->getMessage());
            $this->logger->error("Configuration backup failed", [
                'error' => $e->getMessage()
            ]);
            return 1;
        }
    }

    protected function copyDirectory(string $src, string $dst): void
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
                $srcPath = $src . '/' . $file;
                $dstPath = $dst . '/' . $file;

                if (is_dir($srcPath)) {
                    $this->copyDirectory($srcPath, $dstPath);
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

    protected function cleanOldBackups(string $backupPath, int $keepDays = 7): void
    {
        $pattern = $backupPath . '/config-backup-*.tar*';
        $files = glob($pattern);
        
        $cutoffTime = time() - ($keepDays * 24 * 60 * 60); // 7 days ago
        
        foreach ($files as $file) {
            if (filemtime($file) < $cutoffTime) {
                unlink($file);
                $this->info("Removed old backup: {$file}");
                $this->logger->info("Removed old backup", ['file' => $file]);
            }
        }
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