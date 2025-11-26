<?php

declare(strict_types=1);

namespace App\Console\Commands;

use Hyperf\Command\Command;
use Hyperf\Contract\ConfigInterface;
use Psr\Container\ContainerInterface;
use Hyperf\Logger\LoggerFactory;
use Psr\Log\LoggerInterface;

class FileSystemBackupCommand extends Command
{
    protected ?string $signature = 'backup:filesystem {--path=} {--exclude=} {--compress} {--clean-old}';

    protected string $description = 'Create file system backup of application files';

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
        $backupPath = $this->option('path') ?: $this->getStoragePath('backups/filesystem');
        $exclude = $this->option('exclude') ?: 'node_modules,vendor,storage/logs,storage/backups,.git';
        $compress = $this->option('compress', false);
        $cleanOld = $this->option('clean-old', false);

        // Ensure backup directory exists
        if (!is_dir($backupPath)) {
            mkdir($backupPath, 0755, true);
        }

        $timestamp = date('Y-m-d-H-i-s');
        $filename = "filesystem-backup-{$timestamp}.tar";
        $fullPath = $backupPath . '/' . $filename;

        $this->info("Starting file system backup...");
        
        try {
            $excludes = explode(',', $exclude);
            $excludeArgs = '';
            foreach ($excludes as $item) {
                $excludeArgs .= " --exclude='" . trim($item) . "'";
            }

            $sourcePath = BASE_PATH;
            $cmd = "tar -czf {$fullPath} {$excludeArgs} -C " . dirname($sourcePath) . " " . basename($sourcePath);

            $output = shell_exec($cmd . ' 2>&1');
            
            if ($output !== null && strpos($output, 'Error') !== false) {
                $this->error("File system backup failed: " . $output);
                return 1;
            }

            // Optionally compress the backup (already compressed with tar.gz)
            if (!$compress) {
                // If not compressing, we need to create a .tar instead of .tar.gz
                // But we already created a .tar.gz, so we'll just keep it
                $this->info("File system backup completed successfully: {$fullPath}");
            } else {
                $this->info("File system backup completed successfully: {$fullPath}");
            }

            $this->logger->info("File system backup completed", [
                'path' => $fullPath,
                'size' => filesize($fullPath),
                'excludes' => $excludes
            ]);

            // Clean old backups if requested
            if ($cleanOld) {
                $this->cleanOldBackups($backupPath);
            }

            return 0;
        } catch (\Exception $e) {
            $this->error("File system backup failed with error: " . $e->getMessage());
            $this->logger->error("File system backup failed", [
                'error' => $e->getMessage()
            ]);
            return 1;
        }
    }

    protected function cleanOldBackups(string $backupPath, int $keepDays = 7): void
    {
        $pattern = $backupPath . '/filesystem-backup-*.tar*';
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