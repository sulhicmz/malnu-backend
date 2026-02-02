# CI/CD Pipeline Implementation Guide

This guide provides the complete implementation for fixing the CI/CD pipeline and adding automated testing as described in [Issue #134](https://github.com/sulhicmz/malnu-backend/issues/134).

## Problem Statement

The current CI/CD setup has critical issues:
1. **No Automated Testing**: Workflows run OpenCode agents but don't execute actual tests
2. **Redundant Workflows**: 11+ workflows with no quality checks
3. **Missing Quality Gates**: No PHPStan, PHP CS Fixer, or security audits
4. **No Coverage Reporting**: Tests exist but aren't executed in CI

## Solution Overview

This implementation adds 3 new workflow files that provide:
- ✅ Automated PHPUnit testing with coverage reporting
- ✅ PHPStan static analysis
- ✅ PHP CS Fixer code style checks
- ✅ Composer security audit
- ✅ Dependency review for PRs
- ✅ Manual deployment workflow with quality gates

## Implementation Steps

### Step 1: Create `.github/workflows/ci.yml`

This workflow runs on every pull request and push to main/develop/master branches.

```yaml
name: CI - Testing and Quality Checks

on:
  pull_request:
    branches:
      - main
      - develop
      - master
  push:
    branches:
      - main
      - develop
      - master
  workflow_dispatch:

permissions:
  contents: read

concurrency:
  group: ci-${{ github.workflow }}-${{ github.ref }}
  cancel-in-progress: true

jobs:
  test:
    name: Run Tests
    runs-on: ubuntu-latest

    strategy:
      matrix:
        php-version: ['8.2', '8.3']

    steps:
      - name: Checkout code
        uses: actions/checkout@v4

      - name: Setup PHP ${{ matrix.php-version }}
        uses: shivammathur/setup-php@v2
        with:
          php-version: ${{ matrix.php-version }}
          extensions: mbstring, pdo, pdo_sqlite, redis
          coverage: xdebug
          tools: composer:v2

      - name: Get Composer cache directory
        id: composer-cache
        run: echo "dir=$(composer config cache-files-dir)" >> $GITHUB_OUTPUT

      - name: Cache Composer dependencies
        uses: actions/cache@v4
        with:
          path: ${{ steps.composer-cache.outputs.dir }}
          key: ${{ runner.os }}-composer-${{ hashFiles('**/composer.lock') }}
          restore-keys: |
            ${{ runner.os }}-composer-

      - name: Install dependencies
        run: composer install --prefer-dist --no-progress --no-interaction

      - name: Create test database
        run: |
          mkdir -p database
          touch database/database.sqlite

      - name: Run PHPUnit tests
        run: composer test

      - name: Upload coverage to Codecov
        if: matrix.php-version == '8.2'
        uses: codecov/codecov-action@v4
        with:
          token: ${{ secrets.CODECOV_TOKEN }}
          files: build/coverage/clover.xml
          fail_ci_if_error: false

      - name: Archive coverage reports
        if: matrix.php-version == '8.2'
        uses: actions/upload-artifact@v4
        with:
          name: coverage-report-php-${{ matrix.php-version }}
          path: build/coverage/
          retention-days: 7

  phpstan:
    name: PHPStan Static Analysis
    runs-on: ubuntu-latest

    steps:
      - name: Checkout code
        uses: actions/checkout@v4

      - name: Setup PHP
        uses: shivammathur/setup-php@v2
        with:
          php-version: '8.2'
          tools: composer:v2

      - name: Get Composer cache directory
        id: composer-cache
        run: echo "dir=$(composer config cache-files-dir)" >> $GITHUB_OUTPUT

      - name: Cache Composer dependencies
        uses: actions/cache@v4
        with:
          path: ${{ steps.composer-cache.outputs.dir }}
          key: ${{ runner.os }}-composer-${{ hashFiles('**/composer.lock') }}
          restore-keys: |
            ${{ runner.os }}-composer-

      - name: Install dependencies
        run: composer install --prefer-dist --no-progress --no-interaction

      - name: Run PHPStan
        run: composer analyse

  phpcsfixer:
    name: PHP CS Fixer Code Style
    runs-on: ubuntu-latest

    steps:
      - name: Checkout code
        uses: actions/checkout@v4

      - name: Setup PHP
        uses: shivammathur/setup-php@v2
        with:
          php-version: '8.2'
          tools: composer:v2

      - name: Get Composer cache directory
        id: composer-cache
        run: echo "dir=$(composer config cache-files-dir)" >> $GITHUB_OUTPUT

      - name: Cache Composer dependencies
        uses: actions/cache@v4
        with:
          path: ${{ steps.composer-cache.outputs.dir }}
          key: ${{ runner.os }}-composer-${{ hashFiles('**/composer.lock') }}
          restore-keys: |
            ${{ runner.os }}-composer-

      - name: Install dependencies
        run: composer install --prefer-dist --no-progress --no-interaction

      - name: Run PHP CS Fixer (check mode)
        run: composer cs-diff
```

### Step 2: Create `.github/workflows/security.yml`

This workflow runs security audits on every pull request, push, and weekly schedule.

```yaml
name: Security - Dependency & Vulnerability Scanning

on:
  pull_request:
    branches:
      - main
      - develop
      - master
  push:
    branches:
      - main
      - develop
      - master
  schedule:
    - cron: '0 0 * * 0'
  workflow_dispatch:

permissions:
  contents: read

concurrency:
  group: security-${{ github.workflow }}-${{ github.ref }}
  cancel-in-progress: false

jobs:
  composer-audit:
    name: Composer Audit
    runs-on: ubuntu-latest

    steps:
      - name: Checkout code
        uses: actions/checkout@v4

      - name: Setup PHP
        uses: shivammathur/setup-php@v2
        with:
          php-version: '8.2'
          tools: composer:v2

      - name: Get Composer cache directory
        id: composer-cache
        run: echo "dir=$(composer config cache-files-dir)" >> $GITHUB_OUTPUT

      - name: Cache Composer dependencies
        uses: actions/cache@v4
        with:
          path: ${{ steps.composer-cache.outputs.dir }}
          key: ${{ runner.os }}-composer-${{ hashFiles('**/composer.lock') }}
          restore-keys: |
            ${{ runner.os }}-composer-

      - name: Install dependencies
        run: composer install --prefer-dist --no-progress --no-interaction

      - name: Run composer audit
        run: composer audit --format=json | tee audit-output.json

      - name: Check for vulnerabilities
        id: check-vulnerabilities
        run: |
          VULNERABILITIES=$(cat audit-output.json | jq '.advisories | length')
          echo "vulnerabilities=$VULNERABILITIES" >> $GITHUB_OUTPUT
          if [ "$VULNERABILITIES" -gt 0 ]; then
            echo "::error::Found $VULNERABILITIES security vulnerabilities"
            echo "Run 'composer audit' locally for details"
            exit 1
          else
            echo "✅ No security vulnerabilities found"
          fi

      - name: Upload audit report
        if: always()
        uses: actions/upload-artifact@v4
        with:
          name: composer-audit-report
          path: audit-output.json
          retention-days: 30

  dependency-review:
    name: Dependency Review
    runs-on: ubuntu-latest
    if: github.event_name == 'pull_request'

    permissions:
      contents: read
      pull-requests: read

    steps:
      - name: Checkout code
        uses: actions/checkout@v4

      - name: Dependency Review
        uses: actions/dependency-review-action@v4
        with:
          fail-on-severity: moderate
          deny-licenses: GPL-3.0, AGPL-3.0
```

### Step 3: Create `.github/workflows/deploy.yml`

This workflow provides manual deployment triggers with pre-deployment quality checks.

```yaml
name: Deployment

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
      version:
        description: 'Version tag to deploy (leave empty for latest)'
        required: false
        default: ''
        type: string

permissions:
  contents: write
  pull-requests: write

jobs:
  pre-deploy-checks:
    name: Pre-deployment Quality Checks
    runs-on: ubuntu-latest

    steps:
      - name: Checkout code
        uses: actions/checkout@v4
        with:
          ref: ${{ github.event.inputs.version || 'main' }}

      - name: Setup PHP
        uses: shivammathur/setup-php@v2
        with:
          php-version: '8.2'
          tools: composer:v2

      - name: Get Composer cache directory
        id: composer-cache
        run: echo "dir=$(composer config cache-files-dir)" >> $GITHUB_OUTPUT

      - name: Cache Composer dependencies
        uses: actions/cache@v4
        with:
          path: ${{ steps.composer-cache.outputs.dir }}
          key: ${{ runner.os }}-composer-${{ hashFiles('**/composer.lock') }}
          restore-keys: |
            ${{ runner.os }}-composer-

      - name: Install dependencies
        run: composer install --prefer-dist --no-progress --no-interaction

      - name: PHP Syntax Check
        run: |
          find app -name "*.php" -print0 | xargs -0 -n1 php -l

      - name: Create test database
        run: |
          mkdir -p database
          touch database/database.sqlite

      - name: Run tests
        run: composer test
        env:
          APP_ENV: testing
          DB_CONNECTION: sqlite_testing
          CACHE_DRIVER: array

  deploy:
    name: Deploy to ${{ github.event.inputs.environment }}
    runs-on: ubuntu-latest
    needs: pre-deploy-checks
    environment:
      name: ${{ github.event.inputs.environment }}
      url: ${{ github.event.inputs.environment == 'production' && 'https://api.malnu.com' || 'https://api-staging.malnu.com' }}

    steps:
      - name: Deployment notice
        run: |
          echo "🚀 Deploying to ${{ github.event.inputs.environment }}"
          echo "Version: ${{ github.event.inputs.version || 'latest' }}"

      - name: Deployment placeholder
        run: |
          echo "⚠️  Actual deployment steps need to be configured"
          echo "Add your deployment logic here:"
          echo "  - Cloudflare Workers deployment"
          echo "  - Supabase deployment"
          echo "  - SSH deployment to VPS"
          echo "  - Docker container deployment"

      - name: Create GitHub Deployment
        if: success()
        uses: actions/create-github-app-token@v1
        id: app-token
        with:
          app-id: ${{ vars.DEPLOYMENT_APP_ID }}
          private-key: ${{ secrets.DEPLOYMENT_PRIVATE_KEY }}

      - name: Update deployment status
        if: steps.app-token.outputs.token != ''
        env:
          GH_TOKEN: ${{ steps.app-token.outputs.token }}
        run: |
          gh api repos/:owner/:repo/deployments -X POST -f ref="${{ github.event.inputs.version || 'main' }}" -f environment="${{ github.event.inputs.environment }}" -f auto_inactive=true || true
```

### Step 4: Optional - Disable OpenCode Workflows

After verifying the new workflows work correctly, you may want to disable some or all of the OpenCode workflows to avoid duplicate CI runs. To disable a workflow:

1. Add `.disabled` suffix to the filename, e.g.:
   ```
   mv .github/workflows/on-push.yml .github/workflows/on-push.yml.disabled
   ```

2. Commit and push the renamed files

3. Verify workflows still work as expected

Recommended workflows to keep:
- `ci.yml` - Essential for code quality
- `security.yml` - Essential for security
- `deploy.yml` - Essential for deployments

OpenCode workflows to consider disabling:
- `on-push.yml`, `on-pull.yml` - Redundant with ci.yml
- `iterate.yml` - May cause duplicate CI runs
- `oc-*.yml` - OpenCode agent workflows

### Step 5: Configure Codecov (Optional)

To enable test coverage reporting:

1. Go to [Codecov.io](https://codecov.io)
2. Sign up with your GitHub account
3. Add the `CODECOV_TOKEN` secret to your repository:
   - Settings → Secrets and variables → Actions → New repository secret
   - Name: `CODECOV_TOKEN`
   - Value: Your Codecov token from https://codecov.io/gh/sulhicmz/malnu-backend

## Manual Installation Commands

Execute these commands to create the workflow files:

```bash
# Create ci.yml
cat > .github/workflows/ci.yml << 'CI_EOF'
name: CI - Testing and Quality Checks

on:
  pull_request:
    branches:
      - main
      - develop
      - master
  push:
    branches:
      - main
      - develop
      - master
  workflow_dispatch:

permissions:
  contents: read

concurrency:
  group: ci-${{ github.workflow }}-${{ github.ref }}
  cancel-in-progress: true

jobs:
  test:
    name: Run Tests
    runs-on: ubuntu-latest

    strategy:
      matrix:
        php-version: ['8.2', '8.3']

    steps:
      - name: Checkout code
        uses: actions/checkout@v4

      - name: Setup PHP ${{ matrix.php-version }}
        uses: shivammathur/setup-php@v2
        with:
          php-version: ${{ matrix.php-version }}
          extensions: mbstring, pdo, pdo_sqlite, redis
          coverage: xdebug
          tools: composer:v2

      - name: Get Composer cache directory
        id: composer-cache
        run: echo "dir=$(composer config cache-files-dir)" >> $GITHUB_OUTPUT

      - name: Cache Composer dependencies
        uses: actions/cache@v4
        with:
          path: ${{ steps.composer-cache.outputs.dir }}
          key: ${{ runner.os }}-composer-${{ hashFiles('**/composer.lock') }}
          restore-keys: |
            ${{ runner.os }}-composer-

      - name: Install dependencies
        run: composer install --prefer-dist --no-progress --no-interaction

      - name: Create test database
        run: |
          mkdir -p database
          touch database/database.sqlite

      - name: Run PHPUnit tests
        run: composer test

      - name: Upload coverage to Codecov
        if: matrix.php-version == '8.2'
        uses: codecov/codecov-action@v4
        with:
          token: ${{ secrets.CODECOV_TOKEN }}
          files: build/coverage/clover.xml
          fail_ci_if_error: false

      - name: Archive coverage reports
        if: matrix.php-version == '8.2'
        uses: actions/upload-artifact@v4
        with:
          name: coverage-report-php-${{ matrix.php-version }}
          path: build/coverage/
          retention-days: 7

  phpstan:
    name: PHPStan Static Analysis
    runs-on: ubuntu-latest

    steps:
      - name: Checkout code
        uses: actions/checkout@v4

      - name: Setup PHP
        uses: shivammathur/setup-php@v2
        with:
          php-version: '8.2'
          tools: composer:v2

      - name: Get Composer cache directory
        id: composer-cache
        run: echo "dir=$(composer config cache-files-dir)" >> $GITHUB_OUTPUT

      - name: Cache Composer dependencies
        uses: actions/cache@v4
        with:
          path: ${{ steps.composer-cache.outputs.dir }}
          key: ${{ runner.os }}-composer-${{ hashFiles('**/composer.lock') }}
          restore-keys: |
            ${{ runner.os }}-composer-

      - name: Install dependencies
        run: composer install --prefer-dist --no-progress --no-interaction

      - name: Run PHPStan
        run: composer analyse

  phpcsfixer:
    name: PHP CS Fixer Code Style
    runs-on: ubuntu-latest

    steps:
      - name: Checkout code
        uses: actions/checkout@v4

      - name: Setup PHP
        uses: shivammathur/setup-php@v2
        with:
          php-version: '8.2'
          tools: composer:v2

      - name: Get Composer cache directory
        id: composer-cache
        run: echo "dir=$(composer config cache-files-dir)" >> $GITHUB_OUTPUT

      - name: Cache Composer dependencies
        uses: actions/cache@v4
        with:
          path: ${{ steps.composer-cache.outputs.dir }}
          key: ${{ runner.os }}-composer-${{ hashFiles('**/composer.lock') }}
          restore-keys: |
            ${{ runner.os }}-composer-

      - name: Install dependencies
        run: composer install --prefer-dist --no-progress --no-interaction

      - name: Run PHP CS Fixer (check mode)
        run: composer cs-diff
CI_EOF

# Create security.yml
cat > .github/workflows/security.yml << 'SECURITY_EOF'
name: Security - Dependency & Vulnerability Scanning

on:
  pull_request:
    branches:
      - main
      - develop
      - master
  push:
    branches:
      - main
      - develop
      - master
  schedule:
    - cron: '0 0 * * 0'
  workflow_dispatch:

permissions:
  contents: read

concurrency:
  group: security-${{ github.workflow }}-${{ github.ref }}
  cancel-in-progress: false

jobs:
  composer-audit:
    name: Composer Audit
    runs-on: ubuntu-latest

    steps:
      - name: Checkout code
        uses: actions/checkout@v4

      - name: Setup PHP
        uses: shivammathur/setup-php@v2
        with:
          php-version: '8.2'
          tools: composer:v2

      - name: Get Composer cache directory
        id: composer-cache
        run: echo "dir=$(composer config cache-files-dir)" >> $GITHUB_OUTPUT

      - name: Cache Composer dependencies
        uses: actions/cache@v4
        with:
          path: ${{ steps.composer-cache.outputs.dir }}
          key: ${{ runner.os }}-composer-${{ hashFiles('**/composer.lock') }}
          restore-keys: |
            ${{ runner.os }}-composer-

      - name: Install dependencies
        run: composer install --prefer-dist --no-progress --no-interaction

      - name: Run composer audit
        run: composer audit --format=json | tee audit-output.json

      - name: Check for vulnerabilities
        id: check-vulnerabilities
        run: |
          VULNERABILITIES=$(cat audit-output.json | jq '.advisories | length')
          echo "vulnerabilities=$VULNERABILITIES" >> $GITHUB_OUTPUT
          if [ "$VULNERABILITIES" -gt 0 ]; then
            echo "::error::Found $VULNERABILITIES security vulnerabilities"
            echo "Run 'composer audit' locally for details"
            exit 1
          else
            echo "✅ No security vulnerabilities found"
          fi

      - name: Upload audit report
        if: always()
        uses: actions/upload-artifact@v4
        with:
          name: composer-audit-report
          path: audit-output.json
          retention-days: 30

  dependency-review:
    name: Dependency Review
    runs-on: ubuntu-latest
    if: github.event_name == 'pull_request'

    permissions:
      contents: read
      pull-requests: read

    steps:
      - name: Checkout code
        uses: actions/checkout@v4

      - name: Dependency Review
        uses: actions/dependency-review-action@v4
        with:
          fail-on-severity: moderate
          deny-licenses: GPL-3.0, AGPL-3.0
SECURITY_EOF

# Create deploy.yml
cat > .github/workflows/deploy.yml << 'DEPLOY_EOF'
name: Deployment

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
      version:
        description: 'Version tag to deploy (leave empty for latest)'
        required: false
        default: ''
        type: string

permissions:
  contents: write
  pull-requests: write

jobs:
  pre-deploy-checks:
    name: Pre-deployment Quality Checks
    runs-on: ubuntu-latest

    steps:
      - name: Checkout code
        uses: actions/checkout@v4
        with:
          ref: ${{ github.event.inputs.version || 'main' }}

      - name: Setup PHP
        uses: shivammathur/setup-php@v2
        with:
          php-version: '8.2'
          tools: composer:v2

      - name: Get Composer cache directory
        id: composer-cache
        run: echo "dir=$(composer config cache-files-dir)" >> $GITHUB_OUTPUT

      - name: Cache Composer dependencies
        uses: actions/cache@v4
        with:
          path: ${{ steps.composer-cache.outputs.dir }}
          key: ${{ runner.os }}-composer-${{ hashFiles('**/composer.lock') }}
          restore-keys: |
            ${{ runner.os }}-composer-

      - name: Install dependencies
        run: composer install --prefer-dist --no-progress --no-interaction

      - name: PHP Syntax Check
        run: |
          find app -name "*.php" -print0 | xargs -0 -n1 php -l

      - name: Create test database
        run: |
          mkdir -p database
          touch database/database.sqlite

      - name: Run tests
        run: composer test
        env:
          APP_ENV: testing
          DB_CONNECTION: sqlite_testing
          CACHE_DRIVER: array

  deploy:
    name: Deploy to ${{ github.event.inputs.environment }}
    runs-on: ubuntu-latest
    needs: pre-deploy-checks
    environment:
      name: ${{ github.event.inputs.environment }}
      url: ${{ github.event.inputs.environment == 'production' && 'https://api.malnu.com' || 'https://api-staging.malnu.com' }}

    steps:
      - name: Deployment notice
        run: |
          echo "🚀 Deploying to ${{ github.event.inputs.environment }}"
          echo "Version: ${{ github.event.inputs.version || 'latest' }}"

      - name: Deployment placeholder
        run: |
          echo "⚠️  Actual deployment steps need to be configured"
          echo "Add your deployment logic here:"
          echo "  - Cloudflare Workers deployment"
          echo "  - Supabase deployment"
          echo "  - SSH deployment to VPS"
          echo "  - Docker container deployment"

      - name: Create GitHub Deployment
        if: success()
        uses: actions/create-github-app-token@v1
        id: app-token
        with:
          app-id: ${{ vars.DEPLOYMENT_APP_ID }}
          private-key: ${{ secrets.DEPLOYMENT_PRIVATE_KEY }}

      - name: Update deployment status
        if: steps.app-token.outputs.token != ''
        env:
          GH_TOKEN: ${{ steps.app-token.outputs.token }}
        run: |
          gh api repos/:owner/:repo/deployments -X POST -f ref="${{ github.event.inputs.version || 'main' }}" -f environment="${{ github.event.inputs.environment }}" -f auto_inactive=true || true
DEPLOY_EOF

# Add to git and commit
git add .github/workflows/ci.yml .github/workflows/security.yml .github/workflows/deploy.yml
git commit -m "feat(ci): Implement CI/CD pipeline with automated testing and quality gates

- Add ci.yml workflow with automated testing, PHPStan static analysis, and PHP CS Fixer checks
- Add security.yml workflow with composer audit and dependency review
- Add deploy.yml workflow with manual deployment triggers and pre-deployment checks

Fixes #134"
git push
```

## Verification

After applying the workflow files, verify:

1. **CI Workflow**:
   - Create a test pull request
   - Check that tests run on the PR
   - Verify coverage is reported

2. **Security Workflow**:
   - Check that `composer audit` runs on PRs
   - Verify weekly scheduled scans work

3. **Deploy Workflow**:
   - Go to Actions tab in GitHub
   - Select "Deployment" workflow
   - Click "Run workflow" with staging environment
   - Verify pre-deployment checks run

## Troubleshooting

### Tests fail in CI
- Check that `composer test` runs locally
- Verify database configuration in phpunit.xml.dist
- Ensure all dependencies are installed

### PHPStan fails
- Run `composer analyse` locally to see errors
- Update phpstan.neon if needed

### Code style fails
- Run `composer cs-fix` locally to fix style issues

### Composer audit fails
- Run `composer audit` locally to see vulnerabilities
- Update affected packages: `composer update package-name`

## Benefits

After implementing these workflows:

- ✅ **Automated Testing**: All PRs automatically run tests before merge
- ✅ **Code Quality**: PHPStan static analysis and PHP CS Fixer prevent low-quality code
- ✅ **Security**: Composer audit and dependency review detect vulnerabilities
- ✅ **Test Coverage**: Coverage reporting provides insights into code quality
- ✅ **Simplified CI/CD**: 3 focused workflows replace 11+ redundant ones

## Related Issues

- Fixes #134 - CRITICAL: Fix CI/CD pipeline and add automated testing
- Supersedes PR #604
- Supersedes PR #625
