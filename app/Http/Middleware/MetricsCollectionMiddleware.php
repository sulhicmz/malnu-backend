<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Hyperf\Config\ConfigInterface;
use Hyperf\Di\Annotation\Inject;
use Hyperf\Redis\Redis;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Log\LoggerInterface;

class MetricsCollectionMiddleware implements MiddlewareInterface
{
    #[Inject]
    protected ConfigInterface $config;

    #[Inject]
    protected Redis $redis;

    #[Inject]
    protected LoggerInterface $logger;

    public function __construct(ContainerInterface $container)
    {
        $this->config = $container->get(ConfigInterface::class);
        $this->redis = $container->get(Redis::class);
        $this->logger = $container->get(LoggerInterface::class);
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $enabled = $this->config->get('monitoring.enabled', true);

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

            $this->recordRequestMetrics($request, $response, $duration);

            return $response;
        } catch (\Throwable $e) {
            $duration = round((microtime(true) - $startTime) * 1000, 2);

            $this->recordErrorMetrics($request, $e, $duration);

            throw $e;
        }
    }

    protected function shouldExcludePath(string $path): bool
    {
        $excludePaths = $this->config->get('monitoring.exclude_paths', [
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

    protected function recordRequestMetrics(ServerRequestInterface $request, ResponseInterface $response, float $duration): void
    {
        try {
            $statusCode = $response->getStatusCode();
            $method = $request->getMethod();
            $path = $request->getUri()->getPath();

            $this->redis->incr('metrics:requests:total');
            $this->redis->expire('metrics:requests:total', 86400);

            if ($statusCode < 400) {
                $this->redis->incr('metrics:requests:successful');
                $this->redis->expire('metrics:requests:successful', 86400);
            } else {
                $this->redis->incr('metrics:requests:failed');
                $this->redis->expire('metrics:requests:failed', 86400);
            }

            $this->redis->lpush('metrics:requests:response_times', $duration);
            $this->redis->expire('metrics:requests:response_times', 300);
            $this->redis->ltrim('metrics:requests:response_times', 0, 999);

            $this->updateResponseTimeMetrics();

            $key = "metrics:requests:{$method}:{$statusCode}";
            $this->redis->incr($key);
            $this->redis->expire($key, 86400);

            if ($statusCode >= 400) {
                $this->logger->warning('Request completed with client error', [
                    'type' => 'metrics',
                    'method' => $method,
                    'path' => $path,
                    'status_code' => $statusCode,
                    'duration_ms' => $duration,
                ]);
            }
        } catch (\Throwable $e) {
            $this->logger->error('Failed to record request metrics', [
                'type' => 'metrics_error',
                'error' => $e->getMessage(),
            ]);
        }
    }

    protected function recordErrorMetrics(ServerRequestInterface $request, \Throwable $e, float $duration): void
    {
        try {
            $errorType = get_class($e);
            $errorMessage = $e->getMessage();
            $method = $request->getMethod();
            $path = $request->getUri()->getPath();

            $this->redis->incr('metrics:errors:total');
            $this->redis->expire('metrics:errors:total', 86400);

            $errorTypes = json_decode($this->redis->get('metrics:errors:types') ?? '{}', true);
            $errorKey = $errorType;

            if (! isset($errorTypes[$errorKey])) {
                $errorTypes[$errorKey] = 0;
            }

            $errorTypes[$errorKey]++;

            $this->redis->setex('metrics:errors:types', 86400, json_encode($errorTypes));

            if ($e instanceof \Error || $e instanceof \RuntimeException) {
                $this->redis->incr('metrics:errors:critical');
                $this->redis->expire('metrics:errors:critical', 86400);
            } else {
                $this->redis->incr('metrics:errors:warning');
                $this->redis->expire('metrics:errors:warning', 86400);
            }

            $this->logger->error('Request failed with exception', [
                'type' => 'metrics_error',
                'method' => $method,
                'path' => $path,
                'duration_ms' => $duration,
                'exception' => $errorType,
                'message' => $errorMessage,
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);
        } catch (\Throwable $ex) {
            $this->logger->error('Failed to record error metrics', [
                'type' => 'metrics_error',
                'error' => $ex->getMessage(),
            ]);
        }
    }

    protected function updateResponseTimeMetrics(): void
    {
        try {
            $responseTimes = $this->redis->lrange('metrics:requests:response_times', 0, -1);

            if (empty($responseTimes)) {
                return;
            }

            $count = count($responseTimes);
            $sum = array_sum($responseTimes);
            $avg = $sum / $count;

            sort($responseTimes);
            $p95Index = (int) ($count * 0.95);
            $p99Index = (int) ($count * 0.99);

            $p95 = $responseTimes[$p95Index] ?? 0;
            $p99 = $responseTimes[$p99Index] ?? 0;

            $this->redis->setex('metrics:requests:avg_response_time', 300, $avg);
            $this->redis->setex('metrics:requests:p95_response_time', 300, $p95);
            $this->redis->setex('metrics:requests:p99_response_time', 300, $p99);
        } catch (\Throwable $e) {
            $this->logger->error('Failed to update response time metrics', [
                'type' => 'metrics_error',
                'error' => $e->getMessage(),
            ]);
        }
    }
}
