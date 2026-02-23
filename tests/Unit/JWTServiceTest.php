<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Services\JWTService;
use Tests\TestCase;

/**
 * @internal
 * @covers \App\Services\JWTService
 */
class JWTServiceTest extends TestCase
{
    private JWTService $jwtService;

    protected function setUp(): void
    {
        parent::setUp();

        $this->jwtService = new JWTService();
    }

    public function testGenerateTokenWithValidPayload(): void
    {
        $payload = [
            'id' => '123',
            'email' => 'test@example.com',
        ];

        $token = $this->jwtService->generateToken($payload);

        $this->assertIsString($token);
        $this->assertNotEmpty($token);

        $parts = explode('.', $token);
        $this->assertCount(3, $parts);
    }

    public function testGenerateTokenWithIntegerPayload(): void
    {
        $payload = ['user_id' => 456];

        $token = $this->jwtService->generateToken($payload);

        $this->assertIsString($token);
        $this->assertNotEmpty($token);
    }

    public function testGenerateTokenWithStringPayload(): void
    {
        $payload = ['role' => 'admin'];

        $token = $this->jwtService->generateToken($payload);

        $this->assertIsString($token);
        $this->assertNotEmpty($token);
    }

    public function testDecodeTokenWithValidSignature(): void
    {
        $payload = [
            'id' => '789',
            'email' => 'valid@example.com',
        ];

        $token = $this->jwtService->generateToken($payload);
        $decoded = $this->jwtService->decodeToken($token);

        $this->assertIsArray($decoded);
        $this->assertArrayHasKey('iat', $decoded);
        $this->assertArrayHasKey('exp', $decoded);
        $this->assertArrayHasKey('data', $decoded);
        $this->assertEquals($payload, $decoded['data']);
    }

    public function testDecodeTokenWithInvalidSignature(): void
    {
        $payload = ['id' => '999'];
        $token = $this->jwtService->generateToken($payload);

        // Corrupt the signature by changing last character
        $tamperedToken = substr($token, 0, -1) . 'X';

        $decoded = $this->jwtService->decodeToken($tamperedToken);

        $this->assertNull($decoded);
    }

    public function testDecodeTokenWithMalformedStructure(): void
    {
        $malformedTokens = [
            '',                           // Empty string
            'invalid',                     // No dots
            'one.two',                    // Only two parts
            'one.two.three.four',       // Too many parts
            'one..three',                 // Empty part
        ];

        foreach ($malformedTokens as $malformedToken) {
            $decoded = $this->jwtService->decodeToken($malformedToken);
            $this->assertNull($decoded);
        }
    }

    public function testDecodeTokenWithExpiredToken(): void
    {
        $payload = ['id' => 'expired_user'];
        $token = $this->jwtService->generateToken($payload);

        // Manually expire the token by modifying the exp claim
        $parts = explode('.', $token);
        $claims = json_decode(base64_decode(strtr($parts[1], '-_', '+/')), true);
        $claims['exp'] = time() - 3600; // Expired 1 hour ago
        $parts[1] = rtrim(strtr(base64_encode(json_encode($claims)), '+/', '-_'), '=');
        $expiredToken = implode('.', $parts);

        $decoded = $this->jwtService->decodeToken($expiredToken);

        $this->assertNull($decoded);
    }

    public function testRefreshTokenWithValidToken(): void
    {
        $originalPayload = ['id' => 'refresh_test', 'email' => 'refresh@example.com'];
        $oldToken = $this->jwtService->generateToken($originalPayload);

        $newToken = $this->jwtService->refreshToken($oldToken);

        $this->assertIsString($newToken);
        $this->assertNotEmpty($newToken);
        $this->assertNotEquals($oldToken, $newToken);

        $oldDecoded = $this->jwtService->decodeToken($oldToken);
        $newDecoded = $this->jwtService->decodeToken($newToken);

        $this->assertEquals($originalPayload, $oldDecoded['data']);
        $this->assertEquals($originalPayload, $newDecoded['data']);
    }

    public function testRefreshTokenWithExpiredRefreshPeriod(): void
    {
        $payload = ['id' => 'expired_refresh'];

        // Create a token with very old iat timestamp
        $header = json_encode(['typ' => 'JWT', 'alg' => 'HS256']);
        $claims = json_encode([
            'iat' => time() - (20160 * 60 + 1), // Expired refresh period (20160 minutes + 1 second)
            'exp' => time() - 3600,
            'data' => $payload,
        ]);
        $base64Header = rtrim(strtr(base64_encode($header), '+/', '-_'), '=');
        $base64Claims = rtrim(strtr(base64_encode($claims), '+/', '-_'), '=');
        $signature = hash_hmac('sha256', $base64Header . '.' . $base64Claims, config('jwt.secret'), true);
        $base64Signature = rtrim(strtr(base64_encode($signature), '+/', '-_'), '=');
        $expiredToken = $base64Header . '.' . $base64Claims . '.' . $base64Signature;

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Token refresh period expired');

        $this->jwtService->refreshToken($expiredToken);
    }

    public function testRefreshTokenWithInvalidToken(): void
    {
        $invalidTokens = [
            'invalid.token.format',
            'malformed.token.here',
        ];

        foreach ($invalidTokens as $invalidToken) {
            $this->expectException(\Exception::class);
            $this->expectExceptionMessage('Invalid token');

            $this->jwtService->refreshToken($invalidToken);
        }
    }

    public function testGetExpirationTime(): void
    {
        $expirationTime = $this->jwtService->getExpirationTime();

        $this->assertIsInt($expirationTime);
        $this->assertGreaterThan(0, $expirationTime);
        // Default TTL is 120 minutes = 7200 seconds
        $this->assertEquals(7200, $expirationTime);
    }

    public function testBase64UrlEncode(): void
    {
        $data = '{"test":"data"}';

        $encoded = rtrim(strtr(base64_encode($data), '+/', '-_'), '=');

        $this->assertStringNotContainsString('+', $encoded);
        $this->assertStringNotContainsString('/', $encoded);
        $this->assertStringNotContainsString('=', $encoded);
    }

    public function testBase64UrlDecode(): void
    {
        $data = '{"test":"data"}';
        $encoded = rtrim(strtr(base64_encode($data), '+/', '-_'), '=');

        $decoded = base64_decode(str_pad(strtr($encoded, '-_', '+/'), strlen($encoded) % 4, '=', STR_PAD_RIGHT));

        $this->assertEquals($data, $decoded);
    }

    public function testGenerateTokenWithComplexPayload(): void
    {
        $complexPayload = [
            'id' => 'complex_id',
            'email' => 'complex@example.com',
            'role' => 'administrator',
            'permissions' => ['read', 'write', 'delete'],
            'metadata' => [
                'department' => 'engineering',
                'location' => 'remote',
            ],
        ];

        $token = $this->jwtService->generateToken($complexPayload);

        $this->assertIsString($token);
        $this->assertNotEmpty($token);

        $decoded = $this->jwtService->decodeToken($token);
        $this->assertEquals($complexPayload, $decoded['data']);
    }

    public function testDecodeTokenCaseInsensitiveSignature(): void
    {
        $payload = ['id' => 'case_test'];
        $token = $this->jwtService->generateToken($payload);

        // Tokens should be case-sensitive for signatures
        $decoded1 = $this->jwtService->decodeToken($token);
        $decoded2 = $this->jwtService->decodeToken(strtolower($token));

        $this->assertIsArray($decoded1);
        $this->assertNull($decoded2); // Lowercase should fail
    }
}
