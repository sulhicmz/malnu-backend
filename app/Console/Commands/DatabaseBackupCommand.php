<?php

declare(strict_types=1);

namespace App\Console\Commands;

use Hyperf\Command\Command;
use Hyperf\Contract\ConfigInterface;
use Psr\Container\ContainerInterface;
use Hyperf\Logger\LoggerFactory;
use Psr\Log\LoggerInterface;

class DatabaseBackupCommand extends Command
{
    protected ?string $signature = 'backup:database {--database=} {--path=} {--compress} {--clean-old}';

    protected string $description = 'Create database backup for MySQL or SQLite';

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
        $database = $this->option('database') ?: $this->config->get('database.default', 'mysql');
        $backupPath = $this->option('path') ?: $this->getStoragePath('backups/database');
        $compress = $this->option('compress', false);
        $cleanOld = $this->option('clean-old', false);

        // Ensure backup directory exists
        if (!is_dir($backupPath)) {
            mkdir($backupPath, 0755, true);
        }

        $timestamp = date('Y-m-d-H-i-s');
        $filename = "database-backup-{$database}-{$timestamp}.sql";
        $fullPath = $backupPath . '/' . $filename;

        $this->info("Starting database backup for {$database}...");
        
        try {
            $success = false;
            
            if ($database === 'mysql') {
                $success = $this->backupMysql($fullPath);
            } elseif ($database === 'sqlite') {
                $success = $this->backupSqlite($fullPath);
            } else {
                $this->error("Unsupported database type: {$database}");
                return 1;
            }

            if ($success) {
                // Optionally compress the backup
                if ($compress) {
                    $this->compressBackup($fullPath);
                    $fullPath .= '.gz';
                    $filename .= '.gz';
                }

                $this->info("Database backup completed successfully: {$fullPath}");
                $this->logger->info("Database backup completed", [
                    'database' => $database,
                    'path' => $fullPath,
                    'size' => filesize($fullPath)
                ]);

                // Clean old backups if requested
                if ($cleanOld) {
                    $this->cleanOldBackups($backupPath, $database);
                }

                return 0;
            } else {
                $this->error("Database backup failed");
                $this->logger->error("Database backup failed", ['database' => $database]);
                return 1;
            }
        } catch (\Exception $e) {
            $this->error("Database backup failed with error: " . $e->getMessage());
            $this->logger->error("Database backup failed", [
                'database' => $database,
                'error' => $e->getMessage()
            ]);
            return 1;
        }
    }

    protected function backupMysql(string $path): bool
    {
        $host = $this->config->get('database.connections.mysql.host', 'localhost');
        $databaseName = $this->config->get('database.connections.mysql.database');
        $username = $this->config->get('database.connections.mysql.username');
        $password = $this->config->get('database.connections.mysql.password');
        $port = $this->config->get('database.connections.mysql.port', 3306);

        $cmd = "mysqldump --host={$host} --port={$port} --user={$username} --password={$password} --single-transaction --routines --triggers --hex-blob --opt --quote-names {$databaseName}";

        $output = shell_exec($cmd . ' 2>&1');
        
        if ($output === null || strpos($output, 'Error') !== false) {
            $this->error("MySQL dump failed: " . $output);
            return false;
        }

        file_put_contents($path, $output);
        return true;
    }

    protected function backupSqlite(string $path): bool
    {
        $databasePath = $this->config->get('database.connections.sqlite.database');
        
        if (!file_exists($databasePath)) {
            $this->error("SQLite database file does not exist: {$databasePath}");
            return false;
        }

        // Copy the SQLite database file
        $success = copy($databasePath, $path);
        
        if (!$success) {
            $this->error("Failed to copy SQLite database: {$databasePath}");
            return false;
        }

        return true;
    }

    protected function compressBackup(string $path): void
    {
        $gzPath = $path . '.gz';
        $bufferSize = 4096;
        
        $file = fopen($path, 'rb');
        $gzFile = gzopen($gzPath, 'wb9');
        
        if ($file && $gzFile) {
            while (!feof($file)) {
                gzwrite($gzFile, fread($file, $bufferSize));
            }
            fclose($file);
            gzclose($gzFile);
            
            // Remove original file after compression
            unlink($path);
        }
    }

    protected function cleanOldBackups(string $backupPath, string $database, int $keepDays = 7): void
    {
        $pattern = $backupPath . '/database-backup-' . $database . '-*.sql*';
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