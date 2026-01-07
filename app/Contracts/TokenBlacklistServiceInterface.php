<?php

declare(strict_types=1);

namespace App\Contracts;

interface TokenBlacklistServiceInterface
{
    public function blacklistToken(string $token): void;

    public function isTokenBlacklisted(string $token): bool;

    public function cleanExpiredTokens(int $ttlSeconds = 86400): void;
}
