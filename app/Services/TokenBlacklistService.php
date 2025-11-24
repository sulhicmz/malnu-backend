<?php

namespace App\Services;

class TokenBlacklistService
{
    private int $defaultTtl;
    
    public function __construct()
    {
        // Get default TTL from environment or use 2 hours as default (in seconds)
        $this->defaultTtl = (int)(($_ENV['JWT_TTL'] ?? 120) * 60); // Convert minutes to seconds
    }
    
    /**
     * Add token to blacklist with expiration based on token's TTL
     */
    public function blacklistToken(string $token): void
    {
        // Use raw Redis connection via phpredis extension
        $redis = $this->getRedisConnection();
        if ($redis) {
            // Use Redis SETEX to automatically expire the token after the TTL
            $redis->setex('jwt_blacklist:' . sha1($token), $this->defaultTtl, time());
        }
    }
    
    /**
     * Check if token is blacklisted
     */
    public function isTokenBlacklisted(string $token): bool
    {
        $redis = $this->getRedisConnection();
        if ($redis) {
            return (bool) $redis->exists('jwt_blacklist:' . sha1($token));
        }
        return false;
    }
    
    /**
     * Clean expired tokens from blacklist (not needed with Redis EXPIRE)
     * Redis automatically removes expired keys
     */
    public function cleanExpiredTokens(int $ttlSeconds = 86400): void
    {
        // No action needed - Redis handles expiration automatically
        // This method is kept for compatibility but doesn't need to do anything
    }
    
    /**
     * Get Redis connection using environment configuration
     * @return mixed
     */
    private function getRedisConnection()
    {
        try {
            $redis = new \Redis();
            $redis->connect(
                $_ENV['REDIS_HOST'] ?? 'localhost',
                (int)($_ENV['REDIS_PORT'] ?? 6379)
            );
            
            $auth = $_ENV['REDIS_AUTH'] ?? null;
            if ($auth && $auth !== '(null)') {
                $redis->auth($auth);
            }
            
            $db = (int)($_ENV['REDIS_DB'] ?? 0);
            $redis->select($db);
            
            return $redis;
        } catch (\Exception $e) {
            // Log error or handle as needed
            return null;
        }
    }
}