<?php

declare(strict_types=1);

namespace App\Services;

use App\Contracts\TokenBlacklistServiceInterface;

class TokenBlacklistService implements TokenBlacklistServiceInterface
{
    private array $blacklistedTokens = [];
    
    /**
     * Add token to blacklist
     */
    public function blacklistToken(string $token): void
    {
        $this->blacklistedTokens[$token] = time();
    }
    
    /**
     * Check if token is blacklisted
     */
    public function isTokenBlacklisted(string $token): bool
    {
        return isset($this->blacklistedTokens[$token]);
    }
    
    /**
     * Clean expired tokens from blacklist
     */
    public function cleanExpiredTokens(int $ttlSeconds = 86400): void // Default 24 hours
    {
        $currentTime = time();
        foreach ($this->blacklistedTokens as $token => $timestamp) {
            if ($currentTime - $timestamp > $ttlSeconds) {
                unset($this->blacklistedTokens[$token]);
            }
        }
    }
}