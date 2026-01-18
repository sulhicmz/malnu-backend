# CI/CD Pipeline Implementation for Issue #134

This document contains the workflow configurations that need to be manually added to `.github/workflows/` to fix the CI/CD pipeline as described in issue #134.

Due to GitHub security restrictions, workflow files must be manually created by a repository maintainer with appropriate permissions.

## Workflows to Remove

The following redundant workflows should be removed:
- `oc-researcher.yml`
- `oc-cf-supabase.yml`
- `oc-issue-solver.yml`
- `oc-maintainer.yml`
- `oc-pr-handler.yml`
- `oc-problem-finder.yml`
- `on-pull.yml`
- `on-push.yml`
- `openhands.yml`
- `workflow-monitor.yml`

## New Workflows to Create

### 1. ci.yml

Create `.github/workflows/ci.yml` with the following content:

```yaml
name: CI

on:
  push:
    branches: [ main, develop, dev ]
  pull_request:
    branches: [ main, develop, dev ]
  workflow_dispatch:

jobs:
  test:
    name: Tests & Quality Checks
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
          extensions: dom, curl, libxml, mbstring, zip, pcntl, pdo, sqlite, pdo_sqlite, bcmath, soap, intl, gd, exif, iconv, imagick
          coverage: xdebug
          tools: composer:v2

      - name: Get Composer Cache Directory
        id: composer-cache
        run: |
          echo "dir=$(composer config cache-files-dir)" >> $GITHUB_OUTPUT

      - name: Cache Composer dependencies
        uses: actions/cache@v4
        with:
          path: ${{ steps.composer-cache.outputs.dir }}
          key: ${{ runner.os }}-composer-${{ hashFiles('**/composer.lock') }}
          restore-keys: |
            ${{ runner.os }}-composer-

      - name: Install dependencies
        run: |
          composer install --prefer-dist --no-progress --no-suggest --no-interaction

      - name: Run PHPUnit tests
        run: |
          vendor/bin/phpunit --configuration phpunit.xml.dist --coverage-clover=build/coverage/clover.xml --colors=always

      - name: Upload coverage to Codecov
        uses: codecov/codecov-action@v4
        with:
          file: ./build/coverage/clover.xml
          fail_ci_if_error: false

      - name: Run PHPStan static analysis
        run: |
          composer analyse
        continue-on-error: false

      - name: Run PHP CS Fixer (check mode)
        run: |
          vendor/bin/php-cs-fixer fix --dry-run --diff --verbose
        continue-on-error: false

      - name: Upload test results
        if: always()
        uses: actions/upload-artifact@v4
        with:
          name: test-results-php-${{ matrix.php-version }}
          path: |
            .phpunit.cache/
            build/coverage/
          retention-days: 7
```

### 2. security.yml

Create `.github/workflows/security.yml` with the following content:

```yaml
name: Security

on:
  push:
    branches: [ main, develop, dev ]
  pull_request:
    branches: [ main, develop, dev ]
  schedule:
    - cron: '0 0 * * 1' # Every Monday at midnight
  workflow_dispatch:

jobs:
  dependency-review:
    name: Dependency Review
    runs-on: ubuntu-latest
    if: github.event_name == 'pull_request'

    steps:
      - name: Checkout code
        uses: actions/checkout@v4

      - name: Dependency Review
        uses: actions/dependency-review-action@v4
        with:
          fail-on-severity: moderate

  composer-audit:
    name: Composer Security Audit
    runs-on: ubuntu-latest

    steps:
      - name: Checkout code
        uses: actions/checkout@v4

      - name: Setup PHP
        uses: shivammathur/setup-php@v2
        with:
          php-version: '8.2'
          tools: composer:v2

      - name: Get Composer Cache Directory
        id: composer-cache
        run: |
          echo "dir=$(composer config cache-files-dir)" >> $GITHUB_OUTPUT

      - name: Cache Composer dependencies
        uses: actions/cache@v4
        with:
          path: ${{ steps.composer-cache.outputs.dir }}
          key: ${{ runner.os }}-composer-${{ hashFiles('**/composer.lock') }}
          restore-keys: |
            ${{ runner.os }}-composer-

      - name: Install dependencies
        run: |
          composer install --prefer-dist --no-progress --no-suggest --no-interaction

      - name: Run Composer audit
        run: |
          composer audit
        continue-on-error: false

  codeql-analysis:
    name: CodeQL Analysis
    runs-on: ubuntu-latest
    permissions:
      actions: read
      contents: read
      security-events: write

    strategy:
      fail-fast: false
      matrix:
        language: [ 'php' ]

    steps:
      - name: Checkout code
        uses: actions/checkout@v4

      - name: Initialize CodeQL
        uses: github/codeql-action/init@v3
        with:
          languages: ${{ matrix.language }}
          queries: security-extended,security-and-quality

      - name: Autobuild
        uses: github/codeql-action/autobuild@v3

      - name: Perform CodeQL Analysis
        uses: github/codeql-action/analyze@v3
        with:
          category: "/language:${{matrix.language}}"
```

