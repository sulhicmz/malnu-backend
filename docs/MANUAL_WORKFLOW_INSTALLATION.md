# Manual Workflow Installation Instructions

Due to GitHub App restrictions on workflow file modifications, the new consolidated workflows need to be installed manually. Follow these steps:

## Files to Create

Create the following files in your repository:

### 1. .github/workflows/ci.yml

```yaml
name: CI

on:
  pull_request:
    branches: [main, master, develop]
  push:
    branches: [main, master, develop]

jobs:
  test:
    name: PHPUnit Tests
    runs-on: ubuntu-latest
    
    steps:
      - name: Checkout code
        uses: actions/checkout@v4
      
      - name: Setup PHP
        uses: shivammathur/setup-php@v2
        with:
          php-version: '8.2'
          extensions: mbstring, pdo, pdo_mysql, redis, json, bcmath
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
      
      - name: Copy environment file
        run: cp .env.example .env
      
      - name: Execute tests
        run: composer test -- --coverage-text --coverage-clover=coverage.xml
      
      - name: Upload coverage to Codecov
        uses: codecov/codecov-action@v4
        with:
          files: ./coverage.xml
          flags: phpunit
          name: codecov-umbrella
          fail_ci_if_error: false

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
          tools: composer:v2, php-cs-fixer
      
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
      
      - name: Run PHP CS Fixer
        run: composer cs-diff

  static-analysis:
    name: PHPStan
    runs-on: ubuntu-latest
    
    steps:
      - name: Checkout code
        uses: actions/checkout@v4
      
      - name: Setup PHP
        uses: shivammathur/setup-php@v2
        with:
          php-version: '8.2'
          tools: composer:v2, phpstan
      
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

  security-scan:
    name: Security Audit
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
      
      - name: Run security audit
        run: composer audit
```

### 2. .github/workflows/pr-automation.yml

```yaml
name: PR Automation

on:
  pull_request:
    types: [opened, synchronize, reopened, edited]
  pull_request_review:
    types: [submitted, edited, dismissed]
  pull_request_target:
    types: [ready_for_review, converted_to_draft]

permissions:
  contents: read
  pull-requests: write
  issues: write

jobs:
  validate-pr:
    name: Validate PR
    runs-on: ubuntu-latest
    if: github.event_name == 'pull_request'
    
    steps:
      - name: Checkout code
        uses: actions/checkout@v4
      
      - name: Check PR description
        run: |
          if [ -z "${{ github.event.pull_request.body }}" ]; then
            echo "PR description is required"
            exit 1
          fi
      
      - name: Check linked issues
        run: |
          if ! echo "${{ github.event.pull_request.body }}" | grep -qE "#[0-9]+|Fixes #|Closes #|Resolves #"; then
            echo "PR should link to an issue using 'Fixes #', 'Closes #', or 'Resolves #'"
            exit 1
          fi

  auto-label:
    name: Auto-label PR
    runs-on: ubuntu-latest
    if: github.event_name == 'pull_request'
    
    steps:
      - name: Checkout code
        uses: actions/checkout@v4
      
      - name: Label based on changed files
        uses: actions/labeler@v5
        with:
          repo-token: ${{ secrets.GITHUB_TOKEN }}
          configuration-path: .github/labeler.yml
          sync-labels: true

  check-mergeability:
    name: Check Mergeability
    runs-on: ubuntu-latest
    if: github.event_name == 'pull_request' || github.event_name == 'pull_request_review'
    
    steps:
      - name: Check for merge conflicts
        uses: eps1lon/status-check-action@v2.0.0
        with:
          checkName: Merge conflicts check
          label: 'status: conflict'
          skipStatusCheck: false
          verbose: true
        env:
          GITHUB_TOKEN: ${{ secrets.GITHUB_TOKEN }}

  check-commits:
    name: Check Commit Messages
    runs-on: ubuntu-latest
    if: github.event_name == 'pull_request'
    
    steps:
      - name: Checkout code
        uses: actions/checkout@v4
        with:
          fetch-depth: 0
      
      - name: Check commit messages
        uses: wagoid/commitlint-github-action@v6
        with:
          configFile: .commitlintrc.js
          failOnWarnings: true
        env:
          GITHUB_TOKEN: ${{ secrets.GITHUB_TOKEN }}

  size-check:
    name: PR Size Check
    runs-on: ubuntu-latest
    if: github.event_name == 'pull_request'
    
    steps:
      - name: Checkout code
        uses: actions/checkout@v4
        with:
          fetch-depth: 0
      
      - name: Get changed files
        id: changed-files
        uses: tj-actions/changed-files@v45
        with:
          files_separator: ','
      
      - name: Count changed lines
        run: |
          ADDED=$(git diff --shortstat origin/${{ github.base_ref }} | grep -oP '\d+(?= insertion)' || echo 0)
          DELETED=$(git diff --shortstat origin/${{ github.base_ref }} | grep -oP '\d+(?= deletion)' || echo 0)
          TOTAL=$((ADDED + DELETED))
          echo "Total changes: $TOTAL lines"
          
          if [ $TOTAL -gt 500 ]; then
            echo "Warning: Large PR detected ($TOTAL lines). Consider splitting into smaller PRs."
          fi
```

