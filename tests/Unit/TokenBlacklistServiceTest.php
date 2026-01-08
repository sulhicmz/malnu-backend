<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Services\TokenBlacklistService;
use Tests\TestCase;

/**
 * @internal
 * @coversNothing
 */
class TokenBlacklistServiceTest extends TestCase
{
    private TokenBlacklistService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new TokenBlacklistService();
    }

    public function testCanAddTokenToBlacklist()
    {
        $token = 'valid_token_123';

        $this->service->blacklistToken($token);

        $this->assertTrue($this->service->isTokenBlacklisted($token));
    }

    public function testCanCheckIfTokenIsBlacklisted()
    {
        $token1 = 'token_1';
        $token2 = 'token_2';

        $this->service->blacklistToken($token1);

        $this->assertTrue($this->service->isTokenBlacklisted($token1));
        $this->assertFalse($this->service->isTokenBlacklisted($token2));
    }

    public function testNonBlacklistedTokenReturnsFalse()
    {
        $token = 'non_existing_token';

        $this->assertFalse($this->service->isTokenBlacklisted($token));
    }

    public function testMultipleTokensCanBeBlacklisted()
    {
        $token1 = 'token_a';
        $token2 = 'token_b';
        $token3 = 'token_c';

        $this->service->blacklistToken($token1);
        $this->service->blacklistToken($token2);
        $this->service->blacklistToken($token3);

        $this->assertTrue($this->service->isTokenBlacklisted($token1));
        $this->assertTrue($this->service->isTokenBlacklisted($token2));
        $this->assertTrue($this->service->isTokenBlacklisted($token3));
    }

    public function testCleanExpiredTokensRemovesOldTokens()
    {
        $oldToken = 'old_token';
        $recentToken = 'recent_token';
        $ttl = 3600;

        $this->service->blacklistToken($oldToken);

        sleep(1);

        $this->service->blacklistToken($recentToken);

        $this->assertTrue($this->service->isTokenBlacklisted($oldToken));
        $this->assertTrue($this->service->isTokenBlacklisted($recentToken));

        $this->service->cleanExpiredTokens(1);

        $this->assertFalse($this->service->isTokenBlacklisted($oldToken));
        $this->assertTrue($this->service->isTokenBlacklisted($recentToken));
    }

    public function testCleanExpiredTokensWithDefaultTtl()
    {
        $token = 'test_token';

        $this->service->blacklistToken($token);

        $this->assertTrue($this->service->isTokenBlacklisted($token));

        $this->service->cleanExpiredTokens();

        $this->assertTrue($this->service->isTokenBlacklisted($token));
    }

    public function testCleanExpiredTokensDoesNotRemoveRecentTokens()
    {
        $token = 'recent_token';

        $this->service->blacklistToken($token);

        $this->service->cleanExpiredTokens(86400);

        $this->assertTrue($this->service->isTokenBlacklisted($token));
    }

    public function testBlacklistingSameTokenOverwritesTimestamp()
    {
        $token = 'duplicate_token';

        $this->service->blacklistToken($token);
        $firstTimestamp = time();

        sleep(1);

        $this->service->blacklistToken($token);
        $secondTimestamp = time();

        $this->assertTrue($this->service->isTokenBlacklisted($token));
        $this->assertGreaterThan($firstTimestamp, $secondTimestamp);
    }

    public function testEmptyStringTokenCanBeBlacklisted()
    {
        $token = '';

        $this->service->blacklistToken($token);

        $this->assertTrue($this->service->isTokenBlacklisted($token));
    }

    public function testLongJwtTokenCanBeBlacklisted()
    {
        $longToken = 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJzdWIiOiIxMjM0NTY3ODkwIiwibmFtZSI6IkpvaG4gRG9lIiwiaWF0IjoxNTE2MjM5MDIyfQ.SflKxwRJSMeKKF2QT4fwpMeJf36POk6yJV_adQssw5c';

        $this->service->blacklistToken($longToken);

        $this->assertTrue($this->service->isTokenBlacklisted($longToken));
    }
}
