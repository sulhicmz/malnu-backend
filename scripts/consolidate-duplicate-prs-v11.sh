#!/bin/bash

# PR Consolidation Script - January 30, 2026
# Purpose: Close duplicate PRs and reduce from 93 to 74 open PRs
# Based on: ORCHESTRATOR_ANALYSIS_REPORT_v11.md

set -e

echo "=========================================="
echo "PR Consolidation Script - v11"
echo "Date: January 30, 2026"
echo "=========================================="
echo ""

# Check if gh CLI is installed
if ! command -v gh &> /dev/null; then
    echo "Error: GitHub CLI (gh) is not installed."
    echo "Please install it from: https://cli.github.com/"
    exit 1
fi

# Check if authenticated
if ! gh auth status &> /dev/null; then
    echo "Error: Not authenticated with GitHub CLI."
    echo "Please run: gh auth login"
    exit 1
fi

echo "✓ GitHub CLI is installed and authenticated"
echo ""

# Phase 1: Close AuthService Performance PRs (12 PRs)
echo "=========================================="
echo "Phase 1: Closing AuthService Performance PRs (12 PRs)"
echo "=========================================="
echo ""

AUTH_SERVICE_PRS=(624 619 618 622 615 613 610 606 602 599 598 596)

for pr in "${AUTH_SERVICE_PRS[@]}"; do
    echo "Closing PR #$pr..."
    gh pr close $pr \
        --comment "This PR is superseded by commit 8a514a2 (perf(auth): Fix critical performance issue - replace getAllUsers() with direct database queries). The issue has already been resolved in main branch.

**Resolution**: The AuthService N+1 query performance issue has been fixed in commit 8a514a2. All 12 duplicate PRs for this issue are being closed as superseded.

**Reference**: Issue #570 - [PERFORMANCE] Fix N+1 query in AuthService login()

---
*This PR was closed as part of the v11 PR consolidation effort (January 30, 2026).*"
    echo "✓ PR #$pr closed"
    echo ""
done

echo "✓ Phase 1 complete: 12 AuthService PRs closed"
echo ""

# Phase 2: Close duplicate error response PRs (1 PR)
echo "=========================================="
echo "Phase 2: Closing duplicate error response PR (1 PR)"
echo "=========================================="
echo ""

echo "Closing PR #639..."
gh pr close 639 \
    --comment "This PR is superseded by #644 (code-quality(middleware): Standardize error response format in JWTMiddleware), which is a more recent implementation of the same fix. Please review and merge #644 instead.

**Canonical PR**: #644 - code-quality(middleware): Standardize error response format in JWTMiddleware
**Issue**: #634 - code-quality(middleware): Standardize error response format across all middleware

---
*This PR was closed as part of the v11 PR consolidation effort (January 30, 2026).*"
echo "✓ PR #639 closed"
echo ""

echo "✓ Phase 2 complete: 1 error response PR closed"
echo ""

# Phase 3: Close duplicate attendance query PRs (1 PR)
echo "=========================================="
echo "Phase 3: Closing duplicate attendance query PR (1 PR)"
echo "=========================================="
echo ""

echo "Closing PR #637..."
gh pr close 637 \
    --comment "This PR is superseded by #642 (perf(attendance): Optimize multiple count queries in calculateAttendanceStatistics()), which is a more recent implementation of the same fix. Please review and merge #642 instead.

**Canonical PR**: #642 - perf(attendance): Optimize multiple count queries in calculateAttendanceStatistics()
**Issue**: #635 - perf(attendance): Optimize multiple count queries in calculateAttendanceStatistics()

---
*This PR was closed as part of the v11 PR consolidation effort (January 30, 2026).*"
echo "✓ PR #637 closed"
echo ""

echo "✓ Phase 3 complete: 1 attendance query PR closed"
echo ""

# Phase 4: Close duplicate workflow permission PRs (2 PRs)
echo "=========================================="
echo "Phase 4: Closing duplicate workflow permission PRs (2 PRs)"
echo "=========================================="
echo ""

echo "Closing PR #614..."
gh pr close 614 \
    --comment "This PR is superseded by #626 (security: Apply GitHub workflow permission hardening (#611)), which is a more recent implementation of the same security fix. Please review and merge #626 instead.

**Canonical PR**: #626 - security: Apply GitHub workflow permission hardening (#611)
**Documentation PR**: #620 - docs: Add manual application guide for workflow permission hardening (#611)
**Issue**: #611 - SECURITY: Apply GitHub workflow permission hardening (reopens #182)

---
*This PR was closed as part of the v11 PR consolidation effort (January 30, 2026).*"
echo "✓ PR #614 closed"
echo ""

