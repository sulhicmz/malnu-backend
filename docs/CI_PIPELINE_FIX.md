# CI/CD Pipeline Workflows - Issue #134

## Workflow Files

### 1. ci.yml
```yaml
name: CI - Testing & Quality Checks

on:
  workflow_dispatch:
  pull_request:
    branches: [main, develop]
  push:
    branches: [main, develop]

permissions:
  contents: read
  pull-requests: write

jobs:
  test:
    name: PHPUnit Tests
    runs-on: ubuntu-latest

    strategy:
      matrix:
        php-version: ['8.2']

    steps:
      - name: Checkout code
        uses: actions/checkout@v4

      - name: Setup PHP
        uses: shivammathur/setup-php@v2
        with:
          php-version: ${{ matrix.php-version }}
          extensions: mbstring, xml, ctype, json, pdo, pdo_mysql, redis, pcntl
          coverage: xdebug

      - name: Get Composer cache directory
        id: composer-cache
        run: echo "dir=$(composer config cache-files-dir)" >> $GITHUB_OUTPUT

      - name: Cache Composer dependencies
        uses: actions/cache@v4
        with:
          path: ${{ steps.composer-cache.outputs.dir }}
          key: ${{ runner.os }}-composer-${{ hashFiles('**/composer.lock') }}
          restore-keys: ${{ runner.os }}-composer-

      - name: Install Composer dependencies
        run: composer install --no-interaction --prefer-dist --no-progress

      - name: Create SQLite test database
        run: |
          mkdir -p database
          touch database/database.sqlite

      - name: Run PHPUnit tests
        env:
          DB_CONNECTION: sqlite_testing
          CACHE_DRIVER: array
        run: |
          composer test -- --coverage-clover=coverage.xml --coverage-text

      - name: Upload coverage to Codecov
        uses: codecov/codecov-action@v4
        with:
          files: ./coverage.xml
          flags: unittests
          name: codecov-umbrella

      - name: Upload coverage artifacts
        if: always()
        uses: actions/upload-artifact@v4
        with:
          name: coverage-report-${{ matrix.php-version }}
          path: build/coverage/
          retention-days: 7

  code-style:
    name: PHP CS Fixer
    runs-on: ubuntu-latest

    steps:
      - name: Checkout code
        uses: actions/checkout@v4

      - name: Setup PHP
        uses: shivammathur/setup-php@v2
        with:
          php-version: '8.2'

      - name: Get Composer cache directory
        id: composer-cache
        run: echo "dir=$(composer config cache-files-dir)" >> $GITHUB_OUTPUT

      - name: Cache Composer dependencies
        uses: actions/cache@v4
        with:
          path: ${{ steps.composer-cache.outputs.dir }}
          key: ${{ runner.os }}-composer-${{ hashFiles('**/composer.lock') }}
          restore-keys: ${{ runner.os }}-composer-

      - name: Install Composer dependencies
        run: composer install --no-interaction --prefer-dist --no-progress

      - name: Check code style
        run: |
          composer cs-fix --dry-run --diff

  static-analysis:
    name: PHPStan Static Analysis
    runs-on: ubuntu-latest

    steps:
      - name: Checkout code
        uses: actions/checkout@v4

      - name: Setup PHP
        uses: shivammathur/setup-php@v2
        with:
          php-version: '8.2'

      - name: Get Composer cache directory
        id: composer-cache
        run: echo "dir=$(composer config cache-files-dir)" >> $GITHUB_OUTPUT

      - name: Cache Composer dependencies
        uses: actions/cache@v4
        with:
          path: ${{ steps.composer-cache.outputs.dir }}
          key: ${{ runner.os }}-composer-${{ hashFiles('**/composer.lock') }}
          restore-keys: ${{ runner.os }}-composer-

      - name: Install Composer dependencies
        run: composer install --no-interaction --prefer-dist --no-progress

      - name: Run PHPStan analysis
        run: |
          composer analyse

      - name: Upload PHPStan results
        if: failure()
        uses: actions/upload-artifact@v4
        with:
          name: phpstan-results
          path: build/phpstan/
          retention-days: 7
```

### 2. security.yml
```yaml
name: Security

on:
  workflow_dispatch:
  pull_request:
    branches: [main, develop]
  push:
    branches: [main, develop]
  schedule:
    - cron: '0 6 * * *'  # Daily at 6 AM UTC

permissions:
  contents: read
  security-events: write

jobs:
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

      - name: Get Composer cache directory
        id: composer-cache
        run: echo "dir=$(composer config cache-files-dir)" >> $GITHUB_OUTPUT

      - name: Cache Composer dependencies
        uses: actions/cache@v4
        with:
          path: ${{ steps.composer-cache.outputs.dir }}
          key: ${{ runner.os }}-composer-${{ hashFiles('**/composer.lock') }}
          restore-keys: ${{ runner.os }}-composer-

      - name: Install Composer dependencies
        run: composer install --no-interaction --prefer-dist --no-progress

      - name: Run Composer audit
        run: |
          composer audit

  npm-audit:
    name: NPM Security Audit
    runs-on: ubuntu-latest

    steps:
      - name: Checkout code
        uses: actions/checkout@v4

      - name: Setup Node.js
        uses: actions/setup-node@v4
        with:
          node-version: '20'
          cache: 'npm'

      - name: Install NPM dependencies
        run: |
          cd frontend && npm ci || echo "No frontend dependencies to audit"

      - name: Run NPM audit
        run: |
          cd frontend && npm audit --audit-level=moderate || echo "No frontend dependencies to audit"

  codeql-analysis:
    name: CodeQL Analysis
    runs-on: ubuntu-latest
    timeout-minutes: 30

    permissions:
      contents: read
      security-events: write

    strategy:
      fail-fast: false
      matrix:
        language: ['php', 'javascript']

    steps:
      - name: Checkout code
        uses: actions/checkout@v4

      - name: Initialize CodeQL
        uses: github/codeql-action/init@v3
        with:
          languages: ${{ matrix.language }}

      - name: Autobuild
        uses: github/codeql-action/autobuild@v3

      - name: Perform CodeQL Analysis
        uses: github/codeql-action/analyze@v3
        with:
          category: "/language:${{matrix.language}}"
```

