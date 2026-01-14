# CI Fixes Required

## CRITICAL: workflow-monitor Permission Issue

**Status**: 🔴 CRITICAL - CI failing consistently  
**Priority**: P0  
**Affected Workflow**: workflow-monitor.yml  
**Impact**: Runs every 30 minutes, fails on each execution

### Problem
The workflow-monitor workflow fails when attempting to trigger on-pull workflow:
```
Error: HTTP 403: Resource not accessible by integration
Command: gh workflow run on-pull.yml
```

### Root Cause
Insufficient GitHub Actions permissions in workflow-monitor.yml:
```yaml
# Current (BROKEN):
permissions:
  actions: read    # ❌ Cannot trigger workflows
  contents: write

# Required (FIX):
permissions:
  actions: write    # ✅ Allows workflow_dispatch
  contents: write
```

### Fix Required
**File**: `.github/workflows/workflow-monitor.yml`  
**Line**: 9  
**Change**: `actions: read` → `actions: write`

### Why This Is Needed
- workflow-monitor uses `gh workflow run on-pull.yml` to trigger on-pull workflow
- GitHub Actions requires `actions: write` permission for workflow_dispatch events
- This is a documented requirement in GitHub Actions permissions documentation
- Other workflows (on-push, on-pull) already use appropriate permissions

### Security Assessment
**Risk Level**: LOW
- Only allows triggering workflows within the same repository
- No external access or privilege escalation
- Consistent with other workflow permissions in the project
- Follows GitHub Actions best practices

### Verification Steps
After applying the fix:
1. Monitor workflow-monitor runs in GitHub Actions
2. Verify on-pull workflow is triggered successfully
3. Confirm no more 403 errors in workflow-monitor logs

## Additional Notes

### Why GITHUB_TOKEN Can't Apply This Fix
The GITHUB_TOKEN used by GitHub Actions has restricted permissions by design. Workflow files require manual application of changes because:
1. Workflow permissions are configured in workflow files themselves
2. Modifying workflow files requires `workflows: write` permission
3. GITHUB_TOKEN typically doesn't have this permission for security
4. This prevents infinite loops and unauthorized workflow modifications

### Manual Application Required
A repository maintainer with appropriate permissions must:
1. Manually edit `.github/workflows/workflow-monitor.yml`
2. Change `actions: read` to `actions: write` on line 9
3. Commit and push the change
4. Verify the next workflow-monitor run succeeds

---

## Status Log

| Date | Attempt | Result | Notes |
|------|----------|---------|-------|
| Jan 14, 2026 | DevOps investigation | Identified permissions issue | Fixed in local commit, cannot push due to permissions |
| Jan 14, 2026 | Issue creation | Failed (insufficient token permissions) | Documented in this file instead |

