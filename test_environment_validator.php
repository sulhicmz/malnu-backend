<?php

declare(strict_types=1);

putenv('APP_ENV=local');
putenv('APP_DEBUG=true');
putenv('APP_KEY=' . str_repeat('a', 32));
putenv('JWT_SECRET=' . str_repeat('b', 32));
putenv('ENV_VALIDATION_ENABLED=true');

require_once __DIR__ . '/app/Services/EnvironmentValidator.php';

use App\Services\EnvironmentValidator;

echo "Testing EnvironmentValidator...\n\n";

try {
    $validator = new EnvironmentValidator();
    $validator->validate();
    echo "✓ Valid configuration passed\n\n";
} catch (RuntimeException $e) {
    echo '✗ Failed: ' . $e->getMessage() . "\n\n";
}

echo "Test 1: Missing APP_KEY\n";
putenv('APP_KEY=');
try {
    $validator = new EnvironmentValidator();
    $validator->validate();
    echo "✗ Should have failed\n";
} catch (RuntimeException $e) {
    echo "✓ Correctly rejected missing APP_KEY\n";
    echo '  Error: ' . $e->getMessage() . "\n";
}
putenv('APP_KEY=' . str_repeat('a', 32));

echo "\nTest 2: Short APP_KEY in production\n";
putenv('APP_ENV=production');
putenv('APP_KEY=short');
try {
    $validator = new EnvironmentValidator();
    $validator->validate();
    echo "✗ Should have failed\n";
} catch (RuntimeException $e) {
    echo "✓ Correctly rejected short APP_KEY\n";
    echo '  Error: ' . $e->getMessage() . "\n";
}
putenv('APP_ENV=local');
putenv('APP_KEY=' . str_repeat('a', 32));

echo "\nTest 3: JWT_SECRET placeholder\n";
putenv('JWT_SECRET=change-me');
try {
    $validator = new EnvironmentValidator();
    $validator->validate();
    echo "✗ Should have failed\n";
} catch (RuntimeException $e) {
    echo "✓ Correctly rejected placeholder JWT_SECRET\n";
    echo '  Error: ' . $e->getMessage() . "\n";
}
putenv('JWT_SECRET=' . str_repeat('b', 32));

echo "\nTest 4: Invalid APP_ENV\n";
putenv('APP_ENV=invalid');
try {
    $validator = new EnvironmentValidator();
    $validator->validate();
    echo "✗ Should have failed\n";
} catch (RuntimeException $e) {
    echo "✓ Correctly rejected invalid APP_ENV\n";
    echo '  Error: ' . $e->getMessage() . "\n";
}
putenv('APP_ENV=local');

echo "\nTest 5: Invalid APP_DEBUG\n";
putenv('APP_DEBUG=not-a-boolean');
try {
    $validator = new EnvironmentValidator();
    $validator->validate();
    echo "✗ Should have failed\n";
} catch (RuntimeException $e) {
    echo "✓ Correctly rejected invalid APP_DEBUG\n";
    echo '  Error: ' . $e->getMessage() . "\n";
}
putenv('APP_DEBUG=true');

echo "\nTest 6: Invalid JWT_TTL\n";
putenv('JWT_TTL=-1');
try {
    $validator = new EnvironmentValidator();
    $validator->validate();
    echo "✗ Should have failed\n";
} catch (RuntimeException $e) {
    echo "✓ Correctly rejected invalid JWT_TTL\n";
    echo '  Error: ' . $e->getMessage() . "\n";
}
putenv('JWT_TTL=30');

echo "\nTest 7: Invalid email address\n";
putenv('MAIL_FROM_ADDRESS=not-an-email');
try {
    $validator = new EnvironmentValidator();
    $validator->validate();
    echo "✗ Should have failed\n";
} catch (RuntimeException $e) {
    echo "✓ Correctly rejected invalid email\n";
    echo '  Error: ' . $e->getMessage() . "\n";
}
putenv('MAIL_FROM_ADDRESS=test@example.com');

echo "\nTest 8: Invalid URL\n";
putenv('APP_URL=not-a-url');
try {
    $validator = new EnvironmentValidator();
    $validator->validate();
    echo "✗ Should have failed\n";
} catch (RuntimeException $e) {
    echo "✓ Correctly rejected invalid URL\n";
    echo '  Error: ' . $e->getMessage() . "\n";
}
putenv('APP_URL=https://example.com');

echo "\nTest 9: Validation disabled\n";
putenv('APP_KEY=');
putenv('JWT_SECRET=');
putenv('ENV_VALIDATION_ENABLED=false');
try {
    $validator = new EnvironmentValidator();
    $validator->validate();
    echo "✓ Correctly skipped validation when disabled\n";
} catch (RuntimeException $e) {
    echo "✗ Should have skipped validation\n";
    echo '  Error: ' . $e->getMessage() . "\n";
}
putenv('APP_KEY=' . str_repeat('a', 32));
putenv('JWT_SECRET=' . str_repeat('b', 32));
putenv('ENV_VALIDATION_ENABLED=true');

echo "\n=== All tests completed ===\n";
