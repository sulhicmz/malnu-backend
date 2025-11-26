# CI/CD Pipeline Upgrade Plan

This document outlines the planned upgrade to fix the critical CI/CD pipeline issues mentioned in issue #134.

## Current Issues
- 7 redundant OpenCode workflows causing complexity
- No automated testing in CI/CD
- Missing security scanning
- No quality gates

## Proposed Solution
Consolidate to 3 essential workflows:

### 1. CI Workflow (ci.yml)
```yaml
name: CI - Test & Quality

on:
  push:
    branches: [ main, develop ]
  pull_request:
    branches: [ main, develop ]

jobs:
  test:
    runs-on: ubuntu-latest
    strategy:
      matrix:
        php-version: [8.2, 8.3]

    services:
      mysql:
        image: mysql:8.0
        env:
          MYSQL_ROOT_PASSWORD: rootpassword
          MYSQL_DATABASE: testdb
        ports:
          - 3306:3306
        options: --health-cmd="mysqladmin ping" --health-interval=10s --health-timeout=5s --health-retries=3

    steps:
    - name: Checkout code
      uses: actions/checkout@v5

    - name: Setup PHP
      uses: shivammathur/setup-php@v2
      with:
        php-version: ${{ matrix.php-version }}
        extensions: mbstring, dom, fileinfo, redis, pdo_mysql
        ini-values: post_max_size=256M, upload_max_filesize=256M
        coverage: xdebug

    - name: Cache Composer dependencies
      uses: actions/cache@v4
      with:
        path: vendor
        key: ${{ runner.os }}-composer-${{ hashFiles('**/composer.lock') }}
        restore-keys: ${{ runner.os }}-composer-

    - name: Install dependencies
      run: |
        composer install --no-interaction --no-progress --optimize-autoloader --prefer-dist

    - name: Copy environment file
      run: cp .env.example .env

    - name: Generate application key
      run: php artisan key:generate

    - name: Run database migrations
      run: php artisan migrate --env=testing --database=sqlite_testing --force

    - name: Run unit tests
      run: composer test -- --coverage-clover=coverage.xml

    - name: Run feature tests
      run: composer test -- --testsuite=Feature

    - name: Run PHPStan analysis
      run: composer analyse

    - name: Run PHP CS Fixer check
      run: composer cs-diff
```

### 2. Security Workflow (security.yml)
```yaml
name: Security Scanning

on:
  push:
    branches: [ main, develop ]
  pull_request:
    branches: [ main, develop ]
  schedule:
    - cron: '0 0 * * 1'  # Weekly

jobs:
  security:
    runs-on: ubuntu-latest

    steps:
    - name: Checkout code
      uses: actions/checkout@v5

    - name: Setup PHP
      uses: shivammathur/setup-php@v2
      with:
        php-version: 8.2
        extensions: mbstring, dom, fileinfo
        coverage: none

    - name: Cache Composer dependencies
      uses: actions/cache@v4
      with:
        path: vendor
        key: ${{ runner.os }}-composer-${{ hashFiles('**/composer.lock') }}
        restore-keys: ${{ runner.os }}-composer-

    - name: Install dependencies
      run: composer install --no-interaction --no-progress --optimize-autoloader --prefer-dist

    - name: Run composer audit
      run: composer audit

    - name: Run security checker
      run: |
        composer require --dev sensiolabs/security-checker
        php vendor/bin/security-checker security:check

    - name: Run PHPStan analysis
      run: composer analyse

    - name: Secret Scanning
      uses: trufflesecurity/truffleHog@v3.0.2
      with:
        path: ./
        base: ${{ github.event.repository.default_branch }}
        extra_args: --only-verified
```

### 3. Deployment Workflow (deploy.yml)
```yaml
name: Deploy

on:
  push:
    tags:
      - 'v*'

env:
  GITHUB_TOKEN: ${{ secrets.GITHUB_TOKEN }}

jobs:
  deploy:
    runs-on: ubuntu-latest
    environment: production

    steps:
    - name: Checkout code
      uses: actions/checkout@v5

    - name: Setup PHP
      uses: shivammathur/setup-php@v2
      with:
        php-version: 8.2
        extensions: mbstring, dom, fileinfo, redis, pdo_mysql
        ini-values: post_max_size=256M, upload_max_filesize=256M

    - name: Cache Composer dependencies
      uses: actions/cache@v4
      with:
        path: vendor
        key: ${{ runner.os }}-composer-${{ hashFiles('**/composer.lock') }}
        restore-keys: ${{ runner.os }}-composer-

    - name: Install dependencies
      run: composer install --no-interaction --no-progress --optimize-autoloader --prefer-dist --no-dev

    - name: Copy environment file
      run: cp .env.example .env

    - name: Generate application key
      run: php artisan key:generate

    - name: Run database migrations
      run: php artisan migrate --force

    - name: Clear caches
      run: |
        php artisan cache:clear
        php artisan config:clear
        php artisan route:clear
        php artisan view:clear

    - name: Run deployment script
      run: |
        # Add your deployment commands here
        echo "Deployment completed successfully"
```

## Files to Remove
The following redundant workflow files should be removed:
- `.github/workflows/oc- researcher.yml`
- `.github/workflows/oc-cf-supabase.yml`
- `.github/workflows/oc-issue-solver.yml`
- `.github/workflows/oc-maintainer.yml`
- `.github/workflows/oc-pr-handler.yml`
- `.github/workflows/oc-problem-finder.yml`
- `.github/workflows/oc-template.md`
- `.github/workflows/openhands.yml`

## Implementation Notes
1. These workflows include proper PHP testing with both unit and feature tests
2. Security scanning with dependency audit and secret scanning
3. PHP code quality checks with PHPStan and PHP CS Fixer
4. Proper environment setup for testing with SQLite in-memory database
5. Deployment workflow with proper environment configuration