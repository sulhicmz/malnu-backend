<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Hyperf\HttpServer\Contract\RequestInterface;
use Hyperf\HttpServer\Contract\ResponseInterface as HttpResponse;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Log\LoggerInterface;
use Ramsey\Uuid\Uuid;

class RequestResponseLoggingMiddleware implements MiddlewareInterface
{
    protected ContainerInterface $container;
    protected RequestInterface $request;
    protected HttpResponse $response;
    protected LoggerInterface $logger;

    protected array $sensitiveFields = [
        'password',
        'password_confirmation',
        'current_password',
        'new_password',
        'token',
        'api_token',
        'access_token',
        'refresh_token',
        'auth_token',
        'secret',
        'api_key',
        'credit_card',
        'card_number',
        'cvv',
        'ssn',
        'social_security_number',
    ];

    protected array $sensitiveHeaders = [
        'authorization',
        'cookie',
        'set-cookie',
        'x-api-key',
        'x-auth-token',
    ];

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
        $this->request = $container->get(RequestInterface::class);
        $this->response = $container->get(HttpResponse::class);
        $this->logger = $container->get(LoggerInterface::class);
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $startTime = microtime(true);

        $requestId = $this->generateRequestId();
        $request = $request->withAttribute('request_id', $requestId);

        if (env('REQUEST_LOGGING_ENABLED', true)) {
            $this->logRequest($request, $requestId);
        }

        $response = $handler->handle($request);

        $duration = microtime(true) - $startTime;

        if (env('REQUEST_LOGGING_ENABLED', true)) {
            $this->logResponse($response, $requestId, $duration);
        }

        return $response
            ->withHeader('X-Request-ID', $requestId)
            ->withHeader('X-Response-Time', number_format($duration * 1000, 2) . 'ms');
    }

    protected function logRequest(ServerRequestInterface $request, string $requestId): void
    {
        $logData = [
            'request_id' => $requestId,
            'method' => $request->getMethod(),
            'uri' => (string) $request->getUri(),
            'query' => $request->getQueryParams(),
            'ip' => $this->getClientIp($request),
            'user_agent' => $request->getHeaderLine('User-Agent'),
            'content_type' => $request->getHeaderLine('Content-Type'),
            'headers' => $this->sanitizeHeaders($request->getHeaders()),
        ];

        if (in_array($request->getMethod(), ['POST', 'PUT', 'PATCH'])) {
            $body = $request->getParsedBody();
            if ($body) {
                $logData['body'] = $this->sanitizeData($body);
            }
        }

        $userId = $request->getAttribute('user');
        if ($userId) {
            $logData['user_id'] = is_array($userId) ? ($userId['id'] ?? null) : $userId;
        }

        $this->logger->info('API Request', $logData);
    }

    protected function logResponse(ResponseInterface $response, string $requestId, float $duration): void
    {
        $logData = [
            'request_id' => $requestId,
            'status_code' => $response->getStatusCode(),
            'reason_phrase' => $response->getReasonPhrase(),
            'duration_ms' => round($duration * 1000, 2),
            'headers' => $this->sanitizeHeaders($response->getHeaders()),
        ];

        $contentType = $response->getHeaderLine('Content-Type');
        if (str_contains($contentType, 'application/json')) {
            $body = (string) $response->getBody();
            if ($body) {
                $decoded = json_decode($body, true);
                if ($decoded) {
                    $logData['body'] = $this->sanitizeData($decoded);
                }
            }
        }

        $logLevel = $response->getStatusCode() >= 500 ? 'error' : 
                   ($response->getStatusCode() >= 400 ? 'warning' : 'info');

        $this->logger->log($logLevel, 'API Response', $logData);
    }

    protected function sanitizeHeaders(array $headers): array
    {
        $sanitized = [];

        foreach ($headers as $name => $values) {
            $lowerName = strtolower($name);

            if (in_array($lowerName, $this->sensitiveHeaders)) {
                $sanitized[$name] = ['[REDACTED]'];
            } else {
                $sanitized[$name] = $values;
            }
        }

        return $sanitized;
    }

    protected function sanitizeData($data): array|string
    {
        if (!is_array($data)) {
            return $this->sanitizeString($data);
        }

        $sanitized = [];

        foreach ($data as $key => $value) {
            $lowerKey = strtolower((string) $key);

            if (in_array($lowerKey, $this->sensitiveFields)) {
                $sanitized[$key] = '[REDACTED]';
            } elseif (is_array($value)) {
                $sanitized[$key] = $this->sanitizeData($value);
            } else {
                $sanitized[$key] = $this->sanitizeString($value);
            }
        }

        return $sanitized;
    }

    protected function sanitizeString($value): string
    {
        if (!is_string($value)) {
            return is_scalar($value) ? (string) $value : get_debug_type($value);
        }

        if (strlen($value) > 1000) {
            return substr($value, 0, 1000) . '... [TRUNCATED]';
        }

        return $value;
    }

    protected function generateRequestId(): string
    {
        return Uuid::uuid4()->toString();
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
