<?php

declare(strict_types=1);

namespace App\Services;

use Hyperf\Redis\Redis;
use Hyperf\Di\Annotation\Inject;
use Psr\Log\LoggerInterface;

class ErrorTrackingService
{
    #[Inject]
    private Redis $redis;

    private LoggerInterface $logger;
    private const ERROR_KEY_PREFIX = 'errors:';
    private const ERROR_TTL = 604800;

    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    public function logError(\Throwable $exception, array $context = []): void
    {
        $errorData = [
            'message' => $exception->getMessage(),
            'code' => $exception->getCode(),
            'file' => $exception->getFile(),
            'line' => $exception->getLine(),
            'trace' => $exception->getTraceAsString(),
            'type' => get_class($exception),
            'timestamp' => date('c'),
            'context' => $context,
        ];

        $errorKey = $this->getErrorKey($exception);
        $this->redis->setex($errorKey, self::ERROR_TTL, json_encode($errorData));

        $this->logger->error($exception->getMessage(), $errorData);
    }

    public function getRecentErrors(int $limit = 100): array
    {
        $pattern = self::ERROR_KEY_PREFIX . '*';
        $keys = $this->redis->keys($pattern);

        if (empty($keys)) {
            return [];
        }

        $errors = [];
        foreach (array_slice($keys, 0, $limit) as $key) {
            $errorData = $this->redis->get($key);
            if ($errorData) {
                $errors[] = json_decode($errorData, true);
            }
        }

        usort($errors, fn($a, $b) => strtotime($b['timestamp']) - strtotime($a['timestamp']));

        return array_slice($errors, 0, $limit);
    }

    public function getErrorStats(): array
    {
        $pattern = self::ERROR_KEY_PREFIX . '*';
        $keys = $this->redis->keys($pattern);

        $errorTypes = [];
        $errorCount = 0;

        foreach ($keys as $key) {
            $errorData = $this->redis->get($key);
            if ($errorData) {
                $decoded = json_decode($errorData, true);
                $type = $decoded['type'] ?? 'unknown';
                $errorTypes[$type] = ($errorTypes[$type] ?? 0) + 1;
                $errorCount++;
            }
        }

        return [
            'total_errors' => $errorCount,
            'error_types' => $errorTypes,
            'timestamp' => date('c'),
        ];
    }

    public function clearOldErrors(int $hoursOld = 168): int
    {
        $pattern = self::ERROR_KEY_PREFIX . '*';
        $keys = $this->redis->keys($pattern);

        $cutoffTime = time() - ($hoursOld * 3600);
        $deletedCount = 0;

        foreach ($keys as $key) {
            $errorData = $this->redis->get($key);
            if ($errorData) {
                $decoded = json_decode($errorData, true);
                $errorTime = strtotime($decoded['timestamp'] ?? 'now');

                if ($errorTime < $cutoffTime) {
                    $this->redis->del($key);
                    $deletedCount++;
                }
            }
        }

        return $deletedCount;
    }

    private function getErrorKey(\Throwable $exception): string
    {
        $hash = md5($exception->getFile() . $exception->getLine() . $exception->getMessage());
        return self::ERROR_KEY_PREFIX . $hash;
    }
}