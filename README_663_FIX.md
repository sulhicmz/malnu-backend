# Security Fix: Remove Admin Merge Bypass from on-pull.yml

**Issue:** #663 - [MAINTENANCE] Apply security fix for issue #629
**Security Impact:** CRITICAL

## Summary

This PR provides the exact fix needed to remove the critical admin merge bypass vulnerability from the `.github/workflows/on-pull.yml` workflow file.

## The Problem

The `.github/workflows/on-pull.yml` file contains a critical security vulnerability at line 241:

```yaml
Use `gh pr merge --admin` to bypass branch protection when conditions are met.
```

This allows the OpenCode CI agent to merge PRs without human approval, completely bypassing branch protection rules.

## The Fix

**Note:** Due to GitHub security restrictions (workflows cannot modify themselves), this change must be applied manually by a repository maintainer with workflow permissions.

### Exact Changes Required in `.github/workflows/on-pull.yml`

**REMOVE lines 233-248:**

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

**REPLACE WITH:**

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

## How to Apply the Fix

**For a repository maintainer with workflow permissions:**

1. Download the patch file from this PR: `663_WORKFLOW_FIX.patch`
2. Navigate to your repository root
3. Apply the patch:

```bash
git apply 663_WORKFLOW_FIX.patch
```

4. Verify the changes:

```bash
git diff .github/workflows/on-pull.yml
```

Expected output should show:
- Removal of "Merge Conditions" section
- Removal of `Use gh pr merge --admin` instruction
- Addition of "PR Preparation" section
- Explicit prohibition of merging

5. Review the changes and confirm the admin bypass is removed
6. Commit with the message:

```bash
git add .github/workflows/on-pull.yml
git commit -m "security: Remove admin merge bypass from on-pull.yml workflow (fixes #663)

Critical security fix to remove the ability for the OpenCode CI agent to bypass branch
protection rules using admin merge privileges.

Changes:
- Removed 'Merge Conditions' section with --admin flag bypass
- Added 'PR Preparation' section instructing agent to prepare PRs for review
- Removed auto-merge, auto-close, and auto-delete actions
- Agent now only comments and labels PRs, leaving merge to human approval
- Branch protection rules are now enforced for all merges

Fixes #663
Fixes #629"
```

7. Push to main branch (or create a separate PR if required):

```bash
git push origin main
```

## Verification

After applying the fix, verify:

- [ ] No `--admin` flag exists in the workflow file
- [ ] No "bypass branch protection" language remains
- [ ] Workflow explicitly states agent MUST NOT merge
- [ ] All merge-related actions require human approval
- [ ] Only comment and label actions are permitted after PR preparation

### Test Workflow Syntax

```bash
python3 -c "import yaml; yaml.safe_load(open('.github/workflows/on-pull.yml')); print('YAML is valid')"
```

Output should be: `YAML is valid`

### Verify Patch Application

```bash
git log --oneline -1
```

Should show the commit with the security fix message.

## Security Impact

**Before Fix:**
- CI agent can merge ANY PR without human approval
- Branch protection rules are completely bypassed
- Sensitive changes could be merged automatically
- **Critical security vulnerability**

**After Fix:**
- CI agent prepares PRs for human review
- Branch protection rules are enforced
- All merges require human oversight
- **Security vulnerability eliminated**

## Related Issues

- **Fixes #663** - Apply security fix for issue #629
- **Fixes #629** - Remove admin merge bypass from on-pull.yml workflow (critical security)
- Related to #611 - GitHub workflow permission hardening

## Why This PR Contains Manual Instructions

The GitHub App does not have `workflows: write` permission, which prevents it from pushing changes to `.github/workflows/` directory. This is a GitHub security restriction - workflows should not be able to modify themselves to prevent accidental or malicious workflow self-modification.

This restriction is intentional and follows GitHub security best practices. Therefore, this PR provides the exact patch and clear step-by-step instructions for manual application by a repository maintainer.

## Next Steps After Fix is Applied

Once the fix is applied and merged:

1. The CI agent will prepare PRs for review but cannot merge without human approval
2. Branch protection rules will be enforced for all merges
3. Consider closing duplicate PRs that claimed to fix this issue:
   - #677 - Patch file approach
   - #678 - Patch file approach
   - #680 - Documentation only
   - #649 - Still contains admin bypass
   - #656 - Still contains admin bypass
   - #661 - Documentation only
   - #659 - Documentation only

## Breaking Changes

None. This only removes dangerous capabilities; workflow will continue to function correctly in "prepare PR for review" mode.
