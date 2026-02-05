# Duplicate Issues Consolidation Plan

> **Date**: January 22, 2026  
> **Orchestrator**: v9  
> **Purpose**: Consolidate duplicate GitHub issues

---

## Executive Summary

This document identifies duplicate GitHub issues across the repository and provides a consolidation plan to reduce issue count from 326+ to a more manageable number.

## Duplicate Issue Groups

### Group 1: Auth N+1 Query Issues (12 duplicates)

**Problem**: All address the same issue - fixing N+1 query in AuthService login() and getUserFromToken() methods.

| Issue | Title | Status | Action |
|-------|-------|--------|--------|
| #602 | perf(auth): Fix N+1 query in login() and getUserFromToken() | CLOSED | Keep closed |
| #599 | perf(auth): Fix N+1 query in AuthService login() and getUserFromToken() | CLOSED | Keep closed |
| #598 | fix(auth): Fix N+1 query in AuthService login() and getUserFromToken() | CLOSED | Keep closed |
| #596 | perf(auth): Fix N+1 query in login() and getUserFromToken() | CLOSED | Keep closed |
| #595 | fix(performance): Replace N+1 query in AuthService login() with direct query | CLOSED | Keep closed |
| #591 | fix(performance): Replace N+1 query in AuthService login() with direct query | CLOSED | Keep closed |
| #576 | fix(performance): Replace N+1 query in AuthService login() with direct query | CLOSED | Keep closed |
| #622 | perf(auth): Fix N+1 query in login() and getUserFromToken() | OPEN | ❌ Close as duplicate of #630 |
| #615 | fix(auth): Fix N+1 query in login() and getUserFromToken() | OPEN | ❌ Close as duplicate of #630 |
| #613 | fix(auth): Replace N+1 query in login() and getUserFromToken() with direct queries | OPEN | ❌ Close as duplicate of #630 |
| #610 | perf(auth): Fix N+1 query in login() and getUserFromToken() | OPEN | ❌ Close as duplicate of #630 |
| #606 | perf(auth): Fix N+1 query in login() and getUserFromToken() | OPEN | ❌ Close as duplicate of #630 |

**Canonical Issue**: #630 - "perf(auth): Fix critical performance issue - getAllUsers() loads ALL users for login"

**Total to Close**: 6 issues

---

### Group 2: Workflow Permission Hardening Issues (4 duplicates)

**Problem**: All address GitHub workflow security and permission hardening.

| Issue | Title | Status | Action |
|-------|-------|--------|--------|
| #626 | security: Apply GitHub workflow permission hardening (#611) | OPEN | ❌ Close as duplicate of #629 |
| #620 | docs: Add manual application guide for workflow permission hardening (#611) | OPEN | ❌ Close as duplicate of #629 |
| #617 | docs: Add workflow permission hardening manual application instructions (#611) | OPEN | ❌ Close as duplicate of #629 |
| #614 | security: Apply GitHub workflow permission hardening (reopens #182) | OPEN | ❌ Close as duplicate of #629 |

**Canonical Issue**: #629 - "security(critical): Remove admin merge bypass from on-pull.yml workflow"

**Total to Close**: 4 issues

---

### Group 3: Replace getAllUsers() Issues (3 duplicates)

**Problem**: All address replacing getAllUsers() with direct database queries.

| Issue | Title | Status | Action |
|-------|-------|--------|--------|
| #624 | fix(auth): Replace getAllUsers() with direct database queries for performance | OPEN | ❌ Close as duplicate of #630 |
| #619 | fix(auth): Replace getAllUsers() with direct queries to fix N+1 query problem | OPEN | ❌ Close as duplicate of #630 |
| #618 | fix(auth): Replace inefficient getAllUsers() with direct queries | OPEN | ❌ Close as duplicate of #630 |

**Canonical Issue**: #630 - "perf(auth): Fix critical performance issue - getAllUsers() loads ALL users for login"

**Total to Close**: 3 issues

---

### Group 4: CI/CD Consolidation Issues (2 duplicates)

**Problem**: All address CI/CD pipeline implementation and consolidation.

