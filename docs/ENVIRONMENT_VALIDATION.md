# Environment Variable Validation

## Overview

The Environment Validator provides comprehensive validation of critical environment variables on application startup. This prevents runtime failures, security vulnerabilities, and difficult-to-debug production issues caused by misconfigured environment variables.

## Architecture

### Components

- **EnvironmentValidator Service** (`app/Services/EnvironmentValidator.php`) - Core validation logic
- **AppServiceProvider** (`app/Providers/AppServiceProvider.php`) - Integrates validation into application lifecycle
- **EnvironmentValidationTest** (`tests/Feature/EnvironmentValidationTest.php`) - Comprehensive test suite

### Validation Flow

```
Application Startup
    ↓
AppServiceProvider::boot()
    ↓
EnvironmentValidator::validate()
    ↓
Check ENV_VALIDATION_ENABLED and APP_ENV
    ↓
Validate all environment variables
    ↓
- Success → Application continues (with warnings if any)
- Failure → Application fails with clear error message
```

## Configuration

### Enable/Disable Validation

Add to your `.env` file:

```bash
# Enable validation (default, recommended)
ENV_VALIDATION_ENABLED=true

# Disable validation (development only, not recommended for production)
ENV_VALIDATION_ENABLED=false
```

### Testing Environment

Validation is automatically skipped when `APP_ENV=testing` regardless of `ENV_VALIDATION_ENABLED` setting.

## Validated Variables

### Required Variables

These variables are **required** and will cause the application to fail if missing or invalid in **production** environment:

| Variable | Validation Rules | Production Requirements |
|----------|----------------|-------------------------|
| `APP_KEY` | Must not be empty or use placeholder value | 32+ characters |
| `JWT_SECRET` | Must not be empty or use placeholder value | 32+ characters |

### Optional Variables

These variables are validated if set (warnings are generated for non-critical issues):

| Variable | Validation Rules |
|----------|----------------|
| `APP_ENV` | Must be: `local`, `production`, or `testing` |
| `APP_DEBUG` | Must be boolean (`true` or `false`). Warning if `true` in production |
| `DB_CONNECTION` | Must be valid: `sqlite`, `mysql`, or `postgres` |
| `DB_HOST` | Required in production if `DB_CONNECTION` is not `sqlite` |
| `DB_DATABASE` | Required in production |
| `DB_PORT` | Must be between 1 and 65535 (if set) |
| `REDIS_HOST` | Required in production if `JWT_BLACKLIST_ENABLED=true` |
| `REDIS_PORT` | Must be between 1 and 65535 (if set) |
| `APP_URL` | Must be valid URL. Warning if missing |
| `JWT_TTL` | Must be positive integer. Warning if > 43,200 (30 days) |
| `JWT_REFRESH_TTL` | Must be positive integer. Warning if > 525,600 (365 days) |

## Validation Levels

### Errors (Fatal)

Validation errors prevent the application from starting. These indicate critical misconfiguration:

- **Empty required variables**: `APP_KEY`, `JWT_SECRET` missing
- **Short secrets**: `APP_KEY` or `JWT_SECRET` less than 32 characters in production
- **Placeholder values**: Using default/placeholder values for secrets in production
- **Invalid values**: Wrong format for environment variables
- **Missing required**: Required variables missing for production configuration

### Warnings (Non-Fatal)

Validation warnings are displayed but do not prevent application startup:

- **Debug mode in production**: `APP_DEBUG=true` in production environment
- **Missing optional variables**: `APP_URL` not set (may cause issues with password reset links)
- **Unusual protocols**: `APP_URL` uses non-standard protocol
- **Very high TTL values**: `JWT_TTL` or `JWT_REFRESH_TTL` are unusually high

## Security Considerations

### Secret Generation

Generate secure secrets using:

```bash
# Generate APP_KEY (32+ characters)
php artisan key:generate

# Or generate manually
openssl rand -hex 32

# Generate JWT_SECRET (32+ characters)
openssl rand -hex 32
```

### Placeholder Values

These placeholder values are blocked in production:

- `base64:your-secret-key-here`
- `your-app-key-here`
- `change-me`
- `secret-key`
- `some-random-string`
- `your-secret-key-here`
- `your-jwt-secret`
- `jwt-secret-key`
- `secret`
- `password`
- `your-secure-jwt-secret-key-here`
- `jwt_secret_change_me`
- `default-secret`

### Production Security Checklist

Before deploying to production, ensure:

- [ ] `APP_KEY` is 32+ characters and not a placeholder
- [ ] `JWT_SECRET` is 32+ characters and not a placeholder
- [ ] `APP_ENV=production`
- [ ] `APP_DEBUG=false`
- [ ] `DB_HOST` and `DB_DATABASE` are set (if not using SQLite)
- [ ] `REDIS_HOST` and `REDIS_PORT` are set (if using JWT blacklist)
- [ ] `APP_URL` is set to production URL
- [ ] `ENV_VALIDATION_ENABLED=true` (recommended)

