<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\SystemManagement;

use App\Http\Controllers\Api\BaseController;
use App\Services\BackupService;
use Hyperf\Contract\ConfigInterface;
use Hyperf\Di\Annotation\Inject;
use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;

class BackupController extends BaseController
{
    protected BackupService $backupService;

    protected ConfigInterface $config;

    protected LoggerInterface $logger;

    public function __construct(
        BackupService $backupService,
        ConfigInterface $config,
        LoggerInterface $logger,
        ContainerInterface $container
    ) {
        parent::__construct($container->get('request'), $container->get('response'), $container);
        $this->backupService = $backupService;
        $this->config = $config;
        $this->logger = $logger;
    }

    public function index()
    {
        try {
            $type = $this->request->input('type', 'all');
            $status = $this->backupService->getBackupStatus();

            $backups = $this->getBackupsByType($type);

            return $this->successResponse([
                'backups' => $backups,
                'statistics' => $status['statistics'] ?? [],
                'latest_backups' => $status['latest_backups'] ?? [],
            ], 'Backup list retrieved successfully');
        } catch (\Exception $e) {
            $this->logger->error('Failed to retrieve backup list: ' . $e->getMessage());
            return $this->serverErrorResponse('Failed to retrieve backup list');
        }
    }

    public function store()
    {
        try {
            $type = $this->request->input('type', 'all');
            $compress = $this->request->input('compress', true);
            $options = [
                'compress' => filter_var($compress, FILTER_VALIDATE_BOOLEAN),
                'connection' => $this->request->input('connection'),
                'description' => $this->request->input('description', ''),
                'user_id' => $this->getCurrentUserId(),
            ];

            $result = $this->backupService->createBackup($type, $options);

            if (!$result['success']) {
                $this->logger->error('Backup creation failed', $result);
                return $this->errorResponse(
                    'Backup creation failed',
                    'BACKUP_FAILED',
                    $result['details'] ?? []
                );
            }

            $this->logger->info('Backup created successfully', $result);

            $this->sendBackupAlert('Backup created successfully', $result);

            return $this->successResponse($result, 'Backup created successfully');
        } catch (\Exception $e) {
            $this->logger->error('Exception during backup creation: ' . $e->getMessage());
            return $this->serverErrorResponse('Backup creation failed due to server error');
        }
    }

    public function show()
    {
        try {
            $backupId = $this->request->route('id');
            $backupPath = $this->resolveBackupPath($backupId);

            if (!$backupPath || !file_exists($backupPath)) {
                return $this->notFoundResponse('Backup file not found');
            }

            $backupInfo = $this->getBackupInfo($backupPath);

            return $this->successResponse($backupInfo, 'Backup details retrieved successfully');
        } catch (\Exception $e) {
            $this->logger->error('Failed to retrieve backup details: ' . $e->getMessage());
            return $this->serverErrorResponse('Failed to retrieve backup details');
        }
    }

    public function destroy()
    {
        try {
            $backupId = $this->request->route('id');
            $backupPath = $this->resolveBackupPath($backupId);

            if (!$backupPath || !file_exists($backupPath)) {
                return $this->notFoundResponse('Backup file not found');
            }

            if (!unlink($backupPath)) {
                return $this->errorResponse('Failed to delete backup file', 'DELETE_FAILED');
            }

            $this->logger->info('Backup deleted: ' . $backupPath);

            return $this->successResponse(['backup_id' => $backupId], 'Backup deleted successfully');
        } catch (\Exception $e) {
            $this->logger->error('Failed to delete backup: ' . $e->getMessage());
            return $this->serverErrorResponse('Failed to delete backup');
        }
    }

    public function restore()
    {
        try {
            $backupId = $this->request->route('id');
            $backupPath = $this->resolveBackupPath($backupId);

            if (!$backupPath || !file_exists($backupPath)) {
                return $this->notFoundResponse('Backup file not found');
            }

            $type = $this->request->input('type', 'all');
            $options = [
                'connection' => $this->request->input('connection'),
                'force' => $this->request->input('force', false),
                'user_id' => $this->getCurrentUserId(),
            ];

            $result = $this->backupService->restoreBackup($backupPath, $type, $options);

            if (!$result['success']) {
                $this->logger->error('Backup restoration failed', $result);
                return $this->errorResponse(
                    'Backup restoration failed',
                    'RESTORE_FAILED',
                    $result['details'] ?? []
                );
            }

            $this->logger->warning('Backup restored: ' . $backupPath, ['user_id' => $options['user_id']]);

            $this->sendBackupAlert('Backup restored successfully', $result);

            return $this->successResponse($result, 'Backup restored successfully');
        } catch (\Exception $e) {
            $this->logger->error('Exception during backup restoration: ' . $e->getMessage());
            return $this->serverErrorResponse('Backup restoration failed due to server error');
        }
    }

