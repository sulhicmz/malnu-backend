<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Services\BackupService;
use Hyperf\HttpServer\Contract\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class BackupController extends Controller
{
    protected BackupService $backupService;
    protected ResponseInterface $response;

    public function __construct(BackupService $backupService, ResponseInterface $response)
    {
        $this->backupService = $backupService;
        $this->response = $response;
    }

    /**
     * Create a new backup
     */
    public function createBackup(ServerRequestInterface $request)
    {
        try {
            $params = $request->getQueryParams();
            $type = $params['type'] ?? 'full';

            switch ($type) {
                case 'database':
                    $path = $this->backupService->createDatabaseBackup();
                    $message = "Database backup created successfully";
                    break;
                case 'files':
                    $path = $this->backupService->createFileBackup();
                    $message = "File backup created successfully";
                    break;
                case 'full':
                default:
                    $results = $this->backupService->createFullBackup();
                    $path = $results;
                    $message = "Full backup created successfully";
                    break;
            }

            return $this->response->json([
                'success' => true,
                'message' => $message,
                'data' => [
                    'path' => $path,
                    'type' => $type,
                    'timestamp' => date('c')
                ]
            ]);
        } catch (\Exception $e) {
            return $this->response->json([
                'success' => false,
                'message' => 'Backup creation failed: ' . $e->getMessage()
            ])->withStatus(500);
        }
    }

    /**
     * Get list of available backups
     */
    public function listBackups(ServerRequestInterface $request)
    {
        try {
            $params = $request->getQueryParams();
            $type = $params['type'] ?? 'all';

            $backups = $this->backupService->getAvailableBackups($type);

            return $this->response->json([
                'success' => true,
                'data' => $backups
            ]);
        } catch (\Exception $e) {
            return $this->response->json([
                'success' => false,
                'message' => 'Failed to list backups: ' . $e->getMessage()
            ])->withStatus(500);
        }
    }

    /**
     * Restore from a backup
     */
    public function restoreBackup(ServerRequestInterface $request)
    {
        try {
            $params = $request->getParsedBody();
            $backupPath = $params['path'] ?? null;

            if (!$backupPath) {
                return $this->response->json([
                    'success' => false,
                    'message' => 'Backup path is required'
                ])->withStatus(400);
            }

            $success = $this->backupService->restoreFromBackup($backupPath);

            if ($success) {
                return $this->response->json([
                    'success' => true,
                    'message' => 'Backup restored successfully'
                ]);
            } else {
                return $this->response->json([
                    'success' => false,
                    'message' => 'Failed to restore backup'
                ])->withStatus(500);
            }
        } catch (\Exception $e) {
            return $this->response->json([
                'success' => false,
                'message' => 'Restore failed: ' . $e->getMessage()
            ])->withStatus(500);
        }
    }

    /**
     * Delete a backup
     */
    public function deleteBackup(ServerRequestInterface $request, string $filename)
    {
        try {
            // Sanitize the filename to prevent directory traversal
            $filename = basename($filename);
            $backupPath = BASE_PATH . '/storage/backups/' . $filename;

            if (!file_exists($backupPath)) {
                return $this->response->json([
                    'success' => false,
                    'message' => 'Backup file not found'
                ])->withStatus(404);
            }

            $success = $this->backupService->deleteBackup($backupPath);

            if ($success) {
                return $this->response->json([
                    'success' => true,
                    'message' => 'Backup deleted successfully'
                ]);
            } else {
                return $this->response->json([
                    'success' => false,
                    'message' => 'Failed to delete backup'
                ])->withStatus(500);
            }
        } catch (\Exception $e) {
            return $this->response->json([
                'success' => false,
                'message' => 'Delete failed: ' . $e->getMessage()
            ])->withStatus(500);
        }
    }

    /**
     * Clean old backups based on retention policy
     */
    public function cleanOldBackups(ServerRequestInterface $request)
    {
        try {
            $params = $request->getQueryParams();
            $daysToKeep = (int)($params['days'] ?? 30);

            $deletedCount = $this->backupService->cleanOldBackups($daysToKeep);

            return $this->response->json([
                'success' => true,
                'message' => "{$deletedCount} old backups cleaned successfully",
                'data' => [
                    'deleted_count' => $deletedCount,
                    'days_kept' => $daysToKeep
                ]
            ]);
        } catch (\Exception $e) {
            return $this->response->json([
                'success' => false,
                'message' => 'Clean operation failed: ' . $e->getMessage()
            ])->withStatus(500);
        }
    }
}