# Security Fix: Remove Admin Merge Bypass from on-pull.yml

**Issue:** #663 - [MAINTENANCE] Apply security fix for issue #629
**Security Impact:** CRITICAL

## The Problem

The `.github/workflows/on-pull.yml` file contains a critical security vulnerability at line 241 that allows bypassing branch protection rules:

```yaml
Use `gh pr merge --admin` to bypass branch protection when conditions are met.
```

This allows the OpenCode CI agent to merge PRs without human approval, completely bypassing security controls.

## The Fix

This PR provides a patch file (`663_SECURITY_FIX.patch`) that removes the admin bypass capability.

## How to Apply

**For a repository maintainer with appropriate permissions:**

1. Download the patch file from this PR
2. Navigate to your repository root
3. Apply the patch:

```bash
# Apply the patch
git apply 663_SECURITY_FIX.patch

# Verify the changes
git diff .github/workflows/on-pull.yml
```

4. Review the changes to confirm:
   - The line `Use gh pr merge --admin to bypass branch protection` is removed
   - The new text explicitly states "Do NOT use --admin flag or any merge bypass mechanism"
   - The workflow now instructs the agent to prepare PRs for review, not merge them

5. Commit and push:

```bash
git add .github/workflows/on-pull.yml
git commit -m "security: Remove admin merge bypass from on-pull.yml workflow (fixes #663)

Critical security fix to remove the ability for the CI agent to bypass branch
protection rules using admin merge privileges.

Fixes #663
Related to #629"

git push
```

## Changes Made

### Removed from `.github/workflows/on-pull.yml`:

```yaml
3. Merge Conditions:
    ONLY merge if:
    - No conflicts
    - Build passes
    - All checks green (dont wait for 'on-pull' check, its you)
    - All PR comments resolved
    - No security-sensitive change without review

    Use `gh pr merge --admin` to bypass branch protection when conditions are met.

    NEVER delete branch if merge fails.

4. After successful merge:
    - Close linked issues
    - Delete remote branch ONLY after successful merge
    - Log action
```

### Added to `.github/workflows/on-pull.yml`:

```yaml
3. PR Preparation:
    The agent SHOULD prepare PRs for human review but MUST NOT merge.

    PR is READY for human review when:
    - No conflicts
    - Build passes
    - All checks green (except 'on-pull' check which is this workflow)
    - All PR comments resolved
    - No security-sensitive change without review
    - Code follows project style guidelines

    SECURITY: Do NOT use --admin flag or any merge bypass mechanism.
    Branch protection rules MUST be enforced - all merges require human approval.

4. After PR is prepared:
    - Comment with status summary
    - Label appropriately (ready for review, needs changes, etc.)
    - Do NOT merge - leave for human review
```

## Verification Checklist

After applying the fix, verify:
- [ ] No `--admin` flag exists in the workflow file
- [ ] No "bypass branch protection" language remains
- [ ] Workflow explicitly states agent MUST NOT merge
- [ ] All merge-related actions require human approval
- [ ] Only comment and label actions are permitted after PR preparation

## Security Impact

### Before Fix:
- CI agent can merge ANY PR without human approval
- Branch protection rules are completely bypassed
- Sensitive changes could be merged automatically
- **Critical security vulnerability**

### After Fix:
- CI agent prepares PRs for human review
- Branch protection rules are enforced
- All merges require human oversight
- **Security vulnerability eliminated**

## Related Issues

- **Fixes #663** - Apply security fix for issue #629
- **Fixes #629** - Remove admin merge bypass from on-pull.yml workflow (critical security)
- Related to #611 - GitHub workflow permission hardening

## Next Steps After Fix is Applied

1. Close duplicate PRs that claim to fix this issue but don't contain the correct fix:
   - #674 - Contains patch file but not the actual fix to the workflow
   - #649 - Still contains admin bypass
   - #656 - Still contains admin bypass
   - #661 - Documentation only
   - #659 - Documentation only

2. The CI agent will prepare PRs for review but cannot merge without human approval

## Breaking Changes

None. This only removes dangerous capabilities; workflow will continue to function correctly in "prepare PR for review" mode.

## Why This PR Cannot Be Merged Automatically

This PR provides a patch file and manual instructions instead of directly modifying the workflow file because:

1. **GitHub Security Restriction:** The GitHub Actions workflow does not have `workflows: write` permission, which prevents it from pushing changes to `.github/workflows/` directory
2. **Security Best Practice:** This restriction is intentional - workflows should not be able to modify themselves
3. **Manual Review Required:** Security fixes to workflow files should always be reviewed and applied manually by maintainers with appropriate permissions

## Testing

After applying the fix:

1. **Test Workflow Syntax**:
   ```bash
   python3 -c "import yaml; yaml.safe_load(open('.github/workflows/on-pull.yml'))"
   ```
   Output should be: `YAML is valid`

2. **Test Workflow Functionality**:
   - Trigger the workflow manually or by creating a PR
   - Verify it runs without errors
   - Verify it prepares PRs for review without attempting to merge

3. **Verify No Auto-Merge**:
   - Check that PRs created by the agent remain open
   - Verify that no auto-merge actions occur
   - Confirm that human approval is still required for merges
