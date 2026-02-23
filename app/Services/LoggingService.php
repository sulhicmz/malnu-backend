<?php

declare(strict_types=1);

namespace App\Services;

use App\Contracts\LoggingServiceInterface;
use Psr\Log\LoggerInterface;
use Psr\Container\ContainerInterface;

/**
 * LoggingService - Centralized logging service with standardized methods.
 *
 * Provides consistent logging across the application with:
 * - Structured log format
 * - Context-aware logging
 * - Security event logging
 * - Request correlation ID tracking
 */
class LoggingService implements LoggingServiceInterface
{
    /**
     * @var LoggerInterface
     */
    private LoggerInterface $logger;

    /**
     * @var ContainerInterface
     */
    private ContainerInterface $container;

    /**
     * Security event types.
     */
    private const SECURITY_EVENT_TYPE = 'security_event';
    private const SYSTEM_EVENT_TYPE = 'system_event';
    private const AUTH_EVENT_TYPE = 'auth_event';

    /**
     * Log levels.
     */
    private const LEVEL_DEBUG = 'debug';
    private const LEVEL_INFO = 'info';
    private const LEVEL_WARNING = 'warning';
    private const LEVEL_ERROR = 'error';
    private const LEVEL_CRITICAL = 'critical';

    /**
     * Constructor.
     *
     * @param LoggerInterface $logger
     * @param ContainerInterface $container
     */
    public function __construct(LoggerInterface $logger, ContainerInterface $container)
    {
        $this->logger = $logger;
        $this->container = $container;
    }

    /**
     * Get current correlation ID from request.
     *
     * @return string|null
     */
    private function getCorrelationId(): ?string
    {
        $request = $this->container->get(\Psr\Http\Message\ServerRequestInterface::class);

        if ($request === null) {
            return null;
        }

        return $request->getHeaderLine('X-Request-ID') ?? $request->getHeaderLine('X-Correlation-ID');
    }

    /**
     * Get current user ID from request context.
     *
     * @return string|null
     */
    private function getUserId(): ?string
    {
        try {
            $request = $this->container->get(\Psr\Http\Message\ServerRequestInterface::class);

            if ($request === null) {
                return null;
            }

            // Try to get from session or JWT token
            $authHeader = $request->getHeaderLine('Authorization');

            if ($authHeader && str_starts_with($authHeader, 'Bearer ')) {
                // Extract user ID from JWT token if possible
                // This is a simplified approach - in production, decode the token
                return null; // Would return actual user ID
            }

            return null;
        } catch (\Throwable $e) {
            return null;
        }
    }

    /**
     * Sanitize sensitive data from context.
     *
     * @param array $context
     * @return array
     */
    private function sanitizeContext(array $context): array
    {
        $sensitiveKeys = ['password', 'token', 'secret', 'api_key', 'credit_card', 'current_password', 'new_password', 'Authorization', 'Cookie'];

        foreach ($context as $key => $value) {
            if (is_array($value) || is_object($value)) {
                $context[$key] = $this->sanitizeContext((array)$value);
            } elseif (in_array(strtolower($key), $sensitiveKeys)) {
                $context[$key] = '[REDACTED]';
            }
        }

        return $context;
    }

    /**
     * Log structured message with context.
     *
     * @param string $level
     * @param string $message
     * @param array $context
     * @return void
     */
    private function log(string $level, string $message, array $context = []): void
    {
        $enhancedContext = array_merge($context, [
            'timestamp' => date('c'), // ISO 8601 format
            'correlation_id' => $this->getCorrelationId(),
            'user_id' => $this->getUserId(),
        ]);

        $sanitizedContext = $this->sanitizeContext($enhancedContext);

        $this->logger->log($level, $message, $sanitizedContext);
    }

    /**
     * Determine log level from status code.
     *
     * @param int $statusCode
     * @return string
     */
    private function getLogLevelFromStatus(int $statusCode): string
    {
        if ($statusCode >= 500) {
            return self::LEVEL_ERROR;
        }
        if ($statusCode >= 400) {
            return self::LEVEL_WARNING;
        }
        if ($statusCode >= 300) {
            return self::LEVEL_INFO;
        }
        return self::LEVEL_INFO;
    }

    // ============ Standard Logging Methods ============

    /**
     * Log debug message.
     *
     * @param string $message
     * @param array $context
     */
    public function debug(string $message, array $context = []): void
    {
        $this->log(self::LEVEL_DEBUG, $message, $context);
    }

    /**
     * Log info message.
     *
     * @param string $message
     * @param array $context
     */
    public function info(string $message, array $context = []): void
    {
        $this->log(self::LEVEL_INFO, $message, $context);
    }

    /**
     * Log warning message.
     *
     * @param string $message
     * @param array $context
     */
    public function warning(string $message, array $context = []): void
    {
        $this->log(self::LEVEL_WARNING, $message, $context);
    }

    /**
     * Log error message.
     *
     * @param string $message
     * @param array $context
     */
    public function error(string $message, array $context = []): void
    {
        $this->log(self::LEVEL_ERROR, $message, $context);
    }

    /**
     * Log critical message.
     *
     * @param string $message
     * @param array $context
     */
    public function critical(string $message, array $context = []): void
    {
        $this->log(self::LEVEL_CRITICAL, $message, $context);
    }