## Error Messages

### Empty APP_KEY

```
Environment validation failed with following errors:

1. APP_KEY is empty. Generate a secure key using: php artisan key:generate

Please fix these errors and restart the application.
```

### Short JWT_SECRET in Production

```
Environment validation failed with following errors:

1. JWT_SECRET must be at least 32 characters long in production. Current length: 16 characters.
   Generate a secure secret using: openssl rand -hex 32

Please fix these errors and restart the application.
```

### Placeholder Value Detected

```
Environment validation failed with following errors:

1. JWT_SECRET is using a placeholder value which is insecure.
   Generate a secure secret using: openssl rand -hex 32

Please fix these errors and restart the application.
```

### Debug Mode in Production (Warning)

```
Warnings:
- APP_DEBUG is set to true in production environment. This is a security risk.
  Set APP_DEBUG=false in production.
```

## Testing

### Run Tests

```bash
# Run environment validation tests
vendor/bin/co-phpunit tests/Feature/EnvironmentValidationTest.php

# Run all tests
composer test
```

### Test Locally

To test validation locally without changing your `.env`:

```bash
# Temporarily set environment variables for testing
ENV_VALIDATION_ENABLED=true APP_ENV=production php artisan start

# Test with invalid configuration
APP_KEY=invalid APP_ENV=production php artisan start
```

## Troubleshooting

### Validation Skipped

If validation is being skipped unexpectedly:

1. Check `ENV_VALIDATION_ENABLED` is set to `true`
2. Check `APP_ENV` is not set to `testing`

### Application Won't Start

If application fails to start with validation errors:

1. Read the error message carefully - it will indicate which variable is invalid
2. Fix the variable in your `.env` file
3. Restart the application

### Warnings But No Errors

If application starts but shows warnings:

1. Review the warnings to understand potential issues
2. Fix warnings for better security and stability
3. Warnings do not prevent application startup but should be addressed

### SQLite Database

If using SQLite:

- `DB_HOST`, `DB_PORT`, `DB_USERNAME`, `DB_PASSWORD` are not required
- `DB_DATABASE` should be the path to `.sqlite` file (e.g., `database/database.sqlite`)
- Database directory must be writable

## Best Practices

1. **Always validate before deployment**: Run validation in staging environment before production
2. **Use unique secrets**: Generate different `APP_KEY` and `JWT_SECRET` for each environment
3. **Never commit secrets**: Add `.env` to `.gitignore` (already done)
4. **Document your values**: Keep secure record of your environment variables
5. **Use `.env.example`**: Reference `.env.example` for all required variables
6. **Review warnings**: Address warnings even if application starts successfully
7. **Generate secure secrets**: Use `openssl rand -hex 32` or `php artisan key:generate`
8. **Disable debug in production**: Always set `APP_DEBUG=false` in production

## Migration Guide

If upgrading from a version without environment validation:

### Step 1: Review Your Configuration

Check your current `.env` file against the validation rules above.

### Step 2: Generate Secure Secrets (If Needed)

If your secrets are too short or use placeholder values:

```bash
# Generate APP_KEY
php artisan key:generate

# Generate JWT_SECRET
openssl rand -hex 32 > .env.new && cat .env >> .env.new && mv .env.new .env
```

### Step 3: Update `.env.example`

Add to your `.env.example` file:

```bash
ENV_VALIDATION_ENABLED=true
```

### Step 4: Test in Development

Test in development environment first:

```bash
composer install
php artisan start
```

### Step 5: Deploy to Production

Deploy only after validation passes in development and staging.

## Integration Points

### Application Startup

Validation runs automatically in `AppServiceProvider::boot()` which is called on application startup.

### CI/CD Pipelines

Add validation step to your CI/CD pipeline:

```bash
# In your deployment pipeline
php artisan config:cache  # This will trigger validation

# Or explicitly run validation
php -r "require 'vendor/autoload.php'; (new App\Services\EnvironmentValidator())->validate();"
```

## Related Documentation

- [Developer Guide](DEVELOPER_GUIDE.md) - Environment setup and configuration
- [Security Analysis](SECURITY_ANALYSIS.md) - Security considerations and best practices
- [API Documentation](API.md) - API configuration and authentication

## Support

If you encounter issues with environment validation:

1. Check error messages carefully - they provide specific guidance
2. Review this documentation for your specific variable
3. Check test file for examples of valid/invalid configurations
4. Review `.env.example` for reference configuration

---

Last updated: January 2026