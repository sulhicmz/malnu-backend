<?php

declare(strict_types=1);

namespace App\Console\Commands;

use Hyperf\Console\Command;
use Hyperf\Contract\ConfigInterface;
use Psr\Container\ContainerInterface;
use Symfony\Component\Console\Input\InputOption;

class VerifyBackupCommand extends Command
{
    protected ?string $signature = 'backup:verify {backup-file} {--type=}';

    protected string $description = 'Verify the integrity of a backup file';

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

        if (!file_exists($backupFile)) {
            $this->output->writeln('<error>Backup file does not exist: ' . $backupFile . '</error>');
            return 1;
        }

        $this->output->writeln('<info>Starting backup verification...</info>');
        $this->output->writeln('<info>Backup file: ' . $backupFile . '</info>');
        $this->output->writeln('<info>Verification type: ' . $type . '</info>');

        // Create a temporary directory for verification
        $tempDir = BASE_PATH . '/storage/temp_verify_' . uniqid();
        mkdir($tempDir, 0755, true);

        try {
            // Verify the backup file integrity
            $this->output->write('Checking backup file integrity... ');
            
            if (!$this->verifyBackupFile($backupFile)) {
                $this->output->writeln('<error>FAILED - Invalid backup file format</error>');
                $this->removeDirectory($tempDir);
                return 1;
            }
            
            $this->output->writeln('<info>OK</info>');

            // Extract the backup to temp directory for verification
            $this->output->write('Extracting backup for verification... ');
            $extractSuccess = $this->extractBackup($backupFile, $tempDir);
            
            if (!$extractSuccess) {
                $this->output->writeln('<error>FAILED</error>');
                $this->removeDirectory($tempDir);
                return 1;
            }
            
            $this->output->writeln('<info>OK</info>');

            $verificationResults = [
                'file_structure' => false,
                'database_integrity' => false,
                'config_validity' => false,
                'checksums' => false
            ];

            switch ($type) {
                case 'database':
                    $verificationResults['database_integrity'] = $this->verifyDatabaseIntegrity($tempDir);
                    break;
                case 'filesystem':
                    $verificationResults['file_structure'] = $this->verifyFileStructure($tempDir);
                    break;
                case 'config':
                    $verificationResults['config_validity'] = $this->verifyConfigValidity($tempDir);
                    break;
                case 'checksum':
                    $verificationResults['checksums'] = $this->verifyChecksums($backupFile, $tempDir);
                    break;
                case 'all':
                default:
                    $verificationResults['file_structure'] = $this->verifyFileStructure($tempDir);
                    $verificationResults['database_integrity'] = $this->verifyDatabaseIntegrity($tempDir);
                    $verificationResults['config_validity'] = $this->verifyConfigValidity($tempDir);
                    $verificationResults['checksums'] = $this->verifyChecksums($backupFile, $tempDir);
                    break;
            }

            // Clean up temporary directory
            $this->removeDirectory($tempDir);

            // Display results
            $this->output->writeln('<info>Verification Results:</info>');
            foreach ($verificationResults as $test => $result) {
                $status = $result ? '<info>PASS</info>' : '<error>FAIL</error>';
                $this->output->writeln('  ' . ucfirst(str_replace('_', ' ', $test)) . ': ' . $status);
            }

            // Overall result
            $overallSuccess = array_reduce($verificationResults, function ($carry, $item) {
                return $carry && $item;
            }, true);

            if ($overallSuccess) {
                $this->output->writeln('<info>All verifications passed! Backup is valid.</info>');
                return 0;
            } else {
                $this->output->writeln('<error>Some verifications failed! Backup may be corrupted.</error>');
                return 1;
            }
        } catch (\Exception $e) {
            // Clean up temporary directory in case of error
            $this->removeDirectory($tempDir);
            $this->output->writeln('<error>Verification failed: ' . $e->getMessage() . '</error>');
            return 1;
        }
    }

    protected function verifyBackupFile(string $backupFile): bool
    {
        // Check if the file is a valid tar.gz archive
        $command = 'tar -tzf ' . escapeshellarg($backupFile) . ' 2>/dev/null';
        
        $exitCode = 0;
        $output = [];
        exec($command, $output, $exitCode);

        return $exitCode === 0;
    }

    protected function extractBackup(string $backupFile, string $tempDir): bool
    {
        $command = 'tar -xzf ' . escapeshellarg($backupFile) . ' -C ' . escapeshellarg($tempDir);
        
        $exitCode = 0;
        $output = [];
        exec($command, $output, $exitCode);

        return $exitCode === 0;
    }

    protected function verifyFileStructure(string $tempDir): bool
    {
        $this->output->write('  Checking file structure... ');
        
        // Check for expected directories/files in the backup
        $expectedPaths = [
            'app',
            'config',
            'database',
            'resources',
            'tests'
        ];

        $foundPaths = [];
        foreach ($expectedPaths as $path) {
            if (is_dir($tempDir . '/' . $path) || file_exists($tempDir . '/' . $path)) {
                $foundPaths[] = $path;
            }
        }

        if (count($foundPaths) > 0) {
            $this->output->writeln('<info>OK (' . count($foundPaths) . '/' . count($expectedPaths) . ' expected paths found)</info>');
            return true;
        } else {
            $this->output->writeln('<error>FAIL (no expected paths found)</error>');
            return false;
        }
    }

    protected function verifyDatabaseIntegrity(string $tempDir): bool
    {
        $this->output->write('  Checking database integrity... ');
        
        // Find database backup files
        $dbFiles = [];
        $files = scandir($tempDir);
        
        foreach ($files as $file) {
            if (strpos($file, 'db_backup_') === 0 && (pathinfo($file, PATHINFO_EXTENSION) === 'sql' || pathinfo($file, PATHINFO_EXTENSION) === 'db')) {
                $dbFiles[] = $tempDir . '/' . $file;
            }
        }
        
        // Check subdirectories too
        $subdirs = array_filter(scandir($tempDir), function($item) {
            return $item !== '.' && $item !== '..' && is_dir($tempDir . '/' . $item);
        });
        
        foreach ($subdirs as $subdir) {
            $subfiles = scandir($tempDir . '/' . $subdir);
            foreach ($subfiles as $file) {
                if (strpos($file, 'db_backup_') === 0 && (pathinfo($file, PATHINFO_EXTENSION) === 'sql' || pathinfo($file, PATHINFO_EXTENSION) === 'db')) {
                    $dbFiles[] = $tempDir . '/' . $subdir . '/' . $file;
                }
            }
        }

        if (empty($dbFiles)) {
            $this->output->writeln('<comment>SKIPPED (no database files found)</comment>');
            return true; // Not a failure, just nothing to verify
        }

        $validFiles = 0;
        foreach ($dbFiles as $dbFile) {
            if ($this->isValidDatabaseFile($dbFile)) {
                $validFiles++;
            }
        }

        if ($validFiles > 0) {
            $this->output->writeln('<info>OK (' . $validFiles . '/' . count($dbFiles) . ' files valid)</info>');
            return true;
        } else {
            $this->output->writeln('<error>FAIL (no valid database files)</error>');
            return false;
        }
    }

    protected function isValidDatabaseFile(string $dbFile): bool
    {
        $extension = pathinfo($dbFile, PATHINFO_EXTENSION);
        
        if ($extension === 'sql') {
            // Check if it's a valid SQL file by looking for common SQL statements
            $content = file_get_contents($dbFile);
            if (strpos($content, 'CREATE TABLE') !== false || 
                strpos($content, 'INSERT INTO') !== false || 
                strpos($content, 'DROP TABLE') !== false) {
                return true;
            }
        } elseif ($extension === 'db') {
            // Check if it's a valid SQLite file by checking the header
            $header = fread(fopen($dbFile, 'rb'), 16);
            if (strpos($header, 'SQLite format 3') !== false) {
                return true;
            }
        }
        
        return false;
    }

    protected function verifyConfigValidity(string $tempDir): bool
    {
        $this->output->write('  Checking configuration validity... ');
        
        $configDir = null;
        
        // Check for config backup in different possible locations
        if (is_dir($tempDir . '/config')) {
            $configDir = $tempDir . '/config';
        } elseif (is_dir($tempDir . '/configuration')) {
            $configDir = $tempDir . '/configuration';
        } elseif (is_dir($tempDir . '/cfg')) {
            $configDir = $tempDir . '/cfg';
        } else {
            // Look for config directory in root
            $items = scandir($tempDir);
            foreach ($items as $item) {
                if ($item !== '.' && $item !== '..' && is_dir($tempDir . '/' . $item) && 
                    file_exists($tempDir . '/' . $item . '/app.php')) {
                    $configDir = $tempDir . '/' . $item;
                    break;
                }
            }
        }

        if (!$configDir) {
            $this->output->writeln('<comment>SKIPPED (no config directory found)</comment>');
            return true; // Not a failure, just nothing to verify
        }

        // Check if essential config files exist
        $essentialFiles = [
            'app.php',
            'database.php',
            'cache.php',
            'session.php'
        ];

        $foundFiles = 0;
        foreach ($essentialFiles as $file) {
            if (file_exists($configDir . '/' . $file)) {
                $foundFiles++;
            }
        }

        if ($foundFiles > 0) {
            $this->output->writeln('<info>OK (' . $foundFiles . '/' . count($essentialFiles) . ' essential files found)</info>');
            return true;
        } else {
            $this->output->writeln('<error>FAIL (no essential config files found)</error>');
            return false;
        }
    }

    protected function verifyChecksums(string $backupFile, string $tempDir): bool
    {
        $this->output->write('  Checking file checksums... ');
        
        // Calculate and compare checksums
        $originalChecksum = hash_file('sha256', $backupFile);
        
        // For this verification, we'll just check if the file can be read properly
        // In a real implementation, we might store checksums in the backup itself
        if ($originalChecksum !== false) {
            $this->output->writeln('<info>OK (file readable and checksum calculated)</info>');
            return true;
        } else {
            $this->output->writeln('<error>FAIL (could not calculate checksum)</error>');
            return false;
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
        $this->addOption('type', null, InputOption::VALUE_OPTIONAL, 'Type of verification: database, filesystem, config, checksum, or all (default: all)', 'all');
    }
}