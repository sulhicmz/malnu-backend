# CI/CD Documentation

## Overview

This repository uses an AI-driven automation system built on **OpenCode CLI** and **GitHub Actions**. The CI/CD system consists of 10 interconnected workflows that automate development, maintenance, issue resolution, and deployment tasks.

### Architecture Philosophy

- **AI-First Approach**: Workflows delegate complex tasks to OpenCode AI agents
- **Event-Driven**: Workflows respond to GitHub events (push, pull_request, schedule)
- **Orchestration**: Workflow-monitor.yml coordinates execution of on-push and on-pull workflows
- **Parallel Processing**: Multiple specialized agents work in parallel on different schedules
- **Self-Healing**: Agents can detect and fix issues, merge PRs, and maintain repository health

---

## Workflow Descriptions

### 1. on-push.yml

**Purpose**: Main automation workflow triggered on repository push events. Runs comprehensive analysis and maintenance tasks through OpenCode CLI.

**Triggers**:
- `push` (any push to repository)
- `workflow_dispatch` (manual trigger)

**Permissions**:
- `contents: write` - Modify repository content
- `pull-requests: write` - Create/modify pull requests

**Jobs**:
- `analyze` - Runs on `ubuntu-24.04-arm`

**Workflow Structure**:
1. **Wait in Queue** - Ensures only one instance runs at a time
2. **Checkout** - Fetches repository with full history
3. **Setup Cache** - Caches OpenCode and npm dependencies
4. **Configure Git** - Sets git user configuration
5. **Install OpenCode** - Installs OpenCode CLI
6. **Check Open Issues** - Counts open issues
7. **12 Flows (00-11)** - Executes numbered prompt files from `.github/prompt/`
8. **Main on-push Flow** - Runs comprehensive repository management prompt if no open issues

**OpenCode Prompts** (00-11):
- Located in `.github/prompt/` directory
- Each prompt contains specific tasks for repository analysis
- Flows run sequentially with retry logic (max 2 attempts per flow)

**Security Notes**:
- Uses multiple secrets (IFLOW_API_KEY, VITE_SUPABASE_URL, CLOUDFLARE_ACCOUNT_ID, etc.)
- Secrets accessed via environment variables (never logged)
- Concurrency group prevents duplicate executions

---

### 2. on-pull.yml

**Purpose**: Pull request event handler. Manages PR review, fixes failing checks, and automates PR merging.

**Triggers**:
- `pull_request` (on PR creation, sync, or reopen)
- `workflow_dispatch` (manual trigger)

**Permissions**:
- `contents: write` - Modify repository content
- `pull-requests: write` - Create/modify pull requests
- `actions: read` - Read GitHub Actions
- `repository-projects: write` - Manage GitHub Projects

**Jobs**:
- `ci` - Runs on `ubuntu-24.04-arm`

**Workflow Structure**:
1. **Wait in Queue** - Prevents concurrent executions
2. **Checkout Code** - Fetches repository with full history
3. **Setup Node.js** - Configures Node.js with caching
4. **Configure Git** - Sets git user configuration
5. **Branch Management** - Checks out `agent-workspace` branch and syncs with main
6. **Install Dependencies** - Runs `npm ci`
7. **Install OpenCode CLI** - Installs OpenCode CLI
8. **iterate1** - Sets up agents and CMZ system
9. **Auto-fix CI Failures** - Automated PR fixing agent

**Security Notes**:
- Git identity configured as `${{ github.actor_id }}+${{ github.actor }}@users.noreply.github.com`
- Concurrency group: `oc-agent`
- Uses continue-on-error for non-critical steps

---

### 3. workflow-monitor.yml

**Purpose**: Orchestrator workflow that ensures on-push and on-pull workflows run continuously. Runs every 30 minutes.

**Triggers**:
- `schedule` - Every 30 minutes (`cron: '*/30 * * * *'`)
- `workflow_dispatch` (manual trigger)

**Permissions**:
- `actions: read` - Read GitHub Actions status
- `contents: write` - Trigger other workflows

**Jobs**:
- `monitor` - Runs on `ubuntu-latest`

