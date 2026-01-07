<?php

declare(strict_types=1);

namespace App\Console\Commands;

use Hypervel\Console\Command;
use Hyperf\Contract\ConfigInterface;
use Psr\Container\ContainerInterface;
use Symfony\Component\Console\Input\InputOption;

class FileSystemBackupCommand extends Command
{
    protected ?string $signature = 'backup:filesystem {--include=} {--exclude=} {--path=} {--compress} {--clean-old}';

    protected string $description = 'Create a backup of the file system';

    protected ContainerInterface $container;

    protected ConfigInterface $config;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
        $this->config = $container->get(ConfigInterface::class);
        parent::__construct();
    }

    public function handle()
    {
        $include = $this->input->getOption('include') ?: 'app,config,database,resources,storage,tests';
        $exclude = $this->input->getOption('exclude') ?: 'node_modules,vendor,.git,.idea,.vscode,storage/logs,storage/framework/cache';
        $backupPath = $this->input->getOption('path') ?: $this->getStoragePath('backups/filesystem');
        $compress = $this->input->getOption('compress');
        $cleanOld = $this->input->getOption('clean-old');

        // Ensure backup directory exists
        if (!is_dir($backupPath)) {
            mkdir($backupPath, 0755, true);
        }

        $filename = $this->generateFilename();

        $this->output->writeln('<info>Starting file system backup...</info>');
        $this->output->writeln('<info>Backup path: ' . $backupPath . '/' . $filename . '</info>');

        $success = $this->createFileSystemBackup($include, $exclude, $backupPath, $filename);

        if ($success) {
            $this->output->writeln('<info>File system backup completed successfully: ' . $backupPath . '/' . $filename . '</info>');

            // Compress if requested
            if ($compress) {
                $this->compressBackup($backupPath, $filename);
            }

            // Clean old backups if requested
            if ($cleanOld) {
                $this->cleanOldBackups($backupPath);
            }

            return 0;
        } else {
            $this->output->writeln('<error>File system backup failed</error>');
            return 1;
        }
    }

    protected function createFileSystemBackup(string $include, string $exclude, string $backupPath, string $filename): bool
    {
        $includePaths = array_map('trim', explode(',', $include));
        $excludePaths = array_map('trim', explode(',', $exclude));
        
        $tarCommand = 'tar -czf ' . escapeshellarg($backupPath . '/' . $filename) . ' ';
        
        // Add exclude patterns
        foreach ($excludePaths as $excludePath) {
            $tarCommand .= '--exclude=' . escapeshellarg($excludePath) . ' ';
        }
        
        // Add include paths
        foreach ($includePaths as $includePath) {
            $fullPath = base_path('/') .$includePath;
            if (is_dir($fullPath) || file_exists($fullPath)) {
                $tarCommand .= escapeshellarg($includePath) . ' ';
            }
        }
        
        $this->output->write('Creating file system backup... ');
        
        $exitCode = 0;
        $output = [];
        exec($tarCommand, $output, $exitCode);

        if ($exitCode === 0) {
            $this->output->writeln('<info>OK</info>');
            return true;
        } else {
            $this->output->writeln('<error>FAILED</error>');
            return false;
        }
    }

    protected function generateFilename(): string
    {
        $timestamp = date('Y-m-d-H-i-s');
        return "filesystem_backup_{$timestamp}.tar.gz";
    }

    protected function compressBackup(string $backupPath, string $filename): void
    {
        // The backup is already compressed as .tar.gz
        $this->output->writeln('<info>File is already compressed as .tar.gz</info>');
    }

    protected function cleanOldBackups(string $backupPath): void
    {
        $this->output->writeln('<info>Cleaning old backups...</info>');
        
        // Find all filesystem backup files
        $pattern = $backupPath . '/filesystem_backup_*.tar.gz';
        $allFiles = glob($pattern);
        
        // Sort by modification time (newest first)
        usort($allFiles, function ($a, $b) {
            return filemtime($b) - filemtime($a);
        });
        
        // Keep only the 5 most recent backups
        $filesToDelete = array_slice($allFiles, 5);
        
        foreach ($filesToDelete as $file) {
            unlink($file);
            $this->output->writeln('<info>Deleted old backup: ' . $file . '</info>');
        }
        
        $this->output->writeln('<info>Old backup cleanup completed.</info>');
    }

    protected function getStoragePath(string $path = ''): string
    {
        $storagePath = base_path('storage');
        if ($path) {
            $storagePath .= '/' . ltrim($path, '/');
        }
        return $storagePath;
    }

    protected function configure(): void
    {
        $this->addOption('include', null, InputOption::VALUE_OPTIONAL, 'Comma-separated list of directories/files to include (default: app,config,database,resources,storage,tests)');
        $this->addOption('exclude', null, InputOption::VALUE_OPTIONAL, 'Comma-separated list of directories/files to exclude (default: node_modules,vendor,.git,.idea,.vscode,storage/logs,storage/framework/cache)');
        $this->addOption('path', null, InputOption::VALUE_OPTIONAL, 'Backup storage path');
        $this->addOption('compress', null, InputOption::VALUE_NONE, 'Compress the backup (already compressed as tar.gz)');
        $this->addOption('clean-old', null, InputOption::VALUE_NONE, 'Clean old backups (keep 5 most recent)');
    }
}