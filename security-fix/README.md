# Security Fix for Issue #663 - Remove Admin Merge Bypass

## Critical Security Vulnerability

The `.github/workflows/on-pull.yml` file contains a critical security vulnerability at line 196:

```yaml
Use `gh pr merge --admin` to bypass branch protection when conditions are met.
```

This allows the OpenCode CI agent to merge PRs WITHOUT enforcing branch protection rules, bypassing human oversight.

## Manual Application Required

Due to GitHub security restrictions, the GitHub App cannot directly modify workflow files without the `workflows` permission. A maintainer with workflow permissions must apply this fix manually.

## How to Apply the Fix

### Step 1: Verify the Current State

Check that the workflow file contains the vulnerable instruction:

```bash
grep -n "gh pr merge --admin" .github/workflows/on-pull.yml
```

This should show line 196 contains the vulnerable instruction.

### Step 2: Replace the Workflow File

Copy the fixed workflow file to the correct location:

```bash
cp security-fix/on-pull-fixed.yml .github/workflows/on-pull.yml
```

### Step 3: Verify the Fix

Check that the admin bypass instruction has been removed:

```bash
grep -n "gh pr merge --admin" .github/workflows/on-pull.yml
```

This should return no results.

### Step 4: Verify the New Text

Check that the new security text is present:

```bash
grep -n "SECURITY: Do NOT use --admin flag" .github/workflows/on-pull.yml
```

This should show line 199 contains the new security warning.

### Step 5: Commit the Changes

```bash
git add .github/workflows/on-pull.yml
git commit -m "security: Remove admin merge bypass from on-pull.yml workflow

Remove the critical security vulnerability that allowed the CI agent to merge
PRs without enforcing branch protection rules. The workflow now explicitly
prohibits using --admin flag and requires all merges to go through human
review per branch protection rules.

Changes:
- Replace 'Merge Conditions' with 'PR Preparation'
- Add explicit instruction that agent MUST NOT merge
- Add SECURITY warning: Do NOT use --admin flag or any merge bypass mechanism
- State that branch protection rules MUST be enforced
- All merges now require human approval

Fixes #629, Fixes #663"
```

### Step 6: Push and Merge

```bash
git push
```

Then merge the PR through GitHub's web interface.

## Verification Checklist

- [ ] The `--admin` flag instruction has been removed
- [ ] The section is renamed from "Merge Conditions" to "PR Preparation"
- [ ] Explicit instruction that agent MUST NOT merge is present
- [ ] SECURITY warning about not using bypass mechanisms is present
- [ ] Statement that branch protection rules MUST be enforced is present
- [ ] Workflow YAML syntax is valid

## Security Impact

### Before Fix (VULNERABLE):
- CI agent could merge ANY PR without human approval
- Branch protection rules completely bypassed
- Critical security vulnerability

### After Fix (SECURE):
- CI agent prepares PRs for human review only
- Branch protection rules enforced
- All merges require human oversight
- Explicit prohibition of bypass mechanisms

## Related Issues

- Fixes #629 - Critical security vulnerability
- Fixes #663 - Apply security fix for issue #629

## Duplicate PRs to Close

Once this fix is merged, the following duplicate PRs should be closed:
- PR #649 - fix/629-correct-security-fix-v2 (Still contains admin bypass)
- PR #656 - fix/629-remove-admin-bypass (Still contains admin bypass)
- PR #661 - docs/629-document-security-vulnerability (Documentation only)
- PR #659 - docs/629-security-fix-documentation (Documentation only)
- PR #648 - docs/issue-629-fix-summary (Documentation only)
- PR #665 - security: Remove admin merge bypass from on-pull.yml workflow (Documentation only)
- PR #667 - security: Apply verified fix for issue #629 and #663 (Documentation only)
- PR #666 - security: Apply verified fix for issue #629 (Documentation only)
