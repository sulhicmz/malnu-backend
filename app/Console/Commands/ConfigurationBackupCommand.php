<?php

declare(strict_types=1);

namespace App\Console\Commands;

use Hyperf\Console\Command;
use Hyperf\Contract\ConfigInterface;
use Psr\Container\ContainerInterface;
use Symfony\Component\Console\Input\InputOption;

class ConfigurationBackupCommand extends Command
{
    protected ?string $signature = 'backup:config {--path=} {--compress} {--clean-old}';

    protected string $description = 'Create a backup of system configurations';

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
        $backupPath = $this->input->getOption('path') ?: $this->getStoragePath('backups/config');
        $compress = $this->input->getOption('compress');
        $cleanOld = $this->input->getOption('clean-old');

        // Ensure backup directory exists
        if (!is_dir($backupPath)) {
            mkdir($backupPath, 0755, true);
        }

        $filename = $this->generateFilename();

        $this->output->writeln('<info>Starting configuration backup...</info>');
        $this->output->writeln('<info>Backup path: ' . $backupPath . '/' . $filename . '</info>');

        $success = $this->createConfigurationBackup($backupPath, $filename);

        if ($success) {
            $this->output->writeln('<info>Configuration backup completed successfully: ' . $backupPath . '/' . $filename . '</info>');

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
            $this->output->writeln('<error>Configuration backup failed</error>');
            return 1;
        }
    }

    protected function createConfigurationBackup(string $backupPath, string $filename): bool
    {
        $this->output->write('Collecting configuration files... ');
        
        // Create a temporary directory for configuration backup
        $tempDir = $backupPath . '/temp_config_' . uniqid();
        mkdir($tempDir, 0755, true);
        
        try {
            // Copy .env file
            if (file_exists(BASE_PATH . '/.env')) {
                copy(BASE_PATH . '/.env', $tempDir . '/.env');
            }
            
            // Copy .env.example file
            if (file_exists(BASE_PATH . '/.env.example')) {
                copy(BASE_PATH . '/.env.example', $tempDir . '/.env.example');
            }
            
            // Copy config directory
            $this->copyDirectory(BASE_PATH . '/config', $tempDir . '/config');
            
            // Copy database migrations
            $this->copyDirectory(BASE_PATH . '/database/migrations', $tempDir . '/database/migrations');
            
            // Copy database seeders
            $this->copyDirectory(BASE_PATH . '/database/seeders', $tempDir . '/database/seeders');
            
            // Copy environment-specific files
            $this->copyEnvironmentFiles($tempDir);
            
            // Create a summary of current configuration
            $this->createConfigSummary($tempDir);
            
            // Create tar archive
            $tarCommand = 'tar -czf ' . escapeshellarg($backupPath . '/' . $filename) . ' -C ' . escapeshellarg($tempDir) . ' .';
            
            $exitCode = 0;
            $output = [];
            exec($tarCommand, $output, $exitCode);
            
            // Clean up temporary directory
            $this->removeDirectory($tempDir);
            
            if ($exitCode === 0) {
                $this->output->writeln('<info>OK</info>');
                return true;
            } else {
                $this->output->writeln('<error>FAILED</error>');
                return false;
            }
        } catch (\Exception $e) {
            // Clean up temporary directory in case of error
            $this->removeDirectory($tempDir);
            $this->output->writeln('<error>FAILED: ' . $e->getMessage() . '</error>');
            return false;
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

    protected function copyEnvironmentFiles(string $tempDir): void
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
            $src = BASE_PATH . '/' . $file;
            if (file_exists($src)) {
                $dstDir = dirname($tempDir . '/' . $file);
                if (!is_dir($dstDir)) {
                    mkdir($dstDir, 0755, true);
                }
                copy($src, $tempDir . '/' . $file);
            }
        }
    }

    protected function createConfigSummary(string $tempDir): void
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
        
        file_put_contents($tempDir . '/config_summary.json', json_encode($summary, JSON_PRETTY_PRINT));
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

    protected function removeDirectory(string $dir): void
    {
        if (!is_dir($dir)) {
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

    protected function generateFilename(): string
    {
        $timestamp = date('Y-m-d-H-i-s');
        return "config_backup_{$timestamp}.tar.gz";
    }

    protected function compressBackup(string $backupPath, string $filename): void
    {
        // The backup is already compressed as .tar.gz
        $this->output->writeln('<info>File is already compressed as .tar.gz</info>');
    }

    protected function cleanOldBackups(string $backupPath): void
    {
        $this->output->writeln('<info>Cleaning old backups...</info>');
        
        // Find all config backup files
        $pattern = $backupPath . '/config_backup_*.tar.gz';
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
        $storagePath = BASE_PATH . '/storage';
        if ($path) {
            $storagePath .= '/' . ltrim($path, '/');
        }
        return $storagePath;
    }

    protected function configure(): void
    {
        $this->addOption('path', null, InputOption::VALUE_OPTIONAL, 'Backup storage path');
        $this->addOption('compress', null, InputOption::VALUE_NONE, 'Compress the backup (already compressed as tar.gz)');
        $this->addOption('clean-old', null, InputOption::VALUE_NONE, 'Clean old backups (keep 5 most recent)');
    }
}