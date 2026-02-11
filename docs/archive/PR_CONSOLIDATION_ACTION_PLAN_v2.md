# Pull Request Consolidation Action Plan

> **Date**: January 23, 2026
> **Purpose**: Eliminate duplicate PRs and reduce repository clutter
> **Impact**: 21+ duplicate PRs identified for consolidation

---

## Executive Summary

The repository has a **critical duplicate PR problem** with 21+ open PRs addressing the same 5 core issues. This creates review overhead, merge conflicts, and contributor confusion.

**Duplicate PR Count**: 21+
**Unique Issues Affected**: 5
**Estimated Time to Consolidate**: 2-3 hours

---

## Duplicate PR Groups

### Group 1: AuthService Performance (#570) - 12 Duplicate PRs

**Issue**: Fix N+1 query in AuthService login() - replace getAllUsers() with direct query

**Status**: ✅ **RESOLVED in commit 8a514a2**

**Duplicate PRs** (12 total):

| PR # | Title | Branch | Action |
|-------|--------|---------|--------|
| #624 | fix(auth): Replace getAllUsers() with direct database queries | fix/issue-570-n-plus-1-query-auth-service-final | CLOSE - superseded by commit 8a514a2 |
| #619 | fix(auth): Replace getAllUsers() with direct queries to fix N+1 query problem | fix/issue-570-n-plus-one-query-login | CLOSE - superseded by commit 8a514a2 |
| #618 | fix(auth): Replace inefficient getAllUsers() with direct queries | fix/issue-570-n-plus-1-query-auth-service | CLOSE - superseded by commit 8a514a2 |
| #622 | perf(auth): Fix N+1 query in login() and getUserFromToken() | fix/issue-570-n-plus-1-query-auth-service-final | CLOSE - superseded by commit 8a514a2 |
| #615 | fix(auth): Fix N+1 query in AuthService login() and getUserFromToken() | fix/570-n-plus-1-query-auth-service | CLOSE - superseded by commit 8a514a2 |
| #613 | fix(auth): Replace N+1 query in login() and getUserFromToken() with direct queries | fix/570-n1-query-auth-login | CLOSE - superseded by commit 8a514a2 |
| #610 | perf(auth): Fix N+1 query in login() and getUserFromToken() | fix/issue-570-n-plus-1-query-v3 | CLOSE - superseded by commit 8a514a2 |
| #606 | perf(auth): Fix N+1 query in login() and getUserFromToken() | fix/issue-570-n-plus-1-query-v2 | CLOSE - superseded by commit 8a514a2 |
| #602 | perf(auth): Fix N+1 query in AuthService login() and getUserFromToken() | fix/570-n-plus-one-query-perf | CLOSE - superseded by commit 8a514a2 |
| #599 | perf(auth): Fix N+1 query in AuthService login() and getUserFromToken() | fix/570-n-plus-one-query-optimization | CLOSE - superseded by commit 8a514a2 |
| #598 | perf(auth): Fix N+1 query in AuthService login() and getUserFromToken() | fix/issue-570-n-plus-one-query-authservice | CLOSE - superseded by commit 8a514a2 |
| #596 | perf(auth): Fix N+1 query in AuthService login() and getUserFromToken() | fix/issue-570-n-plus-one-query-authservice | CLOSE - superseded by commit 8a514a2 |

**Bulk Close Command**:
```bash
gh pr close 624 619 618 622 615 613 610 606 602 599 598 596 \
  --comment "This PR is superseded by commit 8a514a2 (perf(auth): Fix critical performance issue - replace getAllUsers() with direct database queries). The issue has already been resolved in main branch."
```

---

### Group 2: Standardize Error Response (#634) - 2 Duplicate PRs

**Issue**: Standardize error response format in JWTMiddleware

**Status**: ⚠️ Open

**Duplicate PRs** (2 total):

| PR # | Title | Branch | Action |
|-------|--------|---------|--------|
| #639 | fix(middleware): Standardize error response format in JWTMiddleware | fix/634-standardize-middleware-error-response | CLOSE - older PR, superseded by #644 |
| #644 | code-quality(middleware): Standardize error response format in JWTMiddleware | fix/634-standardize-error-response | KEEP - most recent PR |

**Action**:
```bash
gh pr close 639 \
  --comment "This PR is superseded by #644 (code-quality(middleware): Standardize error response format in JWTMiddleware), which is a more recent implementation of the same fix. Please review and merge #644 instead."
```

---

### Group 3: Optimize Attendance Queries (#635) - 2 Duplicate PRs

**Issue**: Optimize multiple count queries in calculateAttendanceStatistics()

**Status**: ⚠️ Open

**Duplicate PRs** (2 total):

| PR # | Title | Branch | Action |
|-------|--------|---------|--------|
| #637 | perf(attendance): Optimize multiple count queries in calculateAttendanceStatistics() | fix/635-optimize-attendance-statistics | CLOSE - older PR, superseded by #642 |
| #642 | perf(attendance): Optimize multiple count queries in calculateAttendanceStatistics() | fix/635-optimize-attendance-queries | KEEP - most recent PR |

