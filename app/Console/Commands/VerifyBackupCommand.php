<?php

declare(strict_types=1);

namespace App\Console\Commands;

use Exception;
use Hypervel\Console\Command;
use Hyperf\Contract\ConfigInterface;
use Psr\Container\ContainerInterface;
use Symfony\Component\Console\Input\InputOption;

class VerifyBackupCommand extends Command
{
    protected ?string $signature = 'backup:verify {backup-file} {--type=} {--detailed} {--output=}';

    protected string $description = 'Verify backup integrity and validity';

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
        $detailed = $this->input->getOption('detailed');
        $outputFormat = $this->input->getOption('output') ?: 'text';

        if (! file_exists($backupFile)) {
            $this->output->writeln('<error>Backup file does not exist: ' . $backupFile . '</error>');
            return 1;
        }

        $this->output->writeln('<info>Starting backup verification...</info>');
        $this->output->writeln('<info>Backup file: ' . $backupFile . '</info>');
        $this->output->writeln('<info>Verification type: ' . $type . '</info>');

        $results = [
            'backup_file' => $backupFile,
            'verification_type' => $type,
            'timestamp' => date('Y-m-d H:i:s'),
            'overall_status' => 'unknown',
            'components' => [],
        ];

        $success = false;

        switch ($type) {
            case 'database':
                $results['components']['database'] = $this->verifyDatabaseBackup($backupFile, $detailed);
                $success = $results['components']['database']['status'] === 'pass';
                break;
            case 'filesystem':
                $results['components']['filesystem'] = $this->verifyFileSystemBackup($backupFile, $detailed);
                $success = $results['components']['filesystem']['status'] === 'pass';
                break;
            case 'config':
                $results['components']['config'] = $this->verifyConfigBackup($backupFile, $detailed);
                $success = $results['components']['config']['status'] === 'pass';
                break;
            case 'checksum':
                $results['components']['checksum'] = $this->verifyChecksum($backupFile);
                $success = $results['components']['checksum']['status'] === 'pass';
                break;
            case 'all':
            default:
                $results['components']['database'] = $this->verifyDatabaseBackup($backupFile, $detailed);
                $results['components']['filesystem'] = $this->verifyFileSystemBackup($backupFile, $detailed);
                $results['components']['config'] = $this->verifyConfigBackup($backupFile, $detailed);
                $results['components']['checksum'] = $this->verifyChecksum($backupFile);

                $allPassed = true;
                foreach ($results['components'] as $component) {
                    if ($component['status'] === 'fail') {
                        $allPassed = false;
                        break;
                    }
                }
                $success = $allPassed;
                break;
        }

        $results['overall_status'] = $success ? 'pass' : 'fail';

        if ($outputFormat === 'json') {
            $this->output->writeln(json_encode($results, JSON_PRETTY_PRINT));
        } else {
            $this->displayResults($results, $detailed);
        }

