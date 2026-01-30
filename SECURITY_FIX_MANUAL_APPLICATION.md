# Security Fix Manual Application - Issue #629

## Summary

This document provides the exact fix for the critical security vulnerability in `.github/workflows/on-pull.yml`.

## Vulnerability

Line 196 in `on-pull.yml` contains an instruction that tells the OpenCode agent to use `gh pr merge --admin` to bypass branch protection rules. This is a **CRITICAL security vulnerability**.

## Fix Required

**File**: `.github/workflows/on-pull.yml`
**Line**: 196

### Before (Vulnerable)

```yaml
              Use `gh pr merge --admin` to bypass branch protection when conditions are met.
```

### After (Fixed)

```yaml
               DO NOT use `gh pr merge --admin` or any other bypass mechanism.
               All merges must respect branch protection rules and require human review.
```

## How to Apply

### Option 1: Manual Edit (Recommended)

1. Go to: https://github.com/sulhicmz/malnu-backend/edit/main/.github/workflows/on-pull.yml
2. Find line 196
3. Replace the vulnerable line with the fixed version shown above
4. Commit the change with message:
   ```
   security: Remove admin merge bypass instruction from on-pull.yml workflow
   ```
5. Push the change

### Option 2: Command Line

If you have write access to the repository:

```bash
git checkout main
git pull origin main
# Edit line 196 as shown above
git add .github/workflows/on-pull.yml
git commit -m "security: Remove admin merge bypass instruction from on-pull.yml workflow"
git push origin main
```

## Verification

After applying the fix, verify that it's correct:

```bash
# Check for --admin flag (should return nothing)
grep -n "gh pr merge.*--admin" .github/workflows/on-pull.yml

# Check for security statement (should return line 196)
grep -n "DO NOT use.*admin" .github/workflows/on-pull.yml
```

## Security Impact

This fix:
- ✅ Prevents the autonomous agent from bypassing branch protection
- ✅ Ensures that all merges require human approval
- ✅ Enforces security best practices
- ✅ Eliminates the critical security vulnerability

## Testing

After applying the fix:
1. Create a test PR
2. Verify that it cannot be merged without human approval
3. Verify that branch protection rules are enforced

## Related

- Issue: #629
- PR: This PR documents the fix
- Branch: `docs/629-security-fix-documentation`

## Context

The fix has been prepared locally but cannot be pushed automatically due to GitHub App permission restrictions (the token lacks the `workflows` permission required to modify `.github/workflows/` files).

---

**Created**: January 30, 2026
**Priority**: CRITICAL
**Issue**: #629
**Estimated Time**: 5 minutes to apply manually
