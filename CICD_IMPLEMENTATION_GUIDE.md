# CI/CD Pipeline Implementation for Issue #134

This directory contains implementation-ready GitHub Actions workflow files for fixing the CI/CD pipeline.

## Overview

Issue #134 requires consolidating 11+ redundant workflows into 3 essential workflows:
1. **ci.yml** - Testing and Quality Checks
2. **security.yml** - Security Scanning
3. **deploy.yml** - Deployment Automation

## Installation Steps for Maintainers

**IMPORTANT**: GitHub App (app/github-actions) lacks `workflows` permission to create/update workflow files directly. A repository maintainer with `workflows` permission must perform these steps manually.

### Step 1: Copy Workflow Files

Copy the following files to `.github/workflows/`:

```bash
cp CICD_WORKFLOW_CI.yml .github/workflows/ci.yml
cp CICD_WORKFLOW_SECURITY.yml .github/workflows/security.yml
cp CICD_WORKFLOW_DEPLOY.yml .github/workflows/deploy.yml
```

### Step 2: Disable Redundant Workflows (Optional)

Disable all OpenCode-related workflows by renaming with `.disabled` suffix:

```bash
cd .github/workflows
mv oc- researcher.yml oc- researcher.yml.disabled
mv oc-cf-supabase.yml oc-cf-supabase.yml.disabled
mv oc-pr-handler.yml oc-pr-handler.yml.disabled
mv oc-issue-solver.yml oc-issue-solver.yml.disabled
mv oc-maintainer.yml oc-maintainer.yml.disabled
mv oc-problem-finder.yml oc-problem-finder.yml.disabled
mv on-push.yml on-push.yml.disabled
mv on-pull.yml on-pull.yml.disabled
mv workflow-monitor.yml workflow-monitor.yml.disabled
mv openhands.yml openhands.yml.disabled
```

### Step 3: Commit and Push

```bash
git add .github/workflows/
git commit -m "feat(ci): Implement CI/CD pipeline with automated testing and quality gates

Add consolidated CI/CD workflows to address issue #134:

1. **ci.yml** - Testing and Quality Checks
   - Automated PHPUnit testing with coverage
   - PHPStan static analysis (level 5)
   - PHP CS Fixer code style checks
   - PHP version matrix (8.2, 8.3)
   - Uploads coverage to Codecov

2. **security.yml** - Security Scanning
   - Composer audit for dependency vulnerabilities
   - CodeQL analysis for JavaScript
   - Dependency review for PRs
   - Weekly scheduled scans

3. **deploy.yml** - Deployment Automation
   - Manual deployment with environment selection
   - Pre-deployment quality gates
   - Placeholder for Cloudflare/Supabase deployment

4. **Disabled Redundant Workflows**
   Disabled all OpenCode workflows with .disabled suffix

Fixes #134"
git push origin <branch-name>
```

### Step 4: Create Pull Request

Create a pull request from your branch with the title:
`feat(ci): Implement CI/CD pipeline with automated testing and quality gates`

Link to issue #134 in the description.

## Workflow Details

### ci.yml - Testing and Quality Checks

Triggers on:
- Push to main/master/develop branches
- Pull requests to main/master/develop branches

Jobs:
1. **test** - Runs PHPUnit tests with coverage on PHP 8.2 and 8.3
2. **phpstan** - Runs PHPStan static analysis (level 5)
3. **php-cs-fixer** - Runs PHP CS Fixer code style checks

### security.yml - Security Scanning

Triggers on:
- Push to main/master/develop branches
- Pull requests to main/master/develop branches
- Weekly scheduled scan (every Sunday at midnight)

Jobs:
1. **composer-audit** - Checks for dependency vulnerabilities
2. **dependency-review** - Reviews dependencies in PRs (PRs only)
3. **codeql** - Runs CodeQL analysis on JavaScript

### deploy.yml - Deployment Automation

Triggers on:
- Manual workflow dispatch only (requires user action)

Jobs:
1. **deploy** - Manual deployment to staging or production with pre-deployment quality gates

## Benefits After Implementation

- ✅ Automated Testing: All PRs and pushes run tests automatically
- ✅ Code Quality: PHPStan and PHP CS Fixer prevent low-quality code
- ✅ Security: Composer audit and CodeQL detect vulnerabilities
- ✅ Simplified CI/CD: Reduced from 11 workflows to 3 essential ones
- ✅ Better Visibility: Test coverage reporting provides insights

## Related Issues

- Fixes #134
- Supersedes PR #604 (provides actual workflow files instead of just documentation)
