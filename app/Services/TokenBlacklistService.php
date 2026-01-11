<?php

declare(strict_types=1);

namespace App\Services;

use App\Contracts\TokenBlacklistServiceInterface;
use Redis;
use RedisException;

class TokenBlacklistService implements TokenBlacklistServiceInterface
{
    private ?Redis $redis = null;
    private string $cachePrefix = 'jwt_blacklist:';
    private string $redisHost;
    private int $redisPort;
    private string $redisDb;
    
    public function __construct()
    {
        $this->redisHost = env('REDIS_HOST') ?? 'localhost';
        $this->redisPort = (int)(env('REDIS_PORT') ?? 6379);
        $this->redisDb = (int)(env('REDIS_DB') ?? 0);
        
        try {
            $this->redis = new Redis();
            $this->redis->connect($this->redisHost, $this->redisPort);
            $this->redis->select($this->redisDb);
        } catch (RedisException $e) {
            error_log('Failed to connect to Redis for token blacklist: ' . $e->getMessage());
        }
    }
    
    /**
     * Add token to blacklist
     */
    public function blacklistToken(string $token): void
    {
        if (!$this->redis) {
            return;
        }
        
        $cacheKey = $this->getCacheKey($token);
        $expiresAt = time() + 86400;
        
        try {
            $this->redis->setex($cacheKey, 86400, $expiresAt);
        } catch (RedisException $e) {
            error_log('Failed to blacklist token: ' . $e->getMessage());
        }
    }
    
    /**
     * Check if token is blacklisted
     */
    public function isTokenBlacklisted(string $token): bool
    {
        if (!$this->redis) {
            return false;
        }
        
        $cacheKey = $this->getCacheKey($token);
        
        try {
            return (bool) $this->redis->exists($cacheKey);
        } catch (RedisException $e) {
            error_log('Failed to check token blacklist status: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Clean expired tokens from blacklist
     */
    public function cleanExpiredTokens(int $ttlSeconds = 86400): void
    {
        
    }
    
    private function getCacheKey(string $token): string
    {
        return $this->cachePrefix . md5($token);
    }
}
