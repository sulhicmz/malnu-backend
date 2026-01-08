<?php

declare(strict_types=1);

namespace App\Middleware;

use Hyperf\Utils\Context;
use Hyperf\Di\Annotation\Inject;
use Hyperf\HttpServer\Contract\ResponseInterface as Response;
use Hyperf\Cache\CacheManager;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Log\LoggerInterface;

class ResponseCacheMiddleware implements MiddlewareInterface
{
    #[Inject]
    private CacheManager $cache;

    #[Inject]
    private LoggerInterface $logger;

    private int $defaultTTL = 3600;

    private array $cacheablePaths = [
        '/api/permissions',
        '/api/roles',
    ];

    private array $excludePaths = [
        '/api/auth/login',
        '/api/auth/register',
        '/api/auth/logout',
    ];

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $path = $request->getUri()->getPath();
        $method = $request->getMethod();

        if (!$this->shouldCache($path, $method)) {
            return $handler->handle($request);
        }

        $cacheKey = $this->getCacheKey($request);

        try {
            $cachedResponse = $this->cache->get($cacheKey);

            if ($cachedResponse !== null) {
                $this->logger->info('Response served from cache', ['path' => $path]);
                return $cachedResponse;
            }
        } catch (\Exception $e) {
            $this->logger->error('Cache read error', ['error' => $e->getMessage()]);
        }

        $response = $handler->handle($request);

        if ($this->isCacheableResponse($response)) {
            try {
                $this->cache->set($cacheKey, $response, $this->getTTL($path));
                $this->addCacheHeaders($response);
                $this->logger->info('Response cached', ['path' => $path]);
            } catch (\Exception $e) {
                $this->logger->error('Cache write error', ['error' => $e->getMessage()]);
            }
        }

        return $response;
    }

    private function shouldCache(string $path, string $method): bool
    {
        if ($method !== 'GET') {
            return false;
        }

        foreach ($this->excludePaths as $excludePath) {
            if (str_starts_with($path, $excludePath)) {
                return false;
            }
        }

        foreach ($this->cacheablePaths as $cacheablePath) {
            if (str_starts_with($path, $cacheablePath)) {
                return true;
            }
        }

        return false;
    }

    private function isCacheableResponse(ResponseInterface $response): bool
    {
        $statusCode = $response->getStatusCode();
        return $statusCode >= 200 && $statusCode < 300;
    }

    private function getCacheKey(ServerRequestInterface $request): string
    {
        $path = $request->getUri()->getPath();
        $query = $request->getUri()->getQuery();
        $userAgent = $request->getHeaderLine('User-Agent');

        return 'response:' . md5($path . $query . $userAgent);
    }

    private function getTTL(string $path): int
    {
        if (str_starts_with($path, '/api/permissions')) {
            return 3600;
        }

        if (str_starts_with($path, '/api/roles')) {
            return 3600;
        }

        return $this->defaultTTL;
    }

    private function addCacheHeaders(ResponseInterface $response): ResponseInterface
    {
        $etag = md5((string) $response->getBody());
        return $response->withHeader('ETag', $etag)
            ->withHeader('Cache-Control', 'public, max-age=' . $this->getTTL($response->getHeaderLine('X-Request-Path') ?: ''));
    }

    /**
     * Clear response cache by path pattern
     */
    public function clearCachePattern(string $pattern): void
    {
        $keys = $this->cache->getRedis()->keys('response:' . $pattern . '*');
        foreach ($keys as $key) {
            $this->cache->delete(substr($key, 9));
        }
    }
}
