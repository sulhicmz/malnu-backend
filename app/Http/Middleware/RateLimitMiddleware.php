<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Enums\ErrorCode;
use App\Services\CacheService;
use Hyperf\HttpServer\Contract\ResponseInterface;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface as PsrResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class RateLimitMiddleware implements MiddlewareInterface
{
    protected ContainerInterface $container;

    protected CacheService $cache;

    protected ResponseInterface $response;

    protected int $maxAttempts;

    protected int $decaySeconds;

    protected string $prefix = 'rate_limit';

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
        $this->cache = new CacheService();
        $this->response = $container->get(ResponseInterface::class);

        $this->maxAttempts = (int) env('RATE_LIMIT_MAX_ATTEMPTS', 60);
        $this->decaySeconds = (int) env('RATE_LIMIT_DECAY_SECONDS', 60);
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): PsrResponseInterface
    {
        $key = $this->resolveRequestSignature($request);

        if ($this->hasTooManyAttempts($key)) {
            return $this->buildResponse($key);
        }

        $this->hit($key);

        $response = $handler->handle($request);

        return $this->addHeaders($response, $key);
    }

    public function setMaxAttempts(int $attempts): self
    {
        $this->maxAttempts = $attempts;
        return $this;
    }

    public function setDecaySeconds(int $seconds): self
    {
        $this->decaySeconds = $seconds;
        return $this;
    }

    public function setPrefix(string $prefix): self
    {
        $this->prefix = $prefix;
        return $this;
    }

    protected function resolveRequestSignature(ServerRequestInterface $request): string
    {
        $ip = $this->getIpAddress($request);
        $uri = (string) $request->getUri();
        $method = $request->getMethod();

        return md5("{$ip}:{$method}:{$uri}");
    }

    protected function getIpAddress(ServerRequestInterface $request): string
    {
        $headers = [
            'X-Forwarded-For',
            'X-Real-IP',
            'CF-Connecting-IP',
            'HTTP_X_FORWARDED_FOR',
            'HTTP_X_REAL_IP',
        ];

        foreach ($headers as $header) {
            $ips = $request->getHeaderLine($header);
            if (! empty($ips)) {
                $ipsArray = explode(',', $ips);
                return trim($ipsArray[0]);
            }
        }

        $serverParams = $request->getServerParams();
        return $serverParams['REMOTE_ADDR'] ?? '127.0.0.1';
    }

    protected function hasTooManyAttempts(string $key): bool
    {
        $attempts = $this->cache->get("{$this->prefix}:{$key}:attempts", 0);

        return $attempts >= $this->maxAttempts;
    }

    protected function hit(string $key): void
    {
        $attemptsKey = "{$this->prefix}:{$key}:attempts";
        $timerKey = "{$this->prefix}:{$key}:timer";

        $attempts = $this->cache->increment($attemptsKey, 1);

        if ($attempts === 1) {
            $this->cache->set($timerKey, time(), $this->decaySeconds);
        }
    }

    protected function clear(string $key): void
    {
        $this->cache->forget("{$this->prefix}:{$key}:attempts");
        $this->cache->forget("{$this->prefix}:{$key}:timer");
    }

    protected function availableIn(string $key): int
    {
        $timerKey = "{$this->prefix}:{$key}:timer";
        $timer = $this->cache->get($timerKey);

        if ($timer === null) {
            return 0;
        }

        return $timer + $this->decaySeconds - time();
    }

    protected function attempts(string $key): int
    {
        return $this->cache->get("{$this->prefix}:{$key}:attempts", 0);
    }

    protected function buildResponse(string $key): PsrResponseInterface
    {
        $retryAfter = $this->availableIn($key);
        $attempts = $this->attempts($key);

        $response = [
            'success' => false,
            'error' => [
                'message' => 'Too many attempts. Please try again later.',
                'code' => ErrorCode::RATE_LIMIT_EXCEEDED,
                'details' => [
                    'attempts' => $attempts,
                    'max_attempts' => $this->maxAttempts,
                    'retry_after' => $retryAfter,
                    'retry_after_human' => $this->formatRetryAfter($retryAfter),
                ],
            ],
            'timestamp' => date('c'),
        ];

        return $this->response->json($response)
            ->withStatus(ErrorCode::getStatusCode(ErrorCode::RATE_LIMIT_EXCEEDED))
            ->withHeader('Retry-After', (string) $retryAfter)
            ->withHeader('X-RateLimit-Limit', (string) $this->maxAttempts)
            ->withHeader('X-RateLimit-Remaining', '0')
            ->withHeader('X-RateLimit-Reset', (string) (time() + $retryAfter));
    }

    protected function addHeaders(PsrResponseInterface $response, string $key): PsrResponseInterface
    {
        $remaining = max(0, $this->maxAttempts - $this->attempts($key));
        $retryAfter = $this->availableIn($key);

        return $response
            ->withHeader('X-RateLimit-Limit', (string) $this->maxAttempts)
            ->withHeader('X-RateLimit-Remaining', (string) $remaining)
            ->withHeader('X-RateLimit-Reset', (string) (time() + max(0, $retryAfter)));
    }

    protected function formatRetryAfter(int $seconds): string
    {
        if ($seconds < 60) {
            return "{$seconds} second" . ($seconds === 1 ? '' : 's');
        }

        $minutes = (int) ceil($seconds / 60);
        return "{$minutes} minute" . ($minutes === 1 ? '' : 's');
    }
}