    public function verify()
    {
        try {
            $backupId = $this->request->route('id');
            $backupPath = $this->resolveBackupPath($backupId);

            if (!$backupPath || !file_exists($backupPath)) {
                return $this->notFoundResponse('Backup file not found');
            }

            $type = $this->request->input('type', 'all');
            $result = $this->backupService->verifyBackup($backupPath, $type);

            $this->logger->info('Backup verification completed: ' . $backupPath, $result);

            return $this->successResponse($result, 'Backup verification completed');
        } catch (\Exception $e) {
            $this->logger->error('Failed to verify backup: ' . $e->getMessage());
            return $this->serverErrorResponse('Backup verification failed');
        }
    }

    public function status()
    {
        try {
            $status = $this->backupService->getBackupStatus();

            $status['system_status'] = $this->getSystemStatus();

            return $this->successResponse($status, 'Backup status retrieved successfully');
        } catch (\Exception $e) {
            $this->logger->error('Failed to retrieve backup status: ' . $e->getMessage());
            return $this->serverErrorResponse('Failed to retrieve backup status');
        }
    }

    public function clean()
    {
        try {
            $type = $this->request->input('type', 'all');
            $keep = (int) $this->request->input('keep', 5);

            $result = $this->backupService->cleanOldBackups($type, $keep);

            if (!$result['success']) {
                $this->logger->error('Backup cleanup failed', $result);
                return $this->errorResponse(
                    'Failed to clean old backups',
                    'CLEANUP_FAILED',
                    $result
                );
            }

            $this->logger->info('Old backups cleaned: ' . json_encode($result));

            return $this->successResponse($result, 'Old backups cleaned successfully');
        } catch (\Exception $e) {
            $this->logger->error('Failed to clean old backups: ' . $e->getMessage());
            return $this->serverErrorResponse('Failed to clean old backups');
        }
    }

    protected function getBackupsByType(string $type): array
    {
        $backupConfig = $this->config->get('backup', []);
        $basePaths = $backupConfig['directories'] ?? [];
        $backups = [];

        switch ($type) {
            case 'database':
                $backups = $this->scanBackups($basePaths['database'] ?? storage_path('backups/database'), 'db_backup_');
                break;
            case 'filesystem':
                $backups = $this->scanBackups($basePaths['filesystem'] ?? storage_path('backups/filesystem'), 'filesystem_backup_');
                break;
            case 'config':
                $backups = $this->scanBackups($basePaths['config'] ?? storage_path('backups/config'), 'config_backup_');
                break;
            case 'all':
            default:
                $backups = $this->scanBackups(storage_path('backups'), 'full_backup_');
                break;
        }

        return $backups;
    }

    protected function scanBackups(string $directory, string $prefix): array
    {
        if (!is_dir($directory)) {
            return [];
        }

        $files = scandir($directory);
        $backups = [];

        foreach ($files as $file) {
            if (strpos($file, $prefix) === 0) {
                $filePath = $directory . '/' . $file;
                $backups[] = [
                    'id' => md5($filePath),
                    'filename' => $file,
                    'path' => $filePath,
                    'size' => filesize($filePath),
                    'size_human' => $this->formatBytes(filesize($filePath)),
                    'created_at' => date('c', filectime($filePath)),
                    'modified_at' => date('c', filemtime($filePath)),
                ];
            }
        }

        usort($backups, function ($a, $b) {
            return strtotime($b['modified_at']) - strtotime($a['modified_at']);
        });

        return $backups;
    }

    protected function resolveBackupPath(string $backupId): ?string
    {
        $basePaths = $this->config->get('backup.directories', []);
        $possiblePaths = [
            ($basePaths['comprehensive'] ?? storage_path('backups')),
            ($basePaths['database'] ?? storage_path('backups/database')),
            ($basePaths['filesystem'] ?? storage_path('backups/filesystem')),
            ($basePaths['config'] ?? storage_path('backups/config')),
        ];

        foreach ($possiblePaths as $basePath) {
            if (is_dir($basePath)) {
                $files = scandir($basePath);
                foreach ($files as $file) {
                    $filePath = $basePath . '/' . $file;
                    if (md5($filePath) === $backupId) {
                        return $filePath;
                    }
                }
            }
        }

        return null;
    }

