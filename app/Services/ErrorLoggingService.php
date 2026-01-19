<?php

declare(strict_types=1);

namespace App\Services;

use Hyperf\Di\Annotation\Inject;
use Psr\Log\LoggerInterface;
use Throwable;

class ErrorLoggingService
{
    #[Inject]
    protected LoggerInterface $logger;

    protected string $environment;

    protected array $errorContext = [];

    public function __construct(LoggerInterface $logger, ConfigInterface $config)
    {
        $this->logger = $logger;
        $this->config = $config;
        $this->environment = $config->get('app.env', 'local');
    }

    public function logError(string $message, array $context = []): void
    {
        $this->logger->error($message, $this->buildContext('error', $context));
    }

    public function logWarning(string $message, array $context = []): void
    {
        $this->logger->warning($message, $this->buildContext('warning', $context));
    }

    public function logInfo(string $message, array $context = []): void
    {
        $this->logger->info($message, $this->buildContext('info', $context));
    }

    public function logDebug(string $message, array $context = []): void
    {
        if ($this->shouldLogDebug()) {
            $this->logger->debug($message, $this->buildContext('debug', $context));
        }
    }

    public function logSecurityEvent(string $event, array $context = []): void
    {
        $this->logger->warning('Security Event: ' . $event, $this->buildContext('security', $context));
    }

    public function logPerformance(string $metric, float $value, array $context = []): void
    {
        $performanceContext = array_merge($context, [
            'metric' => $metric,
            'value' => $value,
            'value_ms' => round($value * 1000, 2),
        ]);

        $this->logger->info('Performance: ' . $metric, $this->buildContext('performance', $performanceContext));
    }

    public function logException(Throwable $exception, array $context = []): void
    {
        $level = $this->getLogLevelForException($exception);
        $exceptionContext = array_merge($context, [
            'exception_type' => get_class($exception),
            'exception_message' => $exception->getMessage(),
            'exception_code' => $exception->getCode(),
            'exception_file' => $exception->getFile(),
            'exception_line' => $exception->getLine(),
            'exception_trace' => $this->shouldLogDebug() ? $exception->getTraceAsString() : '[REDACTED IN PRODUCTION]',
        ]);

        $this->logger->log($level, 'Exception occurred', $this->buildContext('exception', $exceptionContext));
    }

    public function setErrorContext(array $context): void
    {
        $this->errorContext = array_merge($this->errorContext, $context);
    }

    public function getErrorContext(): array
    {
        return $this->errorContext;
    }

    public function clearErrorContext(): void
    {
        $this->errorContext = [];
    }

    protected function buildContext(string $type, array $additionalContext = []): array
    {
        $context = array_merge([
            'log_type' => $type,
            'environment' => $this->environment,
            'timestamp' => date('c'),
        ], $additionalContext, $this->errorContext);

        if (isset($context['user_id'])) {
            $context['user_id'] = '[REDACTED]';
        }

        return $context;
    }

    protected function shouldLogDebug(): bool
    {
        return $this->environment === 'local' || (bool) $this->config->get('app.debug', false);
    }

    protected function getLogLevelForException(Throwable $exception): string
    {
        if ($this->isDatabaseError($exception)) {
            return 'error';
        }

        if ($this->isNetworkError($exception)) {
            return 'warning';
        }

        if ($this->isAuthenticationError($exception)) {
            return 'warning';
        }

        if ($this->isValidationError($exception)) {
            return 'notice';
        }

        return 'error';
    }

    protected function isDatabaseError(Throwable $exception): bool
    {
        $databaseExceptions = [
            'PDOException',
            'Hyperf\Database\Exception\QueryException',
            'Hyperf\Database\Exception\BindException',
            'Hyperf\Database\Exception\ModelNotFoundException',
        ];

        return in_array(get_class($exception), $databaseExceptions);
    }

    protected function isNetworkError(Throwable $exception): bool
    {
        $networkExceptions = [
            'GuzzleHttp\Exception\ConnectException',
            'GuzzleHttp\Exception\RequestException',
            'GuzzleHttp\Exception\ServerException',
        ];

        return in_array(get_class($exception), $networkExceptions);
    }

    protected function isAuthenticationError(Throwable $exception): bool
    {
        $authExceptions = [
            'App\Services\JWTException',
            'Hyperf\Di\Exception\NotFoundException',
        ];

        return in_array(get_class($exception), $authExceptions);
    }

    protected function isValidationError(Throwable $exception): bool
    {
        $validationExceptions = [
            'Hyperf\Validation\ValidationException',
            'Hyperf\Contract\Validation\ValidatorFactoryInterface',
        ];

        return in_array(get_class($exception), $validationExceptions);
    }
}
