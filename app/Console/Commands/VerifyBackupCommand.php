<?php

declare(strict_types=1);

namespace App\Console\Commands;

use Hypervel\Console\Command;
use Hyperf\Contract\ConfigInterface;
use Psr\Container\ContainerInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

class VerifyBackupCommand extends Command
{
    protected ?string $signature = 'backup:verify {backup} {--type=} {--json}';

    protected string $description = 'Verify backup file integrity and structure';

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
        $backupPath = $this->input->getArgument('backup');
        $type = $this->input->getOption('type') ?: $this->detectBackupType($backupPath);
        $jsonOutput = $this->input->getOption('json');

        $results = [
            'backup_file' => $backupPath,
            'backup_type' => $type,
            'timestamp' => date('Y-m-d H:i:s'),
            'verification_status' => 'passed',
            'checks' => []
        ];

        $this->output->writeln('<info>Verifying backup: ' . $backupPath . '</info>');

        if (!file_exists($backupPath)) {
            $results['verification_status'] = 'failed';
            $results['error'] = 'Backup file does not exist';
            $this->outputJson($results, $jsonOutput);
            return 1;
        }

        $fileSize = filesize($backupPath);
        $results['file_size'] = $this->formatBytes($fileSize);
        $results['file_size_bytes'] = $fileSize;

        if ($fileSize === 0) {
            $results['verification_status'] = 'failed';
            $results['error'] = 'Backup file is empty';
            $this->outputJson($results, $jsonOutput);
            return 1;
        }

        $isCompressed = str_ends_with($backupPath, '.tar.gz') || str_ends_with($backupPath, '.tgz');
        $results['is_compressed'] = $isCompressed;

        if ($isCompressed) {
            $checkResult = $this->verifyTarArchive($backupPath);
            $results['checks']['archive_integrity'] = $checkResult;
            if (!$checkResult['passed']) {
                $results['verification_status'] = 'failed';
                $this->outputJson($results, $jsonOutput);
                return 1;
            }
        }

        switch ($type) {
            case 'database':
                $typeCheckResult = $this->verifyDatabaseBackup($backupPath, $isCompressed);
                $results['checks']['database_structure'] = $typeCheckResult;
                break;
            case 'filesystem':
                $typeCheckResult = $this->verifyFilesystemBackup($backupPath, $isCompressed);
                $results['checks']['filesystem_structure'] = $typeCheckResult;
                break;
            case 'config':
                $typeCheckResult = $this->verifyConfigBackup($backupPath, $isCompressed);
                $results['checks']['config_structure'] = $typeCheckResult;
                break;
            case 'comprehensive':
            default:
                $dbResult = $this->verifyDatabaseBackup($backupPath, $isCompressed);
                $fsResult = $this->verifyFilesystemBackup($backupPath, $isCompressed);
                $configResult = $this->verifyConfigBackup($backupPath, $isCompressed);
                $results['checks']['database_structure'] = $dbResult;
                $results['checks']['filesystem_structure'] = $fsResult;
                $results['checks']['config_structure'] = $configResult;
                break;
        }

        $checksumResult = $this->calculateChecksum($backupPath);
        $results['checksum'] = $checksumResult;

        foreach ($results['checks'] as $check) {
            if (!$check['passed']) {
                $results['verification_status'] = 'failed';
                break;
            }
        }

        $this->outputJson($results, $jsonOutput);

