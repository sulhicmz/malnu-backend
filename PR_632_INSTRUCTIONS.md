# Manual Application Instructions for Issue #632 - GitHub Workflow Consolidation

## ⚠️ Manual Action Required

Due to GitHub App workflow permission restrictions, the actual workflow file changes are included as patch files in this PR. A maintainer needs to manually apply the patches before merging.

## Overview

This PR partially addresses issue #632 by adding proper CI/CD workflows (`ci.yml` and `security.yml`) that were previously missing from the repository.

**IMPORTANT**: This PR does NOT delete or modify existing workflow files. It only adds new CI/CD workflows.

## Files to Apply

### 1. ci.yml - Testing and Quality Checks

**Patch File**: `ISSUE_632_CI_WORKFLOW.patch`

**Location**: `.github/workflows/ci.yml`

**Purpose**: Automated testing and quality checks for the codebase

**Features**:
- PHPUnit testing on push/PR to main branch
- PHPStan static analysis
- PHP CS Fixer code style validation
- Composer dependency caching
- Multi-version PHP support (8.2)
- Runs on both push and pull_request events

### 2. security.yml - Security Scanning

**Patch File**: `ISSUE_632_SECURITY_WORKFLOW.patch`

**Location**: `.github/workflows/security.yml`

**Purpose**: Security vulnerability scanning and monitoring

**Features**:
- Composer audit for dependency vulnerabilities
- Dependabot alerts summary for PRs
- Weekly scheduled security scans (Sundays at midnight UTC)
- Automatic PR comments with security findings
- SARIF upload for security events integration

### 3. WORKFLOW_CONSOLIDATION_STATUS.md - Documentation

**Patch File**: `ISSUE_632_DOCUMENTATION.patch`

**Location**: `WORKFLOW_CONSOLIDATION_STATUS.md`

**Purpose**: Documentation of workflow consolidation status and rationale

