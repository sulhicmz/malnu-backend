<?php

declare(strict_types=1);

namespace App\Contracts;

interface LoggingServiceInterface
{
    public function debug(string $message, array $context = []): void;

    public function info(string $message, array $context = []): void;

    public function warning(string $message, array $context = []): void;

    public function error(string $message, array $context = []): void;

    public function critical(string $message, array $context = []): void;

    public function exception(\Throwable $exception, array $context = []): void;

    public function logFailedLogin(string $email, ?string $ipAddress = null): void;

    public function logSuccessfulLogin(string $email, ?string $ipAddress = null): void;

    public function logPermissionDenied(string $action, ?string $resource = null, ?string $userId = null): void;

    public function logTokenBlacklistOperation(string $action, ?string $userId = null, array $context = []): void;

    public function logRateLimitTrigger(string $identifier, ?string $ipAddress, int $limit, string $window): void;

    public function logSuspiciousActivity(string $activity, array $context = []): void;

    public function logSystemEvent(string $action, ?string $result = null, array $context = []): void;

    public function logBackupOperation(string $operation, array $data = []): void;

    public function logRestoreOperation(string $operation, array $data = []): void;

    public function logApiRequest(string $method, string $path, int $statusCode, ?string $userId = null): void;
}
