<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Hyperf\Contract\ConfigInterface;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Log\LoggerInterface;
use Throwable;

class RequestLoggingMiddleware implements MiddlewareInterface
{
    protected ContainerInterface $container;

    protected ConfigInterface $config;

    protected LoggerInterface $logger;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
        $this->config = $container->get(ConfigInterface::class);
        $this->logger = $container->get(LoggerInterface::class);
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $enabled = $this->config->get('logging.request.enabled', true);

        if (! $enabled) {
            return $handler->handle($request);
        }

        $path = $request->getUri()->getPath();

        if ($this->shouldExcludePath($path)) {
            return $handler->handle($request);
        }

        $startTime = microtime(true);

        try {
            $response = $handler->handle($request);

            $duration = round((microtime(true) - $startTime) * 1000, 2);

            $this->logRequestResponse($request, $response, $duration);

            return $response;
        } catch (Throwable $e) {
            $duration = round((microtime(true) - $startTime) * 1000, 2);

            $this->logError($request, $e, $duration);

            throw $e;
        }
    }

    protected function shouldExcludePath(string $path): bool
    {
        $excludePaths = $this->config->get('logging.request.exclude_paths', [
            '/health',
            '/metrics',
            '/favicon.ico',
        ]);

        foreach ($excludePaths as $excludePattern) {
            if (fnmatch($excludePattern, $path)) {
                return true;
            }
        }

        return false;
    }

    protected function logRequestResponse(ServerRequestInterface $request, ResponseInterface $response, float $duration): void
    {
        $logLevel = $this->config->get('logging.request.level', 'info');

        $statusCode = $response->getStatusCode();

        $logContext = [
            'type' => 'http_request',
            'method' => $request->getMethod(),
            'path' => $request->getUri()->getPath(),
            'query' => $this->sanitizeQueryParams($request->getQueryParams()),
            'status_code' => $statusCode,
            'duration_ms' => $duration,
            'ip' => $this->getClientIp($request),
            'user_agent' => $request->getHeaderLine('User-Agent'),
            'content_type' => $request->getHeaderLine('Content-Type'),
        ];

        $includeBody = $this->config->get('logging.request.include_body', false);

        if ($includeBody && in_array(strtoupper($request->getMethod()), ['POST', 'PUT', 'PATCH'])) {
            $logContext['body'] = $this->sanitizeRequestBody($request->getParsedBody());
        }

        if ($statusCode >= 500) {
            $this->logger->error('HTTP request completed with server error', $logContext);
        } elseif ($statusCode >= 400) {
            $this->logger->warning('HTTP request completed with client error', $logContext);
        } elseif ($statusCode >= 300) {
            $this->logger->info('HTTP request completed with redirect', $logContext);
        } else {
            $this->logger->log($logLevel, 'HTTP request completed successfully', $logContext);
        }
    }

    protected function logError(ServerRequestInterface $request, Throwable $e, float $duration): void
    {
        $this->logger->error('HTTP request failed with exception', [
            'type' => 'http_request_error',
            'method' => $request->getMethod(),
            'path' => $request->getUri()->getPath(),
            'query' => $this->sanitizeQueryParams($request->getQueryParams()),
            'duration_ms' => $duration,
            'ip' => $this->getClientIp($request),
            'exception' => get_class($e),
            'message' => $e->getMessage(),
            'file' => $e->getFile(),
            'line' => $e->getLine(),
        ]);
    }

    protected function sanitizeQueryParams(array $params): array
    {
        $sensitiveKeys = ['password', 'token', 'secret', 'api_key', 'credit_card'];

        foreach ($params as $key => $value) {
            $lowerKey = strtolower(str_replace('_', '', $key));

            foreach ($sensitiveKeys as $sensitive) {
                if (str_contains($lowerKey, $sensitive)) {
                    $params[$key] = '[REDACTED]';

                    break;
                }
            }
        }

        return $params;
    }

    protected function sanitizeRequestBody($body): array
    {
        if (! is_array($body)) {
            return ['body' => '[NOT AN ARRAY]'];
        }

        $sensitiveKeys = ['password', 'token', 'secret', 'api_key', 'credit_card', 'current_password', 'new_password'];

        foreach ($body as $key => $value) {
            $lowerKey = strtolower(str_replace('_', '', $key));

            foreach ($sensitiveKeys as $sensitive) {
                if (str_contains($lowerKey, $sensitive)) {
                    $body[$key] = '[REDACTED]';

                    break;
                }
            }

            if (is_array($value)) {
                $body[$key] = $this->sanitizeRequestBody($value);
            }
        }

        return $body;
    }

    protected function getClientIp(ServerRequestInterface $request): string
    {
        $ipHeaders = [
            'X-Forwarded-For',
            'X-Real-Ip',
            'CF-Connecting-IP',
            'HTTP_X_FORWARDED_FOR',
            'HTTP_X_REAL_IP',
        ];

        foreach ($ipHeaders as $header) {
            $ip = $request->getHeaderLine($header);
            if ($ip) {
                $ips = explode(',', $ip);
                if (! empty($ips)) {
                    return trim($ips[0]);
                }
            }
        }

        $serverParams = $request->getServerParams();

        return $serverParams['REMOTE_ADDR'] ?? 'unknown';
    }
}