    /**
     * Log exception with context.
     *
     * @param \Throwable $exception
     * @param array $context
     */
    public function exception(\Throwable $exception, array $context = []): void
    {
        $this->error($exception->getMessage(), array_merge($context, [
            'exception_type' => get_class($exception),
            'exception_message' => $exception->getMessage(),
            'exception_file' => $exception->getFile(),
            'exception_line' => $exception->getLine(),
            'exception_trace' => $exception->getTraceAsString(),
        ]));
    }

    // ============ Security Event Logging ============

    /**
     * Log failed login attempt.
     *
     * @param string $email
     * @param string|null $ipAddress
     */
    public function logFailedLogin(string $email, ?string $ipAddress = null): void
    {
        $this->warning('Failed login attempt', [
            'event_type' => self::AUTH_EVENT_TYPE,
            'event_action' => 'login_failed',
            'email' => $this->sanitizeEmail($email),
            'ip_address' => $ipAddress,
        ]);
    }

    /**
     * Log successful login.
     *
     * @param string $email
     * @param string|null $ipAddress
     */
    public function logSuccessfulLogin(string $email, ?string $ipAddress = null): void
    {
        $this->info('User logged in successfully', [
            'event_type' => self::AUTH_EVENT_TYPE,
            'event_action' => 'login_success',
            'email' => $this->sanitizeEmail($email),
            'ip_address' => $ipAddress,
        ]);
    }

    /**
     * Log permission denied event.
     *
     * @param string $action
     * @param string|null $resource
     * @param string|null $userId
     */
    public function logPermissionDenied(string $action, ?string $resource = null, ?string $userId = null): void
    {
        $this->warning('Permission denied', [
            'event_type' => self::SECURITY_EVENT_TYPE,
            'event_action' => $action,
            'resource' => $resource,
            'user_id' => $userId,
        ]);
    }

    /**
     * Log token blacklist operation.
     *
     * @param string $action
     * @param string|null $userId
     * @param array $context
     */
    public function logTokenBlacklistOperation(string $action, ?string $userId = null, array $context = []): void
    {
        $this->info('Token blacklist operation', [
            'event_type' => self::SECURITY_EVENT_TYPE,
            'event_action' => $action,
            'user_id' => $userId,
        ] + $context);
    }

    /**
     * Log rate limit trigger.
     *
     * @param string $identifier
     * @param string|null $ipAddress
     * @param int $limit
     * @param string $window
     */
    public function logRateLimitTrigger(string $identifier, ?string $ipAddress = null, int $limit, string $window): void
    {
        $this->warning('Rate limit triggered', [
            'event_type' => self::SECURITY_EVENT_TYPE,
            'event_action' => 'rate_limit_exceeded',
            'identifier' => $identifier,
            'ip_address' => $ipAddress,
            'limit' => $limit,
            'window' => $window,
        ]);
    }

    /**
     * Log suspicious activity.
     *
     * @param string $activity
     * @param array $context
     */
    public function logSuspiciousActivity(string $activity, array $context = []): void
    {
        $this->warning('Suspicious activity detected', [
            'event_type' => self::SECURITY_EVENT_TYPE,
            'event_action' => 'suspicious_activity',
            'activity' => $activity,
        ] + $context);
    }

    // ============ System Event Logging ============

    /**
     * Log system event.
     *
     * @param string $action
     * @param string|null $result
     * @param array $context
     */
    public function logSystemEvent(string $action, ?string $result = null, array $context = []): void
    {
        $this->info('System event', [
            'event_type' => self::SYSTEM_EVENT_TYPE,
            'event_action' => $action,
            'result' => $result,
        ] + $context);
    }

    /**
     * Log backup operation.
     *
     * @param string $operation
     * @param array $data
     */
    public function logBackupOperation(string $operation, array $data = []): void
    {
        $this->info('Backup operation completed', array_merge([
            'event_type' => self::SYSTEM_EVENT_TYPE,
            'event_action' => 'backup_' . $operation,
        ], $data));
    }

    /**
     * Log restore operation.
     *
     * @param string $operation
     * @param array $data
     */
    public function logRestoreOperation(string $operation, array $data = []): void
    {
        $this->info('Restore operation completed', array_merge([
            'event_type' => self::SYSTEM_EVENT_TYPE,
            'event_action' => 'restore_' . $operation,
        ], $data));
    }

    /**
     * Log API request with status code.
     *
     * @param string $method
     * @param string $path
     * @param int $statusCode
     * @param int|null $userId
     */
    public function logApiRequest(string $method, string $path, int $statusCode, ?string $userId = null): void
    {
        $level = $this->getLogLevelFromStatus($statusCode);

        $this->log($level, 'API request', [
            'event_type' => 'api_request',
            'method' => $method,
            'path' => $path,
            'status_code' => $statusCode,
            'user_id' => $userId,
        ]);
    }

    // ============ Helper Methods ============

    /**
     * Sanitize email for logging.
     *
     * @param string $email
     * @return string
     */
    private function sanitizeEmail(string $email): string
    {
        // Keep only first few characters and domain for identification
        $parts = explode('@', $email);

        if (count($parts) === 2) {
            $local = substr($parts[0], 0, 3);
            $domain = $parts[1];
            return $local . '***@' . $domain;
        }

        return '***@' . ($parts[1] ?? 'unknown');
    }
}
