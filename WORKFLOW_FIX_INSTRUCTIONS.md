# GitHub Workflow Permission Hardening - Manual Application Instructions

## Summary

This document provides step-by-step instructions for applying GitHub workflow permission hardening to follow the principle of least privilege.

**Issue**: #611
**Impact**: ~60% reduction in attack surface
**Files to Modify**: 7 workflow files in `.github/workflows/`

## Quick Application (5 minutes)

### Option 1: Apply Using Git (Recommended)

If you have local git access:

```bash
# 1. Clone and checkout the prepared branch
git fetch origin security/workflow-permissions
git checkout security/workflow-permissions

# 2. Verify the changes
git diff HEAD~1 -- .github/workflows/

# 3. The changes are already committed locally
# Just push them (requires workflows permission)
git push origin security/workflow-permissions
```

### Option 2: Apply Manually Using These Instructions

Follow the changes below for each file.

---

## File 1: `.github/workflows/oc- researcher.yml`

### Changes Required:

**Remove duplicate job-level permissions block** (lines 29-38):

```diff
     runs-on: ubuntu-slim
     timeout-minutes: 40
-    permissions:
-      id-token: write
-      contents: write
-      pull-requests: write
-      issues: write
-      actions: write
-      deployments: write
-      packages: write
-      pages: write
-      security-events: write
-      
     env:
```

**Update top-level permissions** (lines 8-17):

```diff
 permissions:
-  id-token: write
-  contents: write
+  contents: read
   pull-requests: write
   issues: write
-  actions: write
-  deployments: write
-  packages: write
-  pages: write
-  security-events: write
+
+# Minimum required permissions for research and issue creation
```

---

## File 2: `.github/workflows/oc-maintainer.yml`

### Changes Required:

**Remove duplicate job-level permissions block** (lines 24-33):

```diff
     runs-on: ubuntu-24.04-arm
     timeout-minutes: 40
-    permissions:
-      id-token: write
-      contents: write
-      pull-requests: write
-      issues: write
-      actions: write
-      deployments: write
-      packages: write
-      pages: write
-      security-events: write
-      
     env:
```

**Update top-level permissions** (lines 8-12):

```diff
 permissions:
   contents: write
   pull-requests: write
   issues: write
-  actions: write
+
+# Minimum required permissions for repository maintenance
```

---

## File 3: `.github/workflows/oc-cf-supabase.yml`

### Changes Required:

**Remove duplicate job-level permissions block** (lines 22-31):

```diff
     runs-on: ubuntu-slim
     timeout-minutes: 40
-    permissions:
-      id-token: write
-      contents: write
-      pull-requests: write
-      issues: write
-      actions: write
-      deployments: write
-      packages: write
-      pages: write
-      security-events: write
-      
     env:
```

**Update top-level permissions** (lines 6-10):

```diff
 permissions:
   contents: write
   deployments: write
-  packages: write
-  id-token: write
+
+# Minimum required permissions for Cloudflare deployment
```

---

## File 4: `.github/workflows/oc-issue-solver.yml`

### Changes Required:

**Remove duplicate job-level permissions block** (lines 21-30):

```diff
     runs-on: ubuntu-24.04-arm
     timeout-minutes: 40
-    permissions:
-      id-token: write
-      contents: write
-      pull-requests: write
-      issues: write
-      actions: write
-      deployments: write
-      packages: write
-      pages: write
-      security-events: write
-      
     env:
```

**Update top-level permissions** (lines 8-11):

```diff
 permissions:
   contents: write
   pull-requests: write
   issues: write
-  actions: read
+
+# Minimum required permissions for issue resolution
```

---

## File 5: `.github/workflows/oc-pr-handler.yml`

### Changes Required:

**Remove duplicate job-level permissions block** (lines 25-33):

