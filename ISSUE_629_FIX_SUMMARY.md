# Fix for Issue #629: Remove Admin Merge Bypass

## Summary

Critical security vulnerability in `.github/workflows/on-pull.yml` at line 196 that allows bypassing branch protection rules using `gh pr merge --admin`.

## Changes Required

**File:** `.github/workflows/on-pull.yml`

**Location:** Line 196 (within the OpenCode agent prompt)

### Before:
```yaml
Use `gh pr merge --admin` to bypass branch protection when conditions are met.
```

### After:
```yaml
IMPORTANT: Use `gh pr merge --squash --auto --delete-branch` WITHOUT --admin flag.
Branch protection rules MUST be respected. Do NOT bypass branch protection.
```

## Security Impact

- **Risk Level:** CRITICAL
- **Vulnerability:** The `--admin` flag allows the OpenCode agent to merge PRs without human approval, bypassing all branch protection rules
- **Fix:** Remove `--admin` flag and enforce branch protection rules

## Implementation Status

✅ **Changes committed locally** (commit: 152b1df)

## Deployment Issue

⚠️ **Cannot push to remote**: The GitHub Actions token lacks `workflows: write` permission, which is required to modify workflow files. This is intentional security hardening.

## Resolution Options

### Option 1: Manual Commit (Recommended)
A repository maintainer with write access can:
1. Apply the changes from commit 152b1df manually
2. Commit with the message provided below
3. Push and merge

### Option 2: Grant Temporary Workflows Permission
Temporarily grant `workflows: write` permission to allow the agent to push the changes.

### Option 3: Create PR with Direct Commit
A maintainer can create a PR by:
1. Applying the changes manually
2. Creating a PR from a new branch
3. Merging after review

## Commit Message

```
security(critical): Remove admin merge bypass from on-pull.yml workflow

Replace gh pr merge --admin with --squash --auto --delete-branch to enforce
branch protection rules. The --admin flag allowed bypassing all branch protection
rules, creating a critical security vulnerability where the OpenCode agent could
merge PRs without human approval.

Fixes #629
```

## Verification Steps

After applying changes:
1. Verify the workflow file no longer contains `gh pr merge --admin`
2. Verify the new instruction uses `gh pr merge --squash --auto --delete-branch`
3. Test the workflow to ensure it functions correctly
4. Monitor to ensure branch protection rules are enforced

## Related Issues

- Issue #611: Apply GitHub workflow permission hardening
- WORKFLOW_SECURITY_FIX_SUMMARY.md: Documented security hardening

---

**Created by:** OpenCode Agent
**Issue:** #629
**Date:** 2026-01-29