### 3. .github/workflows/maintenance.yml

```yaml
name: Maintenance

on:
  schedule:
    - cron: '0 2 * * *'
  workflow_dispatch:
    inputs:
      task:
        description: 'Maintenance task to run'
        required: true
        default: 'dependencies'
        type: choice
        options:
          - dependencies
          - cleanup
          - docs
          - all

permissions:
  contents: write
  pull-requests: write
  issues: write

jobs:
  dependencies:
    name: Check Dependencies
    runs-on: ubuntu-latest
    if: github.event.inputs.task == 'dependencies' || github.event.inputs.task == 'all' || github.event_name == 'schedule'
    
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
      
      - name: Check for outdated packages
        run: composer outdated --direct
      
      - name: Check for security vulnerabilities
        run: composer audit
      
      - name: Create issue if vulnerabilities found
        if: failure()
        uses: actions/github-script@v7
        with:
          github-token: ${{ secrets.GITHUB_TOKEN }}
          script: |
            const { data: issues } = await github.rest.issues.listForRepo({
              owner: context.repo.owner,
              repo: context.repo.repo,
              state: 'open',
              labels: 'security,dependencies'
            });
            
            if (issues.length === 0) {
              await github.rest.issues.create({
                owner: context.repo.owner,
                repo: context.repo.repo,
                title: 'Security vulnerabilities detected in dependencies',
                body: 'Composer audit found security vulnerabilities. Please update dependencies.',
                labels: ['security', 'dependencies', 'high-priority']
              });
            }

  cleanup:
    name: Repository Cleanup
    runs-on: ubuntu-latest
    if: github.event.inputs.task == 'cleanup' || github.event.inputs.task == 'all'
    
    steps:
      - name: Checkout code
        uses: actions/checkout@v4
      
      - name: Setup Node.js
        uses: actions/setup-node@v4
        with:
          node-version: '20'
          cache: 'npm'
      
      - name: Install dependencies
        run: npm ci
      
      - name: Check for stale branches
        uses: actions/github-script@v7
        with:
          github-token: ${{ secrets.GITHUB_TOKEN }}
          script: |
            const branches = await github.rest.repos.listBranches({
              owner: context.repo.owner,
              repo: context.repo.repo
            });
            
            const protectedBranches = ['main', 'master', 'develop'];
            const staleThreshold = 30 * 24 * 60 * 60 * 1000; // 30 days
            
            for (const branch of branches.data) {
              if (protectedBranches.includes(branch.name)) continue;
              
              const branchData = await github.rest.repos.getBranch({
                owner: context.repo.owner,
                repo: context.repo.repo,
                branch: branch.name
              });
              
              const lastCommit = new Date(branchData.data.commit.commit.committer.date);
              const age = Date.now() - lastCommit.getTime();
              
              if (age > staleThreshold) {
                console.log(`Branch ${branch.name} is stale (${Math.round(age / (24 * 60 * 60 * 1000))} days)`);
              }
            }

  docs:
    name: Documentation Sync
    runs-on: ubuntu-latest
    if: github.event.inputs.task == 'docs' || github.event.inputs.task == 'all'
    
    steps:
      - name: Checkout code
        uses: actions/checkout@v4
      
      - name: Check for broken links
        uses: gaurav-nelson/github-action-markdown-link-check@v1
        with:
          use-quiet-mode: 'yes'
          use-verbose-mode: 'yes'
          config-file: '.github/link-check-config.json'
        continue-on-error: true
      
      - name: Check README links
        run: |
          if grep -qE '\[.*\]\([^)]+\)' README.md; then
            echo "README contains links - verify they are working"
          fi
      
      - name: Check for outdated documentation
        uses: actions/github-script@v7
        with:
          github-token: ${{ secrets.GITHUB_TOKEN }}
          script: |
            const fs = require('fs');
            const docsPath = 'docs';
            
            if (fs.existsSync(docsPath)) {
              const files = fs.readdirSync(docsPath);
              console.log(`Found ${files.length} documentation files`);
              
              // Check for recently modified files
              const thirtyDaysAgo = Date.now() - (30 * 24 * 60 * 60 * 1000);
              
              for (const file of files) {
                const filePath = `${docsPath}/${file}`;
                const stats = fs.statSync(filePath);
                
                if (stats.mtime.getTime() < thirtyDaysAgo) {
                  console.log(`Warning: ${file} hasn't been updated in 30+ days`);
                }
              }
            }

  workflow-report:
    name: Generate Maintenance Report
    runs-on: ubuntu-latest
    if: always() && github.event_name == 'schedule'
    needs: [dependencies, cleanup, docs]
    
    steps:
      - name: Checkout code
        uses: actions/checkout@v4
      
      - name: Generate report
        run: |
          echo "# Maintenance Report - $(date +%Y-%m-%d)" > maintenance-report.md
          echo "" >> maintenance-report.md
          echo "## Summary" >> maintenance-report.md
          echo "- Dependencies check: ${{ needs.dependencies.result }}" >> maintenance-report.md
          echo "- Repository cleanup: ${{ needs.cleanup.result }}" >> maintenance-report.md
          echo "- Documentation sync: ${{ needs.docs.result }}" >> maintenance-report.md
          echo "" >> maintenance-report.md
          echo "## Actions Taken" >> maintenance-report.md
          echo "See individual job logs for details." >> maintenance-report.md
      
      - name: Upload report as artifact
        uses: actions/upload-artifact@v4
        with:
          name: maintenance-report
          path: maintenance-report.md
          retention-days: 30
