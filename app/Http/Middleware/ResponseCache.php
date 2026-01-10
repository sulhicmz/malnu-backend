<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Services\CacheService;
use Hyperf\Contract\ConfigInterface;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class ResponseCache implements MiddlewareInterface
{
    protected ContainerInterface $container;

    protected CacheService $cache;

    protected ConfigInterface $config;

    protected array $cachedRoutes = [
        'GET' => [],
        'HEAD' => [],
    ];

    protected array $excludedRoutes = [
        '/api/auth',
        '/api/user',
        '/api/attendance',
        '/api/school',
        '/api/calendar',
    ];

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $this->cache = $this->container->get(CacheService::class);
        $this->config = $this->container->get(ConfigInterface::class);

        $method = $request->getMethod();
        $uri = $request->getUri()->getPath();
        $queryString = $request->getUri()->getQuery();
        $cacheKey = $this->getCacheKey($request);

        if (!$this->shouldCache($method, $uri)) {
            return $handler->handle($request);
        }

        if ($this->isExcludedRoute($uri)) {
            return $this->addCacheHeaders($handler->handle($request), 'BYPASS', false);
        }

        $cachedResponse = $this->getCachedResponse($cacheKey);

        if ($cachedResponse !== null) {
            return $this->addCacheHeaders($cachedResponse, 'HIT', true);
        }

        $response = $handler->handle($request);

        if ($this->isCacheable($response)) {
            $this->cacheResponse($cacheKey, $response);
            return $this->addCacheHeaders($response, 'MISS', false);
        }

        return $this->addCacheHeaders($response, 'BYPASS', false);
    }

    protected function shouldCache(string $method, string $uri): bool
    {
        if (!in_array($method, ['GET', 'HEAD'])) {
            return false;
        }

        if ($this->config->get('cache.enabled', false) === false) {
            return false;
        }

        return true;
    }

    protected function isExcludedRoute(string $uri): bool
    {
        foreach ($this->excludedRoutes as $route) {
            if (str_starts_with($uri, $route)) {
                return true;
            }
        }

        return false;
    }

    protected function getCacheKey(ServerRequestInterface $request): string
    {
        $method = $request->getMethod();
        $uri = $request->getUri()->getPath();
        $queryString = $request->getUri()->getQuery();
        $queryParams = http_build_query($request->getQueryParams());

        return "response:{$method}:{$uri}:{$queryParams}";
    }

    protected function getCachedResponse(string $cacheKey): ?ResponseInterface
    {
        $cached = $this->cache->get($cacheKey);

        if ($cached === null) {
            return null;
        }

        return unserialize($cached);
    }

    protected function cacheResponse(string $cacheKey, ResponseInterface $response): void
    {
        $ttl = (int)$this->config->get('cache.ttl', 300);

        $this->cache->set($cacheKey, serialize($response), $ttl);
    }

    protected function isCacheable(ResponseInterface $response): bool
    {
        $statusCode = $response->getStatusCode();

        if ($statusCode !== 200) {
            return false;
        }

        $contentType = $response->getHeaderLine('Content-Type');

        if (empty($contentType)) {
            return false;
        }

        if (!str_contains($contentType, 'application/json')) {
            return false;
        }

        return true;
    }

    protected function addCacheHeaders(ResponseInterface $response, string $status, bool $fromCache): ResponseInterface
    {
        $response = $response->withHeader('X-Cache-Status', $status);

        if ($fromCache) {
            $response = $response->withHeader('Age', $this->getAge($response));
        }

        $response = $response->withHeader('Cache-Control', $this->getCacheControlHeader());

        return $response;
    }

    protected function getCacheControlHeader(): string
    {
        $ttl = (int)$this->config->get('cache.ttl', 300);
        $maxAge = $ttl;

        return "public, max-age={$maxAge}, s-maxage={$maxAge}";
    }

    protected function getAge(ResponseInterface $response): string
    {
        $dateHeader = $response->getHeaderLine('Date');

        if (empty($dateHeader)) {
            return '0';
        }

        $date = strtotime($dateHeader);
        $now = time();

        return max(0, (string)($now - $date));
    }

    protected function getCacheTtl(): int
    {
        return (int)$this->config->get('cache.ttl', 300);
    }
}
