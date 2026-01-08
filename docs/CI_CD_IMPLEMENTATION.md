# CI/CD Pipeline Implementation Note

Due to GitHub App permission limitations, the CI/CD workflow files could not be automatically created and pushed. This document provides the workflow files that need to be manually added to the repository.

## Required Workflows

### 1. CI Workflow (.github/workflows/ci.yml)

Create this file to implement automated testing, code style checks, and static analysis:

```yaml
name: CI

on:
  push:
    branches: [ main, master, develop ]
  pull_request:

permissions:
  contents: read

jobs:
  test:
    name: Test Suite
    runs-on: ubuntu-latest

    strategy:
      matrix:
        php-version: ['8.2', '8.3']

    steps:
      - name: Checkout code
        uses: actions/checkout@v4

      - name: Setup PHP
        uses: shivammathur/setup-php@v2
        with:
          php-version: ${{ matrix.php-version }}
          extensions: mbstring, xml, ctype, json, redis, pdo_mysql
          coverage: xdebug

      - name: Get Composer cache directory
        id: composer-cache
        run: echo "dir=$(composer config cache-files-dir)" >> $GITHUB_OUTPUT

      - name: Cache Composer dependencies
        uses: actions/cache@v3
        with:
          path: ${{ steps.composer-cache.outputs.dir }}
          key: ${{ runner.os }}-composer-${{ hashFiles('**/composer.lock') }}
          restore-keys: |
            ${{ runner.os }}-composer-

      - name: Install dependencies
        run: composer install --prefer-dist --no-progress --no-suggest

      - name: Execute tests with coverage
        run: |
          mkdir -p build/logs
          composer test -- --coverage-clover=build/logs/clover.xml --coverage-text
        env:
          DB_CONNECTION: sqlite_testing

      - name: Upload coverage to Codecov
        uses: codecov/codecov-action@v3
        with:
          file: ./build/logs/clover.xml
          fail_ci_if_error: false

  code-style:
    name: Code Style (PHP CS Fixer)
    runs-on: ubuntu-latest

    steps:
      - name: Checkout code
        uses: actions/checkout@v4

      - name: Setup PHP
        uses: shivammathur/setup-php@v2
        with:
          php-version: '8.3'
          extensions: mbstring, xml, ctype, json

      - name: Get Composer cache directory
        id: composer-cache
        run: echo "dir=$(composer config cache-files-dir)" >> $GITHUB_OUTPUT

      - name: Cache Composer dependencies
        uses: actions/cache@v3
        with:
          path: ${{ steps.composer-cache.outputs.dir }}
          key: ${{ runner.os }}-composer-${{ hashFiles('**/composer.lock') }}
          restore-keys: |
            ${{ runner.os }}-composer-

      - name: Install dependencies
        run: composer install --prefer-dist --no-progress --no-suggest

      - name: Run PHP CS Fixer in dry-run mode
        run: composer cs-diff

  static-analysis:
    name: Static Analysis (PHPStan)
    runs-on: ubuntu-latest

    steps:
      - name: Checkout code
        uses: actions/checkout@v4

      - name: Setup PHP
        uses: shivammathur/setup-php@v2
        with:
          php-version: '8.3'
          extensions: mbstring, xml, ctype, json

      - name: Get Composer cache directory
        id: composer-cache
        run: echo "dir=$(composer config cache-files-dir)" >> $GITHUB_OUTPUT

      - name: Cache Composer dependencies
        uses: actions/cache@v3
        with:
          path: ${{ steps.composer-cache.outputs.dir }}
          key: ${{ runner.os }}-composer-${{ hashFiles('**/composer.lock') }}
          restore-keys: |
            ${{ runner.os }}-composer-

      - name: Install dependencies
        run: composer install --prefer-dist --no-progress --no-suggest

      - name: Run PHPStan
        run: composer analyse
```

### 2. Security Workflow (.github/workflows/security.yml)