**Workflow Structure**:
1. **Checkout Code** - Fetches repository
2. **Check Running Workflows** - Queries GitHub for in-progress workflows
3. **Trigger on-push if not running** - Triggers on-push.yml if not already running
4. **Trigger on-pull if not running** - Triggers on-pull.yml if not already running
5. **Both Workflows Running** - Logs when both are active (no action needed)

**Security Notes**:
- Lightweight monitor with 10-minute timeout
- No direct code changes
- Uses GitHub Actions API to check workflow status

---

### 4. oc-issue-solver.yml

**Purpose**: OpenCode agent dedicated to solving GitHub issues end-to-end. Creates branches, implements fixes, and creates PRs.

**Triggers**:
- `schedule` - Every 30 minutes (`cron: '0/30 * * * *'`)
- `workflow_dispatch` (manual trigger)

**Permissions** (workflow-level):
- `contents: write` - Modify repository content
- `pull-requests: write` - Create/modify pull requests
- `issues: write` - Create/modify issues
- `actions: read` - Read GitHub Actions

**Job Permissions** (expanded):
- `id-token: write` - OIDC token access
- `contents: write`
- `pull-requests: write`
- `issues: write`
- `actions: write` - Write GitHub Actions
- `deployments: write` - Create deployments
- `packages: write` - Publish packages
- `pages: write` - Publish GitHub Pages
- `security-events: write` - Write security events

**Jobs**:
- `opencode` - Runs on `ubuntu-24.04-arm`

**Workflow Structure**:
1. **Checkout** - Fetches repository with shallow clone
2. **Check for Open Issues** - Counts open issues
3. **Skip – No Issues Found** - Exits early if no issues
4. **Install OpenCode CLI** - Installs OpenCode CLI
5. **Run OpenCode1** - Executes issue-solving agent prompt

**Agent Tasks**:
1. Issue selection (prioritization: P0 > P1 > P2 > P3, oldest first)
2. Deep analysis of chosen issue
3. Public plan comment on issue
4. Implementation according to plan
5. Local verification and quality checks
6. Pull request creation linked to issue
7. Final communication and status update

**Security Notes**:
- ⚠️ **EXCESSIVE PERMISSIONS**: Has 9 different write permissions
- Many permissions not needed for issue solving
- Concurrency group: `${{ github.repository }}-global-workflow`
- Timeout: 40 minutes

---

### 5. oc-maintainer.yml

**Purpose**: Repository maintenance agent. Performs health scans, dependency checks, CI monitoring, and security audits.

**Triggers**:
- `schedule` - Daily at 3 AM UTC (`cron: '0 3 * * *'`)
- `workflow_dispatch` (manual trigger)

**Permissions** (workflow-level):
- `contents: write`
- `pull-requests: write`
- `issues: write`
- `actions: write`

**Job Permissions** (expanded):
- `id-token: write`
- `contents: write`
- `pull-requests: write`
- `issues: write`
- `actions: write`
- `deployments: write`
- `packages: write`
- `pages: write`
- `security-events: write`

**Jobs**:
- `opencode` - Runs on `ubuntu-24.04-arm`

**Workflow Structure**:
1. **Checkout** - Fetches repository with shallow clone
2. **Install OpenCode CLI** - Installs OpenCode CLI
3. **Run OpenCode1** - Executes maintenance agent prompt

**Agent Tasks**:
1. Repository health scan (codebase, dependencies, CI/CD, security, documentation)
2. Select and define focused maintenance task
3. Public plan and coordination (create/update issue with scope)
4. Implementation rules (clean, safe, efficient)
5. Testing, verification, and safety checks
6. Maintenance pull request creation
7. Review readiness and communication
8. Repository hygiene and housekeeping

**Security Notes**:
- ⚠️ **EXCESSIVE PERMISSIONS**: Has 9 different write permissions
- Many permissions not needed for maintenance
- Concurrency group: `${{ github.repository }}-global-workflow`
- Timeout: 40 minutes

---

### 6. oc-problem-finder.yml

