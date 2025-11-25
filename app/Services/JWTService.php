<?php

namespace App\Services;

class JWTService
{
    private string $secret;
    private int $ttl;
    private int $refreshTtl;

    public function __construct()
    {
        $jwtSecret = $this->getEnvValue('JWT_SECRET');
        
        if (empty($jwtSecret)) {
            throw new \RuntimeException(
                "JWT_SECRET is not set in environment variables. " .
                "Please set JWT_SECRET in your .env file. " .
                "Use a secure random string of at least 32 bytes."
            );
        }
        
        if (strlen($jwtSecret) < 32) {
            throw new \RuntimeException(
                "JWT_SECRET must be at least 32 characters long for security. " .
                "Current length: " . strlen($jwtSecret)
            );
        }
        
        $this->secret = $jwtSecret;
        $this->ttl = (int)$this->getEnvValue('JWT_TTL', 120); // in minutes
        $this->refreshTtl = (int)$this->getEnvValue('JWT_REFRESH_TTL', 20160); // in minutes
    }
    
    private function getEnvValue(string $key, $default = null)
    {
        // Try multiple methods to get environment value
        $value = $_ENV[$key] ?? null;
        if ($value !== null) {
            return $value;
        }
        
        $value = $_SERVER[$key] ?? null;
        if ($value !== null) {
            return $value;
        }
        
        $value = getenv($key);
        if ($value !== false) {
            return $value;
        }
        
        return $default;
    }

    /**
     * Generate a new JWT token manually
     */
    public function generateToken(array $payload): string
    {
        $issuedAt = time();
        $expire = $issuedAt + ($this->ttl * 60); // Convert minutes to seconds

        $header = json_encode(['typ' => 'JWT', 'alg' => 'HS256']);
        $claims = json_encode([
            'iat' => $issuedAt,          // Issued at
            'exp' => $expire,            // Expiration time
            'data' => $payload           // User data
        ]);

        $base64Header = $this->base64UrlEncode($header);
        $base64Claims = $this->base64UrlEncode($claims);

        $signature = hash_hmac('sha256', $base64Header . '.' . $base64Claims, $this->secret, true);
        $base64Signature = $this->base64UrlEncode($signature);

        return $base64Header . '.' . $base64Claims . '.' . $base64Signature;
    }

    /**
     * Decode and validate token manually
     */
    public function decodeToken(string $token): ?array
    {
        $parts = explode('.', $token);
        if (count($parts) !== 3) {
            return null;
        }

        [$header, $claims, $signature] = $parts;

        // Verify signature
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

    /**
     * Refresh token
     */
    public function refreshToken(string $token): string
    {
        $payload = $this->decodeToken($token);
        
        if (!$payload) {
            throw new \Exception('Invalid token');
        }

        // Check if refresh is still valid
        $refreshExpire = $payload['iat'] + ($this->refreshTtl * 60);
        if (time() > $refreshExpire) {
            throw new \Exception('Token refresh period expired');
        }

        // Generate new token with same payload data
        return $this->generateToken($payload['data']);
    }

    /**
     * Get token expiration time
     */
    public function getExpirationTime(): int
    {
        return $this->ttl * 60; // Return in seconds
    }

    private function base64UrlEncode(string $data): string
    {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }

    private function base64UrlDecode(string $data): string
    {
        return base64_decode(str_pad(strtr($data, '-_', '+/'), strlen($data) % 4, '=', STR_PAD_RIGHT));
    }
}