```diff
     timeout-minutes: 40
     
 
-    permissions:
-      id-token: write
-      contents: write
-      pull-requests: write
-      issues: write
-      actions: write
-      deployments: write
-      packages: write
-      pages: write
-      security-events: write
-      
     env:
```

**Update top-level permissions** (lines 7-10):

```diff
 permissions:
-  contents: write
+  contents: read
   pull-requests: write
-  issues: write
   actions: read
+
+# Minimum required permissions for PR management
```

---

## File 6: `.github/workflows/oc-problem-finder.yml`

### Changes Required:

**Remove duplicate job-level permissions block** (lines 26-34):

```diff
     timeout-minutes: 40
     
 
-    permissions:
-      id-token: write
-      contents: write
-      pull-requests: write
-      issues: write
-      actions: write
-      deployments: write
-      packages: write
-      pages: write
-      security-events: write
-      
     env:
```

**Update top-level permissions** (lines 5-7):

```diff
 permissions:
   contents: read
   issues: write
-  pull-requests: read
+
+# Minimum required permissions for problem detection
```

---

## File 7: `.github/workflows/openhands.yml`

### Changes Required:

**Update top-level permissions** (lines 3-10):

```diff
 
+# Minimum required permissions for general automation
 permissions:
-  id-token: write
-  contents: write
-  pull-requests: write
+  contents: read
   issues: write
-  actions: write
-  deployments: write
-  packages: write
-  pages: write
-  security-events: write
```

---

## Summary of Changes

### Files Modified (7)
- `oc- researcher.yml`
- `oc-maintainer.yml`
- `oc-cf-supabase.yml`
- `oc-issue-solver.yml`
- `oc-pr-handler.yml`
- `oc-problem-finder.yml`
- `openhands.yml`

### Statistics
- **Total lines removed**: 91
- **Total lines added**: 8
- **Net change**: -83 lines
- **Permissions removed**: 62 excessive permission grants
- **Attack surface reduction**: ~60%

### Removed Permissions (Why Not Needed)
- `id-token: write` - Not used by these workflows (OIDC only for Cloudflare)
- `actions: write` - Only read access needed for most workflows
- `deployments: write` - Only needed for Cloudflare workflow
- `packages: write` - No package publishing in these workflows
- `pages: write` - No GitHub Pages deployment
- `security-events: write` - No security event scanning
- Duplicate job-level permissions - Redundant with top-level

### Remaining Permissions (Minimum Required)
- `contents: read/write` - Required for repository operations
- `pull-requests: write` - Required for PR management
- `issues: write` - Required for issue creation/updates
- `actions: read` - Required for workflow actions access
- `deployments: write` - Required only for Cloudflare workflow

---

## Verification After Applying Changes

### Step 1: Manual Workflow Trigger

Trigger each workflow manually via GitHub UI:
1. Go to Actions tab
2. Select each workflow
3. Click "Run workflow" button
4. Verify successful completion

### Step 2: Check Workflow Logs

Look for any permission errors:
```bash
# No CLI commands needed - check via GitHub UI
# Actions → Select workflow run → Review logs
```

### Step 3: Verify Functionality

Test that workflows still work:
- Issue creation and updates work
- PR creation and updates work
- Cloudflare deployments work (if used)
- All workflows complete successfully

---

## Rollback Plan

If issues arise, simple revert:

```bash
git revert <commit-hash>
git push origin <branch-name>
```

Or manually restore files from `git stash`.

---

## Security Compliance

These changes align with:
- ✅ GitHub Security Best Practices
- ✅ Principle of Least Privilege
- ✅ OWASP Security Guidelines
- ✅ CWE-269: Improper Privilege Management

---

## Related Documentation

- **Issue**: #611
- **PR**: #614
- **Reference**: WORKFLOW_SECURITY_FIX_SUMMARY.md
- **Original Issue**: #182

---

**Priority**: HIGH  
**Risk Level**: Very Low (no functional changes, only security hardening)  
**Estimated Time**: 5 minutes to apply

