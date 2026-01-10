<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Hyperf\HttpServer\Contract\ResponseInterface;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface as PsrResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Log\LoggerInterface;
use Throwable;

class ApiErrorHandlingMiddleware implements MiddlewareInterface
{
    protected ContainerInterface $container;

    protected ResponseInterface $response;

    protected LoggerInterface $logger;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
        $this->response = $container->get(ResponseInterface::class);
        $this->logger = $container->get(LoggerInterface::class);
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): PsrResponseInterface
    {
        try {
            return $handler->handle($request);
        } catch (Throwable $throwable) {
            return $this->handleException($throwable, $request);
        }
    }

    protected function handleException(Throwable $throwable, ServerRequestInterface $request): PsrResponseInterface
    {
        $errorInfo = $this->classifyException($throwable);
        $statusCode = $this->getStatusCodeForException($throwable);
        $errorCode = $this->getErrorCodeForException($throwable);
        $message = $this->getUserFriendlyMessage($throwable);

        $context = [
            'error_code' => $errorCode,
            'error_type' => $errorInfo['type'],
            'error_class' => get_class($throwable),
            'message' => $throwable->getMessage(),
            'file' => $throwable->getFile(),
            'line' => $throwable->getLine(),
            'trace' => $throwable->getTraceAsString(),
            'request_uri' => $request->getUri()->getPath(),
            'request_method' => $request->getMethod(),
            'user_agent' => $request->getHeaderLine('User-Agent'),
            'ip_address' => $this->getClientIp($request),
        ];

        $this->logError($throwable, $statusCode, $context);

        $errorResponse = [
            'success' => false,
            'error' => [
                'message' => $message,
                'code' => $errorCode,
                'type' => $errorInfo['type'],
                'details' => $errorInfo['include_details'] ? [
                    'exception' => $errorInfo['log_exception_only'] ? null : get_class($throwable),
                    'file' => $errorInfo['log_exception_only'] ? null : $throwable->getFile(),
                    'line' => $errorInfo['log_exception_only'] ? null : $throwable->getLine(),
                ] : null,
            ],
            'timestamp' => date('c'),
        ];

        return $this->response->json($errorResponse)->withStatus($statusCode);
    }

    protected function classifyException(Throwable $throwable): array
    {
        $class = get_class($throwable);

        if (str_contains($class, 'Validation')) {
            return ['type' => 'validation', 'include_details' => true, 'log_exception_only' => false];
        }

        if (str_contains($class, 'Authentication') || str_contains($class, 'Auth')) {
            return ['type' => 'authentication', 'include_details' => false, 'log_exception_only' => false];
        }

        if (str_contains($class, 'Authorization') || str_contains($class, 'Forbidden')) {
            return ['type' => 'authorization', 'include_details' => false, 'log_exception_only' => false];
        }

        if (str_contains($class, 'NotFound') || str_contains($class, 'ModelNotFound')) {
            return ['type' => 'not_found', 'include_details' => false, 'log_exception_only' => false];
        }

        if (str_contains($class, 'Database') || str_contains($class, 'Query')) {
            return ['type' => 'database', 'include_details' => false, 'log_exception_only' => true];
        }

        if (str_contains($class, 'Timeout') || str_contains($class, 'Connection')) {
            return ['type' => 'timeout', 'include_details' => false, 'log_exception_only' => true];
        }

        return ['type' => 'server', 'include_details' => false, 'log_exception_only' => true];
    }

    protected function getStatusCodeForException(Throwable $throwable): int
    {
        $code = $throwable->getCode();

        if ($code >= 400 && $code < 600) {
            return $code;
        }

        $class = get_class($throwable);

        if (str_contains($class, 'Validation')) {
            return 422;
        }

        if (str_contains($class, 'Authentication') || str_contains($class, 'Auth')) {
            return 401;
        }

        if (str_contains($class, 'Authorization') || str_contains($class, 'Forbidden')) {
            return 403;
        }

        if (str_contains($class, 'NotFound') || str_contains($class, 'ModelNotFound')) {
            return 404;
        }

        return 500;
    }

    protected function getErrorCodeForException(Throwable $throwable): string
    {
        $class = get_class($throwable);

        if (str_contains($class, 'Validation')) {
            return config('error-codes.error_codes.VALIDATION.FAILED', 'VAL_001');
        }

        if (str_contains($class, 'Authentication') || str_contains($class, 'Auth')) {
            return config('error-codes.error_codes.AUTH.UNAUTHORIZED', 'AUTH_005');
        }

        if (str_contains($class, 'Authorization') || str_contains($class, 'Forbidden')) {
            return config('error-codes.error_codes.AUTH.FORBIDDEN', 'AUTH_006');
        }

        if (str_contains($class, 'NotFound') || str_contains($class, 'ModelNotFound')) {
            return config('error-codes.error_codes.RESOURCE.NOT_FOUND', 'RES_001');
        }

        if (str_contains($class, 'Database') || str_contains($class, 'Query')) {
            return config('error-codes.error_codes.SERVER.DATABASE_ERROR', 'SRV_002');
        }

        if (str_contains($class, 'Timeout') || str_contains($class, 'Connection')) {
            return config('error-codes.error_codes.SERVER.TIMEOUT', 'SRV_004');
        }

        return config('error-codes.error_codes.SERVER.INTERNAL_ERROR', 'SRV_001');
    }

    protected function getUserFriendlyMessage(Throwable $throwable): string
    {
        $class = get_class($throwable);

        if (str_contains($class, 'Validation')) {
            return 'Validation failed. Please check your input.';
        }

        if (str_contains($class, 'Authentication') || str_contains($class, 'Auth')) {
            return 'Authentication failed. Please check your credentials.';
        }

        if (str_contains($class, 'Authorization') || str_contains($class, 'Forbidden')) {
            return 'You do not have permission to perform this action.';
        }

        if (str_contains($class, 'NotFound') || str_contains($class, 'ModelNotFound')) {
            return 'The requested resource was not found.';
        }

        return 'An unexpected error occurred. Please try again later.';
    }

    protected function logError(Throwable $throwable, int $statusCode, array $context): void
    {
        $level = $this->getLogLevel($statusCode);

        $this->logger->log(
            $level,
            'API Exception: ' . $throwable->getMessage(),
            $context
        );
    }

    protected function getLogLevel(int $statusCode): string
    {
        if ($statusCode >= 500) {
            return 'error';
        }

        if ($statusCode >= 400) {
            return 'warning';
        }

        return 'info';
    }

    protected function getClientIp(ServerRequestInterface $request): string
    {
        $serverParams = $request->getServerParams();

        $headers = [
            'HTTP_X_FORWARDED_FOR',
            'HTTP_CLIENT_IP',
            'HTTP_X_REAL_IP',
            'REMOTE_ADDR',
        ];

        foreach ($headers as $header) {
            if (isset($serverParams[$header])) {
                $ip = explode(',', $serverParams[$header])[0];
                return trim($ip);
            }
        }

        return $serverParams['REMOTE_ADDR'] ?? '0.0.0.0';
    }
}