echo "Closing PR #617..."
gh pr close 617 \
    --comment "This PR is superseded by #620 (docs: Add manual application guide for workflow permission hardening (#611)), which is a more recent documentation update. Please review #620 instead.

**Canonical PR**: #626 - security: Apply GitHub workflow permission hardening (#611)
**Documentation PR**: #620 - docs: Add manual application guide for workflow permission hardening (#611)
**Issue**: #611 - SECURITY: Apply GitHub workflow permission hardening (reopens #182)

---
*This PR was closed as part of the v11 PR consolidation effort (January 30, 2026).*"
echo "✓ PR #617 closed"
echo ""

echo "✓ Phase 4 complete: 2 workflow permission PRs closed"
echo ""

# Phase 5: Close duplicate password validation PRs (2 PRs)
echo "=========================================="
echo "Phase 5: Closing duplicate password validation PRs (2 PRs)"
echo "=========================================="
echo ""

echo "Closing PR #640..."
gh pr close 640 \
    --comment "This PR is superseded by #651 (fix(auth): Remove duplicate password_verify check in changePassword() method), which is a more recent implementation of the same fix. Please review and merge #651 instead.

**Canonical PR**: #651 - fix(auth): Remove duplicate password_verify check in changePassword() method
**Issue**: #633 - code-quality(auth): Remove duplicate password_verify check in changePassword() method

---
*This PR was closed as part of the v11 PR consolidation effort (January 30, 2026).*"
echo "✓ PR #640 closed"
echo ""

echo "Closing PR #578..."
gh pr close 578 \
    --comment "This PR is superseded by #651 (fix(auth): Remove duplicate password_verify check in changePassword() method), which is a more recent implementation of the same fix. Please review and merge #651 instead.

**Canonical PR**: #651 - fix(auth): Remove duplicate password_verify check in changePassword() method
**Issue**: #633 - code-quality(auth): Remove duplicate password_verify check in changePassword() method

---
*This PR was closed as part of the v11 PR consolidation effort (January 30, 2026).*"
echo "✓ PR #578 closed"
echo ""

echo "✓ Phase 5 complete: 2 password validation PRs closed"
echo ""

# Phase 6: Close duplicate security fix PRs (1 PR)
echo "=========================================="
echo "Phase 6: Closing duplicate security fix PR (1 PR)"
echo "=========================================="
echo ""

echo "Closing PR #645..."
gh pr close 645 \
    --comment "This PR is superseded by #649 (fix(security): Remove admin merge bypass from on-pull.yml workflow (CORRECT fix)), which is the correct implementation of the security fix. Please review and merge #649 instead.

**Canonical PR**: #649 - fix(security): Remove admin merge bypass from on-pull.yml workflow (CORRECT fix)
**Issue**: #629 - security(critical): Remove admin merge bypass from on-pull.yml workflow

**CRITICAL PRIORITY**: This is a critical security issue that has been open for 7 days. Please review and merge #649 immediately.

---
*This PR was closed as part of the v11 PR consolidation effort (January 30, 2026).*"
echo "✓ PR #645 closed"
echo ""

echo "✓ Phase 6 complete: 1 security fix PR closed"
echo ""

# Summary
echo "=========================================="
echo "PR Consolidation Summary"
echo "=========================================="
echo ""
echo "PRs Closed:"
echo "  - Phase 1 (AuthService): 12 PRs"
echo "  - Phase 2 (Error Response): 1 PR"
echo "  - Phase 3 (Attendance): 1 PR"
echo "  - Phase 4 (Workflow): 2 PRs"
echo "  - Phase 5 (Password): 2 PRs"
echo "  - Phase 6 (Security): 1 PR"
echo "  -"
echo "  Total: 19 PRs closed"
echo ""
echo "Before: 93 open PRs"
echo "After: ~74 open PRs"
echo "Reduction: 19 PRs (20%)"
echo ""
echo "=========================================="
echo "✓ PR Consolidation Complete!"
echo "=========================================="
echo ""

# Update issues with comments
echo "Updating affected issues with comments..."
echo ""

echo "Updating issue #570..."
gh issue comment 570 \
    --body "### PR Status Update - January 30, 2026

The AuthService performance issue (N+1 query in login()) has been resolved in commit 8a514a2.

**Duplicate PRs Closed**: 12
**Resolution**: Direct database query implementation
**Commit**: https://github.com/sulhicmz/malnu-backend/commit/8a514a2

All 12 duplicate PRs for this issue have been superseded by the commit in main branch and have been closed as part of the v11 PR consolidation effort.