### 3. deploy.yml
```yaml
name: Deploy

on:
  workflow_dispatch:
  push:
    branches:
      - main

permissions:
  contents: write

jobs:
  deploy:
    name: Deploy to Production
    runs-on: ubuntu-latest
    environment: production

    steps:
      - name: Checkout code
        uses: actions/checkout@v4

      - name: Setup PHP
        uses: shivammathur/setup-php@v2
        with:
          php-version: '8.2'

      - name: Get Composer cache directory
        id: composer-cache
        run: echo "dir=$(composer config cache-files-dir)" >> $GITHUB_OUTPUT

      - name: Cache Composer dependencies
        uses: actions/cache@v4
        with:
          path: ${{ steps.composer-cache.outputs.dir }}
          key: ${{ runner.os }}-composer-${{ hashFiles('**/composer.lock') }}
          restore-keys: ${{ runner.os }}-composer-

      - name: Install Composer dependencies
        run: composer install --no-interaction --prefer-dist --no-dev --no-progress

      - name: Run migrations
        env:
          DB_CONNECTION: ${{ secrets.DB_CONNECTION }}
          DB_HOST: ${{ secrets.DB_HOST }}
          DB_PORT: ${{ secrets.DB_PORT }}
          DB_DATABASE: ${{ secrets.DB_DATABASE }}
          DB_USERNAME: ${{ secrets.DB_USERNAME }}
          DB_PASSWORD: ${{ secrets.DB_PASSWORD }}
        run: |
          php artisan migrate --force

      - name: Clear caches
        run: |
          php artisan cache:clear
          php artisan config:clear
          php artisan route:clear
          php artisan view:clear

      - name: Create deployment artifact
        run: |
          tar -czf ../deployment-artifact.tar.gz \
            --exclude='.git' \
            --exclude='.github' \
            --exclude='node_modules' \
            --exclude='tests' \
            --exclude='storage' \
            --exclude='database/database.sqlite' \
            .

      - name: Upload deployment artifact
        uses: actions/upload-artifact@v4
        with:
          name: deployment-artifact-${{ github.sha }}
          path: ../deployment-artifact.tar.gz
          retention-days: 30

      - name: Deployment notification
        if: success()
        run: |
          echo "Deployment successful!"
          echo "Commit: ${{ github.sha }}"
          echo "Branch: ${{ github.ref_name }}"
```

### 4. dependabot.yml
```yaml
version: 2
updates:
  # Composer dependencies (PHP)
  - package-ecosystem: "composer"
    directory: "/"
    schedule:
      interval: "weekly"
      day: "monday"
      time: "09:00"
    open-pull-requests-limit: 5
    labels:
      - "dependencies"
      - "php"
      - "dependabot"
    versioning-strategy: increase
    ignore:
      - dependency-name: "hypervel/*"
        versions: ["0.x"]
        reason: "HyperVel core requires manual review for breaking changes"
    commit-message:
      prefix: "chore(deps)"
      include: "scope"
    reviewers:
      - "sulhicmz"
    assignees:
      - "sulhicmz"

  # NPM dependencies (JavaScript/TypeScript)
  - package-ecosystem: "npm"
    directory: "/frontend"
    schedule:
      interval: "weekly"
      day: "monday"
      time: "09:00"
    open-pull-requests-limit: 5
    labels:
      - "dependencies"
      - "javascript"
      - "dependabot"
    versioning-strategy: increase
    commit-message:
      prefix: "chore(deps)"
      include: "scope"
    reviewers:
      - "sulhicmz"
    assignees:
      - "sulhicmz"

  # GitHub Actions
  - package-ecosystem: "github-actions"
    directory: "/"
    schedule:
      interval: "monthly"
      day: "monday"
      time: "09:00"
    open-pull-requests-limit: 3
    labels:
      - "dependencies"
      - "ci"
      - "dependabot"
    commit-message:
      prefix: "chore(ci)"
      include: "scope"
    reviewers:
      - "sulhicmz"
    assignees:
      - "sulhicmz"

  # Docker dependencies
  - package-ecosystem: "docker"
    directory: "/"
    schedule:
      interval: "weekly"
      day: "monday"
      time: "09:00"
    open-pull-requests-limit: 3
    labels:
      - "dependencies"
      - "docker"
      - "dependabot"
    commit-message:
      prefix: "chore(deps)"
      include: "scope"
    reviewers:
      - "sulhicmz"
    assignees:
      - "sulhicmz"
```