**Content**:
- Explanation of what was implemented
- Why existing workflows were not removed (they're actively used)
- Recommended approach for full consolidation
- Next steps and risk assessment

## Step-by-Step Application

### Option 1: Using `git apply` (Recommended)

```bash
# 1. Ensure you're on the branch that has these patch files
git checkout fix/632-consolidate-github-workflows

# 2. Apply the ci.yml patch
git apply ISSUE_632_CI_WORKFLOW.patch

# 3. Apply the security.yml patch
git apply ISSUE_632_SECURITY_WORKFLOW.patch

# 4. Apply the documentation patch
git apply ISSUE_632_DOCUMENTATION.patch

# 5. Verify the files were created
ls -la .github/workflows/ci.yml .github/workflows/security.yml
ls -la WORKFLOW_CONSOLIDATION_STATUS.md

# 6. Add the files to git
git add .github/workflows/ci.yml .github/workflows/security.yml WORKFLOW_CONSOLIDATION_STATUS.md

# 7. Commit the changes
git commit -m "refactor(ci): Add CI/CD workflows for testing and security (manual application)"

# 8. Push to the branch
git push origin fix/632-consolidate-github-workflows
```

### Option 2: Manual File Creation

If `git apply` doesn't work, create the files manually:

1. **ci.yml**: Copy contents from the patch file and create `.github/workflows/ci.yml`
2. **security.yml**: Copy contents from the patch file and create `.github/workflows/security.yml`
3. **WORKFLOW_CONSOLIDATION_STATUS.md**: Copy contents from the patch file and create root-level file

Then commit and push as shown in Option 1.

### Option 3: Using `patch` Command

```bash
# Apply patches using the patch command
patch -p1 < ISSUE_632_CI_WORKFLOW.patch
patch -p1 < ISSUE_632_SECURITY_WORKFLOW.patch
patch -p1 < ISSUE_632_DOCUMENTATION.patch

# Then git add and commit
git add .github/workflows/ci.yml .github/workflows/security.yml WORKFLOW_CONSOLIDATION_STATUS.md
git commit -m "refactor(ci): Add CI/CD workflows for testing and security (manual application)"
git push origin fix/632-consolidate-github-workflows
```

## Verification After Applying

### 1. Check Workflow Files Exist

```bash
# Verify new workflows are present
ls -la .github/workflows/

# Should show:
# - ci.yml (new)
# - security.yml (new)
# - (existing workflows remain unchanged)
```

### 2. Validate Workflow YAML Syntax

```bash
# Check if YAML is valid
gh workflow view .github/workflows/ci.yml --yaml
gh workflow view .github/workflows/security.yml --yaml
```

### 3. Trigger Workflows Manually

After applying patches and pushing:

```bash
# Trigger ci.yml manually for testing
gh workflow run ci.yml

# Trigger security.yml manually for testing
gh workflow run security.yml
```

### 4. Check Workflow Runs

```bash
# Check recent workflow runs
gh run list --limit 5

# View specific workflow run details
gh run view <run-id>
```

## What About Existing Workflows?

### Why They Weren't Removed

After checking workflow run history, I discovered that several existing agent automation workflows are actively in use:

- `oc-issue-solver.yml`: Currently in_progress (as of 2026-01-22)
- `oc-maintainer.yml`: Last ran today (2026-01-22)
- `oc-pr-handler.yml`: Last ran today (2026-01-22)
- `workflow-monitor.yml`: Actively running every 30 minutes

**Removing these active workflows would disrupt the project's automation.**

### Recommended Next Steps

For full consolidation (addressing the rest of issue #632):

1. **Review Documentation**: Read `WORKFLOW_CONSOLIDATION_STATUS.md` for full context
2. **Analyze Agent Workflows**: Document what each `oc-*.yml` workflow does
3. **Plan Consolidation**: Decide which agent workflows can be merged or removed
4. **Test Thoroughly**: Ensure automation continues to work after any changes
5. **Consolidate in Phases**: Remove workflows one at a time with monitoring

## Benefits of This PR

Even without removing all old workflows, this PR provides:

✅ **Traditional CI/CD** - The project now has proper automated testing and quality checks
✅ **Security Scanning** - Automated dependency vulnerability checks with PR comments
✅ **Better Documentation** - Clear explanation of workflow consolidation status
✅ **No Disruption** - Active agent automation continues to work unchanged
✅ **Foundation for Future** - New workflows follow standard GitHub Actions patterns

## Troubleshooting

### Patch Apply Fails

If `git apply` fails with errors:

```bash
# Try with -3-way merge flag
git apply --3way ISSUE_632_CI_WORKFLOW.patch
git apply --3way ISSUE_632_SECURITY_WORKFLOW.patch
git apply --3way ISSUE_632_DOCUMENTATION.patch
```

### Workflow Doesn't Trigger

After applying patches and pushing:

1. Check if branch protection rules are blocking workflow runs
2. Verify workflow permissions in `.github/workflows/ci.yml` and `.github/workflows/security.yml`
3. Check Actions tab in GitHub UI for error messages
4. Ensure you have necessary permissions to trigger workflows

### YAML Syntax Errors

If workflows have YAML syntax errors:

```bash
# Use yamllint or similar tool to validate
yamllint .github/workflows/ci.yml
yamllint .github/workflows/security.yml

# Or use GitHub CLI to validate
gh workflow view .github/workflows/ci.yml --yaml
```

## Related Issues

- **Fixes**: Partially addresses #632 (GitHub workflow consolidation)
- **Related**: #134 (CI/CD pipeline with automated testing)
- **Related**: #629 (Remove admin merge bypass from on-pull.yml)
- **Related**: #611 (GitHub workflow permission hardening)

## Questions?

If you have questions or need clarification:

1. Review `WORKFLOW_CONSOLIDATION_STATUS.md` for detailed context
2. Check the patch files to see exactly what changes are being made
3. Refer to issue #632 for the original problem statement
4. Test workflows in a feature branch before merging to main

## Summary

This PR adds proper CI/CD workflows to the repository without disrupting active agent automation. After manual application of these patch files, the repository will have:

- ✅ Automated testing and quality checks (`ci.yml`)
- ✅ Security scanning and vulnerability detection (`security.yml`)
- ✅ Clear documentation of consolidation status
- ✅ Foundation for future workflow consolidation

Full consolidation of existing agent automation workflows should be addressed in a separate PR/issue after careful analysis and planning.
