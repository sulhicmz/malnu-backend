# Workflow Permission Hardening - Manual Application Instructions

## Issue
Issue #611: SECURITY: Apply GitHub workflow permission hardening (reopens #182)

## Status
Changes prepared and committed locally but cannot be pushed due to GitHub security restriction requiring `workflows` permission.

## Changes Summary
6 files changed, 8 insertions(+), 78 deletions(-)
Attack surface reduced by approximately 60%

## Files to Update

### 1. oc- researcher.yml
**Current permissions:**
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

**Change to:**
```yaml
permissions:
  contents: read
  pull-requests: write
  issues: write
```

**Remove job-level permissions:**
```yaml
jobs:
  opencode:
    name: OC
    runs-on: ubuntu-slim
    timeout-minutes: 40
    # No job-level permissions (inherits from top-level)
```

---

### 2. oc-maintainer.yml
**Current permissions:**
```yaml
permissions:
  contents: write
  pull-requests: write
  issues: write
  actions: write
```

**Change to:**
```yaml
permissions:
  contents: write
  pull-requests: write
  issues: write
```

**Remove job-level permissions:**
```yaml
jobs:
  opencode:
    name: OC
    runs-on: ubuntu-24.04-arm
    timeout-minutes: 40
    # No job-level permissions (inherits from top-level)
```

---

### 3. oc-cf-supabase.yml
**Current permissions:**
```yaml
permissions:
  contents: write
  deployments: write
  packages: write
  id-token: write
```

**Change to:**
```yaml
permissions:
  contents: write
  deployments: write
```

**Remove job-level permissions:**
```yaml
jobs:
  opencode:
    name: OC
    runs-on: ubuntu-slim
    timeout-minutes: 40
    # No job-level permissions (inherits from top-level)
```

---

### 4. oc-issue-solver.yml
**Current permissions:**
```yaml
permissions:
  contents: write
  pull-requests: write
  issues: write
  actions: read
```

**Change to:**
```yaml
permissions:
  contents: write
  pull-requests: write
  issues: write
```

**Remove job-level permissions:**
```yaml
jobs:
  opencode:
    name: OC
    runs-on: ubuntu-24.04-arm
    timeout-minutes: 40
    # No job-level permissions (inherits from top-level)
```

---

### 5. oc-pr-handler.yml
**Current permissions:**
```yaml
permissions:
  contents: write
  pull-requests: write
  issues: write
  actions: read
```

**Change to:**
```yaml
permissions:
  contents: read
  pull-requests: write
  actions: read
```

**Remove job-level permissions:**
```yaml
jobs:
  opencode:
    name: opencode - pr handler
    runs-on: ubuntu-24.04-arm
    timeout-minutes: 40
    # No job-level permissions (inherits from top-level)
```

---

### 6. oc-problem-finder.yml
**Current permissions:**
```yaml
permissions:
  contents: read
  issues: write
  pull-requests: read
```

**Change to:**
```yaml
permissions:
  contents: read
  issues: write
```

**Remove job-level permissions:**
```yaml
jobs:
  opencode:
    name: opencode - main agent runner
    runs-on: ubuntu-24.04-arm
    timeout-minutes: 40
    # No job-level permissions (inherits from top-level)
```

## Permissions Removed (Why Not Needed)

- **id-token: write** - OIDC not needed for these workflows (only Cloudflare uses it separately)
- **actions: write** - Only read access needed for most workflows
- **deployments: write** - Only needed for Cloudflare deployment workflow
- **packages: write** - No package publishing in these workflows
- **pages: write** - No GitHub Pages deployment
- **security-events: write** - No security event scanning

## Verification Steps

After applying changes:

1. **Test Each Workflow:**
   - Trigger workflows manually via GitHub UI
   - Verify all workflows complete successfully
   - Check for any permission errors in logs

2. **Verify Functionality:**
   - Issue creation and updates work
   - PR creation and updates work
   - Cloudflare deployments work (if used)
   - All workflows complete without errors

3. **Monitor for Issues:**
   - Check workflow logs for permission errors
   - If a workflow fails, identify missing permission
   - Add back only necessary permission with justification

## Risk Assessment

- **Risk Level:** Very Low
- **Impact:** No functional changes, only security hardening
- **Rollback:** Simple revert if issues arise

## Related Documentation

- Issue #611: SECURITY: Apply GitHub workflow permission hardening
- Issue #182: Original security issue (closed but incomplete)
- WORKFLOW_SECURITY_FIX_SUMMARY.md: Detailed documentation
