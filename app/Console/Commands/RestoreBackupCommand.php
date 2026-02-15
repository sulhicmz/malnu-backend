<?php

declare(strict_types=1);

namespace App\Console\Commands;

use Exception;
use Hypervel\Contracts\Config\Repository;
use Hypervel\Console\Command;
use Psr\Container\ContainerInterface;
use Symfony\Component\Console\Input\InputOption;
use App\Helpers\ProcessHelper;

class RestoreBackupCommand extends Command
{
    protected ?string $signature = 'restore:backup {backup-file} {--type=} {--connection=} {--force}';

    protected string $description = 'Restore a backup of database, file system, or configuration';

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
        $backupFile = $this->input->getArgument('backup-file');
        $type = $this->input->getOption('type') ?: 'all';
        $connection = $this->input->getOption('connection') ?: $this->config->get('database.default', 'mysql');
        $force = $this->input->getOption('force');

        if (! file_exists($backupFile)) {
            $this->output->writeln('<error>Backup file does not exist: ' . $backupFile . '</error>');
            return 1;
        }

        if (! $force) {
            $confirmation = $this->output->ask('<question>This will overwrite existing data. Are you sure? (y/N)</question>', 'N');
            if (strtolower($confirmation) !== 'y') {
                $this->output->writeln('<info>Restore operation cancelled.</info>');
                return 0;
            }
        }

        $this->output->writeln('<info>Starting restore operation...</info>');
        $this->output->writeln('<info>Backup file: ' . $backupFile . '</info>');
        $this->output->writeln('<info>Restore type: ' . $type . '</info>');

        // Create a temporary directory for extraction
        $tempDir = base_path('storage/temp_restore_' . uniqid());
        mkdir($tempDir, 0755, true);

