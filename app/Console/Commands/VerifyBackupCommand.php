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
    protected ?string $signature = 'backup:verify {backup-file} {--type=all} {--checksum} {--detailed}';

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
        $type = $this->input->getOption('type');
        $verifyChecksum = $this->input->getOption('checksum');
        $detailed = $this->input->getOption('detailed');

        if (! file_exists($backupFile)) {
            $this->output->writeln('<error>Backup file does not exist: ' . $backupFile . '</error>');
            return 1;
        }

        $this->output->writeln('<info>Starting backup verification...</info>');
        $this->output->writeln('<info>Backup file: ' . $backupFile . '</info>');
        $this->output->writeln('<info>Verification type: ' . $type . '</info>');

        $results = [
            'backup_file' => $backupFile,
            'file_exists' => true,
            'file_size' => filesize($backupFile),
            'file_modified' => date('Y-m-d H:i:s', filemtime($backupFile)),
            'verification_results' => [],
            'overall_valid' => false,
        ];

        $validations = $this->performVerifications($backupFile, $type, $verifyChecksum, $detailed);

        $results['verification_results'] = $validations['details'];
        $results['overall_valid'] = $validations['valid'];

        $this->displayResults($results, $detailed);

        if ($results['overall_valid']) {
            $this->output->writeln('<info>Backup verification PASSED</info>');
            return 0;
        }
        $this->output->writeln('<error>Backup verification FAILED</error>');
        return 1;
    }

    protected function performVerifications(string $backupFile, string $type, bool $verifyChecksum, bool $detailed): array
    {
        $details = [];
        $allValid = true;

        $this->output->write('Verifying file integrity... ');
        $fileValid = $this->verifyFileIntegrity($backupFile);
        $details['file_integrity'] = [
            'valid' => $fileValid,
            'message' => $fileValid ? 'Backup file is valid' : 'Backup file is corrupted',
        ];
        $this->output->writeln($fileValid ? '<info>OK</info>' : '<error>FAILED</error>');

        if (! $fileValid) {
            return ['valid' => false, 'details' => $details];
        }

        $this->output->write('Verifying backup structure... ');
        $structureValid = $this->verifyBackupStructure($backupFile, $type);
        $details['structure'] = [
            'valid' => $structureValid['valid'],
            'message' => $structureValid['message'],
            'components' => $structureValid['components'] ?? [],
        ];
        $this->output->writeln($structureValid['valid'] ? '<info>OK</info>' : '<error>FAILED</error>');

        if (! $structureValid['valid']) {
            $allValid = false;
        }

        if ($verifyChecksum) {
            $this->output->write('Calculating checksums... ');
            $checksums = $this->calculateChecksums($backupFile);
            $details['checksums'] = $checksums;
            $this->output->writeln('<info>OK</info>');
        }

        if ($detailed) {
            $this->output->write('Analyzing backup contents... ');
            $contents = $this->analyzeBackupContents($backupFile);
            $details['contents'] = $contents;
            $this->output->writeln('<info>OK</info>');
        }

        $this->output->write('Verifying file sizes... ');
        $sizeValid = $this->verifyFileSizes($backupFile);
        $details['file_sizes'] = [
            'valid' => $sizeValid,
            'total_size' => filesize($backupFile),
            'formatted_size' => $this->formatBytes(filesize($backupFile)),
        ];
        $this->output->writeln($sizeValid ? '<info>OK</info>' : '<error>FAILED</error>');

        if (! $sizeValid) {
            $allValid = false;
        }

        return ['valid' => $allValid, 'details' => $details];
    }

    protected function verifyFileIntegrity(string $backupFile): bool
    {
        $command = 'tar -tzf ' . escapeshellarg($backupFile) . ' 2>/dev/null';
        $exitCode = 0;
        exec($command, $output, $exitCode);

        return $exitCode === 0;
    }

    protected function verifyBackupStructure(string $backupFile, string $type): array
    {
        $command = 'tar -tzf ' . escapeshellarg($backupFile) . ' 2>/dev/null';
        $exitCode = 0;
        $output = [];
        exec($command, $output, $exitCode);

        if ($exitCode !== 0) {
            return [
                'valid' => false,
                'message' => 'Could not read backup archive',
            ];
        }

        $components = [];
        $valid = true;

        $hasDatabase = false;
        $hasFilesystem = false;
        $hasConfig = false;
        $hasSummary = false;

        foreach ($output as $file) {
            if (strpos($file, 'db_backup_') === 0) {
                $hasDatabase = true;
                $components[] = 'database';
            }
            if (strpos($file, 'filesystem_backup') !== false || strpos($file, '/app/') !== false) {
                $hasFilesystem = true;
                $components[] = 'filesystem';
            }
            if (strpos($file, 'config_backup') !== false || strpos($file, '/.env') !== false) {
                $hasConfig = true;
                $components[] = 'configuration';
            }
            if (strpos($file, 'backup_summary.json') !== false || strpos($file, 'config_summary.json') !== false) {
                $hasSummary = true;
            }
        }

        $components = array_unique($components);

        switch ($type) {
            case 'database':
                $valid = $hasDatabase;
                break;
            case 'filesystem':
                $valid = $hasFilesystem;
                break;
            case 'config':
                $valid = $hasConfig;
                break;
            case 'all':
            default:
                $valid = $hasSummary || ($hasDatabase && $hasFilesystem && $hasConfig);
                break;
        }

        $message = $valid
            ? 'Backup structure is valid'
            : 'Backup structure is invalid or incomplete';

        return [
            'valid' => $valid,
            'message' => $message,
            'components' => $components,
        ];
    }

    protected function calculateChecksums(string $backupFile): array
    {
        $checksums = [];

        $md5Hash = md5_file($backupFile);
        $sha256Hash = hash_file('sha256', $backupFile);

        $checksums['md5'] = $md5Hash !== false ? $md5Hash : 'Failed to calculate';
        $checksums['sha256'] = $sha256Hash !== false ? $sha256Hash : 'Failed to calculate';

        return $checksums;
    }

    protected function analyzeBackupContents(string $backupFile): array
    {
        $command = 'tar -tzf ' . escapeshellarg($backupFile) . ' 2>/dev/null';
        $exitCode = 0;
        $output = [];
        exec($command, $output, $exitCode);

        $contents = [
            'total_files' => count($output),
            'directories' => [],
            'file_types' => [],
        ];

        foreach ($output as $file) {
            $pathParts = explode('/', $file);
            if (count($pathParts) > 1) {
                $dir = $pathParts[0];
                if (! in_array($dir, $contents['directories'])) {
                    $contents['directories'][] = $dir;
                }
            }

            $extension = pathinfo($file, PATHINFO_EXTENSION);
            if ($extension && ! isset($contents['file_types'][$extension])) {
                $contents['file_types'][$extension] = 0;
            }
            if ($extension) {
                $contents['file_types'][$extension]++;
            }
        }

        return $contents;
    }

    protected function verifyFileSizes(string $backupFile): bool
    {
        $fileSize = filesize($backupFile);

        if ($fileSize === false) {
            return false;
        }

        if ($fileSize < 1024) {
            return false;
        }

        return true;
    }

    protected function displayResults(array $results, bool $detailed): void
    {
        $this->output->writeln('');
        $this->output->writeln('<comment>Verification Results:</comment>');
        $this->output->writeln('  File: ' . $results['backup_file']);
        $this->output->writeln('  Size: ' . $results['verification_results']['file_sizes']['formatted_size']);
        $this->output->writeln('  Modified: ' . $results['file_modified']);
        $this->output->writeln('');

        foreach ($results['verification_results'] as $key => $result) {
            if ($key === 'contents') {
                continue;
            }

            $status = $result['valid'] ?? true;
            $label = ucfirst(str_replace('_', ' ', $key));
            $this->output->writeln('  ' . $label . ': ' . ($status ? '<info>PASS</info>' : '<error>FAIL</error>'));

            if (isset($result['message'])) {
                $this->output->writeln('    Message: ' . $result['message']);
            }

            if ($detailed && $key === 'checksums') {
                $this->output->writeln('    MD5: ' . $result['md5']);
                $this->output->writeln('    SHA256: ' . $result['sha256']);
            }

            if (isset($result['components']) && ! empty($result['components'])) {
                $this->output->writeln('    Components: ' . implode(', ', $result['components']));
            }
        }

        if ($detailed && isset($results['verification_results']['contents'])) {
            $this->output->writeln('');
            $this->output->writeln('  Contents:');
            $this->output->writeln('    Total files: ' . $results['verification_results']['contents']['total_files']);
            $this->output->writeln('    Directories: ' . implode(', ', $results['verification_results']['contents']['directories']));
        }
    }

    protected function formatBytes(int $bytes, int $precision = 2): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];

        for ($i = 0; $bytes > 1024; $i++) {
            $bytes /= 1024;
        }

        return round($bytes, $precision) . ' ' . $units[$i];
    }

    protected function configure(): void
    {
        $this->addArgument('backup-file', InputOption::VALUE_REQUIRED, 'Path to the backup file');
        $this->addOption('type', null, InputOption::VALUE_OPTIONAL, 'Type of verification: database, filesystem, config, or all (default: all)', 'all');
        $this->addOption('checksum', null, InputOption::VALUE_NONE, 'Calculate and display file checksums');
        $this->addOption('detailed', null, InputOption::VALUE_NONE, 'Show detailed verification results');
    }
}