---
*This update was created as part of the v11 PR consolidation effort (January 30, 2026).*"
echo "✓ Issue #570 updated"
echo ""

echo "Updating issue #634..."
gh issue comment 634 \
    --body "### PR Status Update - January 30, 2026

**Canonical PR**: #644 - code-quality(middleware): Standardize error response format in JWTMiddleware
**Duplicate PR Closed**: #639

Please review and merge #644.

---
*This update was created as part of the v11 PR consolidation effort (January 30, 2026).*"
echo "✓ Issue #634 updated"
echo ""

echo "Updating issue #635..."
gh issue comment 635 \
    --body "### PR Status Update - January 30, 2026

**Canonical PR**: #642 - perf(attendance): Optimize multiple count queries in calculateAttendanceStatistics()
**Duplicate PR Closed**: #637

Please review and merge #642.

---
*This update was created as part of the v11 PR consolidation effort (January 30, 2026).*"
echo "✓ Issue #635 updated"
echo ""

echo "Updating issue #611..."
gh issue comment 611 \
    --body "### PR Status Update - January 30, 2026

**Canonical PR**: #626 - security: Apply GitHub workflow permission hardening (#611)
**Documentation PR**: #620 - docs: Add manual application guide for workflow permission hardening (#611)
**Duplicate PRs Closed**: #614, #617

Please review and merge #626 and #620.

---
*This update was created as part of the v11 PR consolidation effort (January 30, 2026).*"
echo "✓ Issue #611 updated"
echo ""

echo "Updating issue #633..."
gh issue comment 633 \
    --body "### PR Status Update - January 30, 2026

**Canonical PR**: #651 - fix(auth): Remove duplicate password_verify check in changePassword() method
**Duplicate PRs Closed**: #640, #578

Please review and merge #651.

---
*This update was created as part of the v11 PR consolidation effort (January 30, 2026).*"
echo "✓ Issue #633 updated"
echo ""

echo "Updating issue #629..."
gh issue comment 629 \
    --body "### PR Status Update - January 30, 2026

**Canonical PR**: #649 - fix(security): Remove admin merge bypass from on-pull.yml workflow (CORRECT fix)
**Duplicate PR Closed**: #645

**CRITICAL PRIORITY**: This is a critical security issue that has been open for 7 days. Please review and merge #649 immediately.

**Risk**: The workflow contains instructions to use \`gh pr merge --admin\` to bypass branch protection rules, allowing OpenCode agent to merge PRs without human review.

**Solution**: Remove \`--admin\` flag, add human approval requirement for all merges.

---
*This update was created as part of the v11 PR consolidation effort (January 30, 2026).*"
echo "✓ Issue #629 updated"
echo ""

echo "Updating issue #572..."
gh issue comment 572 \
    --body "### PR Consolidation Progress - January 30, 2026

**Consolidation Status**: Phase 1 Complete
**PRs Closed**: 19 out of 50+ duplicates
**Open PRs Reduced**: From 93 to ~74 (20% reduction)

**PRs Closed by Group**:
- Group 1 (AuthService Performance): 12 PRs (superseded by commit 8a514a2)
- Group 2 (Error Response): 1 PR (superseded by #644)
- Group 3 (Attendance): 1 PR (superseded by #642)
- Group 4 (Workflow): 2 PRs (superseded by #626, #620)
- Group 5 (Password): 2 PRs (superseded by #651)
- Group 6 (Security): 1 PR (superseded by #649)

**Next Steps**:
1. Review and merge canonical PRs: #644, #642, #626, #620, #651, #649
2. Continue consolidation for remaining duplicate PRs
3. Create GitHub Projects for better organization

---
*This update was created as part of the v11 PR consolidation effort (January 30, 2026).*"
echo "✓ Issue #572 updated"
echo ""

echo "=========================================="
echo "✓ All issues updated!"
echo "=========================================="
echo ""

echo "Next Steps:"
echo "1. Review and merge canonical PRs:"
echo "   - #644 - Standardize error response format"
echo "   - #642 - Optimize attendance queries"
echo "   - #626 - Workflow permission hardening"
echo "   - #620 - Workflow permission documentation"
echo "   - #651 - Remove duplicate password check"
echo "   - #649 - Remove admin merge bypass (CRITICAL!)"
echo ""
echo "2. Create GitHub Projects using: docs/GITHUB_PROJECTS_SETUP_EXECUTION_GUIDE_v11.md"
echo ""
echo "3. Continue consolidation for remaining duplicate PRs"
echo ""
echo "=========================================="
echo "✓ Script execution complete!"
echo "=========================================="