| Issue | Title | Status | Action |
|-------|-------|--------|--------|
| #604 | fix(cicd): Implement CI/CD pipeline with automated testing and quality gates | OPEN | ❌ Close as duplicate of #632 |
| #625 | docs(ci): Add comprehensive CI/CD pipeline implementation guide for issue #134 | OPEN | ❌ Close as duplicate of #632 |

**Canonical Issue**: #632 - "refactor(ci): Consolidate redundant GitHub workflows (11 → 3-4 workflows)"

**Total to Close**: 2 issues

---

### Group 5: Duplicate Password Validation Issues (2 duplicates)

**Problem**: All address removing duplicate password verification code.

| Issue | Title | Status | Action |
|-------|-------|--------|--------|
| #578 | fix(code-quality): Remove duplicate password validation in AuthService | OPEN | ❌ Close as duplicate of #633 |
| #589 | fix(code-quality): Remove duplicate password validation in AuthService changePassword() | CLOSED | Keep closed |

**Canonical Issue**: #633 - "code-quality(auth): Remove duplicate password_verify check in changePassword() method"

**Total to Close**: 1 issue

---

### Group 6: Form Request Validation Issues (5 duplicates)

**Problem**: All address implementing Form Request validation to eliminate duplicate validation code.

| Issue | Title | Status | Action |
|-------|-------|--------|--------|
| #560 | feat: Implement Form Request validation classes to eliminate duplicate validation code | CLOSED | Keep closed |
| #543 | feat: Implement Form Request validation classes to eliminate duplicate validation code | OPEN | ❌ Close as duplicate of #560 |
| #542 | feat(validation): Implement Form Request validation to eliminate duplicate validation code | CLOSED | Keep closed |
| #539 | feat(issue-349): Implement Form Request validation to eliminate duplicate validation code | CLOSED | Keep closed |
| #557 | fix(validation): Implement Form Request validation to eliminate duplicate validation code | CLOSED | Keep closed |

**Canonical Issue**: #560 - "feat: Implement Form Request validation classes to eliminate duplicate validation code"

**Total to Close**: 1 issue

---

### Group 7: Transportation Management Issues (2 duplicates)

**Problem**: All address implementing transportation management system.

| Issue | Title | Status | Action |
|-------|-------|--------|--------|
| #600 | feat: Implement comprehensive transportation management system | OPEN | ❌ Close as duplicate of #547 |
| #547 | feat(transportation): Implement comprehensive transportation management system | CLOSED | Keep closed |

**Canonical Issue**: #547 - "feat(transportation): Implement comprehensive transportation management system"

**Total to Close**: 1 issue

---

### Group 8: CI/CD Consolidation Issues (2 duplicates)

**Problem**: All address CI/CD workflow consolidation.

| Issue | Title | Status | Action |
|-------|-------|--------|--------|
| #559 | fix(ci): Consolidate CI/CD pipeline and add automated testing | CLOSED | Keep closed |
| #556 | fix(ci): Consolidate CI/CD pipeline with automated testing and quality gates | CLOSED | Keep closed |
| #558 | feat: Add CI/CD pipeline with automated testing and quality gates | CLOSED | Keep closed |
| #555 | feat(ci): Add comprehensive CI/CD pipelines with automated testing | CLOSED | Keep closed |

**Canonical Issue**: #632 - "refactor(ci): Consolidate redundant GitHub workflows (11 → 3-4 workflows)"

**Total to Close**: 0 issues (all already closed)

---

## Consolidation Summary

### Issues to Close as Duplicates

| Group | Canonical Issue | Issues to Close | Count |
|-------|----------------|-----------------|-------|
| 1 | #630 | #622, #615, #613, #610, #606 | 5 |
| 2 | #629 | #626, #620, #617, #614 | 4 |
| 3 | #630 | #624, #619, #618 | 3 |
| 4 | #632 | #604, #625 | 2 |
| 5 | #633 | #578 | 1 |
| 6 | #560 | #543 | 1 |
| 7 | #547 | #600 | 1 |
| **Total** | | | **17** |

### Impact

- **Current Open Issues**: 326+
- **Issues to Close**: 17
- **Projected Open Issues After Cleanup**: ~309
- **Reduction**: ~5%

