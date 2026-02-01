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

## Verification

After applying the fix, verify:

- [ ] No `--admin` flag exists in the workflow file
- [ ] No "bypass branch protection" language remains
- [ ] Workflow explicitly states agent MUST NOT merge
- [ ] All merge-related actions require human approval
- [ ] Only comment and label actions are permitted after PR preparation

Test workflow syntax:

```bash
python3 -c "import yaml; yaml.safe_load(open('.github/workflows/on-pull.yml')); print('YAML is valid')"
```

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
- Related to #611 - SECURITY: Apply GitHub workflow permission hardening

## Breaking Changes

None. This only removes dangerous capabilities; workflow will continue to function correctly in "prepare PR for review" mode.
