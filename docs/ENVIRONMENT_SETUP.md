# Environment Configuration Guide

This guide explains how to properly configure environment variables for the Malnu Backend application.

## Overview

The application uses environment variables to manage configuration across different environments (development, staging, production). These variables control database connections, authentication, caching, and more.

## Quick Setup

### Automated Setup (Recommended)

Run the automated setup script to generate secure secrets and configure your environment:

```bash
./scripts/setup-env.sh
```

This script:
- Copies `.env.example` to `.env`
- Generates secure random `APP_KEY` and `JWT_SECRET` values
- Warns about fields that need manual configuration
- Provides next steps

### Manual Setup

If you prefer manual setup:

```bash
# Copy the example file
cp .env.example .env

# Generate secure secrets (Linux/macOS)
APP_KEY=$(openssl rand -base64 32)
JWT_SECRET=$(openssl rand -hex 32)

# Or use php artisan to generate APP_KEY only
php artisan key:generate
```

Then edit `.env` to replace placeholder values.

## Critical Configuration

### APP_KEY

**Purpose**: Application encryption key for encrypting sessions, cookies, and other sensitive data.

**Format**: 32-byte base64 encoded string

**Generate**: `openssl rand -base64 32`

**Security**: 
- Must be unique per environment
- Never share between development, staging, production
- Regenerate if compromised

**Example**: `fhBpQ3YR2zxt+qv+NPmvl4DitYGSfVSGSG1DQoudf0k=`

### JWT_SECRET

**Purpose**: Secret key for signing and verifying JWT authentication tokens.

**Format**: 64-character hexadecimal string

**Generate**: `openssl rand -hex 32`

**Security**:
- Must be cryptographically random
- Change immediately if compromised
- Never commit to version control
- Rotate periodically (recommended: every 90 days)

**Example**: `87252e80386769179a8fc968d1396e478cb006fe6f87af781ebabb0c1ed767e7`

## Database Configuration

### MySQL (Docker - Default)

```env
DB_CONNECTION=mysql
DB_HOST=mysql
DB_PORT=3306
DB_DATABASE=malnu
DB_USERNAME=malnu_user
DB_PASSWORD=malnu_password_change_in_production
```

### PostgreSQL (Docker)

```env
DB_CONNECTION=postgres
DB_HOST=postgres
DB_PORT=5432
DB_DATABASE=malnu
DB_USERNAME=malnu_user
DB_PASSWORD=malnu_password_change_in_production
```

### SQLite (Development Only)

```env
DB_CONNECTION=sqlite
# SQLite uses database/database.sqlite file
# Comment out all DB_HOST, DB_PORT, DB_USERNAME, DB_PASSWORD settings
```

### External Database

For databases not running in Docker:

```env
# Change host from service name to localhost
DB_HOST=localhost
```

## Authentication Configuration

### JWT Settings

```env
# Access token lifetime (30 minutes)
JWT_TTL=30

# Refresh token lifetime (24 hours)
JWT_REFRESH_TTL=1440

# Token blacklisting (for secure logout)
JWT_BLACKLIST_ENABLED=true

# JWT signing algorithm
JWT_ALGO=HS256
```

### Session Configuration

```env
# Session driver (options: file, database, redis)
SESSION_DRIVER=database

# Session lifetime (120 minutes)
SESSION_LIFETIME=120

# Session encryption
SESSION_ENCRYPT=false
```

## Caching Configuration

```env
# Cache driver (options: array, redis, database)
CACHE_DRIVER=redis

# Session storage (recommended: redis for distributed systems)
SESSION_DRIVER=database
```

## Mail Configuration

For development using [Mailtrap](https://mailtrap.io):

```env
MAIL_MAILER=smtp
MAIL_HOST=smtp.mailtrap.io
MAIL_PORT=2525
MAIL_USERNAME=your-username
MAIL_PASSWORD=your-password
MAIL_ENCRYPTION=null
```

For production, use SMTP services like SendGrid, AWS SES, or your own SMTP server.

## Security Headers

The following headers can be configured for enhanced security:

```env
# Enable all security headers
SECURITY_HEADERS_ENABLED=true

# Content Security Policy
CSP_ENABLED=true
CSP_DEFAULT_SRC="'self'"
CSP_SCRIPT_SRC="'self' 'unsafe-inline' 'unsafe-eval'"
CSP_STYLE_SRC="'self' 'unsafe-inline'"
CSP_IMG_SRC="'self' data: https:"
```

## Environment-Specific Configuration

### Development

```env
APP_ENV=local
APP_DEBUG=true
LOG_LEVEL=debug
```

### Production

```env
APP_ENV=production
APP_DEBUG=false
LOG_LEVEL=error
```

**Important**: Always set `APP_DEBUG=false` in production to prevent exposing sensitive information.

## Validation

The application validates critical environment variables on startup when:

```env
ENV_VALIDATION_ENABLED=true
```

Critical variables are checked and the application will fail to start if:
- `APP_KEY` is empty
- `JWT_SECRET` is empty
- `DB_CONNECTION` is invalid

## Security Best Practices

1. **Never Commit .env**: The `.env` file contains secrets and must never be committed to version control. It's in `.gitignore`.

2. **Unique Secrets per Environment**: Each environment (dev, staging, production) should have different secrets.

3. **Rotate Secrets Regularly**: Change `APP_KEY` and `JWT_SECRET` periodically (recommended: every 90 days).

4. **Secure Storage**: Store production `.env` files securely with restricted file permissions:
   ```bash
   chmod 600 .env
   ```

5. **Use Strong Random Generation**: Always use OpenSSL or PHP's `random_bytes()` function. Never use predictable values.

6. **Environment Variable Validation**: Keep `ENV_VALIDATION_ENABLED=true` to catch missing or invalid configuration.

7. **Backup Configuration**: Keep secure backups of production `.env` files for disaster recovery.

## Troubleshooting

### Application Won't Start

Check if critical environment variables are set:

```bash
# Check .env file exists
ls -la .env

# Verify critical secrets are not empty
grep "APP_KEY=" .env
grep "JWT_SECRET=" .env
```

### Database Connection Errors

For Docker:
```bash
# Check if MySQL service is running
docker-compose ps

# View MySQL logs
docker-compose logs mysql
```

For external databases:
```bash
# Test connection from application server
telnet db_host 3306  # MySQL
telnet db_host 5432  # PostgreSQL
```

### Permission Denied Errors

Check file permissions:

```bash
# Ensure storage directory is writable
chmod -R 775 storage/

# Ensure .env is readable only by owner
chmod 600 .env
```

### JWT Authentication Fails

Verify JWT_SECRET is the same between:
1. `.env` file (used by application)
2. `.env.example` file (used for reference)
3. Any secrets management system

The secret must match exactly to validate tokens correctly.

## Additional Resources

- [Docker Compose Guide](#docker-setup)
- [Security Policy](../../SECURITY.md)
- [Contributing Guidelines](CONTRIBUTING.md)