**Action**:
```bash
gh pr close 637 \
  --comment "This PR is superseded by #642 (perf(attendance): Optimize multiple count queries in calculateAttendanceStatistics()), which is a more recent implementation of the same fix. Please review and merge #642 instead."
```

---

### Group 4: Workflow Permission Hardening (#611) - 4 Duplicate PRs

**Issue**: Apply GitHub workflow permission hardening

**Status**: ⚠️ Open

**Duplicate PRs** (3 total, 1 docs PR):

| PR # | Title | Branch | Action |
|-------|--------|---------|--------|
| #626 | security: Apply GitHub workflow permission hardening (#611) | fix/611-workflow-permission-hardening | KEEP - actual security fix |
| #614 | security: Apply GitHub workflow permission hardening (reopens #182) | security/workflow-permissions | CLOSE - superseded by #626 |
| #620 | docs: Add manual application guide for workflow permission hardening (#611) | docs/611-workflow-permission-manual-guide | REVIEW - may keep as documentation |
| #617 | docs: Add workflow permission hardening manual application instructions (#611) | docs/workflow-permission-fix-instructions | CLOSE - superseded by #620 |

**Action**:
```bash
gh pr close 614 \
  --comment "This PR is superseded by #626 (security: Apply GitHub workflow permission hardening (#611)), which is a more recent implementation of the same security fix. Please review and merge #626 instead."

gh pr close 617 \
  --comment "This PR is superseded by #620 (docs: Add manual application guide for workflow permission hardening (#611)), which is a more recent documentation update. Please review #620 instead."
```

---

### Group 5: Duplicate Password Check (#633) - 1 Duplicate PR

**Issue**: Remove duplicate password_verify check in changePassword() method

**Status**: ⚠️ Open

**Duplicate PRs** (1 total):

| PR # | Title | Branch | Action |
|-------|--------|---------|--------|
| #640 | fix(auth): Remove duplicate password_verify check in changePassword() method | fix/633-remove-duplicate-password-check | KEEP - only PR for this issue |

