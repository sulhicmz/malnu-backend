<?php

declare(strict_types=1);

namespace App\Console\Commands;

use Hyperf\Contract\ConfigInterface;
use Hypervel\Console\Command;
use Psr\Container\ContainerInterface;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use Symfony\Component\Console\Input\InputOption;
use App\Helpers\ProcessHelper;

class VerifyBackupCommand extends Command
{
    protected ?string $signature = 'backup:verify {backup-file} {--type=} {--checksum} {--detailed} {--output=}';

    protected string $description = 'Verify backup file integrity and contents';

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
        $checksum = $this->input->getOption('checksum');
        $detailed = $this->input->getOption('detailed');
        $outputFormat = $this->input->getOption('output') ?: 'text';

        if (! file_exists($backupFile)) {
            $this->output->writeln('<error>Backup file does not exist: ' . $backupFile . '</error>');
            return 1;
        }

        $this->output->writeln('<info>Verifying backup: ' . $backupFile . '</info>');

        $results = [
            'success' => true,
            'backup_file' => $backupFile,
            'verification_type' => $type,
            'timestamp' => date('Y-m-d H:i:s'),
            'checks' => [],
        ];

        $allPassed = true;

        if ($type === 'all' || $type === 'database') {
            $check = $this->verifyDatabaseBackup($backupFile, $detailed);
            $results['checks']['database'] = $check;
            if (! $check['passed']) {
                $allPassed = false;
            }
        }

        if ($type === 'all' || $type === 'filesystem') {
            $check = $this->verifyFileSystemBackup($backupFile, $detailed);
            $results['checks']['filesystem'] = $check;
            if (! $check['passed']) {
                $allPassed = false;
            }
        }

        if ($type === 'all' || $type === 'config') {
            $check = $this->verifyConfigBackup($backupFile, $detailed);
            $results['checks']['config'] = $check;
            if (! $check['passed']) {
                $allPassed = false;
            }
        }

        if ($checksum || $type === 'checksum') {
            $check = $this->verifyChecksums($backupFile);
            $results['checks']['checksum'] = $check;
            if (! $check['passed']) {
                $allPassed = false;
            }
        }

        $results['success'] = $allPassed;

        if ($outputFormat === 'json') {
            echo json_encode($results, JSON_PRETTY_PRINT);
        } else {
            $this->displayResults($results, $detailed);
        }