### 3. deploy.yml

Create `.github/workflows/deploy.yml` with the following content:

```yaml
name: Deploy

on:
  push:
    branches: [ main ]
  workflow_dispatch:
    inputs:
      environment:
        description: 'Deployment environment'
        required: true
        default: 'production'
        type: choice
        options:
          - production
          - staging

jobs:
  pre-deploy-checks:
    name: Pre-deployment Checks
    runs-on: ubuntu-latest
    environment: ${{ github.event.inputs.environment || 'production' }}

    steps:
      - name: Checkout code
        uses: actions/checkout@v4

      - name: Setup PHP
        uses: shivammathur/setup-php@v2
        with:
          php-version: '8.2'
          tools: composer:v2

      - name: Get Composer Cache Directory
        id: composer-cache
        run: |
          echo "dir=$(composer config cache-files-dir)" >> $GITHUB_OUTPUT

      - name: Cache Composer dependencies
        uses: actions/cache@v4
        with:
          path: ${{ steps.composer-cache.outputs.dir }}
          key: ${{ runner.os }}-composer-${{ hashFiles('**/composer.lock') }}
          restore-keys: |
            ${{ runner.os }}-composer-

      - name: Install dependencies
        run: |
          composer install --prefer-dist --no-progress --no-suggest --no-interaction

      - name: Run tests before deployment
        run: |
          vendor/bin/phpunit --configuration phpunit.xml.dist --colors=always

      - name: Run static analysis
        run: |
          composer analyse

  deploy:
    name: Deploy Application
    runs-on: ubuntu-latest
    needs: pre-deploy-checks
    environment: ${{ github.event.inputs.environment || 'production' }}

    steps:
      - name: Checkout code
        uses: actions/checkout@v4

      - name: Deploy to server
        run: |
          echo "Deployment logic would go here"
          echo "Deploying to: ${{ github.event.inputs.environment || 'production' }}"
          # Add your deployment commands here
          # Example: SSH to server and pull latest changes, run migrations, etc.

      - name: Notify deployment success
        if: success()
        run: |
          echo "Deployment successful"

      - name: Notify deployment failure
        if: failure()
        run: |
          echo "Deployment failed"
```

## Configuration Notes

1. **PHPUnit Configuration**: The workflow uses existing `phpunit.xml.dist` configuration which already sets up SQLite in-memory database for tests.

2. **PHPStan Configuration**: Uses existing `phpstan.neon` configuration at level 5.

3. **PHP CS Fixer**: Uses existing `.php-cs-fixer.php` or default rules.

4. **Code Coverage**: Generates clover XML report and uploads to Codecov.

5. **Security**: Implements multiple security checks including Composer audit, dependency review, and CodeQL analysis.

## Testing the Workflows

After applying these changes, verify that:

1. Push commits trigger the CI workflow
2. Pull requests trigger both CI and Security workflows
3. All tests pass in the CI workflow
4. Code quality checks (PHPStan, PHP CS Fixer) run successfully
5. Security scans run without errors
6. Deployment workflow only triggers on main branch push or manual dispatch

## Benefits

This implementation provides:
- Automated testing on every push and PR
- Code quality gates with static analysis and style checks
- Security scanning for dependencies and code
- Pre-deployment checks to ensure code quality
- Coverage reporting for test insights
- Reduced complexity from 10+ workflows to 3 essential workflows

## Related Issue

Fixes #134