```

### 4. .github/labeler.yml

```yaml
docs:
  - changed:
      - 'docs/**/*'
      - '*.md'
frontend:
  - changed:
      - 'frontend/**/*'
backend:
  - changed:
      - 'app/**/*'
      - 'config/**/*'
      - 'routes/**/*'
tests:
  - changed:
      - 'tests/**/*'
ci:
  - changed:
      - '.github/workflows/**/*'
dependencies:
  - changed:
      - 'composer.json'
      - 'composer.lock'
      - 'package.json'
      - 'package-lock.json'
database:
  - changed:
      - 'database/**/*'
      - 'migrations/**/*'
```

### 5. .commitlintrc.js

```javascript
module.exports = {
  extends: ['@commitlint/config-conventional'],
  rules: {
    'type-enum': [2, 'always', [
      'feat',
      'fix',
      'docs',
      'style',
      'refactor',
      'perf',
      'test',
      'build',
      'ci',
      'chore',
      'revert'
    ]],
    'type-case': [2, 'always', 'lower-case'],
    'subject-case': [0],
    'subject-empty': [2, 'never'],
    'subject-full-stop': [2, 'never', '.'],
    'header-max-length': [2, 'always', 72],
    'body-max-line-length': [2, 'always', 100]
  }
};
```

### 6. .github/link-check-config.json

```json
{
  "ignorePatterns": [
    {
      "pattern": "^http://localhost"
    },
    {
      "pattern": "^http://127.0.0.1"
    },
    {
      "pattern": "^http://example.com"
    },
    {
      "pattern": "^https://github\\.com/.*\\/"
    }
  ],
  "aliveStatusCodes": [200, 206, 301, 302, 303, 307, 308],
  "retryOn429": true,
  "retryCount": 5,
  "fallbackRetryDelay": "10000",
  "timeout": "20000"
}
```

## Installation Steps

1. Create all 6 files listed above in your repository
2. Commit these files
3. Create a pull request with title: "feat: Consolidate GitHub Actions workflows"
4. Reference issue #225 in the PR description
5. After merging, remove the old workflow files

## Old Workflows to Remove

After the new workflows are working, remove these files:
- .github/workflows/oc-problem-finder.yml
- .github/workflows/oc-pr-handler.yml
- .github/workflows/oc-issue-solver.yml
- .github/workflows/oc-maintainer.yml
- .github/workflows/oc-cf-supabase.yml
- .github/workflows/on-pull.yml
- .github/workflows/on-push.yml
- .github/workflows/openhands.yml

## Verification

After installation, verify that:
1. All 3 new workflows appear in Actions tab
2. PRs trigger CI checks and PR automation
3. Maintenance workflow runs daily or can be triggered manually
4. Old workflows are removed after confirming new ones work

## Next Steps

See `docs/WORKFLOW_CONSOLIDATION.md` for comprehensive documentation about:
- Workflow benefits
- Migration guide
- Troubleshooting
- Future improvements
