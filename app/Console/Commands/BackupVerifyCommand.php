<?php

declare(strict_types=1);

namespace App\Console\Commands;

use Hyperf\Command\Command;
use Hyperf\Contract\ConfigInterface;
use Psr\Container\ContainerInterface;
use Hyperf\Logger\LoggerFactory;
use Psr\Log\LoggerInterface;

class BackupVerifyCommand extends Command
{
    protected ?string $signature = 'backup:verify {--type=} {--path=} {--report}';

    protected string $description = 'Verify backup integrity and generate reports';

    protected ContainerInterface $container;

    protected ConfigInterface $config;

    protected LoggerInterface $logger;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
        parent::__construct();
        $this->config = $container->get(ConfigInterface::class);
        $this->logger = $container->get(LoggerFactory::class)->get('backup');
    }

    public function handle()
    {
        $type = $this->option('type') ?: 'all';
        $backupPath = $this->option('path');
        $report = $this->option('report', false);

        $this->info("Starting backup verification for type: {$type}...");

        try {
            $results = [];
            
            switch ($type) {
                case 'database':
                    $results['database'] = $this->verifyDatabaseBackups($backupPath);
                    break;
                case 'filesystem':
                    $results['filesystem'] = $this->verifyFilesystemBackups($backupPath);
                    break;
                case 'config':
                    $results['config'] = $this->verifyConfigBackups($backupPath);
                    break;
                case 'all':
                    $results['database'] = $this->verifyDatabaseBackups();
                    $results['filesystem'] = $this->verifyFilesystemBackups();
                    $results['config'] = $this->verifyConfigBackups();
                    break;
                default:
                    $this->error("Invalid verification type: {$type}. Supported types: database, filesystem, config, all");
                    return 1;
            }

            $overallSuccess = true;
            foreach ($results as $type => $result) {
                if (!$result['success']) {
                    $overallSuccess = false;
                    break;
                }
            }

            if ($report) {
                $this->generateReport($results);
            }

            if ($overallSuccess) {
                $this->info("Backup verification completed successfully for type: {$type}");
                $this->logger->info("Backup verification completed", [
                    'type' => $type,
                    'results' => $results
                ]);
                return 0;
            } else {
                $this->error("Backup verification failed for type: {$type}");
                $this->logger->error("Backup verification failed", [
                    'type' => $type,
                    'results' => $results
                ]);
                return 1;
            }
        } catch (\Exception $e) {
            $this->error("Backup verification failed with error: " . $e->getMessage());
            $this->logger->error("Backup verification failed", [
                'type' => $type,
                'error' => $e->getMessage()
            ]);
            return 1;
        }
    }

    protected function verifyDatabaseBackups(?string $backupPath = null): array
    {
        if (!$backupPath) {
            $backupPath = $this->getStoragePath('backups/database');
        }

        if (!is_dir($backupPath)) {
            $this->error("Database backup directory does not exist: {$backupPath}");
            return ['success' => false, 'message' => "Directory does not exist: {$backupPath}"];
        }

        $files = glob($backupPath . '/database-backup-*.sql*');
        $results = [
            'total_backups' => count($files),
            'verified_backups' => 0,
            'failed_backups' => 0,
            'backups' => []
        ];

        foreach ($files as $file) {
            $filename = basename($file);
            $fileSize = filesize($file);
            $lastModified = date('Y-m-d H:i:s', filemtime($file));
            
            // Check if file is not empty and has reasonable size
            $isValid = $fileSize > 0;
            
            // For SQL files, we can check if they contain expected content
            if (substr($file, -4) === '.sql' || substr($file, -7) === '.sql.gz') {
                // For compressed files, we'll just check if they can be decompressed
                if (substr($file, -3) === '.gz') {
                    $isValid = $this->canDecompress($file);
                } else {
                    // For SQL files, check if they contain basic SQL structure
                    $content = file_get_contents($file, false, null, 0, 1000); // Read first 1000 chars
                    $isValid = $content !== false && (strpos($content, 'CREATE TABLE') !== false || 
                                                     strpos($content, 'INSERT INTO') !== false ||
                                                     strpos($content, 'DROP TABLE') !== false);
                }
            }

            $result = [
                'filename' => $filename,
                'size' => $fileSize,
                'last_modified' => $lastModified,
                'valid' => $isValid
            ];

            $results['backups'][] = $result;

            if ($isValid) {
                $results['verified_backups']++;
            } else {
                $results['failed_backups']++;
            }
        }

        $results['success'] = $results['failed_backups'] === 0 && $results['total_backups'] > 0;
        $results['message'] = $results['success'] ? 
            "All {$results['verified_backups']} database backups are valid" : 
            "{$results['failed_backups']} out of {$results['total_backups']} database backups failed verification";

        return $results;
    }

    protected function verifyFilesystemBackups(?string $backupPath = null): array
    {
        if (!$backupPath) {
            $backupPath = $this->getStoragePath('backups/filesystem');
        }

        if (!is_dir($backupPath)) {
            $this->error("File system backup directory does not exist: {$backupPath}");
            return ['success' => false, 'message' => "Directory does not exist: {$backupPath}"];
        }

        $files = glob($backupPath . '/filesystem-backup-*.tar*');
        $results = [
            'total_backups' => count($files),
            'verified_backups' => 0,
            'failed_backups' => 0,
            'backups' => []
        ];

        foreach ($files as $file) {
            $filename = basename($file);
            $fileSize = filesize($file);
            $lastModified = date('Y-m-d H:i:s', filemtime($file));
            
            // Check if file is not empty and has reasonable size
            $isValid = $fileSize > 0;
            
            // For tar files, we can check if they can be listed
            if (substr($file, -4) === '.tar' || substr($file, -7) === '.tar.gz') {
                $isValid = $this->canListTar($file);
            }

            $result = [
                'filename' => $filename,
                'size' => $fileSize,
                'last_modified' => $lastModified,
                'valid' => $isValid
            ];

            $results['backups'][] = $result;

            if ($isValid) {
                $results['verified_backups']++;
            } else {
                $results['failed_backups']++;
            }
        }

        $results['success'] = $results['failed_backups'] === 0 && $results['total_backups'] > 0;
        $results['message'] = $results['success'] ? 
            "All {$results['verified_backups']} file system backups are valid" : 
            "{$results['failed_backups']} out of {$results['total_backups']} file system backups failed verification";

        return $results;
    }

    protected function verifyConfigBackups(?string $backupPath = null): array
    {
        if (!$backupPath) {
            $backupPath = $this->getStoragePath('backups/config');
        }

        if (!is_dir($backupPath)) {
            $this->error("Config backup directory does not exist: {$backupPath}");
            return ['success' => false, 'message' => "Directory does not exist: {$backupPath}"];
        }

        $files = glob($backupPath . '/config-backup-*.tar*');
        $results = [
            'total_backups' => count($files),
            'verified_backups' => 0,
            'failed_backups' => 0,
            'backups' => []
        ];

        foreach ($files as $file) {
            $filename = basename($file);
            $fileSize = filesize($file);
            $lastModified = date('Y-m-d H:i:s', filemtime($file));
            
            // Check if file is not empty and has reasonable size
            $isValid = $fileSize > 0;
            
            // For tar files, we can check if they can be listed
            if (substr($file, -4) === '.tar' || substr($file, -7) === '.tar.gz') {
                $isValid = $this->canListTar($file);
            }

            $result = [
                'filename' => $filename,
                'size' => $fileSize,
                'last_modified' => $lastModified,
                'valid' => $isValid
            ];

            $results['backups'][] = $result;

            if ($isValid) {
                $results['verified_backups']++;
            } else {
                $results['failed_backups']++;
            }
        }

        $results['success'] = $results['failed_backups'] === 0 && $results['total_backups'] > 0;
        $results['message'] = $results['success'] ? 
            "All {$results['verified_backups']} config backups are valid" : 
            "{$results['failed_backups']} out of {$results['total_backups']} config backups failed verification";

        return $results;
    }

    protected function canDecompress(string $file): bool
    {
        $tempFile = tempnam(sys_get_temp_dir(), 'verify_');
        if ($tempFile === false) {
            return false;
        }

        $success = $this->decompressFile($file, $tempFile);
        if ($success) {
            unlink($tempFile);
        }

        return $success;
    }

    protected function canListTar(string $file): bool
    {
        $cmd = "tar -tzf {$file} 2>/dev/null | head -n 1";
        $output = shell_exec($cmd);
        
        // If the command returns any output, the tar file is valid
        return $output !== null;
    }

    protected function decompressFile(string $compressedFile, string $outputFile): bool
    {
        $gzFile = gzopen($compressedFile, 'rb');
        if (!$gzFile) {
            return false;
        }

        $file = fopen($outputFile, 'wb');
        if (!$file) {
            gzclose($gzFile);
            return false;
        }

        while (!gzeof($gzFile)) {
            fwrite($file, gzread($gzFile, 4096));
        }

        fclose($file);
        gzclose($gzFile);

        return true;
    }

    protected function generateReport(array $results): void
    {
        $reportPath = $this->getStoragePath('reports');
        if (!is_dir($reportPath)) {
            mkdir($reportPath, 0755, true);
        }

        $timestamp = date('Y-m-d-H-i-s');
        $reportFile = $reportPath . '/backup-verification-report-' . $timestamp . '.txt';

        $reportContent = "Backup Verification Report - " . date('Y-m-d H:i:s') . "\n";
        $reportContent .= str_repeat("=", 50) . "\n\n";

        foreach ($results as $type => $result) {
            $reportContent .= "Type: {$type}\n";
            $reportContent .= "  Total Backups: {$result['total_backups']}\n";
            $reportContent .= "  Verified: {$result['verified_backups']}\n";
            $reportContent .= "  Failed: {$result['failed_backups']}\n";
            $reportContent .= "  Status: " . ($result['success'] ? 'PASS' : 'FAIL') . "\n";
            $reportContent .= "  Message: {$result['message']}\n\n";

            if (isset($result['backups']) && is_array($result['backups'])) {
                $reportContent .= "  Backup Details:\n";
                foreach ($result['backups'] as $backup) {
                    $reportContent .= "    - {$backup['filename']} ({$backup['size']} bytes, {$backup['last_modified']}, " . 
                                    ($backup['valid'] ? 'VALID' : 'INVALID') . ")\n";
                }
                $reportContent .= "\n";
            }
        }

        file_put_contents($reportFile, $reportContent);
        $this->info("Verification report generated: {$reportFile}");
    }

    private function getStoragePath(string $subPath = ''): string
    {
        $storagePath = BASE_PATH . '/storage';
        if ($subPath) {
            $storagePath .= '/' . ltrim($subPath, '/');
        }
        return $storagePath;
    }
}