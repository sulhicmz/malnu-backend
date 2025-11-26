<?php

declare(strict_types=1);

namespace App\Services;

use Carbon\Carbon;

class BackupService
{
    /**
     * Create a database backup
     */
    public function createDatabaseBackup(): string
    {
        $timestamp = date('Y-m-d-H-i-s');
        $backupDir = BASE_PATH . '/storage/backups/database';
        $filename = "backup_{$timestamp}.sql";
        $fullPath = $backupDir . '/' . $filename;

        // Create backup directory if it doesn't exist
        if (!is_dir($backupDir)) {
            mkdir($backupDir, 0755, true);
        }

        // For now, create a simple placeholder backup file
        // In a real implementation, this would connect to the database and dump the schema/data
        $backupContent = "-- Database Backup Created: " . date('c') . "\n";
        $backupContent .= "-- This is a placeholder backup file\n";
        
        file_put_contents($fullPath, $backupContent);

        // Log the backup
        $this->logBackup($filename, $fullPath);

        return $fullPath;
    }

    /**
     * Create a file backup
     */
    public function createFileBackup(array $directories = []): string
    {
        if (empty($directories)) {
            $directories = [
                BASE_PATH . '/app',
                BASE_PATH . '/config',
                BASE_PATH . '/resources'
            ];
        }

        $timestamp = date('Y-m-d-H-i-s');
        $backupDir = BASE_PATH . '/storage/backups/files';
        $filename = "files_backup_{$timestamp}.tar.gz";
        $fullPath = $backupDir . '/' . $filename;

        // Create backup directory if it doesn't exist
        if (!is_dir($backupDir)) {
            mkdir($backupDir, 0755, true);
        }

        // For now, create a simple placeholder file
        // In a real implementation, this would create an actual archive
        file_put_contents($fullPath, "Placeholder for file backup");

        // Log the backup
        $this->logBackup($filename, $fullPath, 'files');

        return $fullPath;
    }

    /**
     * Create a full system backup
     */
    public function createFullBackup(): array
    {
        $results = [
            'database' => $this->createDatabaseBackup(),
            'files' => $this->createFileBackup()
        ];

        return $results;
    }

    /**
     * Get list of available backups
     */
    public function getAvailableBackups(string $type = 'all'): array
    {
        $backups = [];
        $baseDir = BASE_PATH . '/storage/backups';

        if ($type === 'all' || $type === 'database') {
            $dbDir = $baseDir . '/database';
            if (is_dir($dbDir)) {
                $dbFiles = array_diff(scandir($dbDir), ['.', '..']);
                foreach ($dbFiles as $file) {
                    $path = $dbDir . '/' . $file;
                    $backups[] = [
                        'type' => 'database',
                        'filename' => $file,
                        'path' => $path,
                        'size' => filesize($path),
                        'created_at' => date('c', filemtime($path))
                    ];
                }
            }
        }

        if ($type === 'all' || $type === 'files') {
            $filesDir = $baseDir . '/files';
            if (is_dir($filesDir)) {
                $fileFiles = array_diff(scandir($filesDir), ['.', '..']);
                foreach ($fileFiles as $file) {
                    $path = $filesDir . '/' . $file;
                    $backups[] = [
                        'type' => 'files',
                        'filename' => $file,
                        'path' => $path,
                        'size' => filesize($path),
                        'created_at' => date('c', filemtime($path))
                    ];
                }
            }
        }

        // Sort by creation date (newest first)
        usort($backups, function ($a, $b) {
            return strtotime($b['created_at']) - strtotime($a['created_at']);
        });

        return $backups;
    }

    /**
     * Restore from backup
     */
    public function restoreFromBackup(string $backupPath): bool
    {
        if (!file_exists($backupPath)) {
            throw new \Exception("Backup file does not exist: {$backupPath}");
        }

        // In a real implementation, this would restore the backup
        // For now, just return true to indicate the method exists
        return true;
    }

    /**
     * Delete a backup
     */
    public function deleteBackup(string $backupPath): bool
    {
        if (file_exists($backupPath)) {
            return unlink($backupPath);
        }
        return false;
    }

    /**
     * Log backup activity
     */
    private function logBackup(string $filename, string $path, string $type = 'database'): void
    {
        $logData = [
            'timestamp' => date('c'),
            'type' => $type,
            'filename' => $filename,
            'path' => $path,
            'size' => filesize($path),
            'status' => 'completed'
        ];

        $logFile = BASE_PATH . '/storage/logs/backup.log';
        file_put_contents($logFile, json_encode($logData) . PHP_EOL, FILE_APPEND | LOCK_EX);
    }

    /**
     * Clean old backups based on retention policy
     */
    public function cleanOldBackups(int $daysToKeep = 30): int
    {
        $deletedCount = 0;
        $cutoffTime = time() - ($daysToKeep * 24 * 60 * 60);

        $backupDirs = [
            BASE_PATH . '/storage/backups/database',
            BASE_PATH . '/storage/backups/files'
        ];

        foreach ($backupDirs as $dir) {
            if (is_dir($dir)) {
                $files = array_diff(scandir($dir), ['.', '..']);
                foreach ($files as $file) {
                    $filePath = $dir . '/' . $file;
                    if (filemtime($filePath) < $cutoffTime) {
                        if (unlink($filePath)) {
                            $deletedCount++;
                        }
                    }
                }
            }
        }

        return $deletedCount;
    }
}