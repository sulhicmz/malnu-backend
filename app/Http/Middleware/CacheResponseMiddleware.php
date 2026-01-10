<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Hyperf\HttpServer\Contract\ResponseInterface;
use Hyperf\HttpServer\Request;
use Psr\Http\Message\ResponseInterface as PsrResponse;
use Psr\Http\Message\ServerRequestInterface;
use Psr\SimpleCache\CacheInterface;

class CacheResponseMiddleware
{
    private CacheInterface $cache;
    private int $cacheTtl;
    private array $cacheableMethods = ['GET', 'HEAD', 'OPTIONS'];
    private array $cacheableStatusCodes = [200, 301, 302, 304];

    public function __construct(CacheInterface $cache)
    {
        $this->cache = $cache;
        $this->cacheTtl = (int) env('CACHE_RESPONSE_TTL', 300);
    }

    public function process(ServerRequestInterface $request, Request $handler): PsrResponse
    {
        $response = $handler($request);

        if (!$this->isCacheable($request, $response)) {
            return $response;
        }

        $cacheKey = $this->getCacheKey($request);

        $existingResponse = $this->cache->get($cacheKey);
        if ($existingResponse && !$this->hasChanged($request, $response)) {
            return $this->addCacheHeaders($response, $cacheKey, true);
        }

        $this->cache->set($cacheKey, $response, $this->cacheTtl);
        return $this->addCacheHeaders($response, $cacheKey, false);
    }

    private function isCacheable(ServerRequestInterface $request, PsrResponse $response): bool
    {
        if (!in_array($request->getMethod(), $this->cacheableMethods, true)) {
            return false;
        }

        if (!in_array($response->getStatusCode(), $this->cacheableStatusCodes, true)) {
            return false;
        }

        $contentType = $response->getHeaderLine('Content-Type');
        if (strpos($contentType, 'application/json') === false) {
            return false;
        }

        return !$response->getHeaderLine('Cache-Control');
    }

    private function hasChanged(ServerRequestInterface $request, PsrResponse $response): bool
    {
        $requestETag = $request->getHeaderLine('If-None-Match');
        if (!$requestETag) {
            return true;
        }

        $responseETag = $response->getHeaderLine('ETag');
        if (!$responseETag) {
            return true;
        }

        return $requestETag !== $responseETag;
    }

    private function getCacheKey(ServerRequestInterface $request): string
    {
        return 'response:' . md5($request->getUri()->getPath() . ':' . $request->getMethod());
    }

    private function addCacheHeaders(PsrResponse $response, string $cacheKey, bool $hit): PsrResponse
    {
        $age = $hit ? $this->getAge($cacheKey) : '0';
        $maxAge = $this->cacheTtl;
        $expires = gmdate('D, d M Y H:i:s', time() + $this->cacheTtl);

        return $response
            ->withHeader('Cache-Control', "public, max-age={$maxAge}, must-revalidate")
            ->withHeader('ETag', '"' . $cacheKey . '"')
            ->withHeader('X-Cache-Key', $cacheKey)
            ->withHeader('X-Cache-Status', $hit ? 'HIT' : 'MISS')
            ->withHeader('Age', $age);
    }

    private function getAge(string $cacheKey): string
    {
        $cachedAt = $this->cache->get("{$cacheKey}:timestamp");
        if (!$cachedAt) {
            return '0';
        }

        return (string) (time() - strtotime($cachedAt));
    }
}
