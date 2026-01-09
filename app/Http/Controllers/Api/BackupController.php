<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Services\BackupService;

class BackupController extends BaseController
{
    private BackupService $backupService;

    public function __construct()
    {
        parent::__construct();
        $this->backupService = new BackupService();
    }

    public function getStatus()
    {
        try {
            $status = $this->backupService->getBackupStatus();
            return $this->successResponse($status, 'Backup status retrieved successfully');
        } catch (\Exception $e) {
            return $this->serverErrorResponse('Failed to retrieve backup status: ' . $e->getMessage());
        }
    }

    public function verifyBackup()
    {
        try {
            $backupFile = $this->request->input('backup_file');
            
            if (empty($backupFile)) {
                return $this->validationErrorResponse(['backup_file' => ['Backup file is required']]);
            }

            $sanitizedFile = basename($backupFile);
            $backupPath = BASE_PATH . '/storage/backups/' . $sanitizedFile;

            if (! file_exists($backupPath)) {
                return $this->notFoundResponse('Backup file not found');
            }

            $result = $this->backupService->verifyBackup($backupPath, 'all');
            
            if ($result['success']) {
                return $this->successResponse($result, 'Backup verification passed');
            }
            
            return $this->errorResponse('Backup verification failed', 'BACKUP_VERIFICATION_FAILED', $result);
        } catch (\Exception $e) {
            return $this->serverErrorResponse('Failed to verify backup: ' . $e->getMessage());
        }
    }

    public function listBackups()
    {
        try {
            $status = $this->backupService->getBackupStatus();
            
            $backups = [
                'database' => $this->getBackupFiles('database'),
                'filesystem' => $this->getBackupFiles('filesystem'),
                'config' => $this->getBackupFiles('config'),
                'comprehensive' => $this->getBackupFiles('comprehensive'),
            ];

            return $this->successResponse([
                'backups' => $backups,
                'statistics' => $status['statistics'],
            ], 'Backup list retrieved successfully');
        } catch (\Exception $e) {
            return $this->serverErrorResponse('Failed to list backups: ' . $e->getMessage());
        }
    }

    public function getLatestBackups()
    {
        try {
            $status = $this->backupService->getBackupStatus();
            return $this->successResponse($status['latest_backups'], 'Latest backups retrieved successfully');
        } catch (\Exception $e) {
            return $this->serverErrorResponse('Failed to retrieve latest backups: ' . $e->getMessage());
        }
    }

    protected function getBackupFiles(string $type): array
    {
        $backupPath = BASE_PATH . '/storage/backups';
        
        $pattern = match($type) {
            'database' => 'db_backup_*',
            'filesystem' => 'filesystem_backup_*',
            'config' => 'config_backup_*',
            'comprehensive' => 'full_backup_*',
            default => '*_backup_*',
        };

        $files = [];
        
        if ($type === 'comprehensive') {
            $dir = $backupPath;
        } else {
            $dir = $backupPath . '/' . $type;
        }

        if (is_dir($dir)) {
            $allFiles = scandir($dir);
            
            foreach ($allFiles as $file) {
                if ($file !== '.' && $file !== '..') {
                    if (str_starts_with($file, substr($pattern, 0, -1))) {
                        $filePath = $dir . '/' . $file;
                        $files[] = [
                            'name' => $file,
                            'size' => filesize($filePath),
                            'size_formatted' => $this->formatBytes(filesize($filePath)),
                            'modified' => date('Y-m-d H:i:s', filemtime($filePath)),
                        ];
                    }
                }
            }
        }

        usort($files, function ($a, $b) {
            return strtotime($b['modified']) - strtotime($a['modified']);
        });

        return $files;
    }

    protected function formatBytes(int $bytes, int $precision = 2): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];

        for ($i = 0; $bytes > 1024; $i++) {
            $bytes /= 1024;
        }

        return round($bytes, $precision) . ' ' . $units[$i];
    }
}
