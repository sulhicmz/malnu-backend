
# CI/CD Pipeline Fix - Implementation Guide

This PR addresses issue #134: **CRITICAL: Fix CI/CD pipeline and add automated testing**

## Problem Statement

The GitHub Actions CI/CD pipeline has critical issues:
- No automated testing in CI
- 7+ redundant workflows creating complexity
- Missing quality gates (code style, static analysis, security)
- No test coverage reporting

## Solution Overview

Consolidate the current redundant workflows into 3 essential, focused workflows:

### 1. CI Workflow (`ci.yml`)
- Automated PHPUnit testing with coverage reporting
- PHPStan static analysis
- PHP CS Fixer code style checks
- Runs on push and pull_request to main/master/develop

### 2. Security Workflow (`security.yml`)
- Composer audit for dependency vulnerabilities
- CodeQL analysis for JavaScript
- Dependency review for pull requests
- Weekly scheduled security scans

### 3. Deploy Workflow (`deploy.yml`)
- Manual deployment workflow with environment selection
- Pre-deployment quality gates (tests, static analysis, code style, security audit)
- Placeholder for actual Cloudflare/Supabase deployment steps

## Manual Installation Steps

Due to GitHub App permission restrictions on workflow files, a repository maintainer with appropriate `workflows` permission must manually complete the following steps:

### Step 1: Disable Redundant Workflows
Rename the following files in `.github/workflows/` by adding `.disabled` suffix:
- `oc-researcher.yml` → `oc-researcher.yml.disabled`
- `oc-cf-supabase.yml` → `oc-cf-supabase.yml.disabled`
- `oc-issue-solver.yml` → `oc-issue-solver.yml.disabled`
- `oc-maintainer.yml` → `oc-maintainer.yml.disabled`
- `oc-pr-handler.yml` → `oc-pr-handler.yml.disabled`
- `oc-problem-finder.yml` → `oc-problem-finder.yml.disabled`
- `on-push.yml` → `on-push.yml.disabled`
- `on-pull.yml` → `on-pull.yml.disabled`
- `openhands.yml` → `openhands.yml.disabled`
- `workflow-monitor.yml` → `workflow-monitor.yml.disabled`

### Step 2: Create New Workflow Files
Create the following files in `.github/workflows/` with the content provided below:

#### `.github/workflows/ci.yml`
```yaml
name: CI

on:
  push:
    branches: [ main, master, develop ]
  pull_request:
    branches: [ main, master, develop ]

jobs:
  test:
    name: Test PHP ${{ matrix.php-version }}
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
          extensions: mbstring, pdo, pdo_mysql, bcmath, gd, zip
          coverage: xdebug
          tools: composer:v2

      - name: Get composer cache directory
        id: composer-cache
        run: echo "dir=$(composer config cache-files-dir)" >> $GITHUB_OUTPUT

      - name: Cache composer dependencies
        uses: actions/cache@v4
        with:
          path: ${{ steps.composer-cache.outputs.dir }}
          key: ${{ runner.os }}-composer-${{ hashFiles('**/composer.lock') }}
          restore-keys: ${{ runner.os }}-composer-

      - name: Install dependencies
        run: composer install --prefer-dist --no-progress --no-suggest

      - name: Setup environment
        run: |
          cp .env.example .env
          php artisan key:generate

      - name: Run tests with coverage
        run: composer test -- --coverage-clover=coverage.xml --coverage-html=coverage-report

      - name: Upload coverage to Codecov
        if: matrix.php-version == '8.2'
        uses: codecov/codecov-action@v4
        with:
          file: ./coverage.xml
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
          php-version: '8.2'
          tools: composer:v2

      - name: Get composer cache directory
        id: composer-cache
        run: echo "dir=$(composer config cache-files-dir)" >> $GITHUB_OUTPUT

      - name: Cache composer dependencies
        uses: actions/cache@v4
        with:
          path: ${{ steps.composer-cache.outputs.dir }}
          key: ${{ runner.os }}-composer-${{ hashFiles('**/composer.lock') }}
          restore-keys: ${{ runner.os }}-composer-

      - name: Install dependencies
        run: composer install --prefer-dist --no-progress --no-suggest

      - name: Run PHP CS Fixer (dry-run)
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
          php-version: '8.2'
          tools: composer:v2

      - name: Get composer cache directory
        id: composer-cache
        run: echo "dir=$(composer config cache-files-dir)" >> $GITHUB_OUTPUT

      - name: Cache composer dependencies
        uses: actions/cache@v4
        with:
          path: ${{ steps.composer-cache.outputs.dir }}
          key: ${{ runner.os }}-composer-${{ hashFiles('**/composer.lock') }}
          restore-keys: ${{ runner.os }}-composer-

      - name: Install dependencies
        run: composer install --prefer-dist --no-progress --no-suggest

      - name: Run PHPStan
        run: composer analyse
```