**Purpose**: Orchestrator agent for repository analysis and task generation. Uses Indonesian language in prompt.

**Triggers**:
- `schedule` - Daily at midnight UTC (`cron: '0 0 * * *'`)
- `workflow_dispatch` (manual trigger)

**Permissions** (workflow-level):
- `contents: read` - Read repository content
- `issues: write` - Create/modify issues
- `pull-requests: read` - Read pull requests

**Job Permissions** (expanded):
- `id-token: write`
- `contents: write`
- `pull-requests: write`
- `issues: write`
- `actions: write`
- `deployments: write`
- `packages: write`
- `pages: write`
- `security-events: write`

**Jobs**:
- `opencode` - Runs on `ubuntu-24.04-arm`

**Workflow Structure**:
1. **Harden Runner** - Applies security hardening
2. **Checkout** - Fetches repository with shallow clone
3. **Install OpenCode CLI** - Installs OpenCode CLI
4. **Run OpenCode1** - Executes problem-finding agent prompt

**Agent Tasks** (Indonesian):
1. Deep repository analysis (structure, architecture, modules, API, workflows)
2. GitHub project management (create/update projects, columns, metadata)
3. Roadmap & prioritization (short, medium, long-term roadmaps)
4. Task generation (granular, actionable issues/PRs)
5. Issue management (analyze, normalize, label issues)
6. Document management (all docs in 'docs/' folder)
7. Closing (commit, push, create PR)

**Security Notes**:
- ⚠️ **EXCESSIVE PERMISSIONS**: Has 9 different write permissions (despite workflow-level `contents: read`)
- Uses `IFLOW_MODEL: iflowcn/qwen3-coder-plus`
- Concurrency group: `${{ github.repository }}-global-workflow`
- Timeout: 40 minutes

---

### 7. oc-pr-handler.yml

**Purpose**: Pull request maintenance agent. Reviews, fixes, and merges PRs. Operates as `maskom_team@ma-malnukananga.sch.id`.

**Triggers**:
- `schedule` - Every 6 hours (`cron: '0 9,15,21 * * *'`)
- `pull_request` (on PR opened, synchronized, reopened)
- `workflow_dispatch` (manual trigger)

**Permissions** (workflow-level):
- `contents: write`
- `pull-requests: write`
- `issues: write`
- `actions: read`

**Job Permissions** (expanded):
- `id-token: write`
- `contents: write`
- `pull-requests: write`
- `issues: write`
- `actions: write`
- `deployments: write`
- `packages: write`
- `pages: write`
- `security-events: write`

**Jobs**:
- `opencode` - Runs on `ubuntu-24.04-arm`

**Workflow Structure**:
1. **Harden Runner** - Applies security hardening
2. **Checkout** - Fetches repository with shallow clone
3. **Install OpenCode CLI** - Installs OpenCode CLI
4. **Run OpenCode1** - Executes PR handling agent prompt

**Agent Tasks**:
1. Pull request and branch selection (prioritization)
2. Deep analysis of chosen PR or branch
3. Public plan comment on PR
4. Implementation and handling review comments
5. Local verification and CI/build checks
6. Conversation resolution and review status
7. Final PR update, merge, and communication
8. Repository hygiene and housekeeping

**Security Notes**:
- ⚠️ **EXCESSIVE PERMISSIONS**: Has 9 different write permissions
- Git identity: `maskom_team@ma-malnukananga.sch.id`
- Concurrency group: `${{ github.repository }}-global-workflow`
- Timeout: 40 minutes

---

### 8. oc-cf-supabase.yml

**Purpose**: DevOps specialist for Cloudflare and Supabase integration. Manages deployments, configurations, and optimizations.

**Triggers**:
- `workflow_dispatch` (manual trigger only)

**Permissions** (workflow-level):
- `contents: write`
- `deployments: write`
- `packages: write`
- `id-token: write`

**Job Permissions** (expanded):
- `contents: write`
- `pull-requests: write`
- `issues: write`
- `actions: write`
- `deployments: write`
- `packages: write`
- `pages: write`
- `security-events: write`

**Jobs**:
- `opencode` - Runs on `ubuntu-slim`

