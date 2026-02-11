<?php

declare(strict_types=1);

namespace App\Console\Commands;

use Exception;
use Hyperf\Contract\ConfigInterface;
use Hypervel\Console\Command;
use Psr\Container\ContainerInterface;
use Symfony\Component\Console\Input\InputOption;
use App\Helpers\ProcessHelper;

class ComprehensiveBackupCommand extends Command
{
    protected ?string $signature = 'backup:all {--connection=} {--path=} {--compress} {--clean-old} {--no-db} {--no-fs} {--no-config}';

    protected string $description = 'Create a comprehensive backup of database, file system, and configuration';

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
        $backupPath = $this->input->getOption('path') ?: $this->getStoragePath('backups');
        $compress = $this->input->getOption('compress');
        $cleanOld = $this->input->getOption('clean-old');
        $skipDb = $this->input->getOption('no-db');
        $skipFs = $this->input->getOption('no-fs');
        $skipConfig = $this->input->getOption('no-config');

        // Ensure backup directory exists
        if (! is_dir($backupPath)) {
            mkdir($backupPath, 0755, true);
        }

        $timestamp = date('Y-m-d-H-i-s');
        $mainBackupDir = $backupPath . '/full_backup_' . $timestamp;
        mkdir($mainBackupDir, 0755, true);

        $this->output->writeln('<info>Starting comprehensive backup...</info>');
        $this->output->writeln('<info>Main backup directory: ' . $mainBackupDir . '</info>');

        $results = [
            'database' => false,
            'filesystem' => false,
            'configuration' => false,
        ];

        // Backup database if not skipped
        if (! $skipDb) {
            $this->output->writeln('<info>Backing up database...</info>');
            $results['database'] = $this->backupDatabase($connection, $mainBackupDir);
        } else {
            $this->output->writeln('<comment>Database backup skipped</comment>');
        }

        // Backup file system if not skipped
        if (! $skipFs) {
            $this->output->writeln('<info>Backing up file system...</info>');
            $results['filesystem'] = $this->backupFileSystem($mainBackupDir);
        } else {
            $this->output->writeln('<comment>File system backup skipped</comment>');
        }

        // Backup configuration if not skipped
        if (! $skipConfig) {
            $this->output->writeln('<info>Backing up configuration...</info>');
            $results['configuration'] = $this->backupConfiguration($mainBackupDir);
        } else {
            $this->output->writeln('<comment>Configuration backup skipped</comment>');
        }

        // Create a summary of the backup
        $this->createBackupSummary($mainBackupDir, $results, $timestamp);

        // Create main backup archive
        $finalFilename = "full_backup_{$timestamp}.tar.gz";

        $this->output->write('Creating main backup archive... ');

        $result = ProcessHelper::execute('tar', ['-czf', $backupPath . '/' . $finalFilename, '-C', $mainBackupDir]);

        if ($result['successful']) {
            $this->output->writeln('<info>OK</info>');

            // Clean up temporary directory
            $this->removeDirectory($mainBackupDir);

            $this->output->writeln('<info>Full backup completed successfully: ' . $backupPath . '/' . $finalFilename . '</info>');

            // Clean old backups if requested
            if ($cleanOld) {
                $this->cleanOldBackups($backupPath);
            }

            return 0;
        }
        $this->output->writeln('<error>FAILED to create main backup archive</error>');

        // Clean up temporary directory
        $this->removeDirectory($mainBackupDir);