        return $results['verification_status'] === 'passed' ? 0 : 1;
    }

    protected function detectBackupType(string $backupPath): string
    {
        $basename = basename($backupPath);
        
        if (str_contains($basename, 'db_backup') || str_contains($basename, 'database_backup')) {
            return 'database';
        } elseif (str_contains($basename, 'filesystem_backup')) {
            return 'filesystem';
        } elseif (str_contains($basename, 'config_backup')) {
            return 'config';
        } elseif (str_contains($basename, 'full_backup') || str_contains($basename, 'comprehensive_backup')) {
            return 'comprehensive';
        }
        
        return 'unknown';
    }

    protected function verifyTarArchive(string $backupPath): array
    {
        $command = 'tar -tzf ' . escapeshellarg($backupPath) . ' 2>&1';
        $exitCode = 0;
        $output = [];
        exec($command, $output, $exitCode);

        $result = [
            'passed' => $exitCode === 0,
            'message' => $exitCode === 0 ? 'Archive integrity verified' : 'Archive integrity check failed',
            'file_count' => count($output)
        ];

        if ($exitCode !== 0) {
            $result['error'] = implode("\n", $output);
        }

        if (!$jsonOutput = $this->input->getOption('json')) {
            $this->output->writeln(($result['passed'] ? '<info>' : '<error>') . 
                '  Archive integrity: ' . $result['message'] . '</>');
            if ($result['passed']) {
                $this->output->writeln('  Files in archive: ' . $result['file_count']);
            }
        }

        return $result;
    }

    protected function verifyDatabaseBackup(string $backupPath, bool $isCompressed): array
    {
        $requiredContent = ['INSERT INTO', 'CREATE TABLE', 'DROP TABLE'];
        $content = '';
        $passed = true;
        $errors = [];

        if ($isCompressed) {
            $command = 'tar -xzf ' . escapeshellarg($backupPath) . ' -O 2>&1';
            $output = [];
            exec($command, $output, $exitCode);
            $content = implode("\n", $output);
        } else {
            $content = file_get_contents($backupPath);
        }

        foreach ($requiredContent as $required) {
            if (!str_contains($content, $required)) {
                $passed = false;
                $errors[] = "Missing required content: {$required}";
            }
        }

        $result = [
            'passed' => $passed,
            'message' => $passed ? 'Database backup structure verified' : 'Database backup structure invalid',
            'content_size' => strlen($content)
        ];

        if (!$passed) {
            $result['errors'] = $errors;
        }

        if (!$jsonOutput = $this->input->getOption('json')) {
            $this->output->writeln(($result['passed'] ? '<info>' : '<error>') . 
                '  Database structure: ' . $result['message'] . '</>');
        }

        return $result;
    }

    protected function verifyFilesystemBackup(string $backupPath, bool $isCompressed): array
    {
        $requiredDirs = ['app/', 'config/', 'database/'];
        $passed = true;
        $errors = [];
        $foundDirs = [];

        if ($isCompressed) {
            $command = 'tar -tzf ' . escapeshellarg($backupPath) . ' 2>&1';
            $output = [];
            exec($command, $output, $exitCode);
            $content = implode("\n", $output);
        } else {
            $content = file_get_contents($backupPath);
        }

        foreach ($requiredDirs as $dir) {
            if (str_contains($content, $dir)) {
                $foundDirs[] = $dir;
            } else {
                $passed = false;
                $errors[] = "Missing required directory: {$dir}";
            }
        }

        $result = [
            'passed' => $passed,
            'message' => $passed ? 'Filesystem backup structure verified' : 'Filesystem backup structure incomplete',
            'found_directories' => $foundDirs
        ];

        if (!$passed) {
            $result['errors'] = $errors;
        }

        if (!$jsonOutput = $this->input->getOption('json')) {
            $this->output->writeln(($result['passed'] ? '<info>' : '<error>') . 
                '  Filesystem structure: ' . $result['message'] . '</>');
        }

        return $result;
    }

    protected function verifyConfigBackup(string $backupPath, bool $isCompressed): array
    {
        $requiredFiles = ['.env', 'config/'];
        $passed = true;
        $errors = [];
        $foundFiles = [];

        if ($isCompressed) {
            $command = 'tar -tzf ' . escapeshellarg($backupPath) . ' 2>&1';
            $output = [];
            exec($command, $output, $exitCode);
            $content = implode("\n", $output);
        } else {
            $content = file_get_contents($backupPath);
        }

        foreach ($requiredFiles as $file) {
            if (str_contains($content, $file)) {
                $foundFiles[] = $file;
            } else {
                $passed = false;
                $errors[] = "Missing required file/directory: {$file}";
            }
        }

        $result = [
            'passed' => $passed,
            'message' => $passed ? 'Configuration backup verified' : 'Configuration backup incomplete',
            'found_files' => $foundFiles
        ];

        if (!$passed) {
            $result['errors'] = $errors;
        }

        if (!$jsonOutput = $this->input->getOption('json')) {
            $this->output->writeln(($result['passed'] ? '<info>' : '<error>') . 
                '  Configuration structure: ' . $result['message'] . '</>');
        }

        return $result;
    }

    protected function calculateChecksum(string $filePath): array
    {
        $md5 = md5_file($filePath);
        $sha256 = hash_file('sha256', $filePath);

        $result = [
            'md5' => $md5,
            'sha256' => $sha256
        ];

        if (!$jsonOutput = $this->input->getOption('json')) {
            $this->output->writeln('<info>  MD5: ' . $md5 . '</info>');
            $this->output->writeln('<info>  SHA256: ' . $sha256 . '</info>');
        }

        return $result;
    }

    protected function formatBytes(int $bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        $bytes /= pow(1024, $pow);
        return round($bytes, 2) . ' ' . $units[$pow];
    }

    protected function outputJson(array $data, bool $jsonOutput): void
    {
        if ($jsonOutput) {
            echo json_encode($data, JSON_PRETTY_PRINT);
        } else {
            $this->output->writeln('');
            $this->output->writeln('<info>Verification Status: ' . ($data['verification_status'] === 'passed' ? 'PASSED' : 'FAILED') . '</info>');
            $this->output->writeln('<info>Backup Type: ' . $data['backup_type'] . '</info>');
            $this->output->writeln('<info>File Size: ' . $data['file_size'] . '</info>');
        }
    }

    protected function configure(): void
    {
        $this->addArgument('backup', InputArgument::REQUIRED, 'Path to the backup file');
        $this->addOption('type', null, InputOption::VALUE_OPTIONAL, 'Type of backup (database, filesystem, config, comprehensive)');
        $this->addOption('json', null, InputOption::VALUE_NONE, 'Output results in JSON format');
    }
}
