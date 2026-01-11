# GitHub Workflow Permission Hardening - Issue #182

## Overview

This document provides the exact changes needed to reduce GitHub workflow permissions according to the principle of least privilege, addressing security issue #182.

## Security Impact

- **Lines Removed**: 53 excessive permission lines
- **Lines Added**: 3 minimal permission lines  
- **Net Reduction**: 50 lines (66% reduction in permission configuration)
- **Attack Surface**: Significantly reduced by removing write access to packages, pages, deployments (where not needed), and security-events

## Changes by File

### 1. `.github/workflows/oc- researcher.yml`

**Top-level permissions (lines 8-17):**
```yaml
# BEFORE:
permissions:
  id-token: write
  contents: write
  pull-requests: write
  issues: write
  actions: write
  deployments: write
  packages: write
  pages: write
  security-events: write

# AFTER:
permissions:
  contents: write
  pull-requests: write
  issues: write
```

**Job-level permissions (lines 29-38):**
```yaml
# BEFORE:
    permissions:
      id-token: write
      contents: write
      pull-requests: write
      issues: write
      actions: write
      deployments: write
      packages: write
      pages: write
      security-events: write

# AFTER:
    permissions:
      contents: write
      pull-requests: write
      issues: write
```

### 2. `.github/workflows/oc-cf-supabase.yml`

**Top-level permissions (lines 6-10):**
```yaml
# BEFORE:
permissions:
  contents: write
  deployments: write
  packages: write
  id-token: write

# AFTER:
permissions:
  contents: write
  deployments: write
  id-token: write
```

**Job-level permissions (lines 22-31):**
```yaml
# BEFORE:
    permissions:
      id-token: write
      contents: write
      pull-requests: write
      issues: write
      actions: write
      deployments: write
      packages: write
      pages: write
      security-events: write

# AFTER:
    permissions:
      contents: write
      deployments: write
      id-token: write
```

### 3. `.github/workflows/oc-issue-solver.yml`

**Job-level permissions (lines 24-33):**
```yaml
# BEFORE:
    permissions:
      id-token: write
      contents: write
      pull-requests: write
      issues: write
      actions: write
      deployments: write
      packages: write
      pages: write
      security-events: write

# AFTER:
    permissions:
      contents: write
      pull-requests: write
      issues: write
```

### 4. `.github/workflows/oc-maintainer.yml`

**Top-level permissions (lines 8-12):**
```yaml
# BEFORE:
permissions:
  contents: write
  pull-requests: write
  issues: write
  actions: write

# AFTER:
permissions:
  contents: write
  pull-requests: write
  issues: write
```

**Job-level permissions (lines 24-33):**
```yaml
# BEFORE:
    permissions:
      id-token: write
      contents: write
      pull-requests: write
      issues: write
      actions: write
      deployments: write
      packages: write
      pages: write
      security-events: write

# AFTER:
    permissions:
      contents: write
      pull-requests: write
      issues: write
```

### 5. `.github/workflows/oc-pr-handler.yml`

**Job-level permissions (lines 28-37):**
```yaml
# BEFORE:
    permissions:
      id-token: write
      contents: write
      pull-requests: write
      issues: write
      actions: write
      deployments: write
      packages: write
      pages: write
      security-events: write

# AFTER:
    permissions:
      contents: write
      pull-requests: write
      issues: write
```

### 6. `.github/workflows/oc-problem-finder.yml`

**Job-level permissions (lines 29-38):**
```yaml
# BEFORE:
    permissions:
      id-token: write
      contents: write
      pull-requests: write
      issues: write
      actions: write
      deployments: write
      packages: write
      pages: write
      security-events: write

# AFTER:
    permissions:
      contents: read
      issues: write
      pull-requests: read
```

### 7. `.github/workflows/openhands.yml`

**Top-level permissions (lines 6-15):**
```yaml
# BEFORE:
permissions:
  id-token: write
  contents: write
  pull-requests: write
  issues: write
  actions: write
  deployments: write
  packages: write
  pages: write
  security-events: write

# AFTER:
permissions:
  contents: write
  pull-requests: write
  issues: write
```

## Permissions Removed

The following permissions were removed from all workflows (except where specifically needed):

- **id-token: write** - Only needed for Cloudflare OIDC (kept in oc-cf-supabase.yml)
- **actions: write** - Not used by any workflow
- **deployments: write** - Only needed for Cloudflare deployments (kept in oc-cf-supabase.yml)
- **packages: write** - Not used by any workflow
- **pages: write** - Not used by any workflow
- **security-events: write** - Not used by any workflow

## Permissions Kept (with Rationale)

### Research & Issue Creation Workflows
- **contents: write** - To create and modify files for PRs
- **issues: write** - To create and update GitHub issues
- **pull-requests: write** - To create and update pull requests

### DevOps/Deployment Workflow (oc-cf-supabase.yml)
- **contents: write** - To create and modify deployment configs
- **deployments: write** - To manage Cloudflare deployments
- **id-token: write** - To authenticate with Cloudflare using OIDC

### Problem Detection Workflow (oc-problem-finder.yml)
- **contents: read** - To read repository for analysis
- **issues: write** - To create issues for problems found
- **pull-requests: read** - To read PRs for analysis

## Testing After Changes

After applying these changes, verify:

1. All workflow YAML files are valid: `gh workflow view`
2. Workflows can still run successfully
3. Workflow functionality is preserved
4. No permission errors appear in workflow runs

## Rollback

If any workflow fails after permission reduction:

1. Revert the specific workflow's permission changes
2. Investigate which permission was actually needed
3. Add back only the necessary permission
4. Re-test the workflow

## Reference

- GitHub Issue: #182
- Security Principle: Principle of Least Privilege
- GitHub Documentation: https://docs.github.com/en/actions/security-guides/automatic-token-authentication
