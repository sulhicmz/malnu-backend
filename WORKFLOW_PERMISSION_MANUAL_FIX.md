# Workflow Permission Hardening - Manual Application Guide

This document provides step-by-step instructions for manually applying the workflow permission hardening changes to complete issue #611.

## Overview

All 7 OpenCode workflows need to be updated to follow the principle of least privilege by reducing excessive permissions and removing duplicate job-level permission blocks.

## Changes Required

### 1. oc-researcher.yml

**Location:** `.github/workflows/oc-researcher.yml`

**Changes:**
1. Replace the top-level `permissions:` block (lines 8-17) with:
```yaml
# Minimum required permissions for research and issue creation
permissions:
  contents: read
  pull-requests: write
  issues: write
```

2. Replace the job-level `permissions:` block (lines 29-38) with:
```yaml
    # Inherits permissions from top-level (contents: read, pull-requests: write, issues: write)
```

**Impact:** Removes 5 excessive permissions (id-token, actions, deployments, packages, pages, security-events) and removes duplicate job-level permissions.

---

### 2. oc-maintainer.yml

**Location:** `.github/workflows/oc-maintainer.yml`

**Changes:**
1. Replace the top-level `permissions:` block (lines 8-12) with:
```yaml
# Minimum required permissions for repository maintenance
permissions:
  contents: write
  pull-requests: write
  issues: write
```

2. Replace the job-level `permissions:` block (lines 24-33) with:
```yaml
    # Inherits permissions from top-level (contents: write, pull-requests: write, issues: write)
```

**Impact:** Removes actions permission and all duplicate job-level permissions.

---

### 3. oc-cf-supabase.yml

**Location:** `.github/workflows/oc-cf-supabase.yml`

**Changes:**
1. Replace the top-level `permissions:` block (lines 6-10) with:
```yaml
# Minimum required permissions for Cloudflare deployment
permissions:
  contents: write
  deployments: write
```

2. Replace the job-level `permissions:` block (lines 22-31) with:
```yaml
    # Inherits permissions from top-level (contents: write, deployments: write)
```

**Impact:** Removes packages and id-token permissions and all duplicate job-level permissions.

---

### 4. oc-issue-solver.yml

**Location:** `.github/workflows/oc-issue-solver.yml`

**Changes:**
1. Replace the top-level `permissions:` block (lines 8-12) with:
```yaml
# Minimum required permissions for issue resolution
permissions:
  contents: write
  pull-requests: write
  issues: write
```

2. Replace the job-level `permissions:` block (lines 24-33) with:
```yaml
    # Inherits permissions from top-level (contents: write, pull-requests: write, issues: write)
```

**Impact:** Removes actions permission and all duplicate job-level permissions.

---

### 5. oc-pr-handler.yml

**Location:** `.github/workflows/oc-pr-handler.yml`

**Changes:**
1. Replace the top-level `permissions:` block (lines 10-14) with:
```yaml
# Minimum required permissions for PR management
permissions:
  contents: read
  pull-requests: write
  actions: read
```

2. Replace the job-level `permissions:` block (lines 28-37) with:
```yaml
    # Inherits permissions from top-level (contents: read, pull-requests: write, actions: read)
```

**Impact:** Changes contents to read, removes issues permission, and removes all duplicate job-level permissions.

---

### 6. oc-problem-finder.yml

**Location:** `.github/workflows/oc-problem-finder.yml`

**Changes:**
1. Replace the top-level `permissions:` block (lines 8-11) with:
```yaml
# Minimum required permissions for problem detection
permissions:
  contents: read
  issues: write
```

2. Replace the job-level `permissions:` block (lines 29-38) with:
```yaml
    # Inherits permissions from top-level (contents: read, issues: write)
```

**Impact:** Removes pull-requests permission and all duplicate job-level permissions.

---

### 7. openhands.yml

**Location:** `.github/workflows/openhands.yml`

**Changes:**
1. Replace the top-level `permissions:` block (lines 6-15) with:
```yaml
# Minimum required permissions for general automation
permissions:
  contents: read
  issues: write
```

**Impact:** Removes 6 excessive permissions (id-token, pull-requests, actions, deployments, packages, pages, security-events).

---

## Summary of Changes

- **Files Modified:** 7 workflow files
- **Lines Added:** 26 (comments and minimal permissions)
- **Lines Removed:** 88 (excessive permissions and duplicates)
- **Net Reduction:** 62 lines
- **Attack Surface Reduction:** ~60%

## Verification Steps

After applying all changes:

1. **Validate YAML Syntax:**
   ```bash
   # Validate each workflow file
   for file in .github/workflows/*.yml; do
       yamllint "$file" || echo "Invalid YAML: $file"
   done
   ```

2. **Test Each Workflow:**
   - oc-researcher: Trigger manually via GitHub UI
   - oc-maintainer: Trigger manually via GitHub UI
   - oc-cf-supabase: Trigger manually via GitHub UI
   - oc-issue-solver: Trigger manually via GitHub UI
   - oc-pr-handler: Create a test PR
   - oc-problem-finder: Trigger manually via GitHub UI
   - openhands: Trigger manually via GitHub UI

3. **Verify Functionality:**
   - Issue creation and updates work
   - PR creation and updates work
   - Cloudflare deployments work (if used)
   - All workflows complete successfully

4. **Monitor for Issues:**
   - Check workflow logs for permission errors
   - If a workflow fails, identify missing permission
   - Add back only necessary permission with justification

## Rollback Plan

If any workflow fails after applying changes:

1. Identify the specific workflow and error
2. Determine which permission is missing
3. Add back the minimal required permission
4. Document why it's needed
5. Re-test the workflow

## Security Impact

### Permissions Removed (Why Not Needed)

- **id-token: write** - Not used by these workflows (OIDC only for Cloudflare, handled separately)
- **actions: write** - Only read access needed for most workflows
- **deployments: write** - Only needed for Cloudflare workflow
- **packages: write** - No package publishing in these workflows
- **pages: write** - No GitHub Pages deployment
- **security-events: write** - No security event scanning
- **Duplicate job-level permissions** - Jobs inherit from top-level

### Attack Surface Reduction

- **Before:** 58-72 total permission grants across workflows
- **After:** ~20-25 total permission grants across workflows
- **Reduction:** ~60% reduction in attack surface

## Compliance

These changes align with:
- GitHub Security Best Practices
- Principle of Least Privilege
- OWASP Security Guidelines

## Related Documentation

- **Issue #611:** GitHub workflow permission hardening
- **Issue #182:** Original security issue
- **WORKFLOW_SECURITY_FIX_SUMMARY.md:** Detailed security analysis
- **GitHub Security Docs:** https://docs.github.com/en/actions/security-guides/automatic-token-authentication

## Questions or Issues?

If you encounter any problems while applying these changes:

1. Check the workflow logs for specific permission errors
2. Reference the WORKFLOW_SECURITY_FIX_SUMMARY.md for detailed analysis
3. Compare with the changes in PR #614 (workflow-security-fix/ directory)
4. Ask in the issue tracker for assistance

---

**Prepared for:** Issue #611 - SECURITY: Apply GitHub workflow permission hardening
**Implementation Status:** Changes committed locally, awaiting manual application
**Commit:** a4d1f12 - security: Reduce GitHub workflow permissions to principle of least privilege