    protected function getBackupInfo(string $backupPath): array
    {
        $info = [
            'filename' => basename($backupPath),
            'path' => $backupPath,
            'size' => filesize($backupPath),
            'size_human' => $this->formatBytes(filesize($backupPath)),
            'created_at' => date('c', filectime($backupPath)),
            'modified_at' => date('c', filemtime($backupPath)),
            'type' => $this->detectBackupType($backupPath),
            'is_compressed' => pathinfo($backupPath, PATHINFO_EXTENSION) === 'gz',
            'is_readable' => is_readable($backupPath),
        ];

        return $info;
    }

    protected function detectBackupType(string $backupPath): string
    {
        $filename = basename($backupPath);

        if (strpos($filename, 'db_backup_') === 0) {
            return 'database';
        } elseif (strpos($filename, 'filesystem_backup_') === 0) {
            return 'filesystem';
        } elseif (strpos($filename, 'config_backup_') === 0) {
            return 'configuration';
        } elseif (strpos($filename, 'full_backup_') === 0) {
            return 'comprehensive';
        }

        return 'unknown';
    }

    protected function getSystemStatus(): array
    {
        $status = 'operational';
        $issues = [];

        $backupDirs = $this->config->get('backup.directories', []);

        foreach ($backupDirs as $name => $path) {
            if (!is_dir($path)) {
                $status = 'degraded';
                $issues[] = "Backup directory missing: {$name}";
            } elseif (!is_writable($path)) {
                $status = 'degraded';
                $issues[] = "Backup directory not writable: {$name}";
            }
        }

        $diskFree = disk_free_space('/');
        $diskTotal = disk_total_space('/');
        $diskUsagePercent = ($diskTotal - $diskFree) / $diskTotal * 100;

        if ($diskUsagePercent > 90) {
            $status = 'warning';
            $issues[] = 'Low disk space for backups';
        }

        return [
            'status' => $status,
            'issues' => $issues,
            'disk_space' => [
                'free' => $this->formatBytes($diskFree),
                'total' => $this->formatBytes($diskTotal),
                'usage_percent' => round($diskUsagePercent, 2),
            ],
        ];
    }

    protected function getCurrentUserId(): ?int
    {
        $user = $this->request->getAttribute('user');

        return $user ? $user['id'] : null;
    }

    protected function sendBackupAlert(string $message, array $context): void
    {
        $alertConfig = $this->config->get('backup.alerts', []);

        if (empty($alertConfig['email']) && empty($alertConfig['webhook'])) {
            return;
        }

        $this->logger->info('Sending backup alert', [
            'message' => $message,
            'context' => $context,
        ]);

        if (!empty($alertConfig['email'])) {
            $this->sendEmailAlert($alertConfig['email'], $message, $context);
        }

        if (!empty($alertConfig['webhook'])) {
            $this->sendWebhookAlert($alertConfig['webhook'], $message, $context);
        }
    }

    protected function sendEmailAlert(string $email, string $message, array $context): void
    {
        $subject = '[Backup System] ' . $message;

        $body = "Backup System Notification\n\n";
        $body .= "Message: {$message}\n\n";
        $body .= "Context:\n";
        $body .= json_encode($context, JSON_PRETTY_PRINT);

        mail($email, $subject, $body);

        $this->logger->info('Email alert sent', ['email' => $email]);
    }

    protected function sendWebhookAlert(string $webhookUrl, string $message, array $context): void
    {
        $payload = [
            'event' => 'backup_alert',
            'message' => $message,
            'timestamp' => date('c'),
            'context' => $context,
        ];

        $ch = curl_init($webhookUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode >= 200 && $httpCode < 300) {
            $this->logger->info('Webhook alert sent successfully', ['url' => $webhookUrl]);
        } else {
            $this->logger->error('Webhook alert failed', [
                'url' => $webhookUrl,
                'http_code' => $httpCode,
                'response' => $response,
            ]);
        }
    }

    protected function formatBytes(int $bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];

        for ($i = 0; $bytes > 1024; $i++) {
            $bytes /= 1024;
        }

        return round($bytes, 2) . ' ' . $units[$i];
    }
}
