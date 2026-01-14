<?php

declare(strict_types=1);

namespace App\Console\Commands;

use Hyperf\Contract\ConfigInterface;
use Hypervel\Console\Command;
use Psr\Container\ContainerInterface;
use Symfony\Component\Console\Input\InputOption;

class DatabaseBackupCommand extends Command
{
    protected ?string $signature = 'backup:database {--connection=} {--path=} {--compress} {--clean-old}';

    protected string $description = 'Create a backup of the database';

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
        $connection = $this->input->getOption('connection') ?: $this->config->get('database.default', 'mysql');
        $backupPath = $this->input->getOption('path') ?: $this->getStoragePath('backups/database');
        $compress = $this->input->getOption('compress');
        $cleanOld = $this->input->getOption('clean-old');

        // Ensure backup directory exists
        if (! is_dir($backupPath)) {
            mkdir($backupPath, 0755, true);
        }

        $databaseConfig = $this->config->get("database.connections.{$connection}", []);

        if (empty($databaseConfig)) {
            $this->output->writeln('<error>Database connection \'' . $connection . '\' not found in configuration.</error>');
            return 1;
        }

        $driver = $databaseConfig['driver'] ?? 'mysql';
        $filename = $this->generateFilename($connection, $driver);

        $this->output->writeln('<info>Starting backup for database connection: ' . $connection . '</info>');
        $this->output->writeln('<info>Backup path: ' . $backupPath . '/' . $filename . '</info>');

        $success = false;

        switch ($driver) {
            case 'mysql':
                $success = $this->backupMysql($databaseConfig, $backupPath, $filename);
                break;
            case 'sqlite':
                $success = $this->backupSqlite($databaseConfig, $backupPath, $filename);
                break;
            default:
                $this->output->writeln('<error>Unsupported database driver: ' . $driver . '</error>');
                return 1;
        }

        if ($success) {
            $this->output->writeln('<info>Database backup completed successfully: ' . $backupPath . '/' . $filename . '</info>');

            // Compress if requested
            if ($compress) {
                $this->compressBackup($backupPath, $filename);
            }

            // Clean old backups if requested
            if ($cleanOld) {
                $this->cleanOldBackups($backupPath, $connection);
            }

            return 0;
        }
        $this->output->writeln('<error>Database backup failed for connection: ' . $connection . '</error>');
        return 1;
    }

    protected function backupMysql(array $config, string $backupPath, string $filename): bool
    {
        $host = $config['host'] ?? 'localhost';
        $port = $config['port'] ?? 3306;
        $database = $config['database'];
        $username = $config['username'];
        $password = $config['password'];

        $command = sprintf(
            'mysqldump --host=%s --port=%s --user=%s --password=%s --single-transaction --routines --triggers %s > %s',
            escapeshellarg($host),
            escapeshellarg((string) $port),
            escapeshellarg($username),
            escapeshellarg($password),
            escapeshellarg($database),
            escapeshellarg("{$backupPath}/{$filename}")
        );

        $this->output->write('Executing mysqldump... ');

        $exitCode = 0;
        $output = [];
        exec($command, $output, $exitCode);

        if ($exitCode === 0) {
            $this->output->writeln('<info>OK</info>');
            return true;
        }
        $this->output->writeln('<error>FAILED</error>');
        return false;
    }

    protected function backupSqlite(array $config, string $backupPath, string $filename): bool
    {
        $databasePath = $config['database'];

        if (! file_exists($databasePath)) {
            $this->output->writeln('<error>SQLite database file does not exist: ' . $databasePath . '</error>');
            return false;
        }

        $this->output->write('Copying SQLite database... ');

        $success = copy($databasePath, "{$backupPath}/{$filename}");

        if ($success) {
            $this->output->writeln('<info>OK</info>');
            return true;
        }
        $this->output->writeln('<error>FAILED</error>');
        return false;
    }

    protected function generateFilename(string $connection, string $driver): string
    {
        $timestamp = date('Y-m-d-H-i-s');
        $extension = $driver === 'mysql' ? 'sql' : 'db';

        return "backup_{$connection}_{$timestamp}.{$extension}";
    }

    protected function compressBackup(string $backupPath, string $filename): void
    {
        $this->output->write('Compressing backup... ');

        $tarFile = $backupPath . '/' . str_replace('.sql', '.tar.gz', $filename);
        $tarFile = str_replace('.db', '.tar.gz', $tarFile);

        $command = sprintf(
            'tar -czf %s -C %s %s',
            escapeshellarg($tarFile),
            escapeshellarg($backupPath),
            escapeshellarg($filename)
        );

        $exitCode = 0;
        exec($command, $output, $exitCode);

        if ($exitCode === 0) {
            // Remove original file after compression
            unlink("{$backupPath}/{$filename}");
            $this->output->writeln('<info>OK</info>');
            $this->output->writeln('<info>Compressed backup created: ' . $tarFile . '</info>');
        } else {
            $this->output->writeln('<error>FAILED</error>');
        }
    }

    protected function cleanOldBackups(string $backupPath, string $connection): void
    {
        $this->output->writeln('<info>Cleaning old backups...</info>');

        // Find all backup files for this connection
        $pattern = $backupPath . '/backup_' . $connection . '_*.sql';
        $sqlFiles = glob($pattern);

        $pattern = $backupPath . '/backup_' . $connection . '_*.db';
        $dbFiles = glob($pattern);

        $pattern = $backupPath . '/backup_' . $connection . '_*.tar.gz';
        $tarFiles = glob($pattern);

        $allFiles = array_merge($sqlFiles, $dbFiles, $tarFiles);

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
        $this->addOption('connection', null, InputOption::VALUE_OPTIONAL, 'Database connection name');
        $this->addOption('path', null, InputOption::VALUE_OPTIONAL, 'Backup storage path');
        $this->addOption('compress', null, InputOption::VALUE_NONE, 'Compress the backup');
        $this->addOption('clean-old', null, InputOption::VALUE_NONE, 'Clean old backups (keep 5 most recent)');
    }
}