        return 1;
    }

    protected function backupDatabase(string $connection, string $backupDir): bool
    {
        $databaseConfig = $this->config->get("database.connections.{$connection}", []);

        if (empty($databaseConfig)) {
            $this->output->writeln('<error>Database connection \'' . $connection . '\' not found in configuration.</error>');
            return false;
        }

        $driver = $databaseConfig['driver'] ?? 'mysql';
        $filename = $this->generateDatabaseFilename($connection, $driver);

        $success = false;

        switch ($driver) {
            case 'mysql':
                $success = $this->backupMysql($databaseConfig, $backupDir, $filename);
                break;
            case 'sqlite':
                $success = $this->backupSqlite($databaseConfig, $backupDir, $filename);
                break;
            default:
                $this->output->writeln('<error>Unsupported database driver: ' . $driver . '</error>');
                return false;
        }

        if ($success) {
            $this->output->writeln('<info>Database backup completed: ' . $filename . '</info>');
            return true;
        }
        $this->output->writeln('<error>Database backup failed</error>');
        return false;
    }

    protected function backupMysql(array $config, string $backupDir, string $filename): bool
    {
        $host = $config['host'] ?? 'localhost';
        $port = $config['port'] ?? 3306;
        $database = $config['database'];
        $username = $config['username'];
        $password = $config['password'];

        $arguments = [
            '--host=' . $host,
            '--port=' . (string) $port,
            '--user=' . $username,
            '--password=' . $password,
            '--single-transaction',
            '--routines',
            '--triggers',
            $database,
            '>',
            "{$backupDir}/{$filename}"
        ];

        $this->output->write('Executing mysqldump... ');

        $result = ProcessHelper::execute('mysqldump', $arguments);

        if ($result['successful']) {
            $this->output->writeln('<info>OK</info>');
            return true;
        }
        $this->output->writeln('<error>FAILED</error>');
        return false;
    }

    protected function backupSqlite(array $config, string $backupDir, string $filename): bool
    {
        $databasePath = $config['database'];

        if (! file_exists($databasePath)) {
            $this->output->writeln('<error>SQLite database file does not exist: ' . $databasePath . '</error>');
            return false;
        }

        $this->output->write('Copying SQLite database... ');

        $success = copy($databasePath, "{$backupDir}/{$filename}");

        if ($success) {
            $this->output->writeln('<info>OK</info>');
            return true;
        }
        $this->output->writeln('<error>FAILED</error>');
        return false;
    }

    protected function backupFileSystem(string $backupDir): bool
    {
        $fsBackupDir = $backupDir . '/filesystem';
        mkdir($fsBackupDir, 0755, true);

        $includePaths = ['app', 'config', 'database', 'resources', 'tests'];
        $excludePaths = ['node_modules', 'vendor', '.git', '.idea', '.vscode', 'storage/logs', 'storage/framework/cache'];

        $arguments = ['-czf', $fsBackupDir . '/filesystem_backup.tar.gz'];

        // Add exclude patterns
        foreach ($excludePaths as $excludePath) {
            $arguments[] = '--exclude=' . $excludePath;
        }

        // Add include paths
        foreach ($includePaths as $includePath) {
            $fullPath = base_path('/') . $includePath;
            if (is_dir($fullPath) || file_exists($fullPath)) {
                $arguments[] = $includePath;
            }
        }

        $this->output->write('Creating file system backup... ');

        $result = ProcessHelper::execute('tar', $arguments);

        if ($result['successful']) {
            $this->output->writeln('<info>OK</info>');
            return true;
        }
        $this->output->writeln('<error>FAILED</error>');
        return false;
    }

    protected function backupConfiguration(string $backupDir): bool
    {
        $configBackupDir = $backupDir . '/config';
        mkdir($configBackupDir, 0755, true);

        try {
            // Copy .env file
            if (file_exists(base_path('.env'))) {
                copy(base_path('.env'), $configBackupDir . '/.env');
            }

            // Copy .env.example file
            if (file_exists(base_path('.env.example'))) {
                copy(base_path('.env.example'), $configBackupDir . '/.env.example');
            }

            // Copy config directory
            $this->copyDirectory(base_path('config'), $configBackupDir . '/config');

            // Copy database migrations
            $this->copyDirectory(base_path('database/migrations'), $configBackupDir . '/database/migrations');

            // Copy database seeders
            $this->copyDirectory(base_path('database/seeders'), $configBackupDir . '/database/seeders');

            // Copy environment-specific files
            $this->copyEnvironmentFiles($configBackupDir);

            // Create a summary of current configuration
            $this->createConfigSummary($configBackupDir);

            $this->output->writeln('<info>Configuration backup completed</info>');
            return true;
        } catch (Exception $e) {
            $this->output->writeln('<error>Configuration backup failed: ' . $e->getMessage() . '</error>');
            return false;
        }
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

    protected function copyEnvironmentFiles(string $configBackupDir): void
    {
        // Copy any environment-specific files
        $envFiles = [
            'artisan',
            'composer.json',
            'composer.lock',
            'Dockerfile',
            'docker-compose.yml',
            'docker-compose.yaml',
            'Procfile',
            'Vagrantfile',
            '.dockerignore',
            '.gitignore',
            'phpunit.xml',
            'phpunit.xml.dist',
            'phpcs.xml',
            'phpcs.xml.dist',
            'phpstan.neon',
            'phpstan.neon.dist',
        ];

        foreach ($envFiles as $file) {
            $src = base_path('/') . $file;
            if (file_exists($src)) {
                $dstDir = dirname($configBackupDir . '/' . $file);
                if (! is_dir($dstDir)) {
                    mkdir($dstDir, 0755, true);
                }
                copy($src, $configBackupDir . '/' . $file);
            }
        }
    }

    protected function createConfigSummary(string $configBackupDir): void
    {
        $config = $this->config->all();

        // Remove sensitive data from config
        $safeConfig = $this->sanitizeConfig($config);

        $summary = [
            'backup_date' => date('Y-m-d H:i:s'),
            'application_name' => $config['app']['name'] ?? 'Unknown',
            'environment' => $config['app']['env'] ?? 'Unknown',
            'debug_mode' => $config['app']['debug'] ?? false,
            'timezone' => $config['app']['timezone'] ?? 'Unknown',
            'locale' => $config['app']['locale'] ?? 'Unknown',
            'database_default' => $config['database']['default'] ?? 'Unknown',
            'cache_driver' => $config['cache']['default'] ?? 'Unknown',
            'queue_driver' => $config['queue']['default'] ?? 'Unknown',
            'session_driver' => $config['session']['driver'] ?? 'Unknown',
        ];

        file_put_contents($configBackupDir . '/config_summary.json', json_encode($summary, JSON_PRETTY_PRINT));
    }

    protected function sanitizeConfig(array $config): array
    {
        $sanitized = [];

        foreach ($config as $key => $value) {
            if (is_array($value)) {
                $sanitized[$key] = $this->sanitizeConfig($value);
            } else {
                // Skip sensitive keys
                if (in_array(strtolower($key), ['key', 'password', 'secret', 'token', 'app_key', 'jwt_secret', 'db_password', 'redis_password'])) {
                    $sanitized[$key] = '***HIDDEN***';
                } else {
                    $sanitized[$key] = $value;
                }
            }
        }

        return $sanitized;
    }

    protected function createBackupSummary(string $backupDir, array $results, string $timestamp): void
    {
        $summary = [
            'backup_date' => date('Y-m-d H:i:s'),
            'backup_type' => 'comprehensive',
            'timestamp' => $timestamp,
            'results' => $results,
            'components' => [
                'database' => ! $results['database'] ? 'failed' : 'completed',
                'filesystem' => ! $results['filesystem'] ? 'failed' : 'completed',
                'configuration' => ! $results['configuration'] ? 'failed' : 'completed',
            ],
        ];

        file_put_contents($backupDir . '/backup_summary.json', json_encode($summary, JSON_PRETTY_PRINT));
    }

    protected function generateDatabaseFilename(string $connection, string $driver): string
    {
        $timestamp = date('Y-m-d-H-i-s');
        $extension = $driver === 'mysql' ? 'sql' : 'db';

        return "db_backup_{$connection}_{$timestamp}.{$extension}";
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

    protected function cleanOldBackups(string $backupPath): void
    {
        $this->output->writeln('<info>Cleaning old backups...</info>');

        // Find all full backup files
        $pattern = $backupPath . '/full_backup_*.tar.gz';
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
        $this->addOption('connection', null, InputOption::VALUE_OPTIONAL, 'Database connection name');
        $this->addOption('path', null, InputOption::VALUE_OPTIONAL, 'Backup storage path');
        $this->addOption('compress', null, InputOption::VALUE_NONE, 'Compress the backup (already compressed as tar.gz)');
        $this->addOption('clean-old', null, InputOption::VALUE_NONE, 'Clean old backups (keep 5 most recent)');
        $this->addOption('no-db', null, InputOption::VALUE_NONE, 'Skip database backup');
        $this->addOption('no-fs', null, InputOption::VALUE_NONE, 'Skip file system backup');
        $this->addOption('no-config', null, InputOption::VALUE_NONE, 'Skip configuration backup');
    }
}
