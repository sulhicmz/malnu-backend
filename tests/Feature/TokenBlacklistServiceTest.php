<?php

declare(strict_types=1);

namespace Tests\Feature;

use Tests\TestCase;
use App\Services\TokenBlacklistService;

class TokenBlacklistServiceTest extends TestCase
{
    private TokenBlacklistService $tokenBlacklistService;
    
    protected function setUp(): void
    {
        parent::setUp();
        
        $this->tokenBlacklistService = new TokenBlacklistService();
    }
    
    public function test_token_blacklisting_and_checking()
    {
        $token = 'test_token_12345';
        
        // Initially, token should not be blacklisted
        $this->assertFalse($this->tokenBlacklistService->isTokenBlacklisted($token));
        
        // Blacklist the token
        $this->tokenBlacklistService->blacklistToken($token);
        
        // Now token should be blacklisted
        $this->assertTrue($this->tokenBlacklistService->isTokenBlacklisted($token));
    }
    
    public function test_multiple_tokens_can_be_blacklisted()
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
    
    public function test_different_tokens_have_different_hash_keys()
    {
        $token1 = 'similar_token';
        $token2 = 'similar_token_different';
        
        $this->tokenBlacklistService->blacklistToken($token1);
        
        // Only token1 should be blacklisted, not token2
        $this->assertTrue($this->tokenBlacklistService->isTokenBlacklisted($token1));
        $this->assertFalse($this->tokenBlacklistService->isTokenBlacklisted($token2));
    }
    
    public function test_clean_expired_tokens_does_not_throw_error()
    {
        $token = 'test_token_cleanup';
        
        $this->tokenBlacklistService->blacklistToken($token);
        
        // This should not throw any errors even if Redis is not available
        $this->expectNotToPerformAssertions();
        $this->tokenBlacklistService->cleanExpiredTokens();
    }
    
    public function test_cache_key_uses_sha256_not_md5()
    {
        $token = 'test_token_sha256';
        
        // Use reflection to access private getCacheKey method
        $reflection = new \ReflectionClass($this->tokenBlacklistService);
        $method = $reflection->getMethod('getCacheKey');
        $method->setAccessible(true);
        
        $cacheKey = $method->invoke($this->tokenBlacklistService, $token);
        
        // Remove the prefix to get just the hash
        $hash = str_replace('jwt_blacklist:', '', $cacheKey);
        
        // SHA-256 produces 64-character hex string
        // MD5 produces 32-character hex string
        $this->assertEquals(64, strlen($hash), 'Cache key hash should be 64 characters (SHA-256), not 32 (MD5)');
        $this->assertMatchesRegularExpression('/^[a-f0-9]{64}$/', $hash, 'Hash should be valid 64-character hex string');
    }
    
    public function test_different_tokens_produce_different_sha256_hashes()
    {
        $token1 = 'token_a_123';
        $token2 = 'token_b_456';
        $token3 = 'token_c_789';
        
        $reflection = new \ReflectionClass($this->tokenBlacklistService);
        $method = $reflection->getMethod('getCacheKey');
        $method->setAccessible(true);
        
        $key1 = $method->invoke($this->tokenBlacklistService, $token1);
        $key2 = $method->invoke($this->tokenBlacklistService, $token2);
        $key3 = $method->invoke($this->tokenBlacklistService, $token3);
        
        // All three cache keys should be different
        $this->assertNotEquals($key1, $key2);
        $this->assertNotEquals($key2, $key3);
        $this->assertNotEquals($key1, $key3);
    }
    
    public function test_token_case_sensitivity_with_sha256()
    {
        $tokenLower = 'test_token';
        $tokenUpper = 'TEST_TOKEN';
        $tokenMixed = 'Test_Token';
        
        $reflection = new \ReflectionClass($this->tokenBlacklistService);
        $method = $reflection->getMethod('getCacheKey');
        $method->setAccessible(true);
        
        $keyLower = $method->invoke($this->tokenBlacklistService, $tokenLower);
        $keyUpper = $method->invoke($this->tokenBlacklistService, $tokenUpper);
        $keyMixed = $method->invoke($this->tokenBlacklistService, $tokenMixed);
        
        // Different cases should produce different hashes (SHA-256 is case-sensitive)
        $this->assertNotEquals($keyLower, $keyUpper);
        $this->assertNotEquals($keyLower, $keyMixed);
        $this->assertNotEquals($keyUpper, $keyMixed);
    }
}
