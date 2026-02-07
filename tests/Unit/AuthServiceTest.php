<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Services\AuthService;
use App\Services\JWTServiceInterface;
use App\Services\TokenBlacklistServiceInterface;
use App\Services\EmailService;
use App\Services\PasswordValidator;
use Tests\TestCase;

/**
 * @internal
 * @covers \App\Services\AuthService
 */
class AuthServiceTest extends TestCase
{
    private AuthService $authService;

    private $mockJwtService;
    private $mockTokenBlacklistService;
    private $mockEmailService;
    private $mockPasswordValidator;

    protected function setUp(): void
    {
        parent::setUp();

        $this->mockJwtService = $this->createMock(JWTServiceInterface::class);
        $this->mockTokenBlacklistService = $this->createMock(TokenBlacklistServiceInterface::class);
        $this->mockEmailService = $this->createMock(EmailService::class);
        $this->mockPasswordValidator = $this->createMock(PasswordValidator::class);

        $this->authService = new AuthService(
            $this->mockJwtService,
            $this->mockTokenBlacklistService,
            $this->mockEmailService,
            $this->mockPasswordValidator
        );
    }

    public function testRegisterWithValidData(): void
    {
        $userData = [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'SecurePass123!',
        ];

        $this->mockPasswordValidator->expects($this->once())
            ->method('validate')
            ->with('SecurePass123!')
            ->willReturn([]);

        $result = $this->authService->register($userData);

        $this->assertIsArray($result);
        $this->assertArrayHasKey('user', $result);
        $this->assertArrayHasKey('id', $result['user']);
        $this->assertArrayHasKey('name', $result['user']);
        $this->assertArrayHasKey('email', $result['user']);
    }

    public function testRegisterWithDuplicateEmail(): void
    {
        $userData = [
            'name' => 'Test User',
            'email' => 'duplicate@example.com',
            'password' => 'SecurePass123!',
        ];

        $this->mockPasswordValidator->expects($this->once())
            ->method('validate')
            ->willReturn([]);

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('User with this email already exists');

        $this->authService->register($userData);
    }

    public function testRegisterWithWeakPassword(): void
    {
        $userData = [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'weak',
        ];

        $this->mockPasswordValidator->expects($this->once())
            ->method('validate')
            ->with('weak')
            ->willReturn(['Password must be at least 8 characters']);

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Password must be at least 8 characters');

        $this->authService->register($userData);
    }

    public function testLoginWithValidCredentials(): void
    {
        $token = 'valid.jwt.token';
        $userArray = [
            'id' => '123',
            'email' => 'login@example.com',
            'name' => 'Login User',
            'password' => password_hash('password', PASSWORD_DEFAULT),
        ];

        $this->mockJwtService->expects($this->once())
            ->method('generateToken')
            ->with(['id' => '123', 'email' => 'login@example.com'])
            ->willReturn($token);

        $this->mockJwtService->expects($this->once())
            ->method('getExpirationTime')
            ->willReturn(7200);

        $result = $this->authService->login('login@example.com', 'password');

        $this->assertIsArray($result);
        $this->assertArrayHasKey('user', $result);
        $this->assertArrayHasKey('token', $result);
        $this->assertArrayHasKey('access_token', $result['token']);
        $this->assertArrayHasKey('token_type', $result['token']);
        $this->assertEquals('bearer', $result['token']['token_type']);
        $this->assertEquals($token, $result['token']['access_token']);
        $this->assertEquals(7200, $result['token']['expires_in']);
    }

    public function testLoginWithInvalidCredentials(): void
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Invalid credentials');

        $this->authService->login('wrong@example.com', 'wrongpassword');
    }

    public function testLoginWithNonExistentUser(): void
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Invalid credentials');

        $this->authService->login('nonexistent@example.com', 'anypassword');
    }

    public function testGetUserFromTokenWithValidToken(): void
    {
        $token = 'valid.token.here';
        $payload = ['data' => ['id' => '123', 'email' => 'test@example.com']];
        $userArray = [
            'id' => '123',
            'email' => 'test@example.com',
            'name' => 'Token User',
        ];

        $this->mockTokenBlacklistService->expects($this->once())
            ->method('isTokenBlacklisted')
            ->with($token)
            ->willReturn(false);

        $this->mockJwtService->expects($this->once())
            ->method('decodeToken')
            ->with($token)
            ->willReturn($payload);

        $result = $this->authService->getUserFromToken($token);

        $this->assertIsArray($result);
        $this->assertEquals('test@example.com', $result['email']);
    }

    public function testGetUserFromTokenWithBlacklistedToken(): void
    {
        $token = 'blacklisted.token';

        $this->mockTokenBlacklistService->expects($this->once())
            ->method('isTokenBlacklisted')
            ->with($token)
            ->willReturn(true);

        $this->mockJwtService->expects($this->never())
            ->method('decodeToken');

        $result = $this->authService->getUserFromToken($token);

        $this->assertNull($result);
    }

    public function testGetUserFromTokenWithInvalidToken(): void
    {
        $token = 'invalid.token';

        $this->mockTokenBlacklistService->expects($this->once())
            ->method('isTokenBlacklisted')
            ->with($token)
            ->willReturn(false);

        $this->mockJwtService->expects($this->once())
            ->method('decodeToken')
            ->with($token)
            ->willReturn(null);

        $result = $this->authService->getUserFromToken($token);

        $this->assertNull($result);
    }

    public function testLogoutAddsTokenToBlacklist(): void
    {
        $token = 'valid.jwt.token';

        $this->mockTokenBlacklistService->expects($this->once())
            ->method('blacklistToken')
            ->with($token);

        $this->authService->logout($token);
    }

    public function testRefreshTokenWithValidToken(): void
    {
        $oldToken = 'old.jwt.token';
        $newToken = 'new.jwt.token';

        $this->mockTokenBlacklistService->expects($this->once())
            ->method('isTokenBlacklisted')
            ->with($oldToken)
            ->willReturn(false);

        $this->mockJwtService->expects($this->once())
            ->method('refreshToken')
            ->with($oldToken)
            ->willReturn($newToken);

        $this->mockJwtService->expects($this->once())
            ->method('getExpirationTime')
            ->willReturn(7200);

        $result = $this->authService->refreshToken($oldToken);

        $this->assertIsArray($result);
        $this->assertArrayHasKey('token', $result);
        $this->assertArrayHasKey('access_token', $result['token']);
        $this->assertArrayHasKey('token_type', $result['token']);
        $this->assertEquals('bearer', $result['token']['token_type']);
        $this->assertEquals($newToken, $result['token']['access_token']);
    }

    public function testRefreshTokenWithBlacklistedToken(): void
    {
        $blacklistedToken = 'blacklisted.token';

        $this->mockTokenBlacklistService->expects($this->once())
            ->method('isTokenBlacklisted')
            ->with($blacklistedToken)
            ->willReturn(true);

        $this->mockJwtService->expects($this->never())
            ->method('refreshToken');

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Token is blacklisted');

        $this->authService->refreshToken($blacklistedToken);
    }

    public function testRefreshTokenWithExpiredToken(): void
    {
        $expiredToken = 'expired.token';

        $this->mockTokenBlacklistService->expects($this->once())
            ->method('isTokenBlacklisted')
            ->with($expiredToken)
            ->willReturn(false);

        $this->mockJwtService->expects($this->once())
            ->method('refreshToken')
            ->with($expiredToken)
            ->willThrowException(new \Exception('Token refresh period expired'));

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Token refresh period expired');

        $this->authService->refreshToken($expiredToken);
    }
}