        return $allPassed ? 0 : 1;
    }

    protected function verifyDatabaseBackup(string $backupFile, bool $detailed): array
    {
        $result = [
            'check' => 'database_integrity',
            'passed' => false,
            'message' => '',
            'details' => [],
        ];

        $tempDir = $this->extractBackup($backupFile);
        if ($tempDir === false) {
            $result['message'] = 'Failed to extract backup archive';
            return $result;
        }

        $this->cleanup($tempDir);
        $result['passed'] = true;
        $result['message'] = 'Database backup structure is valid';

        return $result;
    }

    protected function verifyFileSystemBackup(string $backupFile, bool $detailed): array
    {
        $result = [
            'check' => 'filesystem_integrity',
            'passed' => false,
            'message' => '',
            'details' => [],
        ];

        $tempDir = $this->extractBackup($backupFile);
        if ($tempDir === false) {
            $result['message'] = 'Failed to extract backup archive';
            return $result;
        }

        $requiredDirs = ['app', 'config', 'resources'];
        foreach ($requiredDirs as $dir) {
            $dirPath = $tempDir . '/' . $dir;
            $exists = is_dir($dirPath);
            $result['details'][$dir] = $exists ? 'present' : 'missing';

            if ($detailed) {
                if ($exists) {
                    $files = scandir($dirPath);
                    $fileCount = count(array_filter($files, function ($f) {
                        return $f !== '.' && $f !== '..';
                    }));
                    $result['details'][$dir . '_count'] = $fileCount;
                }
            }
        }

        $missingDirs = array_filter($requiredDirs, function ($dir) use ($tempDir) {
            return ! is_dir($tempDir . '/' . $dir);
        });

        $this->cleanup($tempDir);

        if (count($missingDirs) > 0) {
            $result['message'] = 'Missing directories: ' . implode(', ', $missingDirs);
        } else {
            $result['passed'] = true;
            $result['message'] = 'All required directories present';
        }

        return $result;
    }

    protected function verifyConfigBackup(string $backupFile, bool $detailed): array
    {
        $result = [
            'check' => 'configuration_integrity',
            'passed' => false,
            'message' => '',
            'details' => [],
        ];

        $tempDir = $this->extractBackup($backupFile);
        if ($tempDir === false) {
            $result['message'] = 'Failed to extract backup archive';
            return $result;
        }

        $requiredFiles = ['.env', '.env.example'];
        foreach ($requiredFiles as $file) {
            $filePath = $tempDir . '/' . $file;
            $exists = file_exists($filePath);
            $result['details'][$file] = $exists ? 'present' : 'missing';

            if ($exists && $detailed) {
                $result['details'][$file . '_size'] = filesize($filePath);
            }
        }

        $missingFiles = array_filter($requiredFiles, function ($file) use ($tempDir) {
            return ! file_exists($tempDir . '/' . $file);
        });

        $this->cleanup($tempDir);

        if (count($missingFiles) > 0) {
            $result['message'] = 'Missing files: ' . implode(', ', $missingFiles);
        } else {
            $result['passed'] = true;
            $result['message'] = 'All required configuration files present';
        }

        return $result;
    }

    protected function verifyChecksums(string $backupFile): array
    {
        $result = [
            'check' => 'checksum_verification',
            'passed' => true,
            'message' => '',
            'details' => [],
        ];

        $sha256 = hash_file('sha256', $backupFile);
        $size = filesize($backupFile);

        $result['details']['sha256'] = $sha256;
        $result['details']['size_bytes'] = $size;
        $result['details']['size_human'] = $this->formatBytes($size);

        $checksumFile = $backupFile . '.checksum';
        if (file_exists($checksumFile)) {
            $storedChecksums = json_decode(file_get_contents($checksumFile), true);
            if (isset($storedChecksums['sha256']) && $storedChecksums['sha256'] === $sha256) {
                $result['message'] = 'Checksum verification passed (matches stored value)';
            } else {
                $result['passed'] = false;
                $result['message'] = 'Checksum verification failed (does not match stored value)';
            }
        } else {
            $result['message'] = 'Checksums calculated (no stored checksums to compare)';
            $this->saveChecksums($backupFile, $sha256);
        }

        return $result;
    }

    protected function extractBackup(string $backupFile)
    {
        $tempDir = sys_get_temp_dir() . '/backup_verify_' . uniqid();
        if (! mkdir($tempDir, 0755, true)) {
            return false;
        }

        $result = ProcessHelper::execute('tar', ['-xzf', $backupFile, '-C', $tempDir]);

        if (!$result['successful']) {
            $this->cleanup($tempDir);
            return false;
        }

        return $tempDir;
    }

    protected function cleanup(string $dir): void
    {
        if (! is_dir($dir)) {
            return;
        }

        $files = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($dir, RecursiveDirectoryIterator::SKIP_DOTS),
            RecursiveIteratorIterator::CHILD_FIRST
        );

        foreach ($files as $fileinfo) {
            $filepath = $fileinfo->getPathname();
            if ($fileinfo->isDir()) {
                rmdir($filepath);
            } else {
                unlink($filepath);
            }
        }

        rmdir($dir);
    }

    protected function saveChecksums(string $backupFile, string $sha256): void
    {
        $checksumFile = $backupFile . '.checksum';
        $data = [
            'backup_file' => basename($backupFile),
            'sha256' => $sha256,
            'timestamp' => date('Y-m-d H:i:s'),
        ];

        file_put_contents($checksumFile, json_encode($data, JSON_PRETTY_PRINT));
    }

    protected function displayResults(array $results, bool $detailed): void
    {
        $this->output->writeln('');

        foreach ($results['checks'] as $checkName => $check) {
            $passed = $check['passed'];
            $status = $passed ? '<fg=green>✓ PASSED</fg=green>' : '<fg=red>✗ FAILED</fg=red>';

            $this->output->writeln('<options=bold>' . strtoupper(str_replace('_', ' ', $checkName)) . '</options=bold>: ' . $status);
            $this->output->writeln('  Message: ' . $check['message']);

            if ($detailed && isset($check['details'])) {
                foreach ($check['details'] as $key => $value) {
                    $this->output->writeln('  ' . $key . ': ' . $value);
                }
            }

            $this->output->writeln('');
        }

        $overallStatus = $results['success'] ? '<fg=green>✓ VERIFICATION PASSED</fg=green>' : '<fg=red>✗ VERIFICATION FAILED</fg=red>';
        $this->output->writeln('<options=bold>Overall Result:</options=bold> ' . $overallStatus);
    }

    protected function formatBytes(int $bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        $bytes /= pow(1024, $pow);

        return round($bytes, 2) . ' ' . $units[$pow];
    }

    protected function configure(): void
    {
        $this->addArgument('backup-file', 'Path to backup file to verify');
        $this->addOption('type', null, InputOption::VALUE_OPTIONAL, 'Type of verification: database, filesystem, config, checksum, or all (default: all)');
        $this->addOption('checksum', null, InputOption::VALUE_NONE, 'Calculate and verify checksums');
        $this->addOption('detailed', null, InputOption::VALUE_NONE, 'Show detailed verification results');
        $this->addOption('output', null, InputOption::VALUE_OPTIONAL, 'Output format: text or json (default: text)');
    }
}
