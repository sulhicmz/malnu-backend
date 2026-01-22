# Security Fix for Issue #629

## Critical Security Vulnerability

The on-pull.yml workflow contains an instruction to use `gh pr merge --admin` which allows bypassing branch protection rules. This is a CRITICAL security vulnerability.

## How to Apply the Fix

Since the GitHub App used for automation doesn't have `workflows` permission to push workflow changes, please apply the following change manually:

### Step 1: Apply the Patch

```bash
git apply PR_629_SECURITY_FIX.patch
```

Or manually edit `.github/workflows/on-pull.yml` line 196:

**Change from:**
```yaml
Use `gh pr merge --admin` to bypass branch protection when conditions are met.
```

**Change to:**
```yaml
Use `gh pr merge --squash --auto --delete-branch` (without --admin to enforce branch protection rules).
```

### Step 2: Verify the Changes

```bash
git diff
```

Should show:
```diff
-               Use `gh pr merge --admin` to bypass branch protection when conditions are met.
+               Use `gh pr merge --squash --auto --delete-branch` (without --admin to enforce branch protection rules).
```

### Step 3: Commit and Push

```bash
git add .github/workflows/on-pull.yml
git commit -m "fix(security): Remove admin merge bypass from on-pull.yml workflow"
git push origin fix/629-security-patch-proposal
```

### Step 4: Merge the PR

Once the patch is applied, the PR can be merged normally.

## Security Impact

This fix addresses a CRITICAL security vulnerability where:
- The OpenCode agent could merge PRs without human approval
- Branch protection rules could be bypassed entirely
- Sensitive changes could be merged automatically without review

## Related Issues

Fixes #629