**Workflow Structure**:
1. **Checkout** - Fetches repository with shallow clone
2. **Install OpenCode CLI** - Installs OpenCode CLI
3. **Run OpenCode1** - Executes DevOps specialist prompt

**Agent Tasks**:
1. Inspect repository (Cloudflare: wrangler.toml, cloudflare/*, Supabase: supabase/, client usage)
2. Check GitHub context (issues, PRs, documentation)
3. Decide on single main target (deployment improvement, integration, feature, repair)
4. Cloudflare responsibilities (deployment model, scriptable deployments, improve configs)
5. Supabase responsibilities (client usage, migrations, schema, RLS policies, free-tier optimizations)
6. CI, verification, and safety checks
7. Interaction with GitHub (branches, commits, PRs)

**Security Notes**:
- ⚠️ **EXCESSIVE PERMISSIONS**: Has 9 different write permissions
- Uses Cloudflare credentials (CLOUDFLARE_ACCOUNT_ID, CLOUDFLARE_API_TOKEN)
- Git identity: `maskom_team@ma-malnukananga.sch.id`
- Timeout: 40 minutes

---

### 9. iterate.yml

**Purpose**: Multi-agent orchestration loop. Coordinates 8 specialized agents (BugLover, Palette, Flexy, TestGuard, StorX, CodeKeep, BroCula) working in sequential phases.

**Triggers**:
- `workflow_dispatch` (manual trigger)
- `pull_request` (on PR events)

**Permissions**:
- `contents: write`
- `pull-requests: write`
- `actions: read`
- `repository-projects: write`

**Jobs**:
- `ci` - Runs on `ubuntu-24.04-arm`

**Workflow Structure**:
1. **Wait in Queue** - Prevents concurrent executions
2. **Checkout Code** - Fetches repository with full history
3. **Setup Node.js** - Configures Node.js with caching
4. **Configure Git** - Sets git user configuration
5. **Branch Management** - Manages `agent-workspace` branch and syncs with main
6. **Install Dependencies** - Runs `npm ci`
7. **Install OpenCode CLI** - Installs OpenCode CLI
8. **iterate1** - Executes orchestration loop prompt

**Phases** (0-8):
- **Phase 0**: Git Branch Management (Start)
- **Phase 1**: BugLover - Finds and fixes bugs
- **Phase 2**: Palette - UX improvements
- **Phase 3**: Flexy - Eliminates hardcoded values, adds modularity
- **Phase 4**: TestGuard - Test efficiency and performance
- **Phase 5**: StorX - Strengthens and consolidates features
- **Phase 6**: CodeKeep - Code quality review
- **Phase 7**: BroCula - Browser console errors and lighthouse optimization
- **Phase 8**: Git Branch Management (End)

**Agent Specializations**:
- **BugLover**: Find bugs, errors, console warnings
- **Palette 🎨**: Micro-UX improvements (delight, accessibility)
- **Flexy**: Remove hardcoded, add modularity
- **TestGuard**: Fast tests, determinism, execution efficiency
- **StorX**: Connect features, consolidate logic, remove redundancy
- **CodeKeep**: Logic errors, security risks, performance pitfalls
- **BroCula**: Browser errors, lighthouse optimization

**Security Notes**:
- Continuous loop (back to Phase 0 after Phase 8)
- Branch: `agent-workspace`
- Timeout: 60 minutes
- Uses delegation to specialist agents

---

### 10. oc-researcher.yml

**Purpose**: OpenCode research agent for exploring and analyzing codebase patterns, documentation, and implementations.

**Triggers**:
- To be determined (file exists but triggers not documented in this analysis)

**Status**: This workflow file exists but requires further documentation.

---

## Workflow Permissions Matrix

| Workflow | contents | pull-requests | issues | actions | id-token | deployments | packages | pages | security-events | repo-projects |
|-----------|-----------|----------------|--------|---------|-----------|----------|-------|-----------------|---------------|
| on-push.yml | write | write | - | - | - | - | - | - | - |
| on-pull.yml | write | write | - | read | - | - | - | - | write |
| workflow-monitor.yml | write | - | - | read | - | - | - | - | - |
| oc-issue-solver.yml | write | write | write | read/write | write | write | write | write | write | - |
| oc-maintainer.yml | write | write | - | write | write | write | write | write | write | - |
| oc-problem-finder.yml | read | - | write | write | write | write | write | write | write | - |
| oc-pr-handler.yml | write | write | - | write | write | write | write | write | write | - |
| oc-cf-supabase.yml | write | write | write | write | write | write | write | write | - | - |
| iterate.yml | write | write | - | read | - | - | - | - | write |

**Legend**:
- `write` - Full write access
- `read/write` - Read and write access
- `read` - Read-only access
- `-` - Not granted

---

## Inter-workflow Dependencies

### Trigger Chain

```
┌─────────────────┐
│   User Push   │
└──────┬────────┘
       │
       ├─────────────────┐
       │  on-push     │
       └──────┬────────┘
              │
       ┌─────────────────┐
       │  workflow     │
       │  monitor      │
       └──────┬────────┘
              │
       ├──────────────┬──────────────┐
       │             │             │
  on-push    │      on-pull     │
       │             │             │
       └─────────────┴──────────────┘
```

### Scheduled Workflows

| Workflow | Schedule | Frequency | Purpose |
|----------|-----------|-----------|---------|
| workflow-monitor.yml | Every 30 min | Ensure on-push and on-pull run continuously |
| oc-issue-solver.yml | Every 30 min | Solve open issues |
| oc-maintainer.yml | Daily at 3 AM | Repository maintenance |
| oc-problem-finder.yml | Daily at midnight | Problem finding and task generation |
| oc-pr-handler.yml | Every 6 hours | PR maintenance and merging |

### Workflow Orchestration

**workflow-monitor.yml** is the orchestrator that:
1. Checks if on-push.yml is running
2. Checks if on-pull.yml is running
3. Triggers on-push.yml if not running
4. Triggers on-pull.yml if not running

This ensures continuous automation even if workflows fail or get stuck.

---

## OpenCode Agent System

### What is OpenCode?

OpenCode is an AI-powered CLI tool that:
- Executes complex software engineering tasks
- Integrates with GitHub Actions
- Provides specialized agents for different domains
- Uses LLM models (GLM-4.7-free, Kimi-K2.5-free, etc.)

### Agent Workflows

1. **oc-issue-solver** - End-to-end issue resolution
   - Selects issues by priority (P0 > P1 > P2 > P3)
   - Creates branches, implements fixes, creates PRs
   - Verifies and tests changes

2. **oc-maintainer** - Repository health and maintenance
   - Scans for security issues, outdated dependencies
   - Improves CI/CD reliability
   - Updates documentation

3. **oc-problem-finder** - Problem discovery
   - Analyzes codebase for bugs and improvements
   - Creates structured issues with acceptance criteria
   - Generates user stories

4. **oc-pr-handler** - PR management
   - Reviews and merges PRs
   - Fixes failing CI checks
   - Handles review comments

5. **oc-cf-supabase** - DevOps specialist
   - Manages Cloudflare deployments
   - Handles Supabase integration
   - Optimizes for free-tier limits

6. **oc-researcher** - Codebase research
   - Explores patterns and implementations
   - Analyzes documentation

7. **iterate** - Multi-agent orchestration
   - Coordinates 8 specialized agents
   - Executes in sequential phases (0-8)
   - Continuous improvement loop

### OpenCode CLI Commands

```bash
# Install OpenCode CLI
curl -fsSL https://opencode.ai/install | bash
echo "$HOME/.opencode/bin" >> $GITHUB_PATH

# Run an agent
opencode run "PROMPT" --model opencode/glm-4.7-free --share false

# Available models
- opencode/glm-4.7-free (free tier)
- opencode/kimi-k2.5-free (free tier)
- opencode/minimax-m2.1-free (free tier)
- iflowcn/glm-4.6 (external)
```

---

## Security Guidelines for Workflows

### Principle of Least Privilege

**Current Issues**:
- Many workflows have excessive write permissions (`id-token`, `deployments`, `packages`, `pages`, `security-events`)
- Most workflows don't need OIDC (`id-token: write`)
- No workflows use GitHub Pages (`pages: write`)
- Most workflows don't publish packages (`packages: write`)

### Recommended Minimum Permissions

| Workflow | Recommended | Current | Excessive |
|-----------|--------------|---------|-----------|
| on-push.yml | `contents: write`, `pull-requests: write` | Same | No |
| on-pull.yml | `contents: write`, `pull-requests: write`, `actions: read`, `repository-projects: write` | Same | No |
| workflow-monitor.yml | `actions: read`, `contents: write` | Same | No |
| oc-issue-solver.yml | `contents: write`, `pull-requests: write`, `issues: write` | +6 permissions | Yes |
| oc-maintainer.yml | `contents: write`, `pull-requests: write`, `issues: write` | +6 permissions | Yes |
| oc-problem-finder.yml | `contents: read`, `issues: write`, `pull-requests: read` | +6 permissions | Yes |
| oc-pr-handler.yml | `contents: write`, `pull-requests: write`, `issues: write` | +6 permissions | Yes |
| oc-cf-supabase.yml | `contents: write`, `deployments: write` | +7 permissions | Yes |
| iterate.yml | `contents: write`, `pull-requests: write`, `actions: read`, `repository-projects: write` | No | No |

### Security Hardening

**Applied in Some Workflows**:
- `step-security/harden-runner@v2` - Used in oc-problem-finder.yml and oc-pr-handler.yml
- Egress policy: `audit` - Monitors network egress
- Concurrency groups - Prevents duplicate executions

**Recommendations**:
1. Remove unnecessary `id-token: write` from workflows not using OIDC
2. Remove `pages: write` from all workflows (no Pages deployment)
3. Remove `packages: write` from workflows not publishing packages
4. Remove `security-events: write` from workflows not doing security scanning
5. Remove `deployments: write` from workflows not doing deployments
6. Apply `step-security/harden-runner` to all workflows

---

## Troubleshooting Guide

### Common Issues

#### Workflow Not Running

**Symptoms**:
- Scheduled workflow doesn't execute
- Manual trigger fails

**Solutions**:
1. Check workflow logs in Actions tab
2. Verify secrets are configured
3. Check for syntax errors in YAML
4. Verify concurrency group settings

#### OpenCode Agent Failures

**Symptoms**:
- Agent produces errors
- Prompt execution fails
- Model returns unexpected output

**Solutions**:
1. Check prompt syntax and structure
2. Verify model availability
3. Check timeout settings
4. Review agent logs in workflow run output

#### Workflow Stuck

**Symptoms**:
- Workflow running for > 1 hour
- No progress in logs

**Solutions**:
1. Check if workflow-monitor is triggering it
2. Check for infinite loops in agent prompts
3. Verify timeout settings
4. Manually cancel and re-run

#### Permission Errors

**Symptoms**:
- `Resource not accessible by this integration`
- `Repository access denied`

**Solutions**:
1. Check workflow permissions
2. Verify secrets are configured
3. Check repository settings (branch protection, etc.)
4. Check token permissions

### Workflow-Specific Troubleshooting

#### on-push.yml

**Issue**: 12 prompt flows not executing
**Solution**:
- Verify `.github/prompt/` directory exists
- Check prompt file names (00-11)
- Verify fetch-depth is sufficient

#### on-pull.yml

**Issue**: PR not merging automatically
**Solution**:
- Check Git identity configuration
- Verify branch permissions
- Check if PR is mergeable (no conflicts)
- Verify all required checks are passing

#### oc-issue-solver.yml

**Issue**: Issues not being solved
**Solution**:
- Check if open issues exist
- Verify issue labels (priority, category)
- Check workflow permissions
- Review agent logs for selection logic

---

## Workflow Development Guidelines

### Creating New Workflows

1. **Define Purpose**: Clearly document what workflow does
2. **Choose Triggers**: Select appropriate events (push, pull_request, schedule)
3. **Set Minimum Permissions**: Only grant required permissions
4. **Add Concurrency**: Prevent duplicate executions
5. **Configure Timeout**: Set appropriate time limit
6. **Add Caching**: Cache dependencies to speed up runs
7. **Use Harden Runner**: Apply security hardening
8. **Test Locally**: Verify YAML syntax and logic
9. **Document**: Update this CI_CD.md with new workflow

### Modifying Existing Workflows

1. **Understand Current Behavior**: Review workflow structure and agent prompts
2. **Identify Change Required**: What needs to be modified?
3. **Test Changes**: Test in fork or draft PR
4. **Update Documentation**: Keep CI_CD.md in sync
5. **Communicate Changes**: Comment on relevant issues/PRs

### Workflow Best Practices

1. **Keep Prompts Focused**: Single, clear objective per prompt
2. **Use Retry Logic**: Handle transient failures gracefully
3. **Log Actions**: Clear logging for debugging
4. **Set Appropriate Timeouts**: Balance between completion and resource usage
5. **Use Caching**: Speed up repetitive operations
6. **Apply Security Hardening**: Use `step-security/harden-runner`
7. **Minimum Permissions**: Follow principle of least privilege
8. **Descriptive Names**: Clear workflow and job names
9. **Documentation**: Comment complex logic in workflows

---

## Consolidation Plan

### Current State

- **10 workflows** (vs recommended 3-4)
- **Multiple redundant workflows** doing similar tasks
- **Excessive permissions** on many workflows
- **No traditional CI pipeline** (no unit tests, linters, static analysis)

### Target State

- **3-4 workflows**:
  1. `on-push.yml` - Main push automation
  2. `on-pull.yml` - PR automation
  3. `ci.yml` - Traditional CI pipeline (to be added)
  4. Optional: `deployment.yml` - Deployment automation

- **Consolidated OpenCode agents**:
  - Single workflow with multiple agent prompts
  - Better resource utilization
  - Easier to maintain

### Recommended Changes

1. **Add Traditional CI Pipeline**:
   - Create `ci.yml` with:
     - PHPUnit tests
     - PHP CS Fixer (code style)
     - PHPStan (static analysis)
   - Trigger on push and pull_request

2. **Consolidate OpenCode Workflows**:
   - Merge oc-issue-solver, oc-maintainer, oc-problem-finder into single workflow
   - Merge oc-pr-handler into on-pull
   - Use job-level prompts instead of separate workflow files

3. **Reduce Permissions**:
   - Remove `id-token: write` from all workflows
   - Remove `pages: write` from all workflows
   - Remove `packages: write` from all workflows
   - Remove `security-events: write` from workflows not doing security scanning
   - Remove `deployments: write` from workflows not doing deployments

4. **Simplify Triggers**:
   - Keep on-push and on-pull as event-driven
   - Use scheduled triggers for long-running maintenance tasks only

### Related Issues

- #632: refactor(ci): Consolidate redundant GitHub workflows (11 → 3-4 workflows)
- #225: MEDIUM: Consolidate and optimize GitHub Actions workflows
- #654: feat(ci): Add essential CI checks to GitHub workflows
- #714: [INFRA] Create traditional CI pipeline with automated testing

---

## References

- **GitHub Actions Documentation**: https://docs.github.com/en/actions
- **OpenCode CLI Documentation**: https://www.opencode.ai
- **Workflow Security**: https://docs.github.com/en/actions/security-guides
- **GitHub Actions Permissions**: https://docs.github.com/en/actions/security-guides/automatic-token-authentication
- **Repository**: [malnu-backend](https://github.com/sulhicmz/malnu-backend)

---

## Quick Reference

### View Workflow Runs
```bash
gh run list
gh run view <run-id>
```

### Trigger Workflow Manually
```bash
gh workflow run <workflow-name>
```

### View Workflow Logs
```bash
gh run view <run-id> --log
```

### Cancel Workflow Run
```bash
gh run cancel <run-id>
```

---

**Document Version**: 1.0
**Last Updated**: February 8, 2026
**Maintained By**: OpenCode AI Automation System
