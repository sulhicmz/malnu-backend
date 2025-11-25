<?php
// Simple test script to verify JWT validation

// Mock the $_ENV for testing
$_ENV['JWT_SECRET'] = '';
$_ENV['APP_ENV'] = 'production';

try {
    require_once 'vendor/autoload.php';
    
    // Try to create JWTService with empty secret in production
    $jwtService = new App\Services\JWTService();
    echo "ERROR: JWTService was created without a secret in production mode!\n";
} catch (Exception $e) {
    echo "SUCCESS: Validation works correctly. Error: " . $e->getMessage() . "\n";
}

// Test with valid secret
$_ENV['JWT_SECRET'] = 'fc13c20fb40f1eb359bd83dfadd4efa1d8eb028db811cb7d980ebf0223da4e55';
$_ENV['APP_ENV'] = 'production';

try {
    $jwtService = new App\Services\JWTService();
    echo "SUCCESS: JWTService created with valid secret\n";
    
    // Test token generation
    $payload = ['user_id' => 1, 'email' => 'test@example.com'];
    $token = $jwtService->generateToken($payload);
    echo "SUCCESS: Token generated successfully\n";
    
    $decoded = $jwtService->decodeToken($token);
    if ($decoded && isset($decoded['data']) && $decoded['data']['user_id'] === 1) {
        echo "SUCCESS: Token validation and decoding works\n";
    } else {
        echo "ERROR: Token validation failed\n";
    }
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}