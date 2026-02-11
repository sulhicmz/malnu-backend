# Test Suite Documentation

## Overview
This test suite provides comprehensive coverage for the Hyperf-based school management system. The tests are organized into different categories to ensure proper functionality across all modules.

## Test Categories

### 1. Model Relationship Tests (`Feature/ModelRelationshipTest.php`)
- Tests all model relationships (belongsTo, hasMany, hasOne, etc.)
- Validates foreign key constraints and relationship methods
- Covers all major models in the system

### 2. Model Factory Tests (`Feature/ModelFactoryTest.php`)
- Tests model instantiation and basic properties
- Validates model structure and key attributes
- Ensures all models can be properly created

### 3. API Endpoint Tests (`Feature/ApiEndpointTest.php`)
- Tests core API endpoints for different modules
- Validates API response formats and status codes
- Covers authentication and authorization flows

### 4. Business Logic Tests (`Feature/BusinessLogicTest.php`)
- Tests critical business logic and workflows
- Validates complex operations like student enrollment, grade calculation, etc.
- Ensures business rules are properly enforced

## Running Tests

### Quick Start

```bash
# Run all tests using co-phpunit (Hyperf's coroutine-aware PHPUnit)
composer test

# Run with code coverage
vendor/bin/phpunit --coverage-text

# Run specific test file
vendor/bin/co-phpunit tests/Feature/AuthServiceTest.php
```

### Test Commands

- `composer test` - Run full test suite using co-phpunit
- `vendor/bin/co-phpunit` - Hyperf's coroutine-aware PHPUnit
- `vendor/bin/phpunit` - Standard PHPUnit (if co-phpunit unavailable)

### Test Database

Tests use an in-memory SQLite database for fast execution and isolation. This is configured in:
- `phpunit.xml.dist` - Sets `DB_CONNECTION=sqlite_testing`
- `config/database.php` - Defines `sqlite_testing` connection

Each test run creates a fresh database, runs migrations, and cleans up automatically.

## Test Coverage

This test suite provides coverage for:
- All major models and their relationships
- Core API endpoints
- Critical business logic
- Model factories and instantiation
- Authentication and authorization flows
- Data validation and constraints

## CI/CD Integration

Tests run automatically on GitHub Actions:
- Every push to `main` or `develop` branches
- Every pull request targeting `main` or `develop`

The CI workflow (`.github/workflows/ci.yml`) includes:
1. **PHPUnit Tests** - Runs full test suite
2. **Code Coverage** - Generates and uploads coverage report to Codecov
3. **PHPStan Static Analysis** - Checks code quality
4. **PHP CS Fixer** - Validates code style
5. **Composer Security Audit** - Scans for dependency vulnerabilities

View test results in the GitHub Actions tab after each push or PR.

## Test Coverage Goals

- **80%+ line coverage** for new code
- **100% coverage** for critical security logic (authentication, authorization)
- Test both success and failure paths
- Test edge cases and error conditions

## Documentation

For comprehensive testing guidelines, see:
- **[Testing Guide](../docs/TESTING.md)** - Complete testing documentation
- **[CONTRIBUTING.md](../CONTRIBUTING.md)** - Contribution guidelines

## Future Enhancements

Additional tests planned for:
- Database migration testing
- Integration testing with external services
- Performance and load testing
- Security vulnerability testing
- End-to-end user journey testing