**Action**: None (keep #640 as the only PR for this issue)

---

## Consolidation Execution Plan

### Phase 1: Bulk Close Superseded PRs (30 minutes)

**Priority 1**: AuthService Performance PRs (resolved by commit)
```bash
gh pr close 624 619 618 622 615 613 610 606 602 599 598 596 \
  --comment "This PR is superseded by commit 8a514a2 (perf(auth): Fix critical performance issue - replace getAllUsers() with direct database queries). The issue has already been resolved in main branch."
```

**Priority 2**: Duplicate error response PR
```bash
gh pr close 639 \
  --comment "This PR is superseded by #644 (code-quality(middleware): Standardize error response format in JWTMiddleware), which is a more recent implementation of the same fix. Please review and merge #644 instead."
```

**Priority 3**: Duplicate attendance query PR
```bash
gh pr close 637 \
  --comment "This PR is superseded by #642 (perf(attendance): Optimize multiple count queries in calculateAttendanceStatistics()), which is a more recent implementation of the same fix. Please review and merge #642 instead."
```

**Priority 4**: Duplicate workflow PRs
```bash
gh pr close 614 617 \
  --comment "This PR is superseded by more recent implementations. For the security fix, please review #626. For documentation, please review #620."
```

### Phase 2: Verify Remaining PRs (15 minutes)

```bash
# List remaining open PRs
gh pr list --state open --limit 50

# Verify no duplicates remain
# Check that each remaining PR addresses a unique issue
```

### Phase 3: Update Issue References (30 minutes)

For each affected issue, add comment noting which PR is canonical:

**Issue #570**:
```bash
gh issue comment 570 \
  --body "### PR Status Update

The AuthService performance issue (N+1 query in login()) has been resolved in commit 8a514a2.

**Duplicate PRs Closed**: 12
**Resolution**: Direct database query implementation
**Commit**: https://github.com/sulhicmz/malnu-backend/commit/8a514a2

All 12 duplicate PRs for this issue have been superseded by the commit in main branch."
```

**Issue #634**:
```bash
gh issue comment 634 \
  --body "### PR Status Update

**Canonical PR**: #644 - code-quality(middleware): Standardize error response format in JWTMiddleware
**Duplicate PR Closed**: #639

Please review and merge #644."
```

**Issue #635**:
```bash
gh issue comment 635 \
  --body "### PR Status Update

**Canonical PR**: #642 - perf(attendance): Optimize multiple count queries in calculateAttendanceStatistics()
**Duplicate PR Closed**: #637

Please review and merge #642."
```

**Issue #611**:
```bash
gh issue comment 611 \
  --body "### PR Status Update

**Canonical PR**: #626 - security: Apply GitHub workflow permission hardening (#611)
**Documentation PR**: #620 - docs: Add manual application guide for workflow permission hardening (#611)
**Duplicate PRs Closed**: #614, #617

Please review and merge #626 and #620."
```

### Phase 4: Duplicate Prevention (30 minutes)

Add comment to issue #545 (duplicate PR prevention) to ensure it's being enforced:

```bash
gh issue comment 545 \
  --body "### Duplicate PR Prevention - Update January 23, 2026

**Recent Consolidation**: 21+ duplicate PRs closed
**Main Issue**: AuthService performance (#570) - 12 duplicate PRs
**Root Cause**: Multiple agents/ contributors creating PRs for same issue

**Recommendations**:
1. Run `./scripts/check-duplicate-pr.sh <issue_number>` before creating new PR
2. Always check `gh pr list --state open` for existing PRs
3. Use issue PR checklist to track work in progress

**Consolidation Reference**: [ORCHESTRATOR_ANALYSIS_REPORT_v10.md](docs/ORCHESTRATOR_ANALYSIS_REPORT_v10.md)"
```

---

## Expected Results

### Before Consolidation:
- Open PRs: 30+
- Duplicate PRs: 21+
- Review Overhead: High
- Merge Conflicts: High probability

### After Consolidation:
- Open PRs: ~15 (50% reduction)
- Duplicate PRs: 0
- Review Overhead: Low
- Merge Conflicts: Low probability

---

## Duplicate Prevention Strategies

### 1. Automated Checks

Update `on-pull.yml` workflow to check for duplicate PRs:

```yaml
- name: Check for Duplicate PRs
  run: |
    PR_NUMBER=$(echo $GITHUB_REF | grep -oP 'refs/pull/\K\d+')
    ISSUE_NUMBER=$(gh pr view $PR_NUMBER --json body --jq '.body' | grep -oP '#\K\d+' | head -1)

    if [ -z "$ISSUE_NUMBER" ]; then
      echo "No issue reference found"
      exit 0
    fi

    EXISTING_PRS=$(gh pr list --state open --search "in:title $ISSUE_NUMBER" --json number --jq '.[].number' | grep -v $PR_NUMBER)

    if [ -n "$EXISTING_PRS" ]; then
      echo "Duplicate PR detected! Existing PRs: $EXISTING_PRS"
      gh pr comment $PR_NUMBER --body "⚠️ **DUPLICATE PR DETECTED**

      This PR addresses issue #$ISSUE_NUMBER, which already has open PRs:
      $EXISTING_PRS

      Please review existing PRs and consider closing this one if it duplicates existing work."
      exit 1
    fi
```

### 2. Process Improvements

1. **Issue PR Assignment**: When assigning an issue to a PR, check if PR already exists
2. **Weekly Review**: Review all open PRs weekly for duplicates
3. **Label System**: Use `duplicate` label to mark duplicate PRs automatically
4. **Documentation**: Update CONTRIBUTING.md with duplicate PR prevention guidance

### 3. Documentation Updates

Add to `CONTRIBUTING.md`:

```markdown
## Duplicate PR Prevention

Before creating a PR:

1. **Check for Existing PRs**:
   ```bash
   gh pr list --state open --search "issue-number"
   ```

2. **Use the Duplicate Check Script**:
   ```bash
   ./scripts/check-duplicate-pr.sh <issue-number>
   ```

3. **Comment on Issue First**:
   Before starting work, comment on the issue to claim it:
   - "I'm working on this issue"
   - Include branch name

4. **Link PR to Issue**:
   - Include `Fixes #<issue-number>` in PR title
   - Reference issue in PR description

If you find a duplicate PR:
- Comment on the original PR with findings
- Close your PR with comment explaining the duplicate
- Link to the canonical PR
```

---

## Summary

### PRs to Close (Total: 17)

| Group | PRs to Close | Reason |
|--------|--------------|--------|
| AuthService Performance | 11 (624,619,618,622,615,613,610,606,602,599,598,596) | Superseded by commit 8a514a2 |
| Error Response | 1 (639) | Superseded by #644 |
| Attendance Queries | 1 (637) | Superseded by #642 |
| Workflow Security | 2 (614,617) | Superseded by #626, #620 |

### PRs to Keep (Total: 3)

| PR # | Title | Issue |
|------|--------|--------|
| #640 | fix(auth): Remove duplicate password_verify check | #633 |
| #644 | code-quality(middleware): Standardize error response format | #634 |
| #642 | perf(attendance): Optimize multiple count queries | #635 |
| #626 | security: Apply GitHub workflow permission hardening | #611 |
| #620 | docs: Add manual application guide for workflow permission | #611 |

### Total Reduction: 17 PRs (50% reduction)

---

## References

- [Issue #545](https://github.com/sulhicmz/malnu-backend/issues/545) - Duplicate PR Prevention
- [Issue #572](https://github.com/sulhicmz/malnu-backend/issues/572) - PR Consolidation
- [ORCHESTRATOR_ANALYSIS_REPORT_v10.md](ORCHESTRATOR_ANALYSIS_REPORT_v10.md) - Latest Analysis
- [CONTRIBUTING.md](CONTRIBUTING.md) - Contribution Guidelines

---

**Document Created**: January 23, 2026
**Author**: Repository Orchestrator v10
**Status**: Ready for execution
**Estimated Completion Time**: 2-3 hours
