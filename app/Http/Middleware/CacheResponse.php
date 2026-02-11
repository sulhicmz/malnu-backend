<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Services\CacheService;
use Hyperf\HttpMessage\Stream\SwooleStream;
use Hyperf\HttpServer\Contract\ResponseInterface as HttpResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class CacheResponse implements MiddlewareInterface
{
    private CacheService $cache;

    private int $defaultTTL = 300;

    private array $cacheableMethods = ['GET'];

    private array $excludedPaths = [
        '/api/login',
        '/api/register',
        '/api/refresh',
        '/api/logout',
        '/health',
    ];

    public function __construct(CacheService $cache)
    {
        $this->cache = $cache;
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        if (! $this->shouldCache($request)) {
            return $handler->handle($request);
        }

        $cacheKey = $this->generateCacheKey($request);

        $cachedResponse = $this->cache->get($cacheKey);

        if ($cachedResponse !== null) {
            return $this->buildCachedResponse($cachedResponse);
        }

        $response = $handler->handle($request);

        if ($this->isCacheableResponse($response)) {
            $this->storeResponseInCache($cacheKey, $response);
        }

        return $this->addCacheHeaders($response);
    }

    private function shouldCache(ServerRequestInterface $request): bool
    {
        $method = $request->getMethod();
        $uri = $request->getUri()->getPath();

        if (! in_array($method, $this->cacheableMethods, true)) {
            return false;
        }

        foreach ($this->excludedPaths as $excludedPath) {
            if (str_starts_with($uri, $excludedPath)) {
                return false;
            }
        }

        $authHeader = $request->getHeaderLine('Authorization');

        return empty($authHeader);
    }

    private function generateCacheKey(ServerRequestInterface $request): string
    {
        $params = [
            'method' => $request->getMethod(),
            'uri' => $request->getUri()->getPath(),
            'query' => $request->getQueryParams(),
        ];

        return $this->cache->generateKey('api:response', $params);
    }

    private function isCacheableResponse(ResponseInterface $response): bool
    {
        $statusCode = $response->getStatusCode();

        return $statusCode >= 200 && $statusCode < 300;
    }

    private function storeResponseInCache(string $cacheKey, ResponseInterface $response): void
    {
        $body = $response->getBody()->getContents();

        if (empty($body)) {
            return;
        }

        $cacheData = [
            'status' => $response->getStatusCode(),
            'headers' => $response->getHeaders(),
            'body' => $body,
        ];

        $this->cache->set($cacheKey, $cacheData, $this->defaultTTL);
    }

    private function buildCachedResponse(array $cachedData): ResponseInterface
    {
        $response = \Hyperf\Context\ApplicationContext::getContainer()
            ->get(HttpResponse::class);

        $response = $response->withStatus($cachedData['status']);

        foreach ($cachedData['headers'] as $name => $values) {
            foreach ($values as $value) {
                $response = $response->withAddedHeader($name, $value);
            }
        }

        $response = $response->withBody(new SwooleStream($cachedData['body']));

        return $response->withAddedHeader('X-Cache', 'HIT');
    }

    private function addCacheHeaders(ResponseInterface $response): ResponseInterface
    {
        return $response
            ->withAddedHeader('X-Cache', 'MISS')
            ->withAddedHeader('Cache-Control', "public, max-age={$this->defaultTTL}");
    }
}
