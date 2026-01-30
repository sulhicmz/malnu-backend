# Security Fix for Issue #629

## Critical Security Vulnerability

The on-pull.yml workflow contains a critical security vulnerability at line 196 that instructs the use of `gh pr merge --admin` to bypass branch protection rules.

## The Fix

This branch contains a patch file that removes the vulnerable instruction and replaces it with an explicit security enforcement:

**Before (vulnerable):**
```yaml
Use `gh pr merge --admin` to bypass branch protection when conditions are met.
```

**After (secure):**
```yaml
DO NOT use `gh pr merge --admin` or any other bypass mechanism.
All merges must respect branch protection rules and require human review.
```

## How to Apply

### Step 1: Apply the patch

```bash
git checkout main
git pull origin main
git checkout fix/629-remove-admin-bypass
git apply 0001-security-Remove-admin-merge-bypass-from-on-pull.yml-.patch
```

### Step 2: Verify the changes

```bash
git diff .github/workflows/on-pull.yml
```

You should see the change from the vulnerable instruction to the secure one.

### Step 3: Create a new branch with the fix

```bash
git checkout -b apply-629-security-fix
```

### Step 4: Commit and push

```bash
git add .github/workflows/on-pull.yml
git commit -m "security: Remove admin merge bypass from on-pull.yml workflow

Remove the critical security vulnerability where the workflow instructed
the use of --admin flag to bypass branch protection rules. This ensures
all merges respect branch protection and require human review.

Fixes #629"
git push origin apply-629-security-fix
```

### Step 5: Create pull request

```bash
gh pr create --title "security: Remove admin merge bypass from on-pull.yml workflow" --body "Implements the security fix for issue #629.

Removes the critical security vulnerability where the workflow instructed
the use of --admin flag to bypass branch protection rules.

Fixes #629"
```

## Security Impact

This fix:
- ✅ Removes the critical security vulnerability
- ✅ Explicitly prohibits any bypass mechanism
- ✅ Ensures all merges respect branch protection rules
- ✅ Requires human review for all merges

## Related Issues

Fixes #629
