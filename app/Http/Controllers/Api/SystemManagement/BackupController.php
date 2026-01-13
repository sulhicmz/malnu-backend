<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\SystemManagement;

use App\Http\Controllers\Api\BaseController;
use App\Services\BackupService;
use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Annotation\GetMapping;
use Hyperf\HttpServer\Annotation\PostMapping;
use Hyperf\HttpServer\Annotation\DeleteMapping;
use Hyperf\HttpServer\Annotation\RequestMapping;
use Psr\Container\ContainerInterface;

#[Controller]
class BackupController extends BaseController
{
    private BackupService $backupService;

    public function __construct(BackupService $backupService, ContainerInterface $container)
    {
        parent::__construct($container->get('request'), $container->get('response'), $container);
        $this->backupService = $backupService;
    }

    /**
     * List all backups with optional filtering
     */
    #[GetMapping(path: '/api/backups')]
    public function index()
    {
        try {
            $type = $this->request->input('type', 'all');
            $status = $this->backupService->getBackupStatus();

            return $this->successResponse($status, 'Backup list retrieved successfully');
        } catch (\Exception $e) {
            $this->logger->error('Failed to list backups', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return $this->serverErrorResponse('Failed to retrieve backup list');
        }
    }

    /**
     * Get detailed backup information
     */
    #[GetMapping(path: '/api/backups/{id}')]
    public function show(string $id)
    {
        try {
            $backupPath = $this->sanitizePath($id);
            
            if (!file_exists($backupPath)) {
                return $this->notFoundResponse('Backup file not found');
            }

            $backupInfo = [
                'path' => $backupPath,
                'size' => filesize($backupPath),
                'size_formatted' => $this->formatBytes(filesize($backupPath)),
                'modified' => date('c', filemtime($backupPath)),
                'checksum' => [
                    'md5' => md5_file($backupPath),
                    'sha256' => hash_file('sha256', $backupPath)
                ]
            ];

            return $this->successResponse($backupInfo, 'Backup details retrieved successfully');
        } catch (\Exception $e) {
            $this->logger->error('Failed to get backup details', [
                'backup_id' => $id,
                'error' => $e->getMessage()
            ]);
            return $this->serverErrorResponse('Failed to retrieve backup details');
        }
    }

    /**
     * Create a new backup
     */
    #[PostMapping(path: '/api/backups')]
    public function store()
    {
        try {
            $data = $this->request->all();
            $type = $data['type'] ?? 'all';
            $encrypt = $data['encrypt'] ?? false;

            if (!in_array($type, ['database', 'filesystem', 'config', 'all'])) {
                return $this->errorResponse('Invalid backup type', 'INVALID_TYPE', null, 422);
            }

            $options = [
                'encrypt' => $encrypt
            ];

            $result = $this->backupService->createBackup($type, $options);

            if (!$result['success']) {
                return $this->errorResponse(
                    'Backup creation failed',
                    'BACKUP_FAILED',
                    $result['details'],
                    500
                );
            }

            $this->logger->info('Backup created successfully', [
                'type' => $type,
                'timestamp' => $result['timestamp']
            ]);

            return $this->successResponse($result, 'Backup created successfully', 201);
        } catch (\Exception $e) {
            $this->logger->error('Failed to create backup', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return $this->serverErrorResponse('Failed to create backup');
        }
    }

    /**
     * Restore from a backup
     */
    #[PostMapping(path: '/api/backups/{id}/restore')]
    public function restore(string $id)
    {
        try {
            $data = $this->request->all();
            $type = $data['type'] ?? 'all';
            $backupPath = $this->sanitizePath($id);

            if (!file_exists($backupPath)) {
                return $this->notFoundResponse('Backup file not found');
            }

            $result = $this->backupService->restoreBackup($backupPath, $type);

            if (!$result['success']) {
                return $this->errorResponse(
                    'Backup restoration failed',
                    'RESTORE_FAILED',
                    $result['details'],
                    500
                );
            }

            $this->logger->warning('Backup restore initiated', [
                'backup_path' => $backupPath,
                'type' => $type
            ]);

            return $this->successResponse($result, 'Backup restoration initiated successfully');
        } catch (\Exception $e) {
            $this->logger->error('Failed to restore backup', [
                'backup_id' => $id,
                'error' => $e->getMessage()
            ]);
            return $this->serverErrorResponse('Failed to restore backup');
        }
    }

    /**
     * Verify a backup
     */
    #[PostMapping(path: '/api/backups/{id}/verify')]
    public function verify(string $id)
    {
        try {
            $data = $this->request->all();
            $type = $data['type'] ?? 'all';
            $backupPath = $this->sanitizePath($id);

            if (!file_exists($backupPath)) {
                return $this->notFoundResponse('Backup file not found');
            }

            $result = $this->backupService->verifyBackup($backupPath, $type);

            if (!$result['success']) {
                return $this->errorResponse(
                    'Backup verification failed',
                    'VERIFICATION_FAILED',
                    ['error' => $result['error'] ?? 'Unknown error'],
                    400
                );
            }

            return $this->successResponse($result, 'Backup verified successfully');
        } catch (\Exception $e) {
            $this->logger->error('Failed to verify backup', [
                'backup_id' => $id,
                'error' => $e->getMessage()
            ]);
            return $this->serverErrorResponse('Failed to verify backup');
        }
    }

    /**
     * Delete a backup
     */
    #[DeleteMapping(path: '/api/backups/{id}')]
    public function destroy(string $id)
    {
        try {
            $backupPath = $this->sanitizePath($id);

            if (!file_exists($backupPath)) {
                return $this->notFoundResponse('Backup file not found');
            }

            $deleted = unlink($backupPath);

            if (!$deleted) {
                return $this->serverErrorResponse('Failed to delete backup');
            }

            $this->logger->warning('Backup deleted', [
                'backup_path' => $backupPath
            ]);

            return $this->successResponse(null, 'Backup deleted successfully');
        } catch (\Exception $e) {
            $this->logger->error('Failed to delete backup', [
                'backup_id' => $id,
                'error' => $e->getMessage()
            ]);
            return $this->serverErrorResponse('Failed to delete backup');
        }
    }

    /**
     * Get backup system status
     */
    #[GetMapping(path: '/api/backups/status')]
    public function status()
    {
        try {
            $status = $this->backupService->getBackupStatus();
            
            $status['system_health'] = [
                'disk_space' => [
                    'free' => disk_free_space(BASE_PATH),
                    'free_formatted' => $this->formatBytes(disk_free_space(BASE_PATH)),
                    'total' => disk_total_space(BASE_PATH),
                    'total_formatted' => $this->formatBytes(disk_total_space(BASE_PATH))
                ],
                'backup_directory' => [
                    'writable' => is_writable(BASE_PATH . '/storage'),
                    'exists' => is_dir(BASE_PATH . '/storage')
                ]
            ];

            return $this->successResponse($status, 'Backup system status retrieved successfully');
        } catch (\Exception $e) {
            $this->logger->error('Failed to get backup status', [
                'error' => $e->getMessage()
            ]);
            return $this->serverErrorResponse('Failed to retrieve backup system status');
        }
    }

    /**
     * Clean old backups based on retention policy
     */
    #[PostMapping(path: '/api/backups/clean')]
    public function clean()
    {
        try {
            $data = $this->request->all();
            $type = $data['type'] ?? 'all';
            $keep = (int) ($data['keep'] ?? 5);

            if ($keep < 1 || $keep > 100) {
                return $this->errorResponse('Keep count must be between 1 and 100', 'INVALID_KEEP', null, 422);
            }

            $result = $this->backupService->cleanOldBackups($type, $keep);

            $this->logger->info('Backup cleanup completed', [
                'type' => $type,
                'keep' => $keep,
                'result' => $result
            ]);

            return $this->successResponse($result, 'Backup cleanup completed successfully');
        } catch (\Exception $e) {
            $this->logger->error('Failed to clean backups', [
                'error' => $e->getMessage()
            ]);
            return $this->serverErrorResponse('Failed to clean backups');
        }
    }

    /**
     * Sanitize file path to prevent directory traversal
     */
    protected function sanitizePath(string $path): string
    {
        $path = str_replace(['..', '\\'], '', $path);
        $backupBasePath = BASE_PATH . '/storage/backups';
        
        if (!str_starts_with($path, $backupBasePath)) {
            return $backupBasePath . '/' . ltrim($path, '/');
        }
        
        return $path;
    }

    /**
     * Format bytes to human readable format
     */
    protected function formatBytes(int $bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        $bytes /= pow(1024, $pow);
        return round($bytes, 2) . ' ' . $units[$pow];
    }
}
