<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Services\JWTService;
use Exception;
use Tests\TestCase;

/**
 * @internal
 * @coversNothing
 */
class JWTServiceTest extends TestCase
{
    private JWTService $service;

    protected function setUp(): void
    {
        parent::setUp();
        putenv('APP_ENV=testing');
        putenv('JWT_SECRET=test_secret_key_for_testing_purposes_only');
        $this->service = new JWTService();
    }

    public function testGenerateTokenReturnsValidJwt()
    {
        $payload = ['user_id' => 123, 'email' => 'test@example.com'];
        $token = $this->service->generateToken($payload);

        $this->assertIsString($token);
        $this->assertNotEmpty($token);
        $this->assertMatchesRegularExpression('/^[a-zA-Z0-9_-]+\.[a-zA-Z0-9_-]+\.[a-zA-Z0-9_-]+$/', $token);
    }

    public function testGenerateTokenStructureHasThreeParts()
    {
        $payload = ['user_id' => 123];
        $token = $this->service->generateToken($payload);

        $parts = explode('.', $token);
        $this->assertCount(3, $parts);
    }

    public function testDecodeValidTokenReturnsPayload()
    {
        $payload = ['user_id' => 123, 'email' => 'test@example.com'];
        $token = $this->service->generateToken($payload);

        $decoded = $this->service->decodeToken($token);

        $this->assertIsArray($decoded);
        $this->assertArrayHasKey('iat', $decoded);
        $this->assertArrayHasKey('exp', $decoded);
        $this->assertArrayHasKey('data', $decoded);
        $this->assertEquals($payload, $decoded['data']);
    }

    public function testDecodeMalformedTokenReturnsNull()
    {
        $malformedToken = 'invalid.token';

        $decoded = $this->service->decodeToken($malformedToken);

        $this->assertNull($decoded);
    }

    public function testDecodeEmptyTokenReturnsNull()
    {
        $decoded = $this->service->decodeToken('');

        $this->assertNull($decoded);
    }

    public function testDecodeTokenWithWrongSignatureReturnsNull()
    {
        $payload = ['user_id' => 123];
        $token = $this->service->generateToken($payload);

        $tamperedToken = $token . 'tampered';

        $decoded = $this->service->decodeToken($tamperedToken);

        $this->assertNull($decoded);
    }

    public function testRefreshTokenGeneratesNewToken()
    {
        $payload = ['user_id' => 123];
        $originalToken = $this->service->generateToken($payload);

        $refreshedToken = $this->service->refreshToken($originalToken);

        $this->assertIsString($refreshedToken);
        $this->assertNotEmpty($refreshedToken);
        $this->assertNotEquals($originalToken, $refreshedToken);
    }

    public function testRefreshTokenPreservesPayload()
    {
        $payload = ['user_id' => 123, 'email' => 'test@example.com'];
        $originalToken = $this->service->generateToken($payload);

        $refreshedToken = $this->service->refreshToken($originalToken);
        $decoded = $this->service->decodeToken($refreshedToken);

        $this->assertEquals($payload, $decoded['data']);
    }

    public function testRefreshExpiredTokenThrowsException()
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Token refresh period expired');

        $payload = ['user_id' => 123];
        $expiredToken = $this->createExpiredToken($payload);

        $this->service->refreshToken($expiredToken);
    }

    public function testRefreshInvalidTokenThrowsException()
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Invalid token');

        $invalidToken = 'invalid.token.string';

        $this->service->refreshToken($invalidToken);
    }

    public function testGetExpirationTimeReturnsValueInSeconds()
    {
        $expirationTime = $this->service->getExpirationTime();

        $this->assertIsInt($expirationTime);
        $this->assertGreaterThan(0, $expirationTime);
    }

    public function testTokenWithEmptyPayloadGeneratesSuccessfully()
    {
        $payload = [];
        $token = $this->service->generateToken($payload);

        $decoded = $this->service->decodeToken($token);

        $this->assertIsArray($decoded['data']);
        $this->assertEmpty($decoded['data']);
    }

    public function testTokenWithComplexPayload()
    {
        $payload = [
            'user_id' => 123,
            'email' => 'test@example.com',
            'name' => 'John Doe',
            'roles' => ['admin', 'user'],
            'nested' => [
                'key' => 'value',
            ],
        ];
        $token = $this->service->generateToken($payload);

        $decoded = $this->service->decodeToken($token);

        $this->assertEquals($payload, $decoded['data']);
    }

    public function testTokenExpirationTimeIsFuture()
    {
        $payload = ['user_id' => 123];
        $token = $this->service->generateToken($payload);
        $decoded = $this->service->decodeToken($token);

        $this->assertGreaterThan(time(), $decoded['exp']);
    }

    public function testTokenIssuedAtIsCurrentTime()
    {
        $payload = ['user_id' => 123];
        $token = $this->service->generateToken($payload);
        $decoded = $this->service->decodeToken($token);

        $timeDiff = abs(time() - $decoded['iat']);
        $this->assertLessThan(5, $timeDiff);
    }

    private function createExpiredToken(array $payload): string
    {
        $secret = 'test_secret_key_for_testing_purposes_only';
        $issuedAt = time() - (20161 * 60); // More than refresh TTL
        $expire = $issuedAt - 1;

        $header = json_encode(['typ' => 'JWT', 'alg' => 'HS256']);
        $claims = json_encode([
            'iat' => $issuedAt,
            'exp' => $expire,
            'data' => $payload,
        ]);

        $base64Header = $this->base64UrlEncode($header);
        $base64Claims = $this->base64UrlEncode($claims);

        $signature = hash_hmac('sha256', $base64Header . '.' . $base64Claims, $secret, true);
        $base64Signature = $this->base64UrlEncode($signature);

        return $base64Header . '.' . $base64Claims . '.' . $base64Signature;
    }

    private function base64UrlEncode(string $data): string
    {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }
}
