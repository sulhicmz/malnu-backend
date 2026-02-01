# Security Fix: Remove Admin Merge Bypass from on-pull.yml

**Issue:** #663 - [MAINTENANCE] Apply security fix for issue #629
**Security Impact:** CRITICAL

## The Problem

The `.github/workflows/on-pull.yml` file contains a critical security vulnerability at line 196 that allows bypassing branch protection rules:

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

6. Merge the changes to the main branch

## Changes Summary

### Removed:
- "3. Merge Conditions:" section with auto-merge logic
- `Use gh pr merge --admin` instruction
- "4. After successful merge:" section with auto-close and auto-delete

### Added:
- "3. PR Preparation:" section
- Explicit statement: "The agent SHOULD prepare PRs for human review but MUST NOT merge"
- Security notice: "Do NOT use --admin flag or any merge bypass mechanism"
- Requirement: "Branch protection rules MUST be enforced - all merges require human approval"
- "4. After PR is prepared:" section with comment and label actions only

## Security Impact

**Before Fix:**
- CI agent can merge ANY PR without human approval
- Branch protection rules are completely bypassed
- Sensitive changes could be merged automatically
- Critical security vulnerability

**After Fix:**
- CI agent prepares PRs for human review
- Branch protection rules are enforced
- All merges require human oversight
- Security vulnerability eliminated

## Verification

After applying the fix, verify:
- [ ] No `--admin` flag exists in the workflow file
- [ ] No "bypass branch protection" language remains
- [ ] Workflow explicitly states agent MUST NOT merge
- [ ] All merge-related actions require human approval
- [ ] Only comment and label actions are permitted after PR preparation

## Related Issues

- Fixes #663 - Apply security fix for issue #629
- Fixes #629 - Remove admin merge bypass from on-pull.yml workflow (critical security)
- Related to #611 - GitHub workflow permission hardening

## Why This PR Cannot Be Merged Automatically

This PR cannot be automatically merged because:

1. **GitHub Security Restriction:** The GitHub Actions workflow does not have `workflows: write` permission, which prevents it from pushing changes to `.github/workflows/` directory
2. **Security Best Practice:** This restriction is intentional - workflows should not be able to modify themselves
3. **Manual Review Required:** Security fixes to workflow files should always be reviewed and applied manually by maintainers with appropriate permissions

## Next Steps After Fix is Applied

1. Close duplicate PRs that claim to fix this issue but don't contain the correct fix:
   - #649 - Still contains admin bypass
   - #656 - Still contains admin bypass  
   - #661 - Documentation only
   - #659 - Documentation only

2. Issue #629 will be automatically closed when #663 is resolved

3. CI agent will prepare PRs for review but cannot merge without human approval
