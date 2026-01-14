<?php

declare(strict_types=1);

namespace App\Console\Commands;

use Hyperf\Contract\ConfigInterface;
use Hypervel\Console\Command;
use Hypervel\Support\Facades\Log;
use Psr\Container\ContainerInterface;
use Symfony\Component\Console\Input\InputOption;

class VerifyBackupCommand extends Command
{
    protected ?string $signature = 'backup:verify {backup-file} {--type=}';

    protected string $description = 'Verify a backup file for integrity and correctness';

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

        if (! file_exists($backupFile)) {
            $this->output->writeln('<error>Backup file does not exist: ' . $backupFile . '</error>');
            return 1;
        }

        $this->output->writeln('<info>Starting backup verification...</info>');
        $this->output->writeln('<info>Backup file: ' . $backupFile . '</info>');
        $this->output->writeln('<info>Verification type: ' . $type . '</info>');

        $results = [
            'timestamp' => date('Y-m-d H:i:s'),
            'backup_file' => $backupFile,
            'type' => $type,
            'checks' => [],
            'overall_status' => 'pending',
        ];

        $overallSuccess = true;

        switch ($type) {
            case 'database':
                $overallSuccess = $this->verifyDatabaseBackup($backupFile, $results);
                break;
            case 'filesystem':
                $overallSuccess = $this->verifyFileSystemBackup($backupFile, $results);
                break;
            case 'config':
                $overallSuccess = $this->verifyConfigurationBackup($backupFile, $results);
                break;
            case 'checksum':
                $overallSuccess = $this->verifyChecksums($backupFile, $results);
                break;
            case 'all':
            default:
                $dbSuccess = $this->verifyDatabaseBackup($backupFile, $results);
                $fsSuccess = $this->verifyFileSystemBackup($backupFile, $results);
                $configSuccess = $this->verifyConfigurationBackup($backupFile, $results);
                $checksumSuccess = $this->verifyChecksums($backupFile, $results);

                $overallSuccess = $dbSuccess && $fsSuccess && $configSuccess && $checksumSuccess;
                break;
        }

        $results['overall_status'] = $overallSuccess ? 'passed' : 'failed';

        $this->output->writeln('');
        $this->output->writeln('<info>Verification Results:</info>');
        $this->output->writeln('  Overall status: ' . ($overallSuccess ? '<info>PASSED</info>' : '<error>FAILED</error>'));

        foreach ($results['checks'] as $check => $status) {
            $statusText = $status ? '<info>PASSED</info>' : '<error>FAILED</error>';
            $this->output->writeln('  ' . $check . ': ' . $statusText);
        }

