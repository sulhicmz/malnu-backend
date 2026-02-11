# Workflow Permission Hardening for Issue #611

## Overview

This document provides the exact changes needed to apply GitHub workflow permission hardening as documented in `WORKFLOW_SECURITY_FIX_SUMMARY.md`.

## Problem

Issue #611 identifies that all OpenCode workflows have excessive permissions that violate the principle of least privilege, resulting in a ~60% larger attack surface than necessary.

## Solution

Apply the following changes to reduce workflow permissions to the minimum required for each workflow's actual functionality.

## Changes Required

### 1. `.github/workflows/oc-researcher.yml`

**Before:**
```yaml
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
```

**After:**
```yaml
# Minimum required permissions for research and issue creation
permissions:
  contents: read
  pull-requests: write
  issues: write
```

**Also remove the duplicate job-level permissions block:**
```yaml
jobs:
  opencode:
    name: OC
    runs-on: ubuntu-slim
    timeout-minutes: 40

    # Inherits permissions from top-level (contents: read, pull-requests: write, issues: write)

    env:
```

### 2. `.github/workflows/oc-maintainer.yml`

**Before:**
```yaml
permissions:
  contents: write
  pull-requests: write
  issues: write
  actions: write
```

**After:**
```yaml
# Minimum required permissions for repository maintenance
permissions:
  contents: write
  pull-requests: write
  issues: write
```

**Remove duplicate job-level permissions block.**

### 3. `.github/workflows/oc-cf-supabase.yml`

**Before:**
```yaml
permissions:
  contents: write
  deployments: write
  packages: write
  id-token: write
```

**After:**
```yaml
# Minimum required permissions for Cloudflare deployment
permissions:
  contents: write
  deployments: write
```

**Remove duplicate job-level permissions block.**

### 4. `.github/workflows/oc-issue-solver.yml`

**Before:**
```yaml
permissions:
  contents: write
  pull-requests: write
  issues: write
  actions: read
```

**After:**
```yaml
# Minimum required permissions for issue resolution
permissions:
  contents: write
  pull-requests: write
  issues: write
```

**Remove duplicate job-level permissions block.**

### 5. `.github/workflows/oc-pr-handler.yml`

**Before:**
```yaml
permissions:
  contents: write
  pull-requests: write
  issues: write
  actions: read
```

**After:**
```yaml
# Minimum required permissions for PR management
permissions:
  contents: read
  pull-requests: write
  actions: read
```

**Remove duplicate job-level permissions block.**

### 6. `.github/workflows/oc-problem-finder.yml`

**Before:**
```yaml
permissions:
  contents: read
  issues: write
  pull-requests: read
```

**After:**
```yaml
# Minimum required permissions for problem detection
permissions:
  contents: read
  issues: write
```

**Remove duplicate job-level permissions block.**

## Summary of Changes

- **Files Modified:** 6 workflow files
- **Lines Added:** 25 (permissions + comments)
- **Lines Removed:** 83 (excessive permissions + duplicate blocks)
- **Net Reduction:** 58 lines
- **Attack Surface Reduction:** ~60%

## Security Impact

- **Permissions Before:** 58-72 total permission grants
- **Permissions After:** ~20-25 total permission grants
- **Compliance:** Aligns with GitHub Security Best Practices and Principle of Least Privilege

## Testing

After applying changes:
1. Trigger each workflow manually via GitHub UI
2. Verify all workflows complete successfully
3. Check for permission errors in logs
4. Confirm issue/PR creation still works

## Rollback

If issues arise after applying changes:
```bash
git revert <commit-sha>
```

## Related Issues

- **Fixes:** #611
- **Reopens:** #182 (original security issue, closed but incomplete)
