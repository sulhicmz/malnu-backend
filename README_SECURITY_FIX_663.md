# Critical Security Fix - Issue #663

## Summary

This directory contains the security fix to remove the admin merge bypass vulnerability from `.github/workflows/on-pull.yml`.

## The Vulnerability

The workflow file at line 241 contained a critical security vulnerability:

```yaml
Use `gh pr merge --admin` to bypass branch protection when conditions are met.
```

This instruction allowed the OpenCode CI agent to merge PRs WITHOUT enforcing branch protection rules, completely bypassing human oversight.

**Security Impact:**
- ✅ The CI agent could merge ANY PR without human approval
- ✅ Branch protection rules were completely bypassed
- ✅ Sensitive changes could be merged automatically without review
- ❌ **CRITICAL SECURITY VULNERABILITY**

## The Fix

The patch file `SECURITY_FIX_663.patch` removes this vulnerability by:

**Removed:**
- "Merge Conditions" section with auto-merge logic
- The vulnerable `Use gh pr merge --admin` instruction (line 241)
- "After successful merge" section with auto-close and auto-delete

**Added:**
- "PR Preparation" section
- Explicit statement: "The agent SHOULD prepare PRs for human review but MUST NOT merge"
- Security notice: "Do NOT use --admin flag or any merge bypass mechanism"
- Requirement: "Branch protection rules MUST be enforced - all merges require human approval"
- "After PR is prepared" section with comment and label actions only

## How to Apply This Fix

### Step 1: Apply the Patch

A repository maintainer with workflow permissions should apply the patch:

```bash
# Navigate to the repository root
cd /path/to/malnu-backend

# Apply the patch
git apply SECURITY_FIX_663.patch

# Verify the changes
git diff .github/workflows/on-pull.yml
```

### Step 2: Review the Changes

The diff should show:

```diff
-          3. Merge Conditions:
-              ONLY merge if:
+          3. PR Preparation:
+              The agent SHOULD prepare PRs for human review but MUST NOT merge.
+
+              PR is READY for human review when:
               - No conflicts
               - Build passes
               - All checks green (dont wait for 'on-pull' check, its you)
               - All PR comments resolved
               - No security-sensitive change without review
+              - Code follows project style guidelines

-              Use `gh pr merge --admin` to bypass branch protection when conditions are met.
+              SECURITY: Do NOT use --admin flag or any merge bypass mechanism.
+              Branch protection rules MUST be enforced - all merges require human approval.

-              NEVER delete branch if merge fails.
-
-           4. After successful merge:
-              - Close linked issues
-              - Delete remote branch ONLY after successful merge
-              - Log action
+           4. After PR is prepared:
+              - Comment with status summary
+              - Label appropriately (ready for review, needs changes, etc.)
+              - Do NOT merge - leave for human review
```

### Step 3: Commit the Changes

```bash
git add .github/workflows/on-pull.yml
git commit -m "security: Remove admin merge bypass from on-pull.yml workflow

Critical security fix to remove the ability for the CI agent to bypass
branch protection rules using admin merge privileges.

The workflow previously instructed agents to use 'gh pr merge --admin' which
allowed automated merging without human oversight, completely bypassing
branch protection rules. This vulnerability has been removed.

Changes:
- Removed 'Merge Conditions' section with admin bypass logic
- Added 'PR Preparation' section with explicit security controls
- Added explicit warning: 'Do NOT use --admin flag or any merge bypass mechanism'
- All merges now require human approval per branch protection rules

Fixes #663
Fixes #629"
```

### Step 4: Push and Merge

```bash
git push origin <your-branch>
```

Then create a pull request or merge directly if appropriate.

## Verification Checklist

After applying the fix, verify:

- [ ] No `--admin` flag exists in `.github/workflows/on-pull.yml`
- [ ] No "bypass branch protection" language remains
- [ ] Workflow explicitly states agent MUST NOT merge
- [ ] All merge actions require human approval
- [ ] Only comment and label actions are permitted after PR preparation

## Test Workflow Syntax

Validate the workflow YAML is still correct:

```bash
python3 -c "import yaml; yaml.safe_load(open('.github/workflows/on-pull.yml')); print('YAML is valid')"
```

Expected output: `YAML is valid`

## Security Impact After Fix

**Before Fix (VULNERABLE):**
- ❌ CI agent can merge ANY PR without human approval
- ❌ Branch protection rules are completely bypassed
- ❌ Sensitive changes could be merged automatically

**After Fix (SECURE):**
- ✅ CI agent prepares PRs for human review
- ✅ Branch protection rules are enforced
- ✅ All merges require human oversight
- ✅ Security vulnerability eliminated

## Related Issues

This fix resolves:
- **Issue #663** - [MAINTENANCE] Apply security fix for issue #629
- **Issue #629** - security(critical): Remove admin merge bypass from on-pull.yml workflow

## Duplicate PRs to Close

After this fix is applied, the following PRs should be closed as they don't contain the correct fix:
- #674 - Contains patch file but not the actual fix to the workflow
- #649 - Still contains admin bypass
- #656 - Still contains admin bypass
- #661 - Documentation only
- #659 - Documentation only
- #677 - Patch file approach (this PR supersedes it)

## Urgency

**CRITICAL** - This is a security vulnerability that should be fixed immediately to ensure all merges respect branch protection rules and require human oversight.