        if ($overallSuccess) {
            $this->output->writeln('<info>Backup verification completed successfully!</info>');
            Log::info('Backup verification passed', $results);
            return 0;
        }
        $this->output->writeln('<error>Backup verification failed!</error>');
        Log::error('Backup verification failed', $results);
        return 1;
    }

    protected function verifyDatabaseBackup(string $backupFile, array &$results): bool
    {
        $this->output->write('Verifying database backup... ');

        $checks = [];

        $checkResult = $this->checkFileExists($backupFile);
        $checks['file_exists'] = $checkResult;

        if (! $checkResult) {
            $results['checks']['database'] = false;
            $results['checks']['database_file_exists'] = false;
            $this->output->writeln('<error>FAILED</error>');
            return false;
        }

        $checkResult = $this->checkTarIntegrity($backupFile);
        $checks['tar_integrity'] = $checkResult;

        $checkResult = $this->checkDatabaseContent($backupFile);
        $checks['database_content'] = $checkResult;

        $checkResult = $this->checkFileSize($backupFile, 1024);
        $checks['file_size'] = $checkResult;

        $passed = $checks['file_exists'] && $checks['tar_integrity'] && $checks['database_content'] && $checks['file_size'];

        $results['checks']['database'] = $passed;
        $results['checks']['database_details'] = $checks;

        $this->output->writeln($passed ? '<info>OK</info>' : '<error>FAILED</error>');

        return $passed;
    }

    protected function verifyFileSystemBackup(string $backupFile, array &$results): bool
    {
        $this->output->write('Verifying filesystem backup... ');

        $checks = [];

        $checkResult = $this->checkFileExists($backupFile);
        $checks['file_exists'] = $checkResult;

        if (! $checkResult) {
            $results['checks']['filesystem'] = false;
            $results['checks']['filesystem_file_exists'] = false;
            $this->output->writeln('<error>FAILED</error>');
            return false;
        }

        $checkResult = $this->checkTarIntegrity($backupFile);
        $checks['tar_integrity'] = $checkResult;

        $checkResult = $this->checkFileSystemContent($backupFile);
        $checks['filesystem_content'] = $checkResult;

        $checkResult = $this->checkFileSize($backupFile, 1024);
        $checks['file_size'] = $checkResult;

        $passed = $checks['file_exists'] && $checks['tar_integrity'] && $checks['filesystem_content'] && $checks['file_size'];

        $results['checks']['filesystem'] = $passed;
        $results['checks']['filesystem_details'] = $checks;

        $this->output->writeln($passed ? '<info>OK</info>' : '<error>FAILED</error>');

        return $passed;
    }

    protected function verifyConfigurationBackup(string $backupFile, array &$results): bool
    {
        $this->output->write('Verifying configuration backup... ');

        $checks = [];

        $checkResult = $this->checkFileExists($backupFile);
        $checks['file_exists'] = $checkResult;

        if (! $checkResult) {
            $results['checks']['configuration'] = false;
            $results['checks']['configuration_file_exists'] = false;
            $this->output->writeln('<error>FAILED</error>');
            return false;
        }

        $checkResult = $this->checkTarIntegrity($backupFile);
        $checks['tar_integrity'] = $checkResult;

        $checkResult = $this->checkConfigurationContent($backupFile);
        $checks['configuration_content'] = $checkResult;

        $checkResult = $this->checkFileSize($backupFile, 1024);
        $checks['file_size'] = $checkResult;

        $passed = $checks['file_exists'] && $checks['tar_integrity'] && $checks['configuration_content'] && $checks['file_size'];

        $results['checks']['configuration'] = $passed;
        $results['checks']['configuration_details'] = $checks;

        $this->output->writeln($passed ? '<info>OK</info>' : '<error>FAILED</error>');

        return $passed;
    }

    protected function verifyChecksums(string $backupFile, array &$results): bool
    {
        $this->output->write('Calculating checksums... ');

        if (! file_exists($backupFile)) {
            $results['checks']['checksum'] = false;
            $this->output->writeln('<error>FAILED - file does not exist</error>');
            return false;
        }

        $md5Hash = md5_file($backupFile);
        $sha256Hash = hash_file('sha256', $backupFile);
        $fileSize = filesize($backupFile);

        $results['checks']['checksum'] = true;
        $results['checksums'] = [
            'md5' => $md5Hash,
            'sha256' => $sha256Hash,
            'file_size' => $fileSize,
            'file_size_human' => $this->formatBytes($fileSize),
        ];

        $this->output->writeln('<info>OK</info>');
        $this->output->writeln('  MD5: ' . $md5Hash);
        $this->output->writeln('  SHA256: ' . $sha256Hash);
        $this->output->writeln('  Size: ' . $this->formatBytes($fileSize));

        return true;
    }

    protected function checkFileExists(string $backupFile): bool
    {
        return file_exists($backupFile) && is_readable($backupFile);
    }

    protected function checkTarIntegrity(string $backupFile): bool
    {
        if (! $this->isTarArchive($backupFile)) {
            return $this->checkFileExists($backupFile) && filesize($backupFile) > 0;
        }

        $command = 'tar -tzf ' . escapeshellarg($backupFile) . ' 2>/dev/null';

        $exitCode = 0;
        $output = [];
        exec($command, $output, $exitCode);

        return $exitCode === 0;
    }

    protected function isTarArchive(string $backupFile): bool
    {
        return str_ends_with($backupFile, '.tar')
               || str_ends_with($backupFile, '.tar.gz')
               || str_ends_with($backupFile, '.tgz');
    }

    protected function checkDatabaseContent(string $backupFile): bool
    {
        if (! $this->isTarArchive($backupFile)) {
            if (str_ends_with($backupFile, '.sql')) {
                $content = file_get_contents($backupFile);
                return ! empty($content) && (str_contains($content, '--') || str_contains($content, 'CREATE TABLE'));
            }
            if (str_ends_with($backupFile, '.db')) {
                return filesize($backupFile) > 1024;
            }
            return false;
        }

        $command = 'tar -tzf ' . escapeshellarg($backupFile) . ' 2>/dev/null | head -20';

        $exitCode = 0;
        $output = [];
        exec($command, $output, $exitCode);

        if ($exitCode !== 0) {
            return false;
        }

        $hasDbFile = false;
        foreach ($output as $file) {
            if (str_contains($file, '.sql') || str_contains($file, '.db')) {
                $hasDbFile = true;
                break;
            }
        }

        return $hasDbFile;
    }

    protected function checkFileSystemContent(string $backupFile): bool
    {
        if (! $this->isTarArchive($backupFile)) {
            return false;
        }

        $command = 'tar -tzf ' . escapeshellarg($backupFile) . ' 2>/dev/null | head -20';

        $exitCode = 0;
        $output = [];
        exec($command, $output, $exitCode);

        if ($exitCode !== 0) {
            return false;
        }

        $hasFileSystemContent = false;
        foreach ($output as $file) {
            if (str_contains($file, 'app/') || str_contains($file, 'config/')
                || str_contains($file, 'database/') || str_contains($file, 'resources/')) {
                $hasFileSystemContent = true;
                break;
            }
        }

        return $hasFileSystemContent;
    }

    protected function checkConfigurationContent(string $backupFile): bool
    {
        if (! $this->isTarArchive($backupFile)) {
            return false;
        }

        $command = 'tar -tzf ' . escapeshellarg($backupFile) . ' 2>/dev/null';

        $exitCode = 0;
        $output = [];
        exec($command, $output, $exitCode);

        if ($exitCode !== 0) {
            return false;
        }

        $hasEnvFile = false;
        $hasConfigDir = false;

        foreach ($output as $file) {
            if (str_contains($file, '.env')) {
                $hasEnvFile = true;
            }
            if (str_contains($file, 'config/')) {
                $hasConfigDir = true;
            }
        }

        return $hasEnvFile || $hasConfigDir;
    }

    protected function checkFileSize(string $backupFile, int $minSize): bool
    {
        return filesize($backupFile) >= $minSize;
    }

    protected function formatBytes(int $bytes, int $precision = 2): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];

        for ($i = 0; $bytes > 1024; ++$i) {
            $bytes /= 1024;
        }

        return round($bytes, $precision) . ' ' . $units[$i];
    }

    protected function configure(): void
    {
        $this->addArgument('backup-file', InputOption::VALUE_REQUIRED, 'Path to the backup file to verify');
        $this->addOption('type', null, InputOption::VALUE_OPTIONAL, 'Type of verification: database, filesystem, config, checksum, or all (default: all)', 'all');
    }
}
