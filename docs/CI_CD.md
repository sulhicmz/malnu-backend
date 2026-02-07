# CI/CD Pipeline Documentation

## Overview

This document describes the CI/CD pipeline for Malnu Backend, including automated quality checks, testing, and deployment workflows.

## CI Pipeline

### Trigger Conditions

The CI pipeline (`.github/workflows/ci.yml`) runs on:

- **Push events** to `main` and `develop` branches
- **Pull requests** targeting `main` and `develop` branches
- **Manual dispatch** via GitHub Actions UI

### Jobs

The CI pipeline consists of three parallel jobs:

#### 1. PHPUnit Tests

**Purpose**: Run the full test suite with coverage reporting

**Steps**:
1. Checkout code
2. Setup PHP 8.2 with required extensions
3. Cache and install Composer dependencies
4. Create SQLite test database
5. Run PHPUnit tests with coverage
6. Upload coverage artifacts
7. Check coverage threshold (minimum 30%)

**Coverage Threshold**:
- Minimum: 30%
- Fails workflow if coverage is below threshold

**Artifacts**:
- Coverage reports (retained for 7 days)

#### 2. PHP CS Fixer

**Purpose**: Verify code follows PSR-12 coding standards

**Steps**:
1. Checkout code
2. Setup PHP 8.2
3. Cache and install Composer dependencies
4. Run PHP CS Fixer in dry-run mode

**Behavior**:
- Dry-run mode only checks, doesn't modify code
- Fails if code doesn't match style standards
- Shows diff of required changes

#### 3. PHPStan Static Analysis

**Purpose**: Static code analysis for type safety and code quality

**Steps**:
1. Checkout code
2. Setup PHP 8.2
3. Cache and install Composer dependencies
4. Run PHPStan at level 5 (strictest)

**Behavior**:
- Analyzes `app/` and `config/` directories
- Level 5: Most strict analysis
- Fails workflow if errors found

**Artifacts**:
- PHPStan results (uploaded on failure, retained for 7 days)

### Concurrency

The CI pipeline uses concurrency to cancel in-progress runs for the same branch:
- Prevents resource waste
- Ensures only latest changes are tested

## Pre-commit Quality Checks

Developers can run CI checks locally before pushing:

```bash
# Run tests
composer test

# Check code style (dry-run)
composer cs-fix --dry-run --diff

# Fix code style automatically
composer cs-fix

# Run static analysis
composer analyse
```

## Coverage

### How Coverage is Measured

Coverage is generated using Xdebug and reported in multiple formats:
- **Clover XML**: `coverage.xml`
- **HTML report**: `build/coverage/html/`
- **Text summary**: `build/coverage/coverage.txt`

### Coverage Thresholds

Current minimum coverage: **30%**

To adjust coverage threshold, edit `.github/workflows/ci.yml`:

```yaml
- name: Check coverage threshold
  run: |
    threshold=30  # Change this value
    if [ -f build/coverage/coverage.txt ]; then
      ...
```

### Increasing Coverage

To improve code coverage:

1. Write tests for new features
2. Add tests for bug fixes
3. Refactor untestable code to be testable
4. Use mocks for external dependencies
5. Test edge cases and error conditions

See `docs/TESTING_GUIDELINES.md` for detailed testing guidance.

## OpenCode Workflows

### on-push.yml

Automated repository maintenance workflow that runs on every push:
- Analyzes repository state
- Creates and manages issues
- Solves high-priority issues
- Maintains documentation

**Note**: This workflow is separate from the CI pipeline and uses OpenCode AI agents.

### on-pull.yml

Automated PR review workflow that runs on pull requests:
- Reviews code changes
- Provides feedback
- Maintains code quality

**Note**: This workflow complements the CI pipeline by providing AI-assisted code review.

## Caching

### Composer Dependencies

Composer dependencies are cached to speed up CI runs:
- Cache key: `${{ runner.os }}-composer-${{ hashFiles('**/composer.lock') }}`
- Restores from fallback keys if exact match not found
- Significantly reduces install time

## Troubleshooting

### CI Failures

#### Tests Fail

1. Check test output in GitHub Actions logs
2. Review coverage reports as artifacts
3. Run tests locally to reproduce
4. Check for environment-specific issues

```bash
# Run tests locally
composer test

# Check test database
DB_CONNECTION=sqlite_testing composer test
```

#### Code Style Fails

1. Download artifact or run locally
2. Apply suggested fixes

```bash
# Fix automatically
composer cs-fix

# Check what would be fixed
composer cs-fix --dry-run --diff
```

#### Static Analysis Fails

1. Review PHPStan results in artifacts
2. Understand error messages
3. Fix type issues or add appropriate annotations

```bash
# Run locally with more details
composer analyse --memory-limit 512M
```

#### Coverage Below Threshold

1. Review coverage reports
2. Identify untested code paths
3. Write tests for critical paths
4. Consider adjusting threshold if appropriate

## Quality Gates

All three jobs must pass for merge:
- ✅ PHPUnit tests pass
- ✅ Coverage meets minimum threshold
- ✅ Code style passes (PSR-12)
- ✅ No static analysis errors

## Future Enhancements

Potential improvements to the CI pipeline:

- [ ] Add PHP version matrix (8.2, 8.3, 8.4)
- [ ] Code coverage integration (Codecov, Coveralls)
- [ ] Security scanning (composer audit)
- [ ] Performance benchmarks
- [ ] API documentation generation
- [ ] Integration tests with real database

## Related Documentation

- **[Testing Guidelines](TESTING_GUIDELINES.md)** - Comprehensive testing guidance
- **[Contributing](CONTRIBUTING.md)** - Contribution guidelines
- **[Development Guide](DEVELOPER_GUIDE.md)** - Setup and workflow
- **[Deployment Guide](DEPLOYMENT.md)** - Deployment procedures

## References

- [PHPUnit Documentation](https://phpunit.de/manual/current/en/)
- [PHPStan Documentation](https://phpstan.org/user-guide/getting-started)
- [PHP CS Fixer Documentation](https://cs.symfony.com/)
- [GitHub Actions Documentation](https://docs.github.com/en/actions)
