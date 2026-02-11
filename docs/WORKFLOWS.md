# GitHub Workflows Documentation

**Generated:** 2026-01-31  
**Issue:** #653 - Consolidate redundant GitHub workflows  
**Phase:** 1 - Preparation (Documentation)

## Overview

This document catalogs all GitHub workflows in the repository, their purposes, triggers, and identifies redundancies and optimization opportunities.

**Total Workflows:** 10  
**Target Consolidation:** 3-4 workflows  
**Status:** Analysis complete, ready for consolidation (Phase 2)

---

## Current Workflows

### 1. on-pull.yml

**Purpose:** Pull request automation and CI checks  
**Trigger:**
- `pull_request` - Runs on every PR
- `workflow_dispatch` - Manual trigger

**Timeout:** 30 minutes  
**Runner:** ubuntu-24.04-arm  
**Permissions:** contents: write, pull-requests: write, actions: read, repository-projects: write  
**Concurrency:** oc-agent group (no cancellation)

**Key Functionality:**
- Auto-fix CI failures (OpenCode agent)
- Contains security vulnerability at line 196: `gh pr merge --admin` instruction (see issue #629)
- Orchestrates pull request management

**Related Issues:**
- #629 - Critical: Remove admin merge bypass
- #663 - Apply security fix for #629

**Status:** ⚠️ CRITICAL - Contains security vulnerability

---

### 2. on-push.yml

**Purpose:** Push event automation and orchestrator flows  
**Trigger:**
- `push` - Runs on every push
- `workflow_dispatch` - Manual trigger

**Timeout:** Not specified  
**Runner:** ubuntu-24.04-arm  
**Permissions:** contents: write, pull-requests: write  
**Concurrency:** global group (no cancellation)

**Key Functionality:**
- Runs orchestrator flows (00-11)
- Analyzes code on push events
- May overlap with oc-*.yml workflows

**Redundant With:**
- oc-researcher.yml - Research agent
- oc-issue-solver.yml - Issue solver
- oc-maintainer.yml - Maintainer agent
- oc-problem-finder.yml - Problem finder

**Status:** ✅ Functional but overlaps with dedicated agent workflows

---

### 3. oc-researcher.yml

**Purpose:** Research agent automation  
**Trigger:**
- `schedule`: cron '0 1 * * *' (daily at 01:00 UTC)
- `workflow_dispatch` - Manual trigger

**Timeout:** 40 minutes  
**Runner:** Not specified  
**Permissions:** Full write access (contents, pull-requests, issues, actions, deployments, packages, pages, security-events, id-token)  
**Concurrency:** global-workflow group (no cancellation)

**Key Functionality:**
- Performs research tasks
- Creates documentation
- Analyzes codebase

**Redundant With:**
- on-push.yml - Runs orchestrator flows that may include research

**Status:** ✅ Functional

---

### 4. oc-cf-supabase.yml

**Purpose:** Supabase integration  
**Trigger:**
- `workflow_dispatch` - Manual trigger only

**Timeout:** 40 minutes  
**Runner:** ubuntu-slim  
**Permissions:** contents, deployments, packages, id-token (all write)  
**Concurrency:** global-workflow group (no cancellation)

**Key Functionality:**
- Integrates with Supabase
- Purpose unclear from workflow name alone

**Redundant With:**
- on-push.yml - May include integration tasks

**Status:** ⚠️ UNCLEAR - Manual only, purpose needs documentation

---

### 5. oc-issue-solver.yml

**Purpose:** Issue solver agent  
**Trigger:**
- `schedule`: cron '0/30 * * * *' (every 30 minutes)
- `workflow_dispatch` - Manual trigger

**Timeout:** 40 minutes  
**Runner:** ubuntu-24.04-arm  
**Permissions:** contents, pull-requests, issues (write), actions (read)  
**Concurrency:** global-workflow group (no cancellation)

**Key Functionality:**
- Solves open issues
- Creates pull requests
- Automated issue resolution

**Redundant With:**
- on-pull.yml - May include PR handling

**Status:** ✅ Functional

---

### 6. oc-maintainer.yml

**Purpose:** Maintainer agent automation  
**Trigger:**
- `schedule`: cron '0 3 * * *' (daily at 03:00 UTC)
- `workflow_dispatch` - Manual trigger

**Timeout:** 40 minutes  
**Runner:** ubuntu-24.04-arm  
**Permissions:** contents, pull-requests, issues, actions (all write)  
**Concurrency:** global-workflow group (no cancellation)

**Key Functionality:**
- Performs maintenance tasks
- May triage issues/PRs
- Repository management

**Redundant With:**
- on-pull.yml - PR handling
- on-push.yml - Code analysis

**Status:** ✅ Functional

---

### 7. oc-pr-handler.yml

**Purpose:** Pull request handling agent  
**Trigger:**
- `schedule`: cron '0 9,15,21 * * *' (3x daily at 09:00, 15:00, 21:00 UTC)
- `workflow_dispatch` - Manual trigger
- `pull_request` types: [opened, synchronize, reopened] - On PR events

**Timeout:** 40 minutes  
**Runner:** Not specified  
**Permissions:** contents, pull-requests, issues (write), actions (read)  
**Concurrency:** global-workflow group (no cancellation)

**Key Functionality:**
- Handles pull requests
- Reviews PRs
- Comments on PRs

**Redundant With:**
- on-pull.yml - PR automation and CI (MAJOR OVERLAP)
- oc-maintainer.yml - Maintainer tasks

**Status:** ⚠️ HIGHLY REDUNDANT - Significantly overlaps with on-pull.yml

---

### 8. oc-problem-finder.yml

**Purpose:** Problem finder agent  
**Trigger:**
- `schedule`: cron '0 0 * * *' (daily at 00:00 UTC)
- `workflow_dispatch` - Manual trigger

**Timeout:** 40 minutes  
**Runner:** Not specified  
**Permissions:** contents (read only), issues (write), pull-requests (read)  
**Concurrency:** global-workflow group (no cancellation)
**Environment:** Uses IFLOW_MODEL: iflowcn/qwen3-coder-plus

**Key Functionality:**
- Finds problems in codebase
- Creates issues for discovered problems
- Automated code analysis

**Redundant With:**
- on-push.yml - Code analysis
- oc-issue-solver.yml - Issue creation

**Status:** ✅ Functional

---

### 9. openhands.yml

**Purpose:** Unknown automation  
**Trigger:**
- `workflow_dispatch` - Manual trigger only

**Timeout:** Not specified  
**Runner:** Not specified  
**Permissions:** Full write access (contents, pull-requests, issues, actions, deployments, packages, pages, security-events, id-token)  
**Concurrency:** global-workflow-<workflow> group (no cancellation)

**Key Functionality:**
- Purpose unknown from workflow name
- May be experimental or legacy

**Status:** ⚠️ UNKNOWN - Manual only, no documentation, potential legacy

---

### 10. workflow-monitor.yml

**Purpose:** Workflow monitoring and triggering  
**Trigger:**
- `schedule`: cron '*/30 * * * *' (every 30 minutes)
- `workflow_dispatch` - Manual trigger

**Timeout:** 10 minutes  
**Runner:** ubuntu-latest  
**Permissions:** actions (read), contents (write)

**Key Functionality:**
- Monitors other workflows
- May trigger other workflows
- Health checking

**Status:** ✅ Functional

---

## Workflow Analysis

### Redundancy Matrix

| Workflow | Overlaps With | Redundancy Level | Notes |
|----------|---------------|------------------|--------|
| on-pull.yml | oc-pr-handler.yml | 🔴 HIGH | Both handle PRs, automation, and review |
| on-push.yml | oc-researcher.yml, oc-issue-solver.yml, oc-maintainer.yml, oc-problem-finder.yml | 🟡 MEDIUM | All perform analysis/automation tasks |
| oc-researcher.yml | on-push.yml | 🟡 MEDIUM | Research vs orchestrator flows |
| oc-cf-supabase.yml | on-push.yml | 🟡 MEDIUM | Purpose unclear |
| oc-issue-solver.yml | on-pull.yml, oc-problem-finder.yml | 🟡 MEDIUM | Creates PRs/Issues |
| oc-maintainer.yml | on-pull.yml, oc-pr-handler.yml | 🟡 MEDIUM | Maintainer tasks overlap |
| oc-pr-handler.yml | on-pull.yml | 🔴 HIGH | Both handle PRs |
| oc-problem-finder.yml | on-push.yml, oc-issue-solver.yml | 🟡 MEDIUM | Finds problems, creates issues |
| openhands.yml | Unknown | ⚠️ UNKNOWN | Purpose undocumented |
| workflow-monitor.yml | None | 🟢 UNIQUE | Monitoring role |

### Key Issues Identified

1. **Critical Security Vulnerability** 🔴
   - **File:** on-pull.yml:196
   - **Issue:** Contains `gh pr merge --admin` instruction
   - **Impact:** Can bypass branch protection rules
   - **Related:** Issues #629, #663
   - **Priority:** P0 - Must fix immediately

2. **Major Redundancy** 🔴
   - **Files:** on-pull.yml and oc-pr-handler.yml
   - **Issue:** Both handle PRs, automation, and review
   - **Impact:** Confusing, duplicate runs, wasted compute
   - **Recommendation:** Merge into single workflow

3. **Missing Essential CI Checks** 🟡
   - **Missing:** 
     - PHP unit tests (`composer test`)
     - Code style checks (`php-cs-fixer fix --dry-run`)
     - Static analysis (`composer analyse`)
     - Security scanning
   - **Impact:** No automated quality checks
   - **Recommendation:** Add to consolidated ci.yml

4. **Unclear Purpose** 🟡
   - **Files:** openhands.yml, oc-cf-supabase.yml
   - **Issue:** Purpose unclear, minimal documentation
   - **Impact:** Hard to maintain
   - **Recommendation:** Document or remove if unused

5. **Configuration Drift** 🟡
   - **Issue:** Inconsistent timeouts, runner versions, permissions
   - **Impact:** Hard to maintain, inconsistent behavior
   - **Examples:**
     - Timeouts: 10-40 minutes
     - Runners: ubuntu-latest, ubuntu-24.04-arm, ubuntu-slim
     - Permissions: Vary widely

---

## Proposed Consolidated Architecture

### Target: 3-4 Workflows

#### 1. ci.yml (NEW)
**Purpose:** Essential CI checks and quality gates  
**Triggers:**
- `pull_request` - On every PR
- `push` - On every push to main

**Jobs:**
- Install dependencies (composer install)
- Run tests (`composer test`)
- Code style check (`php-cs-fixer fix --dry-run`)
- Static analysis (`composer analyse`)
- Security scanning
- Build verification

**Benefits:**
- Ensures code quality on every change
- Prevents merging broken code
- Replaces missing CI checks

---

#### 2. automation.yml (CONSOLIDATED)
**Purpose:** OpenCode agent automation (consolidates 5 workflows)  
**Triggers:**
- `schedule` - Multiple schedules based on agent needs
- `workflow_dispatch` - Manual trigger
- `pull_request` - On PR events (for PR handling)

**Consolidates From:**
- oc-researcher.yml
- oc-issue-solver.yml
- oc-maintainer.yml
- oc-pr-handler.yml
- oc-problem-finder.yml

**Jobs:**
- Single job with conditional logic for each agent type
- OR separate jobs for each agent with shared setup

**Key Changes:**
- Remove admin merge bypass (security fix for #629)
- Use standard merge methods (branch protection enforced)
- Consolidated scheduling

**Benefits:**
- Single workflow for all automation
- Reduced compute costs
- Consistent permissions
- Easier to maintain

---

#### 3. deploy.yml (NEW)
**Purpose:** Deployment tasks  
**Triggers:**
- `workflow_dispatch` - Manual trigger
- `push` - On push to main

**Jobs:**
- Run database migrations
- Clear caches
- Deploy to production/staging
- Health checks

**Benefits:**
- Separates deployment concerns
- Manual control for production
- Clear deployment process

---

#### 4. (Optional) integration.yml (NEW/CONSOLIDATED)
**Purpose:** External integrations and monitoring  
**Triggers:**
- `schedule` - Periodic monitoring
- `workflow_dispatch` - Manual trigger

**Consolidates From:**
- oc-cf-supabase.yml
- workflow-monitor.yml
- openhands.yml (if still needed)

**Jobs:**
- Supabase integration
- Workflow monitoring
- Other external service integrations

**Benefits:**
- Isolates integration concerns
- Clear monitoring role
- Removes unclear workflows

---

## Rollback Plan

### Phase 1: Backup Current Workflows

Before making any changes:

```bash
# Create backup directory
mkdir -p .github/workflows/backup

# Backup all workflow files
cp .github/workflows/*.yml .github/workflows/backup/

# Verify backup
ls -la .github/workflows/backup/

# Commit backup
git add .github/workflows/backup/
git commit -m "chore: Backup all workflows before consolidation (#653 Phase 1)"
git push
```

### Phase 2: Add New Workflows (Don't Delete Old Yet)

1. Create new consolidated workflows (ci.yml, automation.yml, deploy.yml)
2. Add to repository
3. Test on feature branch

```bash
# Create new workflows
# (Create ci.yml, automation.yml, deploy.yml)

# Add new workflows
git add .github/workflows/ci.yml
git add .github/workflows/automation.yml
git add .github/workflows/deploy.yml

# Commit
git commit -m "feat(ci): Add consolidated workflows (Phase 2)"
git push
```

### Phase 3: Disable Old Workflows (Don't Delete Yet)

Rename old workflows to disable them (keep them for rollback):

```bash
# Rename to disable (add .disabled extension)
for f in .github/workflows/oc-*.yml .github/workflows/on-*.yml .github/workflows/openhands.yml; do
  mv "$f" "${f%.yml}.yml.disabled"
done

# Commit
git add .github/workflows/*.disabled
git commit -m "chore: Disable old workflows (Phase 3 - safe rollback)"
git push
```

### Phase 4: Monitor for 1 Week

- Monitor CI/CD runs
- Check for missing functionality
- Verify automation works
- Watch for errors

### Phase 5: Delete Old Workflows (If Stable)

After 1 week of stable operation:

```bash
# Delete disabled workflows
rm .github/workflows/*.disabled

# Commit
git commit -m "chore: Remove old workflows after successful consolidation (Phase 5)"
git push
```

### Rollback Procedure

If any phase fails or issues arise:

```bash
# Option 1: Re-enable old workflows
for f in .github/workflows/*.disabled; do
  mv "$f" "${f%.disabled}"
done

# Option 2: Delete new workflows and restore old
rm .github/workflows/ci.yml .github/workflows/automation.yml .github/workflows/deploy.yml
cp .github/workflows/backup/*.yml .github/workflows/

# Commit rollback
git add .github/workflows/
git commit -m "chore: Rollback workflow consolidation (safe rollback activated)"
git push
```

---

## Implementation Roadmap

### Phase 1: Preparation ✅ (This PR)
- [x] Document all existing workflows
- [x] Identify and catalog all triggers and purposes
- [x] Create rollback plan
- [ ] Review and approve documentation

### Phase 2: Consolidate (Future PR)
- [ ] Create ci.yml with essential checks
- [ ] Create automation.yml with consolidated OpenCode logic
- [ ] Test on a feature branch before merging
- [ ] Verify no functionality is lost
- [ ] Fix security vulnerability in on-pull.yml

### Phase 3: Deploy (Future PR)
- [ ] Add new workflows to main branch
- [ ] Disable (not delete) old workflows
- [ ] Monitor for 1 week
- [ ] Document transition

### Phase 4: Cleanup (Future PR)
- [ ] Remove unused environment variables from workflows
- [ ] Update documentation
- [ ] Create onboarding guide for new CI/CD structure
- [ ] Delete old workflows after stable period

---

## Success Criteria

- [ ] Reduced to 3-4 active workflows (Phase 2-3)
- [ ] All essential CI checks present (test, lint, analyse) (Phase 2)
- [ ] No admin merge bypass or security vulnerabilities (Phase 2)
- [ ] All automation functionality preserved (Phase 2-3)
- [ ] Workflow documentation complete (Phase 1 - ✅ Complete)
- [ ] CI/CD costs reduced (fewer redundant runs) (Phase 3)
- [ ] 1 week stable operation (Phase 4)

---

## Related Issues

- **#653** - This issue: Consolidate redundant GitHub workflows
- **#629** - Critical: Remove admin merge bypass from on-pull.yml
- **#663** - Apply security fix for #629
- **#134** - Fix CI/CD pipeline and add automated testing
- **#632** - Partial workflow consolidation attempt (PR #646)

---

## References

- [GitHub Actions Documentation](https://docs.github.com/en/actions)
- [Workflow Syntax](https://docs.github.com/en/actions/reference/workflow-syntax-for-github-actions)
- [Security Best Practices](https://docs.github.com/en/actions/security-guides/security-hardening-for-github-actions)
- [Branch Protection](https://docs.github.com/en/repositories/configuring-branches-and-merges-in-your-repository/defining-the-mergeability-of-pull-requests/about-protected-branches)

---

**Document Version:** 1.0  
**Last Updated:** 2026-01-31  
**Next Review:** After Phase 2 completion