## Execution Plan

### Step 1: Close Duplicate Issues (Batch 1)

```bash
# Close Group 1 duplicates
gh issue close 622 --comment "Closing as duplicate of #630"
gh issue close 615 --comment "Closing as duplicate of #630"
gh issue close 613 --comment "Closing as duplicate of #630"
gh issue close 610 --comment "Closing as duplicate of #630"
gh issue close 606 --comment "Closing as duplicate of #630"

# Close Group 2 duplicates
gh issue close 626 --comment "Closing as duplicate of #629"
gh issue close 620 --comment "Closing as duplicate of #629"
gh issue close 617 --comment "Closing as duplicate of #629"
gh issue close 614 --comment "Closing as duplicate of #629"
```

### Step 2: Close Duplicate Issues (Batch 2)

```bash
# Close Group 3 duplicates
gh issue close 624 --comment "Closing as duplicate of #630"
gh issue close 619 --comment "Closing as duplicate of #630"
gh issue close 618 --comment "Closing as duplicate of #630"

# Close Group 4 duplicates
gh issue close 604 --comment "Closing as duplicate of #632"
gh issue close 625 --comment "Closing as duplicate of #632"
```

### Step 3: Close Duplicate Issues (Batch 3)

```bash
# Close Group 5-7 duplicates
gh issue close 578 --comment "Closing as duplicate of #633"
gh issue close 543 --comment "Closing as duplicate of #560"
gh issue close 600 --comment "Closing as duplicate of #547"
```

### Step 4: Update Canonical Issues

Add comments to canonical issues linking to closed duplicates:

```bash
# For issue #630
gh issue comment 630 --body "This is the canonical issue for the N+1 query problem in AuthService. Duplicates closed: #622, #615, #613, #610, #606, #624, #619, #618"

# For issue #629
gh issue comment 629 --body "This is the canonical issue for workflow security. Duplicates closed: #626, #620, #617, #614"

# For issue #632
gh issue comment 632 --body "This is the canonical issue for CI/CD consolidation. Duplicates closed: #604, #625"

# For issue #633
gh issue comment 633 --body "This is the canonical issue for duplicate password verification. Duplicate closed: #578"

# For issue #560
gh issue comment 560 --body "This is the canonical issue for Form Request validation. Duplicate closed: #543"

# For issue #547
gh issue comment 547 --body "This is the canonical issue for transportation management. Duplicate closed: #600"
```

### Step 5: Update Documentation

Update the following documentation files:
- `docs/DUPLICATE_ISSUES_ANALYSIS.md` - Mark issues as resolved
- `docs/APPLICATION_STATUS.md` - Update issue counts
- `docs/ROADMAP.md` - Reflect duplicate cleanup progress

## Guidelines for Future Issue Creation

To prevent future duplicates:

1. **Search Before Creating**: Always search existing issues before creating new ones
2. **Use Standard Format**: Follow consistent title format: `type(scope): description`
3. **Reference Related Issues**: Link to related issues in description
4. **Use Labels**: Apply appropriate labels to categorize issues

### Issue Title Format

```
type(scope): description

Examples:
- perf(auth): Fix N+1 query in login()
- security(cicd): Remove admin merge bypass
- code-quality(middleware): Standardize error responses
```

### Types
- `feat` - New feature
- `fix` - Bug fix
- `perf` - Performance improvement
- `refactor` - Code refactoring
- `security` - Security-related
- `docs` - Documentation
- `test` - Testing
- `chore` - Maintenance tasks

## References

- [CONTRIBUTING.md](CONTRIBUTING.md) - Contribution guidelines
- [ORCHESTRATOR_ANALYSIS_REPORT_v9.md](ORCHESTRATOR_ANALYSIS_REPORT_v9.md) - Latest orchestrator analysis
- [APPLICATION_STATUS.md](APPLICATION_STATUS.md) - Application status
- [ROADMAP.md](ROADMAP.md) - Development roadmap

---

**Document Created**: January 22, 2026  
**Orchestrator Version**: v9  
**Total Duplicates Identified**: 17  
**Total Issue Groups**: 8  
