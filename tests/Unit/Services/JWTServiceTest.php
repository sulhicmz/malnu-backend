<?php

declare(strict_types=1);

namespace Tests\Unit\Services;

use App\Services\Auth\JWTService;
use App\Models\User;
use Tests\TestCase;

class JWTServiceTest extends TestCase
{
    private JWTService $jwtService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->jwtService = app(JWTService::class);
    }

    public function test_can_generate_token(): void
    {
        $user = User::factory()->create();
        
        $token = $this->jwtService->generateToken($user);
        
        $this->assertNotEmpty($token);
        $this->assertIsString($token);
    }

    public function test_generated_token_contains_user_data(): void
    {
        $user = User::factory()->create([
            'id' => 'test-uuid-123',
            'email' => 'test@example.com',
            'role' => 'student',
        ]);
        
        $token = $this->jwtService->generateToken($user);
        $decoded = $this->jwtService->decodeToken($token);
        
        $this->assertEquals('test-uuid-123', $decoded['sub']);
        $this->assertEquals('test@example.com', $decoded['email']);
        $this->assertEquals('student', $decoded['role']);
    }

    public function test_can_validate_valid_token(): void
    {
        $user = User::factory()->create();
        $token = $this->jwtService->generateToken($user);
        
        $isValid = $this->jwtService->validateToken($token);
        
        $this->assertTrue($isValid);
    }

    public function test_invalid_token_is_rejected(): void
    {
        $isValid = $this->jwtService->validateToken('invalid.token.here');
        
        $this->assertFalse($isValid);
    }

    public function test_expired_token_is_rejected(): void
    {
        $user = User::factory()->create();
        $expiredToken = $this->jwtService->generateToken($user, -3600); // Expired 1 hour ago
        
        $isValid = $this->jwtService->validateToken($expiredToken);
        
        $this->assertFalse($isValid);
    }

    public function test_can_refresh_token(): void
    {
        $user = User::factory()->create();
        $token = $this->jwtService->generateToken($user);
        
        $refreshedToken = $this->jwtService->refreshToken($token);
        
        $this->assertNotEmpty($refreshedToken);
        $this->assertNotEquals($token, $refreshedToken);
        $this->assertTrue($this->jwtService->validateToken($refreshedToken));
    }

    public function test_token_has_expiration(): void
    {
        $user = User::factory()->create();
        $token = $this->jwtService->generateToken($user);
        $decoded = $this->jwtService->decodeToken($token);
        
        $this->assertArrayHasKey('exp', $decoded);
        $this->assertGreaterThan(time(), $decoded['exp']);
    }

    public function test_token_has_issued_at_time(): void
    {
        $user = User::factory()->create();
        $token = $this->jwtService->generateToken($user);
        $decoded = $this->jwtService->decodeToken($token);
        
        $this->assertArrayHasKey('iat', $decoded);
        $this->assertLessThanOrEqual(time(), $decoded['iat']);
    }

    public function test_blacklisted_token_is_rejected(): void
    {
        $user = User::factory()->create();
        $token = $this->jwtService->generateToken($user);
        
        $this->jwtService->blacklistToken($token);
        $isValid = $this->jwtService->validateToken($token);
        
        $this->assertFalse($isValid);
    }
}
