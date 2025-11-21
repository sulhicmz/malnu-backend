<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Hyperf\Contract\ConfigInterface;
use Hyperf\Redis\RedisFactory;
use Hyperf\Utils\Str;

class RateLimit implements MiddlewareInterface
{
    private ConfigInterface $config;
    private RedisFactory $redisFactory;

    public function __construct(ConfigInterface $config, RedisFactory $redisFactory)
    {
        $this->config = $config;
        $this->redisFactory = $redisFactory;
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $rateLimitConfig = $this->config->get('rate_limit', []);
        
        if (!($rateLimitConfig['enabled'] ?? false)) {
            return $handler->handle($request);
        }

        $key = $this->getRateLimitKey($request);
        $maxAttempts = $rateLimitConfig['max_attempts'] ?? 60;
        $decayMinutes = $rateLimitConfig['decay_minutes'] ?? 1;
        
        $redis = $this->redisFactory->get('default');
        $currentAttempts = $redis->get($key);
        
        if ($currentAttempts === false || $currentAttempts === null) {
            // First request, set the counter
            $redis->setex($key, $decayMinutes * 60, 1);
        } else {
            $currentAttempts = (int)$currentAttempts;
            
            if ($currentAttempts >= $maxAttempts) {
                // Rate limit exceeded
                return $this->buildRateLimitResponse($currentAttempts, $maxAttempts, $decayMinutes);
            }
            
            // Increment the counter
            $redis->incr($key);
        }
        
        return $handler->handle($request);
    }

    private function getRateLimitKey(ServerRequestInterface $request): string
    {
        $ip = $this->getClientIp($request);
        $uri = $request->getUri()->getPath();
        $method = $request->getMethod();
        
        return 'rate_limit:' . md5($ip . $uri . $method);
    }

    private function getClientIp(ServerRequestInterface $request): string
    {
        $serverParams = $request->getServerParams();
        
        // Check various headers that might contain the real client IP
        $headers = $request->getHeaders();
        
        if (isset($headers['x-forwarded-for'])) {
            $ips = explode(',', $headers['x-forwarded-for'][0]);
            return trim($ips[0]);
        }
        
        if (isset($headers['x-real-ip'])) {
            return $headers['x-real-ip'][0];
        }
        
        if (isset($headers['x-client-ip'])) {
            return $headers['x-client-ip'][0];
        }
        
        // Fallback to server params
        return $serverParams['remote_addr'] ?? '127.0.0.1';
    }

    private function buildRateLimitResponse(int $current, int $max, int $decayMinutes): ResponseInterface
    {
        $response = new \Hyperf\HttpMessage\Server\Response();
        
        $body = [
            'success' => false,
            'message' => 'Too many requests. Please try again later.',
            'data' => [
                'max_attempts' => $max,
                'current_attempts' => $current,
                'reset_in_minutes' => $decayMinutes
            ]
        ];
        
        return $response
            ->withHeader('Content-Type', 'application/json')
            ->withHeader('X-RateLimit-Limit', $max)
            ->withHeader('X-RateLimit-Remaining', $max - $current)
            ->withHeader('X-RateLimit-Reset', time() + ($decayMinutes * 60))
            ->withStatus(429)
            ->withBody(new \Hyperf\HttpMessage\Stream\SwooleStream(json_encode($body)));
    }
}