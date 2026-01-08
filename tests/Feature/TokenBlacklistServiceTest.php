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
}
