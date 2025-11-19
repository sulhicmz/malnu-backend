# Test Suite Documentation

This directory contains the comprehensive test suite for the HyperVel application.

## Test Structure

```
tests/
├── Unit/
│   ├── Models/           # Model-specific unit tests
│   ├── Services/         # Service layer tests
│   └── Utilities/        # Utility function tests
├── Feature/
│   ├── API/             # API endpoint tests
│   ├── Auth/            # Authentication tests
│   ├── CRUD/            # CRUD operation tests
│   └── Controllers/     # Controller-specific tests
├── Integration/
│   └── Database/        # Database integration tests
└── TestCase.php         # Base test case configuration
```

## Running Tests

To run the entire test suite:
```bash
composer test
```

To run specific test suites:
```bash
# Unit tests only
composer test -- --testsuite=Unit

# Feature tests only
composer test -- --testsuite=Feature

# With coverage report
composer test -- --coverage
```

## Test Coverage

The test suite aims for:
- 80%+ code coverage
- All critical business logic tested
- API endpoints fully validated
- Database operations verified

## Test Conventions

1. All tests extend the base `Tests\TestCase`
2. Use factory methods for creating test data
3. Follow AAA pattern (Arrange, Act, Assert)
4. Use descriptive test method names
5. Test both positive and negative scenarios