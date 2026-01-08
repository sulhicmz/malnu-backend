<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Services\MonitoringService;
use Hyperf\HttpServer\Contract\RequestInterface;
use Hyperf\HttpServer\Contract\ResponseInterface as HttpResponse;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;

class MonitoringMiddleware implements MiddlewareInterface
{
    protected ContainerInterface $container;
    protected RequestInterface $request;
    protected HttpResponse $response;
    protected MonitoringService $monitoringService;
    protected LoggerInterface $logger;

    private array $slowEndpoints = [
        'threshold' => 200,
        'log_slow' => true,
    ];

    private array $excludePaths = [
        '/health',
        '/health/detailed',
        '/monitoring/metrics',
    ];

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
        $this->request = $container->get(RequestInterface::class);
        $this->response = $container->get(HttpResponse::class);
        $this->monitoringService = $container->get(MonitoringService::class);
        $this->logger = $container->get(LoggerInterface::class);
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        if ($this->shouldExclude($request)) {
            return $handler->handle($request);
        }

        $startTime = microtime(true);
        $startTimeMs = (int) ($startTime * 1000);

        $context = $this->buildRequestContext($request);

        try {
            $response = $handler->handle($request);
            $endTime = microtime(true);
            $responseTime = (int) (($endTime - $startTime) * 1000);

            $this->monitoringService->trackRequest([
                'status' => $response->getStatusCode(),
                'response_time' => $responseTime,
                'method' => $request->getMethod(),
                'path' => $request->getUri()->getPath(),
            ]);

            $context['response'] = [
                'status' => $response->getStatusCode(),
                'response_time_ms' => $responseTime,
            ];

            $logLevel = $this->determineLogLevel($response->getStatusCode(), $responseTime);
            $this->logRequest($context, $logLevel);

            return $response;
        } catch (\Throwable $e) {
            $endTime = microtime(true);
            $responseTime = (int) (($endTime - $startTime) * 1000);

            $context['error'] = [
                'message' => $e->getMessage(),
                'code' => $e->getCode(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ];

            $context['response'] = [
                'response_time_ms' => $responseTime,
                'exception' => get_class($e),
            ];

            $this->monitoringService->trackRequest([
                'status' => 500,
                'response_time' => $responseTime,
                'error' => $e->getMessage(),
                'method' => $request->getMethod(),
                'path' => $request->getUri()->getPath(),
            ]);

            $this->monitoringService->trackError($e->getMessage(), $context);

            $this->logRequest($context, LogLevel::ERROR);

            throw $e;
        }
    }

    private function shouldExclude(ServerRequestInterface $request): bool
    {
        $path = $request->getUri()->getPath();

        foreach ($this->excludePaths as $excludePath) {
            if (str_starts_with($path, $excludePath)) {
                return true;
            }
        }

        return false;
    }

    private function buildRequestContext(ServerRequestInterface $request): array
    {
        $user = $request->getAttribute('user');
        $uri = $request->getUri();

        return [
            'timestamp' => date('c'),
            'method' => $request->getMethod(),
            'path' => $uri->getPath(),
            'query' => $uri->getQuery(),
            'ip' => $this->getClientIp($request),
            'user_agent' => $request->getHeaderLine('User-Agent'),
            'user_id' => $user ? ($user['id'] ?? null) : null,
            'request_id' => $this->generateRequestId(),
        ];
    }

    private function determineLogLevel(int $statusCode, int $responseTime): string
    {
        if ($statusCode >= 500) {
            return LogLevel::ERROR;
        }

        if ($statusCode >= 400) {
            return LogLevel::WARNING;
        }

        if ($responseTime > $this->slowEndpoints['threshold']) {
            return LogLevel::WARNING;
        }

        return LogLevel::INFO;
    }

    private function logRequest(array $context, string $level): void
    {
        $message = sprintf(
            '%s %s - %d (%dms)',
            $context['method'],
            $context['path'],
            $context['response']['status'] ?? '??',
            $context['response']['response_time_ms'] ?? 0
        );

        $this->logger->log($level, $message, $context);
    }

    private function getClientIp(ServerRequestInterface $request): string
    {
        $ip = $request->getHeaderLine('X-Forwarded-For');

        if (!empty($ip)) {
            $ips = explode(',', $ip);
            return trim($ips[0]);
        }

        $ip = $request->getHeaderLine('X-Real-IP');
        if (!empty($ip)) {
            return $ip;
        }

        $ip = $request->getHeaderLine('CF-Connecting-IP');
        if (!empty($ip)) {
            return $ip;
        }

        return $request->getServerParams()['REMOTE_ADDR'] ?? 'unknown';
    }

    private function generateRequestId(): string
    {
        return bin2hex(random_bytes(8));
    }
}
