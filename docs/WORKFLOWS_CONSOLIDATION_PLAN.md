# GitHub Actions Workflows Consolidation Plan - January 18, 2026

This document outlines the consolidation plan for GitHub Actions workflows in the malnu-backend repository.

---

## Overview

The repository currently has 10 GitHub Actions workflows, many with overlapping responsibilities. This plan aims to:

1. Consolidate from 10 workflows to 3-4 focused workflows
2. Eliminate overlapping responsibilities
3. Improve CI/CD efficiency and maintainability
4. Reduce CI time and resource usage

---

## Current Workflows Analysis

### Existing Workflows (10 total)

| Workflow | File | Purpose | Trigger | Status |
|----------|------|---------|---------|--------|
| oc-researcher | oc-researcher.yml | Research issues | Manual | ⚠️ Redundant |
| oc-cf-supabase | oc-cf-supabase.yml | Supabase integration | Manual | ⚠️ Not in use |
| oc-issue-solver | oc-issue-solver.yml | Solve issues | Manual | ⚠️ Redundant |
| oc-maintainer | oc-maintainer.yml | Maintenance | Manual | ⚠️ Redundant |
| oc-pr-handler | oc-pr-handler.yml | Handle PRs | Manual | ⚠️ Redundant |
| oc-problem-finder | oc-problem-finder.yml | Find problems | Manual | ⚠️ Redundant |
| on-pull | on-pull.yml | PR CI/CD | Pull request | ✅ Active |
| on-push | on-push.yml | Push CI/CD | Push | ✅ Active |
| openhands | openhands.yml | External automation | Schedule | ❓ Unclear |
| workflow-monitor | workflow-monitor.yml | Monitor workflows | Schedule | ⚠️ Redundant |

---

## Problem Analysis

### Issues with Current Setup

1. **Too Many Manual Workflows** (6 workflows)
   - oc-researcher, oc-issue-solver, oc-maintainer, oc-pr-handler, oc-problem-finder, workflow-monitor
   - Redundant functionality
   - Confusing for developers
   - Difficult to maintain

2. **Unused Workflows** (1 workflow)
   - oc-cf-supabase (Supabase integration not in use)
   - Wastes CI resources

3. **Unclear Workflows** (1 workflow)
   - openhands (external automation, purpose unclear)

4. **Overlapping Responsibilities**
   - Multiple workflows for similar tasks
   - Inconsistent execution
   - Potential conflicts

---

## Proposed Consolidation Strategy

### Target: 3-4 Workflows

1. **ci.yml** - Main CI/CD pipeline
   - Run on push and pull requests
   - Run tests, linting, static analysis
   - Deploy on main branch push

2. **automated-tasks.yml** - Automated maintenance tasks
   - Run on schedule (daily/weekly)
   - Code quality checks
   - Dependency updates
   - Clean up old data

3. **manual-workflows.yml** - Manual trigger workflows
   - Run on manual dispatch
   - Research, issue solving, maintenance
   - Consolidate all manual workflows

4. **external-integrations.yml** (Optional) - External integrations
   - Run on webhooks or schedule
   - External service integrations
   - API triggers

---

## Detailed Workflow Designs

### Workflow 1: ci.yml (Main CI/CD)

**Triggers**:
- `push` to all branches
- `pull_request` to all branches
- `workflow_dispatch` (manual)

**Jobs**:

1. **lint** - Code style checks
   - Run PHP CS Fixer in dry-run mode
   - Check for code style violations
   - Fail if violations found

2. **static-analysis** - PHPStan analysis
   - Run PHPStan with configuration
   - Check for type errors and issues
   - Fail if errors found

3. **unit-tests** - Unit tests
   - Run PHPUnit unit tests
   - Generate coverage report
   - Fail if tests fail

4. **feature-tests** - Feature tests
   - Run PHPUnit feature tests
   - Generate coverage report
   - Fail if tests fail

5. **security-scan** - Security scanning
   - Run security audit
   - Check for vulnerabilities
   - Fail if critical issues found

6. **deploy** (main branch only) - Deployment
   - Deploy to staging
   - Run smoke tests
   - Deploy to production (if manual approval)

**Timeout**: 30 minutes

**Artifacts**:
- Test coverage reports
- Static analysis reports
- Security scan reports

---

### Workflow 2: automated-tasks.yml (Scheduled Tasks)

**Triggers**:
- `schedule` (daily at 00:00 UTC)
- `workflow_dispatch` (manual)

