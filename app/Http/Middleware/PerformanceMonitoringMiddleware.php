<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Services\ErrorLoggingService;
use Hyperf\HttpServer\Contract\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Server\RequestHandlerInterface;

class PerformanceMonitoringMiddleware
{
    private ErrorLoggingService $errorLogger;
    private float $threshold;

    public function __construct(ErrorLoggingService $errorLogger)
    {
        $this->errorLogger = $errorLogger;
        $this->threshold = 2.0;
    }

    public function process(RequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $startTime = microtime(true);

        $uri = $request->getUri()->getPath();
        $method = $request->getMethod();

        try {
            $response = $handler->handle($request);

            $duration = microtime(true) - $startTime;
            $statusCode = $response->getStatusCode();

            $this->logPerformance($uri, $method, $duration, $statusCode);

            $response = $response->withHeader('X-Response-Time', (string) round($duration * 1000, 2) . 'ms');

            if ($duration > $this->threshold) {
                $response = $response->withHeader('X-Slow-Request', 'true');
            }

            return $response;

        } catch (\Throwable $e) {
            $duration = microtime(true) - $startTime;

            $this->errorLogger->logError('Exception during request processing', [
                'uri' => $uri,
                'method' => $method,
                'duration_ms' => round($duration * 1000, 2),
                'exception' => get_class($e),
                'message' => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    private function logPerformance(string $uri, string $method, float $duration, int $statusCode): void
    {
        $context = [
            'uri' => $uri,
            'method' => $method,
            'duration_ms' => round($duration * 1000, 2),
            'status_code' => $statusCode,
            'timestamp' => date('c'),
        ];

        if ($duration > $this->threshold) {
            $this->errorLogger->logPerformance($method . ' ' . $uri, $duration, $statusCode, $context);
        }
    }
}