```yaml
name: Security

on:
  pull_request:
  schedule:
    - cron: '0 0 * * 0'  # Run weekly on Sunday at 00:00 UTC

permissions:
  contents: read
  security-events: write

jobs:
  dependency-audit:
    name: Dependency Security Audit
    runs-on: ubuntu-latest

    steps:
      - name: Checkout code
        uses: actions/checkout@v4

      - name: Setup PHP
        uses: shivammathur/setup-php@v2
        with:
          php-version: '8.3'
          extensions: mbstring, xml, ctype, json

      - name: Get Composer cache directory
        id: composer-cache
        run: echo "dir=$(composer config cache-files-dir)" >> $GITHUB_OUTPUT

      - name: Cache Composer dependencies
        uses: actions/cache@v3
        with:
          path: ${{ steps.composer-cache.outputs.dir }}
          key: ${{ runner.os }}-composer-${{ hashFiles('**/composer.lock') }}
          restore-keys: |
            ${{ runner.os }}-composer-

      - name: Install dependencies
        run: composer install --prefer-dist --no-progress --no-suggest

      - name: Run Composer audit
        run: composer audit --no-dev
        continue-on-error: true

      - name: Run Composer audit (dev dependencies)
        run: composer audit --dev
        continue-on-error: true

  security-scan:
    name: Static Security Analysis
    runs-on: ubuntu-latest

    steps:
      - name: Checkout code
        uses: actions/checkout@v4

      - name: Run PHP security scanner
        uses: zxyle/php-security-scanner@v1
        continue-on-error: true
        with:
          path: ./
```

### 3. Deploy Workflow (.github/workflows/deploy.yml)

```yaml
name: Deploy

on:
  workflow_dispatch:
    inputs:
      environment:
        description: 'Deployment environment'
        required: true
        default: 'staging'
        type: choice
        options:
          - staging
          - production

permissions:
  contents: read
  deployments: write

jobs:
  deploy:
    name: Deploy to ${{ inputs.environment }}
    runs-on: ubuntu-latest
    environment: ${{ inputs.environment }}

    steps:
      - name: Checkout code
        uses: actions/checkout@v4

      - name: Setup PHP
        uses: shivammathur/setup-php@v2
        with:
          php-version: '8.3'
          extensions: mbstring, xml, ctype, json, redis, pdo_mysql

      - name: Get Composer cache directory
        id: composer-cache
        run: echo "dir=$(composer config cache-files-dir)" >> $GITHUB_OUTPUT

      - name: Cache Composer dependencies
        uses: actions/cache@v3
        with:
          path: ${{ steps.composer-cache.outputs.dir }}
          key: ${{ runner.os }}-composer-${{ hashFiles('**/composer.lock') }}
          restore-keys: |
            ${{ runner.os }}-composer-

      - name: Install dependencies
        run: composer install --prefer-dist --no-progress --no-suggest --no-dev

      - name: Run migrations
        run: |
          echo "Running database migrations for ${{ inputs.environment }}"
          php artisan migrate --force
        env:
          DB_CONNECTION: ${{ secrets.DB_CONNECTION }}
          DB_HOST: ${{ secrets.DB_HOST }}
          DB_PORT: ${{ secrets.DB_PORT }}
          DB_DATABASE: ${{ secrets.DB_DATABASE }}
          DB_USERNAME: ${{ secrets.DB_USERNAME }}
          DB_PASSWORD: ${{ secrets.DB_PASSWORD }}

      - name: Clear cache
        run: php artisan cache:clear

      - name: Restart application
        run: |
          echo "Restarting application for ${{ inputs.environment }}"
          php artisan start:restart

  notify:
    name: Notify deployment status
    needs: deploy
    runs-on: ubuntu-latest
    if: always()

    steps:
      - name: Notify on success
        if: needs.deploy.result == 'success'
        run: |
          echo "✅ Deployment to ${{ inputs.environment }} completed successfully"
          # Add notification logic here (Slack, email, etc.)

      - name: Notify on failure
        if: needs.deploy.result == 'failure'
        run: |
          echo "❌ Deployment to ${{ inputs.environment }} failed"
          # Add notification logic here (Slack, email, etc.)
```

## Implementation Steps

1. Create the three workflow files above in `.github/workflows/` directory
2. Commit and push the files to the repository
3. The workflows will automatically trigger on the next push or pull request

## Changes Already Applied

The following changes have been applied to this commit:

1. ✅ Updated `phpunit.xml.dist` with code coverage reporting configuration
2. ✅ Updated `.gitignore` to include `/build` directory for coverage reports
3. ✅ Created this documentation file with workflow specifications

## Additional Notes

- Workflows use existing Composer scripts (`test`, `cs-diff`, `analyse`)
- Coverage reports are generated in `build/` directory (gitignored)
- PHPStan is configured at level 5 in `phpstan.neon`
- Security workflows run weekly on Sundays and on all pull requests

## Next Steps After Manual Workflow Addition

1. Verify workflows run correctly in the Actions tab
2. Configure Codecov integration for coverage reporting (optional)
3. Set up deployment secrets in GitHub repository settings
4. Adjust coverage thresholds as needed
5. Consider adding PHP Mess Detector or other quality checks

---

Related issue: #134
Created: January 8, 2026