        return $success ? 0 : 1;
    }

    protected function verifyDatabaseBackup(string $backupFile, bool $detailed): array
    {
        $result = [
            'component' => 'database',
            'status' => 'unknown',
            'checks' => [],
            'message' => '',
        ];

        try {
            $this->output->write('Verifying database backup... ');

            $tempDir = $this->extractBackup($backupFile);
            if (! $tempDir) {
                $result['status'] = 'fail';
                $result['message'] = 'Failed to extract backup';
                $result['checks'][] = ['name' => 'extraction', 'status' => 'fail'];
                return $result;
            }

            $result['checks'][] = ['name' => 'extraction', 'status' => 'pass'];

            $dbFiles = $this->findDatabaseFiles($tempDir);
            if (empty($dbFiles)) {
                $result['status'] = 'fail';
                $result['message'] = 'No database files found in backup';
                $result['checks'][] = ['name' => 'database_files_exist', 'status' => 'fail'];
                $this->cleanup($tempDir);
                return $result;
            }

            $result['checks'][] = ['name' => 'database_files_exist', 'status' => 'pass'];

            foreach ($dbFiles as $dbFile) {
                $fileCheck = $this->verifyDatabaseFile($dbFile, $detailed);
                $result['checks'][] = $fileCheck;

                if ($fileCheck['status'] === 'fail') {
                    $result['status'] = 'fail';
                    $result['message'] = 'Database file verification failed: ' . basename($dbFile);
                    $this->cleanup($tempDir);
                    return $result;
                }
            }

            $result['status'] = 'pass';
            $result['message'] = 'Database backup verified successfully';
            $result['database_files'] = array_map('basename', $dbFiles);

            $this->cleanup($tempDir);
        } catch (Exception $e) {
            $result['status'] = 'fail';
            $result['message'] = 'Verification error: ' . $e->getMessage();
            $result['checks'][] = ['name' => 'exception', 'status' => 'fail', 'message' => $e->getMessage()];
        }

        return $result;
    }

    protected function verifyFileSystemBackup(string $backupFile, bool $detailed): array
    {
        $result = [
            'component' => 'filesystem',
            'status' => 'unknown',
            'checks' => [],
            'message' => '',
        ];

        try {
            $this->output->write('Verifying file system backup... ');

            $tempDir = $this->extractBackup($backupFile);
            if (! $tempDir) {
                $result['status'] = 'fail';
                $result['message'] = 'Failed to extract backup';
                $result['checks'][] = ['name' => 'extraction', 'status' => 'fail'];
                return $result;
            }

            $result['checks'][] = ['name' => 'extraction', 'status' => 'pass'];

            $expectedDirs = ['app', 'config', 'database', 'resources', 'tests'];
            $foundDirs = [];

            foreach ($expectedDirs as $dir) {
                if (is_dir($tempDir . '/' . $dir)) {
                    $foundDirs[] = $dir;
                    $result['checks'][] = ['name' => "directory_exists_{$dir}", 'status' => 'pass'];
                } else {
                    $result['checks'][] = ['name' => "directory_exists_{$dir}", 'status' => 'warning'];
                }
            }

            if (empty($foundDirs)) {
                $result['status'] = 'fail';
                $result['message'] = 'No expected directories found in file system backup';
                $this->cleanup($tempDir);
                return $result;
            }

            if ($detailed) {
                foreach ($foundDirs as $dir) {
                    $fileCount = $this->countFilesRecursive($tempDir . '/' . $dir);
                    $result['checks'][] = [
                        'name' => "file_count_{$dir}",
                        'status' => 'pass',
                        'count' => $fileCount,
                    ];
                }
            }

            $result['status'] = 'pass';
            $result['message'] = 'File system backup verified successfully';
            $result['found_directories'] = $foundDirs;

            $this->cleanup($tempDir);
        } catch (Exception $e) {
            $result['status'] = 'fail';
            $result['message'] = 'Verification error: ' . $e->getMessage();
            $result['checks'][] = ['name' => 'exception', 'status' => 'fail', 'message' => $e->getMessage()];
        }

        return $result;
    }

    protected function verifyConfigBackup(string $backupFile, bool $detailed): array
    {
        $result = [
            'component' => 'config',
            'status' => 'unknown',
            'checks' => [],
            'message' => '',
        ];

        try {
            $this->output->write('Verifying configuration backup... ');

            $tempDir = $this->extractBackup($backupFile);
            if (! $tempDir) {
                $result['status'] = 'fail';
                $result['message'] = 'Failed to extract backup';
                $result['checks'][] = ['name' => 'extraction', 'status' => 'fail'];
                return $result;
            }

            $result['checks'][] = ['name' => 'extraction', 'status' => 'pass'];

            $expectedFiles = ['.env', '.env.example', 'config_summary.json'];
            $foundFiles = [];

            foreach ($expectedFiles as $file) {
                if (file_exists($tempDir . '/' . $file)) {
                    $foundFiles[] = $file;
                    $result['checks'][] = ['name' => "file_exists_{$file}", 'status' => 'pass'];
                } else {
                    $result['checks'][] = ['name' => "file_exists_{$file}", 'status' => 'warning'];
                }
            }

            $expectedDirs = ['config', 'database'];
            $foundDirs = [];

            foreach ($expectedDirs as $dir) {
                if (is_dir($tempDir . '/' . $dir)) {
                    $foundDirs[] = $dir;
                    $result['checks'][] = ['name' => "directory_exists_{$dir}", 'status' => 'pass'];
                } else {
                    $result['checks'][] = ['name' => "directory_exists_{$dir}", 'status' => 'warning'];
                }
            }

            if (empty($foundFiles) && empty($foundDirs)) {
                $result['status'] = 'fail';
                $result['message'] = 'No expected configuration files or directories found';
                $this->cleanup($tempDir);
                return $result;
            }

            $result['status'] = 'pass';
            $result['message'] = 'Configuration backup verified successfully';
            $result['found_files'] = $foundFiles;
            $result['found_directories'] = $foundDirs;

            $this->cleanup($tempDir);
        } catch (Exception $e) {
            $result['status'] = 'fail';
            $result['message'] = 'Verification error: ' . $e->getMessage();
            $result['checks'][] = ['name' => 'exception', 'status' => 'fail', 'message' => $e->getMessage()];
        }

        return $result;
    }

    protected function verifyChecksum(string $backupFile): array
    {
        $result = [
            'component' => 'checksum',
            'status' => 'unknown',
            'checks' => [],
            'message' => '',
        ];

        try {
            $this->output->write('Verifying checksum... ');

            $actualChecksum = hash_file('sha256', $backupFile);
            if ($actualChecksum === false) {
                $result['status'] = 'fail';
                $result['message'] = 'Failed to generate checksum';
                $result['checks'][] = ['name' => 'checksum_generation', 'status' => 'fail'];
                return $result;
            }

            $result['checks'][] = ['name' => 'checksum_generation', 'status' => 'pass'];

            $checksumFile = $backupFile . '.sha256';
            if (file_exists($checksumFile)) {
                $expectedChecksum = trim(file_get_contents($checksumFile));

                if ($actualChecksum === $expectedChecksum) {
                    $result['checks'][] = ['name' => 'checksum_verification', 'status' => 'pass'];
                    $result['status'] = 'pass';
                    $result['message'] = 'Checksum verified successfully';
                    $result['checksum'] = $actualChecksum;
                    $result['checksum_match'] = true;
                } else {
                    $result['checks'][] = ['name' => 'checksum_verification', 'status' => 'fail'];
                    $result['status'] = 'fail';
                    $result['message'] = 'Checksum mismatch';
                    $result['actual_checksum'] = $actualChecksum;
                    $result['expected_checksum'] = $expectedChecksum;
                    $result['checksum_match'] = false;
                }
            } else {
                $result['checks'][] = ['name' => 'checksum_file_exists', 'status' => 'warning'];
                $result['status'] = 'pass';
                $result['message'] = 'Checksum generated (no checksum file to compare)';
                $result['checksum'] = $actualChecksum;
                $result['checksum_match'] = null;
            }
        } catch (Exception $e) {
            $result['status'] = 'fail';
            $result['message'] = 'Verification error: ' . $e->getMessage();
            $result['checks'][] = ['name' => 'exception', 'status' => 'fail', 'message' => $e->getMessage()];
        }

        return $result;
    }

    protected function extractBackup(string $backupFile): ?string
    {
        $tempDir = base_path('storage/temp_verify_' . uniqid());
        mkdir($tempDir, 0755, true);

        $command = 'tar -xzf ' . escapeshellarg($backupFile) . ' -C ' . escapeshellarg($tempDir);
        $exitCode = 0;
        $output = [];
        exec($command, $output, $exitCode);

        if ($exitCode !== 0) {
            $this->removeDirectory($tempDir);
            return null;
        }

        return $tempDir;
    }

    protected function findDatabaseFiles(string $tempDir): array
    {
        $dbFiles = [];

        $files = $this->scanDirectoryRecursive($tempDir);
        foreach ($files as $file) {
            $extension = pathinfo($file, PATHINFO_EXTENSION);
            $basename = basename($file);

            if ($extension === 'sql' || $extension === 'db') {
                if (strpos($basename, 'db_backup_') === 0 || strpos($basename, 'backup_') === 0) {
                    $dbFiles[] = $file;
                }
            }
        }

        return $dbFiles;
    }

    protected function verifyDatabaseFile(string $dbFile, bool $detailed): array
    {
        $result = ['name' => basename($dbFile), 'status' => 'pass', 'details' => []];

        $extension = pathinfo($dbFile, PATHINFO_EXTENSION);

        if ($extension === 'sql') {
            $content = file_get_contents($dbFile);
            if (empty($content)) {
                $result['status'] = 'fail';
                $result['details'][] = 'SQL file is empty';
                return $result;
            }

            $result['details'][] = 'SQL file has content';

            if ($detailed) {
                $lineCount = count(explode("\n", $content));
                $result['details'][] = "Lines: {$lineCount}";

                if (stripos($content, 'CREATE TABLE') !== false || stripos($content, 'INSERT INTO') !== false) {
                    $result['details'][] = 'Contains SQL statements';
                }
            }
        } elseif ($extension === 'db') {
            $fileSize = filesize($dbFile);
            if ($fileSize === 0) {
                $result['status'] = 'fail';
                $result['details'][] = 'Database file is empty';
                return $result;
            }

            $result['details'][] = "File size: {$fileSize} bytes";

            if ($detailed) {
                $result['details'][] = 'SQLite database file';
            }
        } else {
            $result['status'] = 'fail';
            $result['details'][] = 'Unknown database file type';
        }

        return $result;
    }

    protected function countFilesRecursive(string $dir): int
    {
        $count = 0;
        $files = scandir($dir);

        foreach ($files as $file) {
            if ($file == '.' || $file == '..') {
                continue;
            }

            $path = $dir . '/' . $file;
            if (is_dir($path)) {
                $count += $this->countFilesRecursive($path);
            } else {
                $count++;
            }
        }

        return $count;
    }

    protected function scanDirectoryRecursive(string $dir): array
    {
        $files = [];
        $items = scandir($dir);

        foreach ($items as $item) {
            if ($item == '.' || $item == '..') {
                continue;
            }

            $path = $dir . '/' . $item;
            if (is_dir($path)) {
                $files = array_merge($files, $this->scanDirectoryRecursive($path));
            } else {
                $files[] = $path;
            }
        }

        return $files;
    }

    protected function cleanup(string $tempDir): void
    {
        $this->removeDirectory($tempDir);
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

    protected function displayResults(array $results, bool $detailed): void
    {
        $this->output->writeln('');
        $this->output->writeln('<info>Backup Verification Results:</info>');
        $this->output->writeln('  Overall status: ' . ($results['overall_status'] === 'pass' ? '<info>PASS</info>' : '<error>FAIL</error>'));

        foreach ($results['components'] as $component) {
            $statusColor = $component['status'] === 'pass' ? 'info' : ($component['status'] === 'warning' ? 'comment' : 'error');
            $this->output->writeln('');
            $this->output->writeln("  {$component['component']}: <{$statusColor}>{$component['status']}</{$statusColor}>");

            if (! empty($component['message'])) {
                $this->output->writeln("    Message: {$component['message']}");
            }

            if ($detailed && ! empty($component['checks'])) {
                $this->output->writeln('    Checks:');
                foreach ($component['checks'] as $check) {
                    $checkColor = $check['status'] === 'pass' ? 'info' : ($check['status'] === 'warning' ? 'comment' : 'error');
                    $this->output->writeln("      - {$check['name']}: <{$checkColor}>{$check['status']}</{$checkColor}>");
                    if (isset($check['message'])) {
                        $this->output->writeln("        {$check['message']}");
                    }
                    if (isset($check['count'])) {
                        $this->output->writeln("        Count: {$check['count']}");
                    }
                }
            }
        }

        $this->output->writeln('');
        $this->output->writeln('<info>Verification completed at: ' . $results['timestamp'] . '</info>');
    }

    protected function configure(): void
    {
        $this->addOption('type', null, InputOption::VALUE_OPTIONAL, 'Type of verification: database, filesystem, config, checksum, or all (default: all)', 'all');
        $this->addOption('detailed', null, InputOption::VALUE_NONE, 'Show detailed verification results');
        $this->addOption('output', null, InputOption::VALUE_OPTIONAL, 'Output format: text or json (default: text)', 'text');
    }
}
