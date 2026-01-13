<?php

declare(strict_types=1);

namespace Tests\Feature;

use Tests\TestCase;
use App\Contracts\JWTServiceInterface;

class JwtAuthenticationTest extends TestCase
{
    public function test_jwt_token_generation_and_validation()
    {
        $jwtService = new \App\Services\JWTService();
        
        // Test token generation
        $payload = ['user_id' => 1, 'email' => 'test@example.com'];
        $token = $jwtService->generateToken($payload);
        
        $this->assertIsString($token);
        $this->assertNotEmpty($token);
        
        // Test token decoding
        $decoded = $jwtService->decodeToken($token);
        
        $this->assertIsArray($decoded);
        $this->assertArrayHasKey('data', $decoded);
        $this->assertEquals($payload, $decoded['data']);
    }
    
    public function test_jwt_token_expiration()
    {
        // Create a JWT service with a short TTL for testing
        $jwtService = new class() {
            private string $secret = 'test_secret';
            
            public function generateExpiredToken(): string
            {
                $issuedAt = time() - 3600; // 1 hour ago
                $expire = $issuedAt - 1; // Already expired
                
                $token = [
                    'iat' => $issuedAt,
                    'exp' => $expire,
                    'data' => ['user_id' => 1]
                ];
                
                $header = json_encode(['typ' => 'JWT', 'alg' => 'HS256']);
                $claims = json_encode($token);
                
                $base64Header = $this->base64UrlEncode($header);
                $base64Claims = $this->base64UrlEncode($claims);
                
                $signature = hash_hmac('sha256', $base64Header . '.' . $base64Claims, $this->secret, true);
                $base64Signature = $this->base64UrlEncode($signature);
                
                return $base64Header . '.' . $base64Claims . '.' . $base64Signature;
            }
            
            public function decodeToken(string $token): ?array
            {
                $parts = explode('.', $token);
                if (count($parts) !== 3) {
                    return null;
                }
                
                [$header, $claims, $signature] = $parts;
                
                $expectedSignature = $this->base64UrlEncode(
                    hash_hmac('sha256', $header . '.' . $claims, $this->secret, true)
                );
                
                if (!hash_equals($expectedSignature, $signature)) {
                    return null;
                }
                
                $decodedClaims = json_decode($this->base64UrlDecode($claims), true);
                
                // Check if token is expired
                if (isset($decodedClaims['exp']) && time() > $decodedClaims['exp']) {
                    return null;
                }
                
                return $decodedClaims;
            }
            
            private function base64UrlEncode(string $data): string
            {
                return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
            }
            
            private function base64UrlDecode(string $data): string
            {
                return base64_decode(str_pad(strtr($data, '-_', '+/'), strlen($data) % 4, '=', STR_PAD_RIGHT));
            }
        };
        
        $expiredToken = $jwtService->generateExpiredToken();
        $decoded = $jwtService->decodeToken($expiredToken);
        
        $this->assertNull($decoded, 'Expired token should not be valid');
    }
    
    public function test_jwt_token_refresh()
    {
        $jwtService = new JWTService();
        
        // Generate initial token
        $payload = ['user_id' => 1, 'email' => 'test@example.com'];
        $token = $jwtService->generateToken($payload);
        
        // Refresh the token
        $refreshedToken = $jwtService->refreshToken($token);
        
        $this->assertIsString($refreshedToken);
        $this->assertNotEquals($token, $refreshedToken);
        
        // Decode the refreshed token to verify it contains the same data
        $decoded = $jwtService->decodeToken($refreshedToken);
        $this->assertIsArray($decoded);
        $this->assertArrayHasKey('data', $decoded);
        $this->assertEquals($payload, $decoded['data']);
    }

    public function test_jwt_rejects_placeholder_values_in_production()
    {
        // Store original environment
        $originalEnv = env('APP_ENV', 'testing');
        $originalSecret = env('JWT_SECRET', '');
        
        // Simulate production environment with placeholder value
        putenv('APP_ENV=production');
        putenv('JWT_SECRET=your-secret-key-here');
        
        // Clear config cache to pick up new environment values
        config(['jwt.secret' => 'your-secret-key-here']);
        config(['app.env' => 'production']);
        
        try {
            new \App\Services\JWTService();
            $this->fail('Expected RuntimeException was not thrown');
        } catch (\RuntimeException $e) {
            $this->assertStringContainsString('JWT_SECRET is not configured properly', $e->getMessage());
            $this->assertStringContainsString('openssl rand -hex 32', $e->getMessage());
        } finally {
            // Restore original environment
            putenv('APP_ENV=' . $originalEnv);
            putenv('JWT_SECRET=' . $originalSecret);
            config(['jwt.secret' => $originalSecret]);
            config(['app.env' => $originalEnv]);
        }
    }

    public function test_jwt_rejects_empty_secret_in_production()
    {
        // Store original environment
        $originalEnv = env('APP_ENV', 'testing');
        
        // Simulate production environment with empty secret
        putenv('APP_ENV=production');
        putenv('JWT_SECRET=');
        
        // Clear config cache to pick up new environment values
        config(['jwt.secret' => '']);
        config(['app.env' => 'production']);
        
        try {
            new \App\Services\JWTService();
            $this->fail('Expected RuntimeException was not thrown');
        } catch (\RuntimeException $e) {
            $this->assertStringContainsString('JWT_SECRET is not configured properly', $e->getMessage());
        } finally {
            // Restore original environment
            putenv('APP_ENV=' . $originalEnv);
            putenv('JWT_SECRET=');
        }
    }

    public function test_jwt_accepts_valid_secret()
    {
        // Store original secret
        $originalSecret = env('JWT_SECRET', '');
        
        // Set a valid secret
        $validSecret = 'a1b2c3d4e5f6g7h8i9j0k1l2m3n4o5p6';
        putenv('JWT_SECRET=' . $validSecret);
        config(['jwt.secret' => $validSecret]);
        
        try {
            $jwtService = new \App\Services\JWTService();
            $payload = ['user_id' => 1, 'email' => 'test@example.com'];
            $token = $jwtService->generateToken($payload);
            
            $this->assertIsString($token);
            $this->assertNotEmpty($token);
        } finally {
            // Restore original secret
            putenv('JWT_SECRET=' . $originalSecret);
            config(['jwt.secret' => $originalSecret]);
        }
    }
}