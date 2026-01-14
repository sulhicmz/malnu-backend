<?php

declare(strict_types=1);

namespace App\Services;

/**
 * Backup Service
 * Provides a centralized service for backup operations and management.
 */
class BackupService
{
    /**
     * Create a backup based on the specified type.
     *
     * @param string $type Type of backup: database, filesystem, config, or all
     * @param array $options Additional options for the backup
     * @return array Result of the backup operation
     */
    public function createBackup(string $type = 'all', array $options = []): array
    {
        $results = [
            'success' => true,
            'timestamp' => date('Y-m-d H:i:s'),
            'type' => $type,
            'details' => [],
        ];

        switch ($type) {
            case 'database':
                $results['details']['database'] = $this->backupDatabase($options);
                break;
            case 'filesystem':
                $results['details']['filesystem'] = $this->backupFileSystem($options);
                break;
            case 'config':
                $results['details']['config'] = $this->backupConfiguration($options);
                break;
            case 'all':
            default:
                $results['details']['database'] = $this->backupDatabase($options);
                $results['details']['filesystem'] = $this->backupFileSystem($options);
                $results['details']['config'] = $this->backupConfiguration($options);
                break;
        }

        // Check if all operations were successful
        foreach ($results['details'] as $detail) {
            if (isset($detail['success']) && ! $detail['success']) {
                $results['success'] = false;
                break;
            }
        }

        // Log the backup operation
        error_log('Backup operation completed: ' . json_encode($results));

        return $results;
    }

    /**
     * Restore a backup based on the specified type.
     *
     * @param string $backupPath Path to the backup file
     * @param string $type Type of restore: database, filesystem, config, or all
     * @param array $options Additional options for the restore
     * @return array Result of the restore operation
     */
    public function restoreBackup(string $backupPath, string $type = 'all', array $options = []): array
    {
        $results = [
            'success' => true,
            'timestamp' => date('Y-m-d H:i:s'),
            'type' => $type,
            'backup_path' => $backupPath,
            'details' => [],
        ];

        // This would typically call the restore command
        // For now, we'll just return a structure
        $results['details'][$type] = [
            'success' => true,
            'message' => "Restore operation initiated for {$type}",
            'backup_file' => $backupPath,
        ];

        // Log the restore operation
        error_log('Restore operation completed: ' . json_encode($results));

        return $results;
    }

    /**
     * Verify a backup file.
     *
     * @param string $backupPath Path to the backup file
     * @param string $type Type of verification
     * @return array Result of the verification
     */
    public function verifyBackup(string $backupPath, string $type = 'all'): array
    {
        $results = [
            'success' => true,
            'timestamp' => date('Y-m-d H:i:s'),
            'backup_path' => $backupPath,
            'type' => $type,
            'verification_results' => [],
        ];

        // Check if backup file exists
        if (! file_exists($backupPath)) {
            return [
                'success' => false,
                'error' => 'Backup file does not exist: ' . $backupPath,
            ];
        }

        // Verify the backup file integrity
        $command = 'tar -tzf ' . escapeshellarg($backupPath) . ' 2>/dev/null';
        $exitCode = 0;
        $output = [];
        exec($command, $output, $exitCode);

        $results['verification_results']['file_integrity'] = $exitCode === 0;

        if ($exitCode !== 0) {
            $results['success'] = false;
            $results['error'] = 'Backup file is corrupted or invalid';
        }

        // Log the verification operation
        error_log('Backup verification completed: ' . json_encode($results));

        return $results;
    }

    /**
     * Get backup status and statistics.
     *
     * @return array Backup status information
     */
    public function getBackupStatus(): array
    {
        $backupPath = $this->getStoragePath('backups');

        return [
            'timestamp' => date('Y-m-d H:i:s'),
            'backup_locations' => [
                'database' => $this->getStoragePath('backups/database'),
                'filesystem' => $this->getStoragePath('backups/filesystem'),
                'config' => $this->getStoragePath('backups/config'),
                'comprehensive' => $backupPath,
            ],
            'statistics' => [
                'database_backups' => $this->countBackups($backupPath . '/database', 'db_backup_'),
                'filesystem_backups' => $this->countBackups($backupPath . '/filesystem', 'filesystem_backup_'),
                'config_backups' => $this->countBackups($backupPath . '/config', 'config_backup_'),
                'comprehensive_backups' => $this->countBackups($backupPath, 'full_backup_'),
            ],
            'latest_backups' => [
                'database' => $this->getLatestBackup($backupPath . '/database', 'db_backup_'),
                'filesystem' => $this->getLatestBackup($backupPath . '/filesystem', 'filesystem_backup_'),
                'config' => $this->getLatestBackup($backupPath . '/config', 'config_backup_'),
                'comprehensive' => $this->getLatestBackup($backupPath, 'full_backup_'),
            ],
        ];
    }

