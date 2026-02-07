# Testing Guide

## Overview

This document provides comprehensive guidelines for testing the Malnu Backend application using PHPUnit and the Hyperf Testing framework.

## Prerequisites

Before running tests, ensure you have:
- PHP 8.2 or higher installed
- Composer dependencies installed: `composer install`
- SQLite extension enabled for in-memory test database

## Running Tests

### Run All Tests

```bash
composer test
```

This runs all tests using the co-phpunit command (Hyperf's coroutine-aware PHPUnit).

### Run Specific Test Suites

```bash
# Run unit tests only
vendor/bin/co-phpunit tests/Unit

# Run feature tests only
vendor/bin/co-phpunit tests/Feature

# Run a specific test file
vendor/bin/co-phpunit tests/Feature/AuthServiceTest.php
```

### Run with Coverage Report

```bash
# Generate HTML coverage report
vendor/bin/phpunit --coverage-html build/coverage/html

# Generate Clover XML coverage report (for CI)
vendor/bin/phpunit --coverage-clover build/coverage/clover.xml

# Generate text coverage report
vendor/bin/phpunit --coverage-text
```

## Test Database Configuration

Tests use an in-memory SQLite database for speed and isolation:

```xml
<env name="DB_CONNECTION" value="sqlite_testing"/>
```

This is configured in `phpunit.xml.dist` and defined in `config/database.php`:

```php
'sqlite_testing' => [
    'driver' => 'sqlite',
    'database' => ':memory:',
    'prefix' => '',
    'foreign_key_constraints' => true,
],
```

Each test run uses a fresh database, and migrations are automatically run before tests.

## Test Structure

```
tests/
├── Feature/          # Feature tests for API endpoints, workflows
├── Unit/            # Unit tests for individual classes
├── TestCase.php      # Base test class
└── bootstrap.php     # Test bootstrap file
```

### Feature Tests

Feature tests test the application from the outside, simulating HTTP requests and responses:

```php
<?php

namespace Tests\Feature;

use Tests\TestCase;

class AuthServiceTest extends TestCase
{
    public function test_user_registration()
    {
        $response = $this->post('/api/auth/register', [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => 'SecurePass123!',
        ]);

        $response->assertStatus(201);
        $response->assertJson(['message' => 'User registered successfully']);
    }
}
```

### Unit Tests

Unit tests test individual classes and methods in isolation:

```php
<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Services\AuthService;

class AuthServiceTest extends TestCase
{
    private AuthService $authService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->authService = new AuthService();
    }

    public function test_password_validation()
    {
        $isValid = $this->authService->validatePassword('SecurePass123!');
        $this->assertTrue($isValid);
    }
}
```

## Writing Tests

### Test Naming Conventions

- Test class names should end with `Test`
- Test methods should start with `test_`
- Use descriptive names that explain what is being tested

```php
// ✅ Good
public function test_user_registration_with_valid_data()
public function test_invalid_email_returns_error()

// ❌ Bad
public function test1()
public function testRegister()
```

### Test Structure

Each test should follow this pattern:
1. Setup (arrange)
2. Execute action (act)
3. Assert results (assert)

```php
public function test_student_creation_requires_valid_data(): void
{
    // Arrange: Prepare test data
    $studentData = [
        'name' => 'Test Student',
        'nisn' => '1234567890',
        'email' => 'student@test.com',
    ];

    // Act: Make request
    $response = $this->post('/api/students', $studentData);

    // Assert: Verify result
    $response->assertStatus(201);
    $this->assertDatabaseHas('students', [
        'nisn' => '1234567890',
    ]);
}
```

## Available Assertions

### Response Assertions

```php
// Status assertions
$response->assertStatus(200);
$response->assertSuccessful();
$response->assertNotFound();
$response->assertForbidden();
$response->assertUnauthorized();
$response->assertStatus(422); // Validation error

// Content assertions
$response->assertJson(['key' => 'value']);
$response->assertJsonStructure(['data' => ['id', 'name']]);
$response->assertJsonCount(3);
$response->assertJsonPath('data.0.name', 'John Doe');

// Header assertions
$response->assertHeader('Content-Type', 'application/json');
```

### Database Assertions

```php
$this->assertDatabaseHas('users', ['email' => 'test@example.com']);
$this->assertDatabaseMissing('users', ['email' => 'deleted@example.com']);
$this->assertDatabaseCount('students', 5);
```

### General Assertions

```php
$this->assertTrue($condition);
$this->assertFalse($condition);
$this->assertEquals($expected, $actual);
$this->assertArrayHasKey('key', $array);
$this->assertStringContainsString('substring', $string);
$this->assertGreaterThan($expected, $actual);
$this->expectException(ValidationException::class);
```

## Testing Best Practices

### 1. Isolation

Each test should be independent and not depend on other tests:

```php
// ❌ Bad: Tests depend on execution order
public function test_create_student()
{
    $this->post('/api/students', [...]);
}

public function test_delete_student()
{
    // Fails if run before create_student
    $this->delete('/api/students/1');
}

// ✅ Good: Each test is self-contained
public function test_create_and_delete_student()
{
    $student = Student::factory()->create();
    $this->delete("/api/students/{$student->id}")
        ->assertStatus(204);
    $this->assertDatabaseMissing('students', ['id' => $student->id]);
}
```

### 2. Test One Thing

Each test should verify a single behavior:

```php
// ❌ Bad: Testing multiple things
public function test_student_crud()
{
    $this->post('/api/students', [...]);
    $this->get('/api/students/1');
    $this->put('/api/students/1', [...]);
    $this->delete('/api/students/1');
}

// ✅ Good: Separate tests for each behavior
public function test_can_create_student()
public function test_can_retrieve_student()
public function test_can_update_student()
public function test_can_delete_student()
```

### 3. Use Descriptive Assertions

```php
// ❌ Bad: Vague assertion
$this->assertTrue($result == 'expected');

// ✅ Good: Descriptive assertion message
$this->assertEquals('expected', $result, 'Result should match expected value');
```

### 4. Mock External Services

Don't test external services; mock them instead:

```php
// ❌ Bad: Tests external email service
public function test_sends_email()
{
    $this->post('/api/users', [...]);
    // Will fail if email service is down
}

// ✅ Good: Mock external service
use Mockery;
use App\Services\EmailService;

public function test_sends_email()
{
    $emailService = Mockery::mock(EmailService::class);
    $emailService->shouldReceive('send')
        ->once()
        ->withArgs(function ($to, $subject, $body) {
            return $to === 'user@example.com';
        });

    $this->app->instance(EmailService::class, $emailService);
    // ... rest of test
}
```

## CI/CD Integration

Tests run automatically on:
- Push to `main` or `develop` branches
- Pull requests targeting `main` or `develop`

The CI workflow includes:
1. PHPUnit test execution
2. Code coverage reporting
3. PHPStan static analysis
4. PHP CS Fixer code style checks
5. Composer security audit

View test results and coverage in the Actions tab on GitHub.

## Troubleshooting

### Tests Fail with "Class not found"

Run composer install:
```bash
composer install
```

### Database Connection Errors

Ensure SQLite extension is installed:
```bash
php -m | grep sqlite
```

### Co-PHPUnit Command Not Found

Reinstall dependencies:
```bash
composer install --no-interaction --prefer-dist
```

### Slow Tests

1. Use in-memory database (already configured)
2. Disable Xdebug for faster execution:
   ```bash
   vendor/bin/phpunit --no-coverage
   ```
3. Run only specific test suites during development

### Flaky Tests

If tests fail intermittently:
1. Check for race conditions or timing issues
2. Ensure proper cleanup in tearDown()
3. Add sleeps or waits if testing async operations
4. Mock external services that may be slow

## Coverage Goals

Aim for:
- **80%+ line coverage** for new code
- **100% coverage** for critical security logic (authentication, authorization)
- Test both success and failure paths
- Test edge cases and error conditions

## Resources

- [PHPUnit Documentation](https://phpunit.de/documentation.html)
- [Hyperf Testing Guide](https://hyperf.wiki/3.0/en/testing)
- [Testing Best Practices](https://phpunit.de/manual/current/en/appendixes.best-practices.html)
