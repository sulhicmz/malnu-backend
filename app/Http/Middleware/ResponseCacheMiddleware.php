<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Hyperf\Context\ApplicationContext;
use Hyperf\Di\Annotation\Inject;
use Psr\SimpleCache\CacheInterface;

class ResponseCacheMiddleware
{
    #[Inject]
    private ApplicationContext $context;

    private CacheInterface $cache;

    public function __construct()
    {
        $this->cache = $this->context->getContainer()->get(CacheInterface::class);
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        if ($this->shouldBypassCache($request)) {
            return $handler->handle($request);
        }

        $cacheKey = $this->generateCacheKey($request);

        $cachedResponse = $this->cache->get($cacheKey);

        if ($cachedResponse !== null) {
            return $this->createResponseFromCache($cachedResponse);
        }

        $response = $handler->handle($request);

        if ($this->shouldCacheResponse($response)) {
            $this->cacheResponse($cacheKey, $response);
        }

        return $this->addCacheHeaders($response);
    }

    private function shouldBypassCache(ServerRequestInterface $request): bool
    {
        $method = $request->getMethod();
        $uri = $request->getUri()->getPath();

        if ($method !== 'GET') {
            return true;
        }

        $bypassRoutes = [
            '/auth/login',
            '/auth/logout',
            '/auth/refresh',
        ];

        foreach ($bypassRoutes as $route) {
            if (str_starts_with($uri, $route)) {
                return true;
            }
        }

        return false;
    }

    private function generateCacheKey(ServerRequestInterface $request): string
    {
        $uri = $request->getUri()->getPath();
        $query = $request->getUri()->getQuery();
        $userId = $this->getUserId($request);

        $key = "response:{$uri}";

        if (!empty($query)) {
            $key .= ":{$query}";
        }

        if ($userId) {
            $key .= ":user:{$userId}";
        }

        return $key;
    }

    private function getUserId(ServerRequestInterface $request): ?string
    {
        return $request->getAttribute('user_id');
    }

    private function shouldCacheResponse(ResponseInterface $response): bool
    {
        $statusCode = $response->getStatusCode();

        if ($statusCode !== 200) {
            return false;
        }

        return true;
    }

    private function cacheResponse(string $key, ResponseInterface $response): void
    {
        $this->cache->set($key, $this->serializeResponse($response), 300);
    }

    private function createResponseFromCache(array $cachedData): ResponseInterface
    {
        return \Hyperf\HttpMessage\Server\Response::json(
            $cachedData['data'],
            200,
            [
                'X-Cache' => 'HIT',
                'X-Cache-At' => $cachedData['cached_at'],
            ]
        );
    }

    private function serializeResponse(ResponseInterface $response): array
    {
        return [
            'data' => (string) $response->getBody(),
            'cached_at' => date('Y-m-d H:i:s'),
        ];
    }

    private function addCacheHeaders(ResponseInterface $response): ResponseInterface
    {
        return $response->withHeader('X-Cache-Status', 'MISS')
            ->withHeader('Cache-Control', 'public, max-age=300');
    }
}