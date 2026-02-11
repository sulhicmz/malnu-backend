<?php

declare(strict_types=1);

namespace App\Services;

use App\Contracts\TokenBlacklistServiceInterface;
use App\Services\LoggingService;
use Redis;
use RedisException;

class TokenBlacklistService implements TokenBlacklistServiceInterface
{
    private ?Redis $redis = null;

    private string $cachePrefix = 'jwt_blacklist:';

    private string $redisHost;

    private int $redisPort;

    private string $redisDb;

    private LoggingService $loggingService;

    public function __construct(LoggingService $loggingService)
    {
        $this->loggingService = $loggingService;

        $this->redisHost = config('redis.default.host', 'localhost');
        $this->redisPort = config('redis.default.port', 6379);
        $this->redisDb = config('redis.default.db', 0);

        try {
            $this->redis = new Redis();
            $this->redis->connect($this->redisHost, $this->redisPort);
            $this->redis->select($this->redisDb);
        } catch (RedisException $e) {
            $this->loggingService->error('Failed to connect to Redis for token blacklist', ['exception' => $e->getMessage()]);
        }
    }

    /**
     * Add token to blacklist.
     */
    public function blacklistToken(string $token): void
    {
        if (! $this->redis) {
            return;
        }

        $cacheKey = $this->getCacheKey($token);
        $ttl = config('jwt.blacklist_ttl', 86400);
        $expiresAt = time() + $ttl;

        try {
            $this->redis->setex($cacheKey, $ttl, $expiresAt);
        } catch (RedisException $e) {
            $this->loggingService->logTokenBlacklistOperation('blacklist_token_failed', null, ['exception' => $e->getMessage()]);
        }
    }

    /**
     * Check if token is blacklisted.
     */
    public function isTokenBlacklisted(string $token): bool
    {
        if (! $this->redis) {
            return false;
        }

        $cacheKey = $this->getCacheKey($token);

        try {
            return (bool) $this->redis->exists($cacheKey);
        } catch (RedisException $e) {
            $this->loggingService->error('Failed to check token blacklist status', ['exception' => $e->getMessage()]);
            return false;
        }
    }

    /**
     * Clean expired tokens from blacklist.
     */
    public function cleanExpiredTokens(int $ttlSeconds = 86400): void
    {
    }

    private function getCacheKey(string $token): string
    {
        return $this->cachePrefix . hash('sha256', $token);
    }
}
