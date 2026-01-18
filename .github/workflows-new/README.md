# CI/CD Pipeline Migration Guide

## Overview

This PR replaces 10 redundant OpenCode automation workflows with 3 essential CI/CD workflows for automated testing, code quality checks, and security scanning.

## Files Removed

### Old OpenCode Workflows (redundant, no actual testing):
- `oc-researcher.yml` - OpenCode researcher automation
- `oc-cf-supabase.yml` - OpenCode Cloudflare/Supabase integration
- `oc-issue-solver.yml` - OpenCode issue solver automation
- `oc-maintainer.yml` - OpenCode maintainer automation
- `oc-pr-handler.yml` - OpenCode PR handler automation
- `oc-problem-finder.yml` - OpenCode problem finder automation
- `on-pull.yml` - Pull request automation
- `on-push.yml` - Push event automation
- `openhands.yml` - OpenHands integration
- `workflow-monitor.yml` - Workflow monitoring

### OpenCode Prompts (no longer needed):
- `.github/prompt/00.md` through `.github/prompt/11.md` - Prompt templates
- `.github/prompt/README.md` - Prompts documentation

## New Workflows (in `.github/workflows-new/`)

### 1. `ci.yml` - Testing and Quality Checks

**Triggers:**
- Push to `main`, `develop`, `dev` branches
- Pull requests to `main`, `develop`, `dev` branches

**Jobs:**
- **test**: Runs PHPUnit tests with code coverage
  - PHP 8.2
  - Uses SQLite in-memory database
  - Uploads coverage to Codecov
- **phpstan**: PHPStan static analysis
  - Level 5 (as configured in phpstan.neon)
- **phpcsfixer**: PHP CS Fixer code style checks
  - Runs in dry-run mode to detect style issues

### 2. `security.yml` - Security Scanning

**Triggers:**
- Push to `main`, `develop`, `dev` branches
- Pull requests to `main`, `develop`, `dev` branches
- Weekly cron job on Sundays

**Jobs:**
- **composer-audit**: Dependency vulnerability scanning
- **dependency-review**: Dependency review for pull requests (moderate severity)

### 3. `deploy.yml` - Deployment Automation

**Triggers:**
- Push to `main` branch
- Manual workflow dispatch

**Jobs:**
- **deploy**: Production deployment
  - Installs production dependencies
  - Placeholder for actual deployment steps
  - Currently outputs deployment instructions

## Implementation Steps (for Maintainers)

Due to GitHub App permission restrictions that prevent automated workflow file updates, a repository maintainer needs to manually complete the migration:

1. **Review the new workflow files** in `.github/workflows-new/`:
   - `ci.yml` - Testing and quality gates
   - `security.yml` - Security scanning
   - `deploy.yml` - Deployment automation

2. **Move the workflow files** to their final location:
   ```bash
   mv .github/workflows-new/ci.yml .github/workflows/
   mv .github/workflows-new/deploy.yml .github/workflows/
   mv .github/workflows-new/security.yml .github/workflows/
   rm -rf .github/workflows-new/
   ```

3. **Verify the workflows**:
   - Check that all 3 workflows appear in `.github/workflows/`
   - Confirm old workflow files are deleted
   - No other workflow files should remain

4. **Commit and push** the workflow changes:
   ```bash
   git add .github/workflows/
   git commit -m "Apply new CI/CD workflow files"
   git push
   ```

5. **Close this PR** after confirming workflows are working

## Benefits

- **Automated Testing**: PHPUnit tests run on every push and PR
- **Code Quality Gates**: PHPStan and PHP CS Fixer enforce quality standards
- **Security Scanning**: Weekly automated vulnerability detection
- **Coverage Reporting**: Insight into test coverage metrics
- **Reduced Complexity**: From 10 workflows to 3 focused, essential workflows
- **Pre-deployment Checks**: Tests and analysis must pass before deployment

## Notes

- Uses existing configuration files (phpunit.xml.dist, phpstan.neon, .php-cs-fixer.php)
- Uses existing composer scripts (test, analyse, cs-fix)
- SQLite in-memory database for fast, isolated test execution
- Coverage reporting with clover XML format
- No changes to application code - only CI/CD infrastructure

## Related Issues

Fixes #134
