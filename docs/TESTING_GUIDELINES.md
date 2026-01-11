# Testing Guidelines

This document provides comprehensive guidelines for testing the Malnu Backend school management system.

## Table of Contents

- [Test Structure](#test-structure)
- [Running Tests](#running-tests)
- [Writing Tests](#writing-tests)
- [Test Categories](#test-categories)
- [Code Coverage](#code-coverage)
- [Best Practices](#best-practices)

## Test Structure

```
tests/
├── Unit/              # Unit tests for individual classes
│   ├── ModelTest.php
│   ├── ServiceTest.php
│   └── ...
├── Feature/            # Feature/integration tests
│   ├── AuthServiceTest.php
│   ├── SchoolManagementApiTest.php
│   └── ...
├── TestCase.php        # Base test class
└── bootstrap.php       # Test bootstrap
```

## Running Tests

### Run All Tests

```bash
vendor/bin/phpunit
```

### Run Specific Test Suite

```bash
# Unit tests only
vendor/bin/phpunit tests/Unit

# Feature tests only
vendor/bin/phpunit tests/Feature

# Specific test class
vendor/bin/phpunit tests/Feature/AuthServiceTest.php

# Specific test method
vendor/bin/phpunit --filter test_user_registration_with_database_persistence
```

### Run with Code Coverage

```bash
vendor/bin/phpunit --coverage-html build/coverage/html
```

Coverage reports will be generated in:
- `build/coverage/html/` - HTML format
- `build/coverage/clover.xml` - Clover XML format
- `build/coverage/coverage.txt` - Text format

## Writing Tests

### Test Case Template

```php
<?php

declare(strict_types=1);

namespace Tests\Unit;

use Tests\TestCase;

class ExampleTest extends TestCase
{
    public function test_something_expected_behavior(): void
    {
        // Arrange
        $input = 'test input';
        
        // Act
        $result = someFunction($input);
        
        // Assert
        $this->assertEquals('expected', $result);
    }
}
```

### API Feature Test Template

```php
<?php

declare(strict_types=1);

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use Tymon\JWTAuth\Facades\JWTAuth;

class ApiEndpointTest extends TestCase
{
    protected $user;
    protected $token;
    
    protected function setUp(): void
    {
        parent::setUp();
        
        $this->user = User::factory()->create();
        $this->token = JWTAuth::fromUser($this->user);
    }
    
    public function test_endpoint_returns_success(): void
    {
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->getJson('/api/endpoint');
        
        $response->assertStatus(200)
                 ->assertJson([
                     'success' => true
                 ]);
    }
}
```

## Test Categories

### Unit Tests

Unit tests test individual classes and methods in isolation.

**When to use:**
- Testing model methods
- Testing service business logic
- Testing utility functions
- Testing validation rules

**Example:**
```php
public function testUserEmailValidation(): void
{
    $user = new User(['email' => 'invalid-email']);
    
    $this->assertFalse($user->isValid());
}
```

### Feature/Integration Tests

Feature tests test application behavior through HTTP requests and database interactions.

**When to use:**
- Testing API endpoints
- Testing complete workflows
- Testing database interactions
- Testing middleware

**Example:**
```php
public function testUserCanRegister(): void
{
    $userData = [
        'name' => 'Test User',
        'email' => 'test@example.com',
        'password' => 'SecureP@ssw0rd',
    ];
    
    $response = $this->postJson('/api/auth/register', $userData);
    
    $response->assertStatus(200);
}
```

### Security Tests

Security tests verify that security vulnerabilities are prevented.

**When to use:**
- Testing SQL injection prevention
- Testing XSS protection
- Testing CSRF protection
- Testing authentication
- Testing authorization

**Example:**
```php
public function testSqlInjectionPrevented(): void
{
    $maliciousInput = "'; DROP TABLE users; --";
    
    $response = $this->withHeaders([
        'Authorization' => 'Bearer ' . $this->token,
    ])->postJson('/api/endpoint', ['search' => $maliciousInput]);
    
    $response->assertStatus(200); // Should not error, should sanitize input
}
```

## Code Coverage

### Generating Coverage Reports

```bash
vendor/bin/phpunit --coverage-html build/coverage/html
```

### Viewing Coverage

Open `build/coverage/html/index.html` in a web browser to view detailed coverage information.

### Coverage Goals

- **Minimum**: 80% overall coverage
- **Target**: 90% coverage for critical code
- **Critical paths**: 100% coverage for authentication, authorization, and data validation

### Coverage Exclusions

Certain files/types can be excluded from coverage calculations:

- Third-party library code
- Generated configuration files
- Migration files (run once and not part of application logic)
- View files (for applications with views)

## Best Practices

### 1. Test Naming

Test method names should clearly describe what is being tested:

```php
// Good
public function test_user_registration_with_valid_data_returns_success(): void
{
}

// Avoid
public function testRegistration(): void
{
}
```

### 2. One Assertion Per Test

Keep tests focused on a single behavior:

```php
// Good
public function testUserEmailIsRequired(): void
{
    $user = new User(['name' => 'Test']);
    $this->assertFalse($user->isValid());
}

public function testUserNameIsRequired(): void
{
    $user = new User(['email' => 'test@example.com']);
    $this->assertFalse($user->isValid());
}

// Avoid
public function testUserValidation(): void
{
    $user = new User(['name' => 'Test']);
    $this->assertFalse($user->isValid());
    
    $user2 = new User(['email' => 'test@example.com']);
    $this->assertFalse($user2->isValid());
}
```

### 3. Arrange-Act-Assert Pattern

Organize tests into clear sections:

```php
public function testExample(): void
{
    // Arrange
    $testData = ['key' => 'value'];
    
    // Act
    $result = functionUnderTest($testData);
    
    // Assert
    $this->assertEquals('expected', $result);
}
```

### 4. Use Meaningful Test Data

```php
// Good
public function testUserAgeValidation(): void
{
    $user = new User([
        'birth_date' => '2010-01-01', // 14 years old
    ]);
    $this->assertTrue($user->canRegister());
}

// Avoid
public function testUserAgeValidation(): void
{
    $user = new User([
        'birth_date' => '2000-01-01', // Magic number, unclear meaning
    ]);
    $this->assertTrue($user->canRegister());
}
```

### 5. Test Edge Cases

```php
public function testPasswordValidation(): void
{
    // Minimum length
    $this->assertFalse(validatePassword('1234567'));
    
    // No uppercase
    $this->assertFalse(validatePassword('password123'));
    
    // No lowercase
    $this->assertFalse(validatePassword('PASSWORD123'));
    
    // No number
    $this->assertFalse(validatePassword('Password'));
    
    // Valid password
    $this->assertTrue(validatePassword('Password123'));
}
```

### 6. Isolate Tests

Each test should be independent and not depend on other tests:

```php
public function testCreateStudent(): void
{
    $student = new Student(['name' => 'Test Student']);
    $student->save();
    
    $this->assertDatabaseHas('students', ['name' => 'Test Student']);
}

public function testUpdateStudent(): void
{
    // Don't assume student from previous test exists
    $student = Student::factory()->create();
    
    $student->name = 'Updated Student';
    $student->save();
    
    $this->assertDatabaseHas('students', ['name' => 'Updated Student']);
}
```

### 7. Use Factories for Test Data

```php
public function testStudentRetrieval(): void
{
    $student = Student::factory()->create([
        'name' => 'Factory Student',
    ]);
    
    $retrieved = Student::find($student->id);
    
    $this->assertEquals('Factory Student', $retrieved->name);
}
```

### 8. Mock External Dependencies

```php
public function testEmailNotification(): void
{
    // Mock email service to avoid sending real emails during tests
    $mockEmailService = $this->createMock(EmailService::class);
    $mockEmailService->shouldReceive('send')
        ->once()
        ->andReturn(true);
    
    $userService = new UserService($mockEmailService);
    $result = $userService->sendWelcomeEmail('test@example.com');
    
    $this->assertTrue($result);
}
```

### 9. Clean Up Test Data

Use transactions or database refresh to clean up:

```php
public function testCreateUser(): void
{
    $user = User::factory()->create();
    
    // Test logic...
    
    // Database is automatically cleaned up between tests
}
```

### 10. Test Both Success and Failure Cases

```php
public function testLoginWithValidCredentials(): void
{
    $response = $this->postJson('/api/auth/login', [
        'email' => 'test@example.com',
        'password' => 'password123',
    ]);
    
    $response->assertStatus(200);
}

public function testLoginWithInvalidCredentials(): void
{
    $response = $this->postJson('/api/auth/login', [
        'email' => 'test@example.com',
        'password' => 'wrongpassword',
    ]);
    
    $response->assertStatus(401);
}
```

## Test Maintenance

### Keep Tests Updated

- When adding new features, add tests
- When fixing bugs, add regression tests
- When modifying existing code, update related tests
- Remove obsolete tests

### Review Tests Regularly

- Remove duplicate tests
- Consolidate similar tests
- Refactor complex tests
- Update test documentation

### Test Performance

- Tests should run quickly (aim for < 10 minutes total)
- Use in-memory database when possible
- Minimize external dependencies
- Avoid sleeping in tests

## CI/CD Integration

### Automated Testing

Tests run automatically on:
- Every pull request
- Every push to main branch
- Scheduled runs

### Test Reports

- Coverage reports are generated on every run
- Test results are published as artifacts
- Failed tests block PRs from merging

## Troubleshooting

### Tests Failing Locally But Passing in CI

1. Check environment variables
2. Ensure database is properly configured
3. Clear cache: `php artisan cache:clear`
4. Refresh database: `php artisan migrate:fresh --seed`

### Slow Tests

1. Use `RefreshDatabase` trait for in-memory database
2. Reduce database transactions
3. Use factories instead of manual creation
4. Run tests in parallel if supported

### Flaky Tests

1. Check for race conditions
2. Ensure test isolation
3. Add explicit waits for async operations
4. Use explicit assertions instead of relying on timing

## Resources

- [PHPUnit Documentation](https://phpunit.de/documentation.html)
- [Hyperf Testing Guide](https://hyperf.wiki/3.0/en/testing/)
- [Testing Best Practices](https://phpunit.de/manual/current/en/appendixes.assertions.html)

## Contributing

When contributing to the test suite:

1. Write tests for all new features
2. Ensure new code has test coverage > 80%
3. Run full test suite before committing
4. Follow naming conventions
5. Add documentation for complex test scenarios
