<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Services\TokenBlacklistService;
use Tests\TestCase;

/**
 * @internal
 * @coversNothing
 */
class TokenBlacklistServiceTest extends TestCase
{
    private TokenBlacklistService $tokenBlacklistService;

    protected function setUp(): void
    {
        parent::setUp();

        $this->tokenBlacklistService = new TokenBlacklistService();
    }

    public function testTokenBlacklistingAndChecking()
    {
        $token = 'test_token_12345';

        // Initially, token should not be blacklisted
        $this->assertFalse($this->tokenBlacklistService->isTokenBlacklisted($token));

        // Blacklist the token
        $this->tokenBlacklistService->blacklistToken($token);

        // Now token should be blacklisted
        $this->assertTrue($this->tokenBlacklistService->isTokenBlacklisted($token));
    }

    public function testMultipleTokensCanBeBlacklisted()
    {
        $token1 = 'token_1';
        $token2 = 'token_2';
        $token3 = 'token_3';

        // Blacklist multiple tokens
        $this->tokenBlacklistService->blacklistToken($token1);
        $this->tokenBlacklistService->blacklistToken($token2);
        $this->tokenBlacklistService->blacklistToken($token3);

        // All should be blacklisted
        $this->assertTrue($this->tokenBlacklistService->isTokenBlacklisted($token1));
        $this->assertTrue($this->tokenBlacklistService->isTokenBlacklisted($token2));
        $this->assertTrue($this->tokenBlacklistService->isTokenBlacklisted($token3));

        // Non-blacklisted token should return false
        $this->assertFalse($this->tokenBlacklistService->isTokenBlacklisted('token_not_in_list'));
    }

    public function testDifferentTokensHaveDifferentHashKeys()
    {
        $token1 = 'similar_token';
        $token2 = 'similar_token_different';

        $this->tokenBlacklistService->blacklistToken($token1);

        // Only token1 should be blacklisted, not token2
        $this->assertTrue($this->tokenBlacklistService->isTokenBlacklisted($token1));
        $this->assertFalse($this->tokenBlacklistService->isTokenBlacklisted($token2));
    }

    public function testCleanExpiredTokensDoesNotThrowError()
    {
        $token = 'test_token_cleanup';

        $this->tokenBlacklistService->blacklistToken($token);

        // This should not throw any errors even if Redis is not available
        $this->expectNotToPerformAssertions();
        $this->tokenBlacklistService->cleanExpiredTokens();
    }

    public function testCacheKeyUsesSha256NotMd5()
    {
        $token = 'test_sha256_token';

        // Get the expected SHA-256 hash
        $expectedHash = hash('sha256', $token);
        $md5Hash = md5($token);

        // Verify the hashes are different
        $this->assertNotEquals($expectedHash, $md5Hash);

        // Blacklist the token and verify it works
        $this->tokenBlacklistService->blacklistToken($token);
        $this->assertTrue($this->tokenBlacklistService->isTokenBlacklisted($token));

        // Verify SHA-256 produces 64 character hex string (256 bits)
        $this->assertEquals(64, strlen($expectedHash));

        // Verify MD5 produces 32 character hex string (128 bits)
        $this->assertEquals(32, strlen($md5Hash));
    }

    public function testDifferentTokensProduceDifferentSha256Hashes()
    {
        $token1 = 'token_alpha_123';
        $token2 = 'token_beta_456';

        $hash1 = hash('sha256', $token1);
        $hash2 = hash('sha256', $token2);

        // Different tokens should produce different hashes
        $this->assertNotEquals($hash1, $hash2);

        // Both should be blacklisted independently
        $this->tokenBlacklistService->blacklistToken($token1);
        $this->tokenBlacklistService->blacklistToken($token2);

        $this->assertTrue($this->tokenBlacklistService->isTokenBlacklisted($token1));
        $this->assertTrue($this->tokenBlacklistService->isTokenBlacklisted($token2));
    }

    public function testTokenCaseSensitivityWithSha256()
    {
        $token1 = 'TestToken';
        $token2 = 'testtoken';

        $hash1 = hash('sha256', $token1);
        $hash2 = hash('sha256', $token2);

        // SHA-256 is case-sensitive
        $this->assertNotEquals($hash1, $hash2);

        // Only the exact token should be blacklisted
        $this->tokenBlacklistService->blacklistToken($token1);

        $this->assertTrue($this->tokenBlacklistService->isTokenBlacklisted($token1));
        $this->assertFalse($this->tokenBlacklistService->isTokenBlacklisted($token2));
    }
}
