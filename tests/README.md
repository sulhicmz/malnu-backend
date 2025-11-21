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

## Framework Issue Notice

**IMPORTANT**: The Hyperf framework has namespace import issues that are currently being resolved in PR #138. The tests in this suite are designed to work with the correct Hyperf framework imports but will not run properly until the following issues are fixed:

- `Hypervel` namespace should be `Hyperf`
- All framework imports need to be corrected
- Test methods like `assertInstanceOf`, `assertTrue`, etc. will work after the fix

## Running Tests

Once the framework issues are resolved, you can run the tests using:

```bash
php vendor/bin/phpunit
```

Or to run specific test suites:

```bash
# Run model relationship tests
php vendor/bin/phpunit tests/Feature/ModelRelationshipTest.php

# Run all feature tests
php vendor/bin/phpunit tests/Feature/
```

## Test Coverage

This test suite provides coverage for:
- All major models and their relationships
- Core API endpoints
- Critical business logic
- Model factories and instantiation
- Authentication and authorization flows
- Data validation and constraints

## Future Enhancements

After the framework issues are resolved, additional tests will be added for:
- Database migration testing
- Integration testing with external services
- Performance and load testing
- Security vulnerability testing
- End-to-end user journey testing