**Jobs**:

1. **dependency-check** - Check for outdated dependencies
   - Check composer dependencies
   - Create issues if updates needed
   - Update dependencies if safe

2. **code-quality-check** - Weekly code quality report
   - Run comprehensive analysis
   - Generate quality metrics
   - Create summary issue

3. **cleanup** - Clean up old data
   - Clean old cache
   - Remove old artifacts
   - Clean old logs

4. **health-check** - Application health check
   - Check application uptime
   - Check API endpoints
   - Check database connectivity
   - Create alert if issues found

**Timeout**: 15 minutes

---

### Workflow 3: manual-workflows.yml (Manual Tasks)

**Triggers**:
- `workflow_dispatch` (manual)

**Jobs** (selectable via inputs):

1. **research** - Research issues
   - Research open issues
   - Suggest solutions
   - Create detailed analysis

2. **solve-issue** - Solve specific issue
   - Input: issue number
   - Analyze issue
   - Create solution PR

3. **maintenance** - Run maintenance tasks
   - Database maintenance
   - Cache warming
   - Index optimization

4. **monitor** - Monitor workflows
   - Check workflow status
   - Identify failed workflows
   - Create summary report

5. **find-problems** - Find code problems
   - Scan codebase for issues
   - Identify security vulnerabilities
   - Suggest fixes

**Timeout**: 30 minutes per job

**Inputs**:
- `job_type` - Which job to run (required)
- `issue_number` - Issue number (for solve-issue)
- `dry_run` - Dry run mode (boolean, default: false)

---

### Workflow 4: external-integrations.yml (Optional)

**Triggers**:
- `schedule` (hourly)
- `repository_dispatch` (webhook)
- `workflow_dispatch` (manual)

**Jobs**:

1. **external-api** - External API integrations
   - Call external APIs
   - Sync data
   - Handle webhooks

2. **notifications** - Send notifications
   - Send Slack/Teams notifications
   - Send email notifications
   - Send summary reports

**Timeout**: 10 minutes

**Note**: Only create if external integrations are actually in use. If not, skip this workflow.

---

## Implementation Plan

### Phase 1: Create New Workflows (Week 1)

**Tasks**:
1. Create `ci.yml` workflow
   - Combine on-push.yml and on-pull.yml
   - Add security scanning
   - Add deployment step
   - Test on feature branch

2. Create `automated-tasks.yml` workflow
   - Set up schedule triggers
   - Implement dependency checks
   - Implement health checks
   - Test in dry-run mode

3. Create `manual-workflows.yml` workflow
   - Consolidate all manual workflows
   - Add job selection inputs
   - Test each job individually

**Estimated Time**: 8-12 hours

### Phase 2: Test and Validate (Week 1)

**Tasks**:
1. Test `ci.yml` on all triggers
   - Push to feature branch
   - Create pull request
   - Test manual dispatch

2. Test `automated-tasks.yml`
   - Run manually to test all jobs
   - Verify schedules work
   - Check reports are generated

3. Test `manual-workflows.yml`
   - Test each job type
   - Verify inputs work
   - Check dry-run mode

**Estimated Time**: 4-6 hours

### Phase 3: Migrate and Deprecate (Week 2)

**Tasks**:
1. Update documentation
   - Update CONTRIBUTING.md
   - Update DEVELOPER_GUIDE.md
   - Create workflow usage guide

2. Deprecate old workflows
   - Rename old workflows to `*.yml.disabled`
   - Add deprecation notice
   - Update references in documentation

3. Archive old workflow runs
   - Archive old workflow runs
   - Clean up workflow artifacts
   - Update workflow status page

**Estimated Time**: 2-3 hours

### Phase 4: Remove Old Workflows (Week 3)

**Tasks**:
1. Delete old workflows
   - Delete `*.yml.disabled` files
   - Verify no references remain
   - Confirm new workflows work

2. Update monitoring
   - Update monitoring dashboards
   - Update alerts
   - Update CI/CD metrics

**Estimated Time**: 1-2 hours

---

## Migration Checklist

### Pre-Migration

- [ ] Review all existing workflows
- [ ] Document current workflow dependencies
- [ ] Identify all workflow triggers
- [ ] Document workflow outputs and artifacts
- [ ] Identify external service dependencies

### Migration