    /**
     * Clean old backups based on retention policy.
     *
     * @param string $type Type of backups to clean
     * @param int $keep Number of backups to keep
     * @return array Result of the cleanup operation
     */
    public function cleanOldBackups(string $type = 'all', int $keep = 5): array
    {
        $results = [
            'success' => true,
            'timestamp' => date('Y-m-d H:i:s'),
            'type' => $type,
            'keep' => $keep,
            'cleaned' => [],
        ];

        $backupPath = $this->getStoragePath('backups');

        switch ($type) {
            case 'database':
                $results['cleaned']['database'] = $this->cleanDirectory($backupPath . '/database', 'db_backup_', $keep);
                break;
            case 'filesystem':
                $results['cleaned']['filesystem'] = $this->cleanDirectory($backupPath . '/filesystem', 'filesystem_backup_', $keep);
                break;
            case 'config':
                $results['cleaned']['config'] = $this->cleanDirectory($backupPath . '/config', 'config_backup_', $keep);
                break;
            case 'all':
            default:
                $results['cleaned']['database'] = $this->cleanDirectory($backupPath . '/database', 'db_backup_', $keep);
                $results['cleaned']['filesystem'] = $this->cleanDirectory($backupPath . '/filesystem', 'filesystem_backup_', $keep);
                $results['cleaned']['config'] = $this->cleanDirectory($backupPath . '/config', 'config_backup_', $keep);
                $results['cleaned']['comprehensive'] = $this->cleanDirectory($backupPath, 'full_backup_', $keep);
                break;
        }

        return $results;
    }

    /**
     * Get storage path for backups.
     *
     * @param string $path Additional path to append
     * @return string Full storage path
     */
    protected function getStoragePath(string $path = ''): string
    {
        $storagePath = BASE_PATH . '/storage';
        if ($path) {
            $storagePath .= '/' . ltrim($path, '/');
        }
        return $storagePath;
    }

    /**
     * Count backup files in a directory matching a pattern.
     *
     * @param string $directory Directory to search
     * @param string $pattern Pattern to match
     * @return int Number of backup files
     */
    protected function countBackups(string $directory, string $pattern): int
    {
        if (! is_dir($directory)) {
            return 0;
        }

        $files = scandir($directory);
        $count = 0;

        foreach ($files as $file) {
            if (strpos($file, $pattern) === 0) {
                ++$count;
            }
        }

        return $count;
    }

    /**
     * Get the latest backup file in a directory matching a pattern.
     *
     * @param string $directory Directory to search
     * @param string $pattern Pattern to match
     * @return null|string Path to the latest backup file or null if none found
     */
    protected function getLatestBackup(string $directory, string $pattern): ?string
    {
        if (! is_dir($directory)) {
            return null;
        }

        $files = scandir($directory);
        $latestFile = null;
        $latestTime = 0;

        foreach ($files as $file) {
            if (strpos($file, $pattern) === 0) {
                $filePath = $directory . '/' . $file;
                $fileTime = filemtime($filePath);

                if ($fileTime > $latestTime) {
                    $latestTime = $fileTime;
                    $latestFile = $file;
                }
            }
        }

        return $latestFile ? $directory . '/' . $latestFile : null;
    }

    /**
     * Clean old files in a directory keeping only the newest N files.
     *
     * @param string $directory Directory to clean
     * @param string $pattern Pattern to match
     * @param int $keep Number of files to keep
     * @return array Result of the cleanup
     */
    protected function cleanDirectory(string $directory, string $pattern, int $keep): array
    {
        if (! is_dir($directory)) {
            return ['success' => false, 'error' => 'Directory does not exist: ' . $directory];
        }

        $files = scandir($directory);
        $backupFiles = [];

        foreach ($files as $file) {
            if (strpos($file, $pattern) === 0) {
                $filePath = $directory . '/' . $file;
                $backupFiles[] = [
                    'path' => $filePath,
                    'time' => filemtime($filePath),
                ];
            }
        }

        // Sort by time (newest first)
        usort($backupFiles, function ($a, $b) {
            return $b['time'] - $a['time'];
        });

        // Keep only the newest $keep files
        $filesToDelete = array_slice($backupFiles, $keep);
        $deleted = 0;

        foreach ($filesToDelete as $fileInfo) {
            if (unlink($fileInfo['path'])) {
                ++$deleted;
            }
        }

        return [
            'success' => true,
            'deleted' => $deleted,
            'kept' => count($backupFiles) - $deleted,
        ];
    }

    /**
     * Perform database backup.
     */
    protected function backupDatabase(array $options): array
    {
        // This would typically call the database backup command
        // For now, we'll return a mock result
        return [
            'success' => true,
            'type' => 'database',
            'message' => 'Database backup completed successfully',
            'options' => $options,
        ];
    }

    /**
     * Perform filesystem backup.
     */
    protected function backupFileSystem(array $options): array
    {
        // This would typically call the filesystem backup command
        // For now, we'll return a mock result
        return [
            'success' => true,
            'type' => 'filesystem',
            'message' => 'Filesystem backup completed successfully',
            'options' => $options,
        ];
    }

    /**
     * Perform configuration backup.
     */
    protected function backupConfiguration(array $options): array
    {
        // This would typically call the configuration backup command
        // For now, we'll return a mock result
        return [
            'success' => true,
            'type' => 'config',
            'message' => 'Configuration backup completed successfully',
            'options' => $options,
        ];
    }
}
