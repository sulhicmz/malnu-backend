# CI/CD Setup Documentation

## Overview
This document describes the consolidated CI/CD pipeline for the HyperVel project. The previous setup had 7 redundant workflows which have been consolidated into 3 essential workflows.

## Workflows

### 1. CI Workflow (`.github/workflows/ci.yml`)
This workflow runs on every push and pull request to the main and develop branches. It includes:

- **Testing**: Runs both unit and feature tests across multiple PHP versions (8.2, 8.3)
- **Linting**: Checks code style with PHP CS Fixer and static analysis with PHPStan
- **Security**: Performs basic security checks with composer audit

### 2. Security Workflow (`.github/workflows/security.yml`)
This workflow provides comprehensive security scanning:

- **Dependency Scanning**: Checks for known vulnerabilities in dependencies
- **Code Analysis**: Runs PHPStan for security-related issues
- **Secrets Detection**: Uses TruffleHog to detect accidentally committed secrets
- **CodeQL Analysis**: GitHub's code scanning for security vulnerabilities

### 3. Deploy Workflow (`.github/workflows/deploy.yml`)
This workflow handles production deployments:

- **Build**: Sets up the production environment
- **Migrations**: Runs database migrations
- **Deployment**: Placeholder for actual deployment logic

## Quality Gates

### Automated Testing
- Unit tests must pass with 100% success rate
- Feature tests must pass with 100% success rate
- Code coverage threshold is monitored (target: 80%+)

### Code Quality
- PHP CS Fixer enforces consistent code style
- PHPStan performs static analysis at level 5
- All code must pass these checks before merging

### Security Scanning
- Dependencies are scanned for known vulnerabilities
- Code is analyzed for security issues
- Secrets detection prevents accidental credential commits

## Configuration

### Environment Variables
The following environment variables are used in the workflows:

- `APP_ENV`: Set to `testing` for CI environments
- `DB_CONNECTION`: Uses SQLite for testing
- `CACHE_DRIVER`: Set to `array` for testing
- `QUEUE_CONNECTION`: Set to `sync` for testing

### Code Coverage
Code coverage reports are generated in multiple formats:
- Clover XML for integration with CI tools
- HTML report for manual inspection
- Text output for console review

## Best Practices

### For Developers
1. Always run tests locally before pushing
2. Ensure code style compliance with `composer cs-fix`
3. Run static analysis with `composer analyse` before submitting PRs
4. Keep PRs focused and small to make reviews easier

### For Maintainers
1. Monitor test coverage trends
2. Review security scan results regularly
3. Update dependencies to address security vulnerabilities
4. Review and update workflow configurations as needed

## Troubleshooting

### Failed Tests
- Check the test output for specific failure details
- Run tests locally to reproduce the issue
- Ensure your local environment matches the CI environment

### Security Issues
- Address dependency vulnerabilities by updating packages
- Review CodeQL findings and implement suggested fixes
- Check for accidentally committed secrets

### Workflow Issues
- Verify that all required secrets are configured in the repository
- Check that workflow permissions are properly set
- Review GitHub Actions logs for detailed error information