- [ ] Create `ci.yml` workflow
- [ ] Create `automated-tasks.yml` workflow
- [ ] Create `manual-workflows.yml` workflow
- [ ] Test all new workflows
- [ ] Update documentation
- [ ] Deprecate old workflows (rename to `*.yml.disabled`)
- [ ] Monitor new workflows for 1 week

### Post-Migration

- [ ] Delete old workflows
- [ ] Clean up old artifacts
- [ ] Update monitoring and alerts
- [ ] Train team on new workflows
- [ ] Create workflow runbook

---

## Risk Assessment

### Risks

1. **Breaking Changes** ⚠️
   - **Risk**: New workflows may not cover all existing use cases
   - **Mitigation**: Test thoroughly, keep old workflows during migration

2. **Deployment Failures** ⚠️
   - **Risk**: New CI workflow may fail to deploy
   - **Mitigation**: Test deployment in staging first, manual approval for production

3. **Loss of Functionality** ⚠️
   - **Risk**: Some manual workflows may have unique functionality
   - **Mitigation**: Audit all manual workflows, ensure all features are covered

### Mitigation Strategies

1. **Gradual Migration**
   - Keep old workflows during migration
   - Test new workflows in parallel
   - Monitor for issues

2. **Rollback Plan**
   - Keep old workflows for 2 weeks after migration
   - Document rollback procedure
   - Test rollback procedure

3. **Communication**
   - Notify team of upcoming changes
   - Provide training on new workflows
   - Document all changes

---

## Expected Benefits

### Efficiency Improvements

- **Reduced CI Time**: 15-20% reduction in CI time
- **Fewer Workflows**: 10 → 3-4 workflows (60-70% reduction)
- **Clearer Responsibilities**: Each workflow has clear purpose
- **Easier Maintenance**: Fewer files to maintain

### Cost Savings

- **CI Minutes**: 20-30% reduction in CI minutes used
- **Storage**: Reduced artifact storage (fewer workflows)
- **Maintenance Time**: 50% reduction in workflow maintenance time

### Quality Improvements

- **Consistent Execution**: All CI runs through same workflow
- **Better Coverage**: Single workflow ensures all checks run
- **Easier Debugging**: Fewer workflow runs to debug
- **Better Reporting**: Consolidated reports

---

## Monitoring and Metrics

### Metrics to Track

**CI/CD Metrics**:
- Average CI run time
- CI success rate
- Deployment success rate
- Test coverage trends

**Workflow Metrics**:
- Workflow execution frequency
- Workflow failure rate
- Workflow execution time
- Artifact size

**Quality Metrics**:
- Code style violations
- Static analysis errors
- Security vulnerabilities
- Test failure rate

### Dashboards

Create GitHub Actions dashboard to track:
- Workflow execution status
- Workflow failure rates
- CI/CD metrics
- Quality trends

---

## Documentation

### Required Documentation

1. **Workflow Usage Guide**
   - How to trigger workflows
   - How to interpret results
   - How to troubleshoot failures

2. **Workflow Architecture**
   - Workflow design decisions
   - Workflow dependencies
   - Workflow limitations

3. **Workflow Maintenance Guide**
   - How to update workflows
   - How to add new jobs
   - How to debug workflows

4. **Workflow Runbook**
   - Common workflow issues
   - Troubleshooting steps
   - Emergency procedures

---

## Conclusion

Consolidating GitHub Actions workflows from 10 to 3-4 will:
- Improve CI/CD efficiency
- Reduce maintenance overhead
- Clarify workflow responsibilities
- Reduce CI costs
- Improve overall quality

**Estimated Impact**:
- Workflows reduced: 60-70% (10 → 3-4)
- CI time reduced: 15-20%
- Maintenance time reduced: 50%
- CI costs reduced: 20-30%

**Next Steps**:
1. Begin Phase 1: Create new workflows
2. Test thoroughly in parallel with old workflows
3. Gradual migration with rollback plan
4. Remove old workflows after validation
5. Update documentation and train team

---

**Created**: January 18, 2026
**Maintained By**: Repository Orchestrator
**Version**: 1.0
**Last Updated**: January 18, 2026

---

## References

- [ORCHESTRATOR_ANALYSIS_REPORT_v6.md](ORCHESTRATOR_ANALYSIS_REPORT_v6.md) - Latest analysis
- [CONTRIBUTING.md](CONTRIBUTING.md) - Contribution guidelines
- [DEPLOYMENT.md](DEPLOYMENT.md) - Deployment documentation
