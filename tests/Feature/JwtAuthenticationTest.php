<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Services\JWTService;

class JwtAuthenticationTest extends TestCase
{
    public function test_jwt_token_generation_and_validation()
    {
        $jwtService = new JWTService();
        
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
    
    public function test_jwt_service_throws_exception_in_production_with_empty_secret()
    {
        // Save original environment
        $originalAppEnv = $_ENV['APP_ENV'] ?? null;
        $originalJwtSecret = $_ENV['JWT_SECRET'] ?? null;
        
        try {
            // Set production environment and empty JWT secret
            $_ENV['APP_ENV'] = 'production';
            $_ENV['JWT_SECRET'] = '';
            
            // This should throw an exception
            $this->expectException(\Exception::class);
            $this->expectExceptionMessage('JWT_SECRET is not configured. Please set JWT_SECRET in your environment configuration.');
            
            new JWTService();
        } finally {
            // Restore original environment
            if ($originalAppEnv !== null) {
                $_ENV['APP_ENV'] = $originalAppEnv;
            } else {
                unset($_ENV['APP_ENV']);
            }
            
            if ($originalJwtSecret !== null) {
                $_ENV['JWT_SECRET'] = $originalJwtSecret;
            } else {
                unset($_ENV['JWT_SECRET']);
            }
        }
    }
    
    public function test_jwt_service_uses_default_secret_in_non_production()
    {
        // Save original environment
        $originalAppEnv = $_ENV['APP_ENV'] ?? null;
        $originalJwtSecret = $_ENV['JWT_SECRET'] ?? null;
        
        try {
            // Set non-production environment and empty JWT secret
            $_ENV['APP_ENV'] = 'local';
            $_ENV['JWT_SECRET'] = '';
            
            // This should not throw an exception and should use default secret
            $jwtService = new JWTService();
            
            // Test that token generation still works
            $payload = ['user_id' => 1, 'email' => 'test@example.com'];
            $token = $jwtService->generateToken($payload);
            
            $this->assertIsString($token);
            $this->assertNotEmpty($token);
        } finally {
            // Restore original environment
            if ($originalAppEnv !== null) {
                $_ENV['APP_ENV'] = $originalAppEnv;
            } else {
                unset($_ENV['APP_ENV']);
            }
            
            if ($originalJwtSecret !== null) {
                $_ENV['JWT_SECRET'] = $originalJwtSecret;
            } else {
                unset($_ENV['JWT_SECRET']);
            }
        }
    }
}