        try {
            // Extract the backup file
            $this->output->write('Extracting backup... ');
            $extractSuccess = $this->extractBackup($backupFile, $tempDir);

            if (! $extractSuccess) {
                $this->output->writeln('<error>FAILED</error>');
                $this->removeDirectory($tempDir);
                return 1;
            }

            $this->output->writeln('<info>OK</info>');

            $success = false;

            switch ($type) {
                case 'database':
                    $success = $this->restoreDatabase($tempDir, $connection);
                    break;
                case 'filesystem':
                    $success = $this->restoreFileSystem($tempDir);
                    break;
                case 'config':
                    $success = $this->restoreConfiguration($tempDir);
                    break;
                case 'all':
                default:
                    $dbSuccess = $this->restoreDatabase($tempDir, $connection);
                    $fsSuccess = $this->restoreFileSystem($tempDir);
                    $configSuccess = $this->restoreConfiguration($tempDir);

                    $success = $dbSuccess && $fsSuccess && $configSuccess;
                    break;
            }

            // Clean up temporary directory
            $this->removeDirectory($tempDir);

            if ($success) {
                $this->output->writeln('<info>Restore completed successfully!</info>');
                return 0;
            }
            $this->output->writeln('<error>Restore failed!</error>');
            return 1;
        } catch (Exception $e) {
            // Clean up temporary directory in case of error
            $this->removeDirectory($tempDir);
            $this->output->writeln('<error>Restore failed: ' . $e->getMessage() . '</error>');
            return 1;
        }
    }

    protected function extractBackup(string $backupFile, string $tempDir): bool
    {
        $result = ProcessHelper::execute('tar', ['-xzf', $backupFile, '-C', $tempDir]);

        return $result['successful'];
    }

    protected function restoreDatabase(string $tempDir, string $connection): bool
    {
        $databaseConfig = $this->config->get("database.connections.{$connection}", []);

        if (empty($databaseConfig)) {
            $this->output->writeln('<error>Database connection \'' . $connection . '\' not found in configuration.</error>');
            return false;
        }

        $driver = $databaseConfig['driver'] ?? 'mysql';

        // Find the database backup file
        $dbBackupFile = null;
        $files = scandir($tempDir);

        foreach ($files as $file) {
            if (strpos($file, 'db_backup_') === 0 && (pathinfo($file, PATHINFO_EXTENSION) === 'sql' || pathinfo($file, PATHINFO_EXTENSION) === 'db')) {
                $dbBackupFile = $tempDir . '/' . $file;
                break;
            }
        }

        if (! $dbBackupFile) {
            // Check in subdirectories
            if (is_dir($tempDir . '/database')) {
                $subFiles = scandir($tempDir . '/database');
                foreach ($subFiles as $file) {
                    if (strpos($file, 'db_backup_') === 0 && (pathinfo($file, PATHINFO_EXTENSION) === 'sql' || pathinfo($file, PATHINFO_EXTENSION) === 'db')) {
                        $dbBackupFile = $tempDir . '/database/' . $file;
                        break;
                    }
                }
            }

            if (! $dbBackupFile && is_dir($tempDir . '/db')) {
                $subFiles = scandir($tempDir . '/db');
                foreach ($subFiles as $file) {
                    if (strpos($file, 'db_backup_') === 0 && (pathinfo($file, PATHINFO_EXTENSION) === 'sql' || pathinfo($file, PATHINFO_EXTENSION) === 'db')) {
                        $dbBackupFile = $tempDir . '/db/' . $file;
                        break;
                    }
                }
            }
        }

        if (! $dbBackupFile) {
            $this->output->writeln('<comment>No database backup file found, skipping database restore</comment>');
            return true; // Not a failure, just nothing to restore
        }

        $this->output->write('Restoring database... ');

        $success = false;

        switch ($driver) {
            case 'mysql':
                $success = $this->restoreMysql($dbBackupFile, $databaseConfig);
                break;
            case 'sqlite':
                $success = $this->restoreSqlite($dbBackupFile, $databaseConfig);
                break;
            default:
                $this->output->writeln('<error>Unsupported database driver: ' . $driver . '</error>');
                return false;
        }

        if ($success) {
            $this->output->writeln('<info>OK</info>');
            return true;
        }
        $this->output->writeln('<error>FAILED</error>');
        return false;
    }

    protected function restoreMysql(string $backupFile, array $config): bool
    {
        $host = $config['host'] ?? 'localhost';
        $port = $config['port'] ?? 3306;
        $database = $config['database'];
        $username = $config['username'];
        $password = $config['password'];

        $result = ProcessHelper::execute('mysql', [
            '--host=' . $host,
            '--port=' . (string) $port,
            '--user=' . $username,
            '--password=' . $password,
            $database,
            '<',
            $backupFile
        ]);

        return $result['successful'];
    }

    protected function restoreSqlite(string $backupFile, array $config): bool
    {
        $databasePath = $config['database'];

        // For SQLite, we need to restore the database file
        return copy($backupFile, $databasePath);
    }

    protected function restoreFileSystem(string $tempDir): bool
    {
        $fsBackupDir = null;

        // Check for filesystem backup in different possible locations
        if (is_dir($tempDir . '/filesystem')) {
            $fsBackupDir = $tempDir . '/filesystem';
        } elseif (file_exists($tempDir . '/filesystem_backup.tar.gz')) {
            $extractDir = $tempDir . '/extracted_fs';
            mkdir($extractDir, 0755, true);

            $result = ProcessHelper::execute('tar', ['-xzf', $tempDir . '/filesystem_backup.tar.gz', '-C', $extractDir]);

            if ($result['successful']) {
                $fsBackupDir = $extractDir;
            }
        } elseif (is_dir($tempDir . '/app') || is_dir($tempDir . '/config')) {
            // The backup might be directly in the temp directory
            $fsBackupDir = $tempDir;
        }

        if (! $fsBackupDir) {
            $this->output->writeln('<comment>No filesystem backup found, skipping filesystem restore</comment>');
            return true; // Not a failure, just nothing to restore
        }

        $this->output->write('Restoring file system... ');

        // Define directories to restore
        $restoreDirs = ['app', 'config', 'database', 'resources', 'tests'];

        foreach ($restoreDirs as $dir) {
            $src = $fsBackupDir . '/' . $dir;
            $dst = base_path('/') . $dir;

            if (file_exists($src)) {
                // Remove the destination directory if it exists
                if (is_dir($dst)) {
                    $this->removeDirectory($dst);
                }

                // Copy the directory
                $this->copyDirectory($src, $dst);
            }
        }

        $this->output->writeln('<info>OK</info>');
        return true;
    }

    protected function restoreConfiguration(string $tempDir): bool
    {
        $configBackupDir = null;

        // Check for config backup in different possible locations
        if (is_dir($tempDir . '/config')) {
            $configBackupDir = $tempDir . '/config';
        } elseif (is_dir($tempDir . '/configuration')) {
            $configBackupDir = $tempDir . '/configuration';
        } elseif (is_dir($tempDir . '/cfg')) {
            $configBackupDir = $tempDir . '/cfg';
        } else {
            $configBackupDir = $tempDir;
        }

        if (! is_dir($configBackupDir)) {
            $this->output->writeln('<comment>No configuration backup found, skipping config restore</comment>');
            return true; // Not a failure, just nothing to restore
        }

        $this->output->write('Restoring configuration... ');

        // Restore .env file if it exists in backup
        if (file_exists($configBackupDir . '/.env')) {
            copy($configBackupDir . '/.env', base_path('.env'));
        }

        // Restore config directory if it exists in backup
        if (is_dir($configBackupDir . '/config')) {
            $dst = base_path('config');
            if (is_dir($dst)) {
                $this->removeDirectory($dst);
            }
            $this->copyDirectory($configBackupDir . '/config', $dst);
        }

        // Restore database migrations if they exist in backup
        if (is_dir($configBackupDir . '/database/migrations')) {
            $dst = base_path('database/migrations');
            if (is_dir($dst)) {
                $this->removeDirectory($dst);
            }
            mkdir($dst, 0755, true);
            $this->copyDirectory($configBackupDir . '/database/migrations', $dst);
        }

        // Restore database seeders if they exist in backup
        if (is_dir($configBackupDir . '/database/seeders')) {
            $dst = base_path('database/seeders');
            if (is_dir($dst)) {
                $this->removeDirectory($dst);
            }
            mkdir($dst, 0755, true);
            $this->copyDirectory($configBackupDir . '/database/seeders', $dst);
        }

        $this->output->writeln('<info>OK</info>');
        return true;
    }

    protected function copyDirectory(string $src, string $dst): void
    {
        if (! is_dir($src)) {
            return;
        }

        if (! is_dir($dst)) {
            mkdir($dst, 0755, true);
        }

        $dir = opendir($src);
        while (($file = readdir($dir)) !== false) {
            if ($file == '.' || $file == '..') {
                continue;
            }

            $srcFile = $src . '/' . $file;
            $dstFile = $dst . '/' . $file;

            if (is_dir($srcFile)) {
                $this->copyDirectory($srcFile, $dstFile);
            } else {
                copy($srcFile, $dstFile);
            }
        }
        closedir($dir);
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

    protected function configure(): void
    {
        $this->addOption('type', null, InputOption::VALUE_OPTIONAL, 'Type of restore: database, filesystem, config, or all (default: all)', 'all');
        $this->addOption('connection', null, InputOption::VALUE_OPTIONAL, 'Database connection name');
        $this->addOption('force', null, InputOption::VALUE_NONE, 'Force restore without confirmation');
    }
}