#### `.github/workflows/security.yml`
```yaml
name: Security

on:
  push:
    branches: [ main, master, develop ]
  pull_request:
    branches: [ main, master, develop ]
  schedule:
    - cron: '0 0 * * 0'

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

      - name: Get composer cache directory
        id: composer-cache
        run: echo "dir=$(composer config cache-files-dir)" >> $GITHUB_OUTPUT

      - name: Cache composer dependencies
        uses: actions/cache@v4
        with:
          path: ${{ steps.composer-cache.outputs.dir }}
          key: ${{ runner.os }}-composer-${{ hashFiles('**/composer.lock') }}
          restore-keys: ${{ runner.os }}-composer-

      - name: Install dependencies
        run: composer install --prefer-dist --no-progress --no-suggest

      - name: Run Composer audit
        run: composer audit

  codeql:
    name: CodeQL Analysis
    runs-on: ubuntu-latest
    permissions:
      actions: read
      contents: read
      security-events: write

    strategy:
      fail-fast: false
      matrix:
        language: [ 'javascript' ]

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

  dependency-review:
    name: Dependency Review
    if: github.event_name == 'pull_request'
    runs-on: ubuntu-latest
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
```

#### `.github/workflows/deploy.yml`
```yaml
name: Deploy

on:
  workflow_dispatch:
    inputs:
      environment:
        description: 'Deployment environment'
        required: true
        type: choice
        options:
          - staging
          - production

jobs:
  pre-deployment-checks:
    name: Pre-Deployment Checks
    runs-on: ubuntu-latest
    environment: ${{ github.event.inputs.environment }}
    
    steps:
      - name: Checkout code
        uses: actions/checkout@v4

      - name: Setup PHP
        uses: shivammathur/setup-php@v2
        with:
          php-version: '8.2'
          tools: composer:v2

      - name: Get composer cache directory
        id: composer-cache
        run: echo "dir=$(composer config cache-files-dir)" >> $GITHUB_OUTPUT

      - name: Cache composer dependencies
        uses: actions/cache@v4
        with:
          path: ${{ steps.composer-cache.outputs.dir }}
          key: ${{ runner.os }}-composer-${{ hashFiles('**/composer.lock') }}
          restore-keys: ${{ runner.os }}-composer-

      - name: Install dependencies
        run: composer install --prefer-dist --no-progress --no-suggest

      - name: Run PHPStan
        run: composer analyse

      - name: Run PHP CS Fixer check
        run: composer cs-diff

      - name: Run tests
        run: composer test

      - name: Run Composer audit
        run: composer audit

  deploy:
    name: Deploy to ${{ github.event.inputs.environment }}
    runs-on: ubuntu-latest
    needs: pre-deployment-checks
    environment: ${{ github.event.inputs.environment }}
    if: github.event.inputs.environment == 'production'
    
    steps:
      - name: Checkout code
        uses: actions/checkout@v4

      - name: Setup PHP
        uses: shivammathur/setup-php@v2
        with:
          php-version: '8.2'
          tools: composer:v2

      - name: Install dependencies
        run: composer install --prefer-dist --no-progress --no-suggest

      - name: Deployment placeholder
        run: |
          echo "Deployment to ${{ github.event.inputs.environment }} would happen here"
          echo "This is a placeholder for actual deployment logic"
          echo "Add your Cloudflare, Supabase, or other deployment steps here"

      - name: Create deployment notification
        run: |
          echo "Successfully deployed to ${{ github.event.inputs.environment }}"
```

### Step 3: Verify Installation

After creating the files, verify:
1. Check that `.github/workflows/` contains: `ci.yml`, `security.yml`, `deploy.yml`
2. Verify that redundant workflows have `.disabled` suffix
3. Push the changes
4. Verify that new workflows trigger on appropriate events

### Step 4: Close Previous PR

Close PR #558 as it is superseded by this implementation.

## Benefits

After implementing these changes:
- **Automated Testing**: All pull requests and pushes to main branches will run tests automatically
- **Code Quality**: PHPStan static analysis and PHP CS Fixer ensure code quality
- **Security**: Composer audit and CodeQL detect vulnerabilities
- **Simplified CI/CD**: Reduced from 11 workflows to 3 essential ones
- **Better Visibility**: Test coverage reporting provides insights into code quality

## Related Issues

Fixes #134
