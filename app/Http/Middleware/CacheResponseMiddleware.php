<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Services\CacheService;
use Hyperf\HttpServer\Contract\RequestInterface;
use Hyperf\HttpServer\Contract\ResponseInterface as HttpResponse;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class CacheResponseMiddleware implements MiddlewareInterface
{
    protected ContainerInterface $container;

    protected RequestInterface $request;

    protected HttpResponse $response;

    protected CacheService $cache;

    protected array $config;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
        $this->request = $container->get(RequestInterface::class);
        $this->response = $container->get(HttpResponse::class);
        $this->cache = new CacheService($container);
        $this->config = [
            'default_ttl' => 300,
            'cacheable_methods' => ['GET'],
            'cacheable_routes' => [
                '/api/v1/students',
                '/api/v1/teachers',
                '/api/v1/classes',
                '/api/v1/subjects',
                '/api/v1/calendar',
            ],
            'exclude_query_params' => ['_token', 'timestamp'],
        ];
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $method = $request->getMethod();
        $route = $request->getUri()->getPath();

        if (! $this->shouldCache($method, $route)) {
            return $handler->handle($request);
        }

        $cacheKey = $this->generateCacheKey($request);

        $cachedResponse = $this->cache->get($cacheKey);

        if ($cachedResponse !== null) {
            return $this->response->json($cachedResponse)
                ->withStatus(200)
                ->withHeader('X-Cache', 'HIT');
        }

        $response = $handler->handle($request);

        if ($this->isCacheableResponse($response)) {
            $body = (string) $response->getBody();
            $data = json_decode($body, true);

            if (json_last_error() === JSON_ERROR_NONE && isset($data['success']) && $data['success'] === true) {
                $ttl = $this->getTtlForRoute($route);
                $this->cache->set($cacheKey, $data, $ttl);
            }
        }

        return $response->withHeader('X-Cache', 'MISS');
    }

    public function invalidateRoute(string $route): void
    {
        $pattern = 'api_response:' . md5($route) . '*';
        $this->cache->forgetByPattern($pattern);
    }

    protected function shouldCache(string $method, string $route): bool
    {
        if (! in_array($method, $this->config['cacheable_methods'])) {
            return false;
        }

        foreach ($this->config['cacheable_routes'] as $cacheableRoute) {
            if (str_starts_with($route, $cacheableRoute)) {
                return true;
            }
        }

        return false;
    }

    protected function generateCacheKey(ServerRequestInterface $request): string
    {
        $route = $request->getUri()->getPath();
        $queryParams = $request->getQueryParams();

        foreach ($this->config['exclude_query_params'] as $param) {
            unset($queryParams[$param]);
        }

        ksort($queryParams);
        $queryString = http_build_query($queryParams);

        $key = $queryString ? "{$route}:{$queryString}" : $route;

        return 'api_response:' . md5($key);
    }

    protected function isCacheableResponse(ResponseInterface $response): bool
    {
        $statusCode = $response->getStatusCode();

        return $statusCode >= 200 && $statusCode < 300;
    }

    protected function getTtlForRoute(string $route): int
    {
        if (str_starts_with($route, '/api/v1/students')) {
            return 300;
        }

        if (str_starts_with($route, '/api/v1/teachers')) {
            return 300;
        }

        if (str_starts_with($route, '/api/v1/classes')) {
            return 3600;
        }

        if (str_starts_with($route, '/api/v1/subjects')) {
            return 3600;
        }

        if (str_starts_with($route, '/api/v1/calendar')) {
            return 600;
        }

        return $this->config['default_ttl'];
    }
}
