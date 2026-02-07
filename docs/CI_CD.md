# CI/CD Documentation

This document provides comprehensive documentation for all GitHub workflows in the Malnu Backend repository.

## Table of Contents

- [Workflow Architecture Overview](#workflow-architecture-overview)
- [Workflow Descriptions](#workflow-descriptions)
- [Workflow Triggers & Timing](#workflow-triggers--timing)
- [Workflow Permissions Matrix](#workflow-permissions-matrix)
- [OpenCode Agent Workflows](#opencode-agent-workflows)
- [Workflow Interactions](#workflow-interactions)
- [Security Guidelines for Workflows](#security-guidelines-for-workflows)
- [Troubleshooting Guide](#troubleshooting-guide)
- [Workflow Development Guidelines](#workflow-development-guidelines)

---

## Workflow Architecture Overview

The Malnu Backend repository uses an **AI-powered automation system** built on the OpenCode CLI. Instead of traditional CI/CD pipelines (like running tests, linting, static analysis on every commit), this repository relies on autonomous agents to:

1. **Monitor repository health** - Detect issues, bugs, and code quality problems
2. **Automate issue resolution** - Solve GitHub issues end-to-end
3. **Automate pull request management** - Review, fix, and merge PRs automatically
4. **Maintain repository hygiene** - Clean up branches, consolidate code, update documentation
5. **Orchestrate workflows** - Ensure multiple automation workflows don't conflict

### Key Differences from Traditional CI/CD

| Traditional CI/CD | Malnu Backend OpenCode Automation |
|------------------|-------------------------------|
| Trigger: Every commit/PR | Trigger: Scheduled / Manual dispatch |
| Purpose: Validate code | Purpose: Autonomous development |
| Speed: Fast feedback | Speed: Scheduled batch processing |
| Visibility: Always runs | Visibility: Runs on schedule/dispatch |
| Tools: GitHub Actions, Composer | Tools: OpenCode CLI, Git CLI |

### Why This Approach?

The OpenCode automation approach allows the repository to be **mostly self-maintaining** with minimal human intervention. AI agents can:
- Analyze code quality issues
- Generate user stories and feature requests
- Solve bugs and create PRs
- Consolidate duplicate PRs
- Improve code organization

---

## Workflow Descriptions

### 1. on-push.yml - Main Automation Workflow

**Purpose**: Primary orchestration workflow that drives repository-wide automation.

**What it does**:
- Runs 12 sequential OpenCode agent prompts (00-11) to perform various autonomous tasks
- Only executes if no open pull requests exist (checked via `gh issue list`)
- Skips execution if PRs are present to avoid conflicts

**Triggers**:
- `workflow_dispatch` - Manual trigger
- `push` - Automatic trigger on any push

**Permissions**:
- `contents: write` - Create/modify files and commits
- `pull-requests: write` - Manage PRs

**Key Features**:
- Queue management with `softprops/turnstyle@v2` to avoid concurrent runs
- OpenCode CLI installation and caching
- Git configuration for agent commits
- Retry logic (max 2 retries per prompt)

---

### 2. on-pull.yml - Pull Request Automation Workflow

**Purpose**: Review, fix, and manage pull requests automatically.

**What it does**:
- Creates and manages an `agent-workspace` branch
- Runs OpenCode CLI in iterative mode to fix PR issues
- Can merge PRs automatically when all checks pass
- Uses `gh pr merge --admin` to bypass branch protection (⚠️ **Security note**: see [Security Guidelines](#security-guidelines-for-workflows))

**Triggers**:
- `workflow_dispatch` - Manual trigger
- `pull_request` - On PR opened, synchronized, or reopened

**Permissions**:
- `contents: write` - Write to repository
- `pull-requests: write` - Manage PRs
- `actions: read` - Read GitHub Actions
- `repository-projects: write` - Manage GitHub Projects

**Key Features**:
- Queue management with `softprops/turnstyle@v2`
- Node.js setup for dependencies
- Auto-fix step for CI failures
- Branch management (merges main into agent-workspace)

---

### 3. iterate.yml - Iterative Improvement Workflow

**Purpose**: Orchestrates continuous improvement of the repository through multiple agent phases.

**What it does**:
- Runs through 8 phases (0-8) with different specialized agents:
  - **Phase 0**: Entry decision (check PRs/issues)
  - **Phase 1**: BugLover (find bugs and errors)
  - **Phase 2**: Pallete (UX improvements)
  - **Phase 3**: Flexy (eliminate hardcoding, add modularity)
  - **Phase 4**: TestGuard (test efficiency and performance)
  - **Phase 5**: StorX (consolidate and strengthen features)
  - **Phase 6**: CodeKeep (code quality review)
  - **Phase 7**: BroCula (browser console fixes via playwright)
  - **Phase 8**: Final branch management (commit and push)

**Triggers**:
- `workflow_dispatch` - Manual trigger
- `pull_request` - On any PR

**Permissions**:
- `contents: write` - Write to repository
- `pull-requests: write` - Manage PRs
- `actions: read` - Read GitHub Actions
- `repository-projects: write` - Manage GitHub Projects

**Models Used**:
- Uses `opencode/kimi-k2.5-free` model instead of default `opencode/glm-4.7-free`

**Key Features**:
- Delegation support for specialized tasks (visual-engineering, ultrabrain, etc.)
- Multi-role exploration
- Task generation and execution tracking

---

### 4. workflow-monitor.yml - Workflow Orchestrator

**Purpose**: Monitors and coordinates workflow execution to prevent conflicts.

**What it does**:
- Checks every 30 minutes for running workflows (`on-push`, `on-pull`)
- Triggers `on-push` if not already running
- Triggers `on-pull` if not already running
- Logs status to prevent workflow interference

**Triggers**:
- `schedule: cron('*/30 * * *')` - Every 30 minutes
- `workflow_dispatch` - Manual trigger

**Permissions**:
- `actions: read` - Check workflow status
- `contents: write` - Trigger workflows

**Key Features**:
- Uses GitHub Actions CLI to list and trigger workflows
- Conditional triggering based on workflow state

---

### 5. oc-maintainer.yml - Repository Maintainer Workflow

**Purpose**: Daily maintenance and repository health monitoring.

**What it does**:
- Scheduled to run daily at 3:00 AM
- Performs comprehensive repository scans:
  - Codebase health (dead code, duplicate modules, outdated patterns)
  - Dependencies (outdated, deprecated, insecure packages)
  - CI/CD workflows (failing jobs, redundant workflows, missing checks)
  - Security and compliance (insecure configs, missing security files)
  - Documentation and metadata (outdated docs, broken links, missing guidance)
  - Issues and PRs (maintenance tasks, long-standing items)
- Selects and executes focused maintenance tasks

**Triggers**:
- `schedule: cron('0 3 * * *')` - Daily at 3:00 AM
- `workflow_dispatch` - Manual trigger

**Permissions**:
- **All extensive permissions** (id-token, contents, pull-requests, issues, actions, deployments, packages, pages, security-events: write)

**Key Features**:
- Global concurrency lock (only 1 instance at a time)
- Uses OpenCode agent with detailed prompts
- Focuses on security, correctness, maintainability, and efficiency

---

### 6. oc-pr-handler.yml - Pull Request Maintainer Workflow

**Purpose**: Automated PR review and maintenance.

**What it does**:
- Scheduled to run every 15 minutes past the hour
- Reviews open PRs and handles review feedback
- Maintains PRs to merge-ready state
- Uses special git identity: `maskom_team@ma-malnukananga.sch.id`

**Triggers**:
- `schedule: cron('0 9,15,21 * * *')` - Every 15 minutes
- `workflow_dispatch` - Manual trigger
- `pull_request: [opened, synchronize, reopened]` - On PR activity

**Permissions**:
- **All extensive permissions** (id-token, contents, pull-requests, issues, actions, deployments, packages, pages, security-events: write)

**Key Features**:
- PR selection and prioritization (by labels, failing checks, merge readiness)
- Deep analysis of PRs and branches
- Branch health checks (up-to-date with default, CI status)
- Automatic merging when all checks pass
- Uses `gh pr merge --admin` to bypass branch protection (⚠️ **Security note**: see [Security Guidelines](#security-guidelines-for-workflows))

---

### 7. oc-issue-solver.yml - Issue Solver Workflow

**Purpose**: Solves GitHub issues end-to-end.

**What it does**:
- Scheduled to run every 30 minutes at the top of the hour
- Checks for open issues
- If issues exist, selects one based on priority/impact
- Creates PR to resolve the issue
- Uses OpenCode agent with detailed prompts

**Triggers**:
- `schedule: cron('0/30 * * *')` - Every 30 minutes
- `workflow_dispatch` - Manual trigger

**Permissions**:
- **All extensive permissions** (id-token, contents, pull-requests, issues, actions, deployments, packages, pages, security-events: write)

**Key Features**:
- Issue prioritization (P0 > P1 > P2 > P3, bugs over features)
- Deep analysis of selected issue
- Branch creation from main
- Implementation and testing
- PR creation linked to issue

---

### 8. oc-problem-finder.yml - Problem Finder Workflow

**Purpose**: Identifies code problems and creates issues (Indonesian language).

**What it does**:
- Scheduled to run hourly
- Orchestrator agent that analyzes:
  - Repository structure, architecture, modules, API, workflows, configs
  - Commit history, PRs, issues, discussions, logs
  - Identifies inconsistencies, errors, smells, technical debt
- Creates issues based on findings
- May create sub-issues for complex problems

**Triggers**:
- `schedule: cron('0 0 * * *')` - Every hour
- `workflow_dispatch` - Manual trigger

**Permissions**:
- **Limited permissions** (contents: read, issues: write, pull-requests: read)

**Key Features**:
- Orchestrator agent mode (multi-agent system)
- Focus on Indonesian language prompts (prompts are in Indonesian)

---

### 9. oc-cf-supabase.yml - DevOps Specialist Workflow

**Purpose**: Manages Cloudflare and Supabase deployments and integration.

**What it does**:
- Focuses on deployment infrastructure for:
  - Cloudflare: Workers, Pages, DNS, KV, R2, D1 deployments
  - Supabase: Database schema, migrations, auth, storage, edge functions
- Proposes and implements small features within free-tier constraints
- Fixes failing CI/tests related to deployments

**Triggers**:
- `workflow_dispatch` - Manual trigger

**Permissions**:
- **All extensive permissions** (id-token, contents, pull-requests, issues, actions, deployments, packages, write)

**Environment Variables Used**:
- `CLOUDFLARE_ACCOUNT_ID`, `CLOUDFLARE_API_TOKEN`
- Optional: `SUPABASE_URL`, `SUPABASE_ANON_KEY`, `SUPABASE_SERVICE_ROLE_KEY`, `SUPABASE_DB_PASSWORD`

**Key Features**:
- Deployment model identification and improvement
- Integration with Supabase and Cloudflare
- Free-tier optimization (caching, simple rate limiting, RLS policies)
- Small, incremental features preferred

---

## Workflow Triggers & Timing

| Workflow | Trigger | Timing | Notes |
|-----------|---------|--------|--------|
| on-push.yml | push, workflow_dispatch | Only runs when no open PRs exist | Checked every run via `gh issue list` |
| on-pull.yml | pull_request (opened, synchronize, reopened), workflow_dispatch | Runs on PR activity |  |
| iterate.yml | pull_request, workflow_dispatch | Runs on PR | 60-minute timeout |
| workflow-monitor.yml | schedule (every 30 min), workflow_dispatch | Monitors workflows | 10-minute timeout |
| oc-maintainer.yml | schedule (daily at 3:00 AM), workflow_dispatch | Daily maintenance | 40-minute timeout |
| oc-pr-handler.yml | schedule (every 15 min), pull_request (opened, sync, reopened), workflow_dispatch | PR automation | 40-minute timeout |
| oc-issue-solver.yml | schedule (every 30 min), workflow_dispatch | Issue solving | 40-minute timeout |
| oc-problem-finder.yml | schedule (every hour), workflow_dispatch | Problem finding | 40-minute timeout |
| oc-cf-supabase.yml | workflow_dispatch | DevOps tasks | 40-minute timeout |

---

## Workflow Permissions Matrix

| Workflow | Contents | Pull Requests | Issues | Actions | Deployments | Packages | Pages | Security Events |
|-----------|-----------|---------------|---------|----------|----------|----------|----------------|
| on-push.yml | write | write | - | - | - | - | - |
| on-pull.yml | write | write | - | read | write | - | - |
| iterate.yml | write | write | - | read | write | - | - |
| workflow-monitor.yml | - | - | - | write | - | - | - |
| oc-maintainer.yml | write | write | write | write | write | write | write | write |
| oc-pr-handler.yml | write | write | write | write | write | write | write | write |
| oc-issue-solver.yml | write | write | write | write | write | write | write | write |
| oc-problem-finder.yml | read | - | write | - | - | - | - |
| oc-cf-supabase.yml | write | - | - | write | write | - | - |

**Legend**:
- `write` = Full write access
- `-` = No permission
- `read` = Read-only access

**Security Notes**:
- Most workflows have extensive write permissions (id-token, contents, pull-requests, etc.)
- This is intentional for autonomous agents to manage the repository
- See [Security Guidelines](#security-guidelines-for-workflows) for considerations

---

## OpenCode Agent Workflows

### What is OpenCode?

OpenCode is an AI-powered CLI tool that provides autonomous software engineering agents. The repository uses OpenCode to automate:

- Code analysis and bug detection
- Issue resolution
- PR management
- Repository maintenance
- Documentation updates
- Feature ideation

### Available Models

| Model | Used In | Notes |
|--------|----------|--------|
| opencode/glm-4.7-free | Most workflows (default) | Free tier model |
| opencode/kimi-k2.5-free | iterate.yml | Different model for specialized tasks |
| opencode/minimax-m2.1-free | Configurable via environment |

### Agent Types

The OpenCode agents are specialized for different tasks:

1. **Orchestrator Agents** (on-push, on-pull, oc-maintainer, oc-pr-handler, oc-issue-solver)
   - Coordinate repository-wide automation
   - Manage PRs and issues
   - Maintain repository health

2. **Specialist Agents** (iterate.yml)
   - **BugLover**: Find bugs and errors
   - **Pallete**: UX improvements
   - **Flexy**: Eliminate hardcoding, add modularity
   - **TestGuard**: Test efficiency
   - **StorX**: Consolidate features
   - **CodeKeep**: Code quality review
   - **BroCula**: Browser console fixes (Playwright)

3. **DevOps Specialist** (oc-cf-supabase.yml)
   - Manages Cloudflare and Supabase deployments
   - Implements free-tier features

4. **Problem Finder** (oc-problem-finder.yml)
   - Identifies code problems
   - Creates issues in Indonesian language

### How OpenCode Works

1. **Installation**: Workflows install OpenCode CLI via `curl -fsSL https://opencode.ai/install | bash`
2. **Caching**: `~/.opencode` and `~/.npm` directories are cached for faster execution
3. **Execution**: Workflows run `opencode run` with prompts containing detailed instructions
4. **Model Selection**: Different workflows use different models based on task requirements
5. **Git Configuration**: Workflows configure git user/email for agent commits

---

## Workflow Interactions

### Workflow Orchestrator (workflow-monitor.yml)

The `workflow-monitor.yml` orchestrates workflow execution:

```
┌──────────────────────────────────────┐
│  workflow-monitor (every 30 min)    │
└──────────────────────────────────────┘
              │
              ▼
         ┌──────┴──────┐
         │                │
  on-push?    on-pull?
         │                │
         ▼               ▼
    Trigger on-push   Trigger on-pull
         │                │
         │                │
         │                │
         │                │
         ▼                ▼
    on-push runs   on-pull runs
         │                │
         │                │
         └────────────────┘
```

**Rules**:
- Only one of `on-push` or `on-pull` runs at a time
- Prevents conflicts between automation workflows
- Uses GitHub Actions CLI to trigger workflows

### OpenCode Workflow Dependencies

OpenCode workflows are designed to be sequential and avoid conflicts:

```
on-push.yml (triggered)
    │
    ▼
    ├─→ Check for PRs
    │   └─→ If no PRs: Run 12 prompts
    │         └─→ Trigger iterate.yml (if needed)
    └─→ Update agent workspace
         │
         ▼
on-pull.yml (triggered by PRs)
    │
    ├─→ Branch management
    │   └─→ Run iterate.yml in loop
    │       └─→ Auto-fix CI failures
    │       └─→ Push to main/merge
    │
    ▼
iterate.yml (runs only when no other agent active)
    │
    ▼
    └─→ Run through 8 phases
            │
            └─→ Commit and push
```

---

## Security Guidelines for Workflows

### Permission Principle

The OpenCode workflows use **extensive permissions** intentionally:

```yaml
permissions:
  id-token: write              # Required for OIDC (if needed)
  contents: write              # Required for git operations, PRs, issues
  pull-requests: write         # Required for PR management
  issues: write               # Required for issue creation/closure
  actions: write               # Required for workflow triggering
  deployments: write            # Required for Cloudflare deployments
  packages: write              # Required for publishing (if needed)
  pages: write                 # Required for GitHub Pages (if needed)
  security-events: write        # Required for security scanning
```

**Why These Permissions?**

These permissions allow autonomous agents to:
- Create commits and branches
- Open and merge PRs
- Create and close issues
- Manage GitHub Projects
- Trigger other workflows
- Deploy to Cloudflare/Supabase
- Update branch protection settings

### Security Considerations

⚠️ **CRITICAL: Admin Merge Bypass**

The `on-pull.yml` workflow uses `gh pr merge --admin` to bypass branch protection:

```yaml
# oc-pr-handler.yml
Use `gh pr merge --admin` to bypass branch protection when conditions are met.
```

**Risk**: If someone compromises the GitHub token, they could bypass all branch protection rules and force-merge malicious code.

**Mitigation**:
- GitHub token is scoped to repository (not personal access token)
- Workflow is only triggered by pull_request events or schedule
- Merges only when all checks pass
- See [Issue #629](https://github.com/sulhicmz/malnu-backend/issues/629) for details

### Least Privilege Principle

Only grant permissions that workflows actually need:

| Permission | When Needed | When NOT Needed |
|------------|--------------|----------------|
| `contents: write` | Making commits, creating PRs, editing files | For read-only analysis |
| `pull-requests: write` | Managing PRs | For simple CI checks |
| `issues: write` | Creating/closing issues | For read-only review |
| `deployments: write` | Deploying to Cloudflare | For code-only work |
| `pages: write` | Publishing GitHub Pages | If not using Pages |
| `security-events: write` | Creating security events | If not doing security scanning |

### Secret Management

✅ **Best Practices**:
- Use GitHub Secrets (never hardcode secrets)
- Use environment variables for configuration
- Never commit secrets to repository
- Never print secrets to logs

❌ **Anti-Patterns**:
- Never use personal access tokens
- Never use tokens from external sources
- Never pass secrets in URLs or command arguments

### GitHub Token Usage

| Workflow | Token Type | Usage |
|-----------|-------------|---------|
| Most OpenCode workflows | `secrets.GITHUB_TOKEN` | Scoped to repository |
| oc-problem-finder.yml | `secrets.GITHUB_TOKEN` | Read-only usage |
| oc-cf-supabase.yml | `github.token` | Deployment operations |

---

## Troubleshooting Guide

### Workflow Not Running

**Symptom**: A scheduled workflow (like `oc-maintainer.yml`) didn't run at expected time.

**Diagnosis Steps**:
1. Check workflow run history: `gh run list --workflow <workflow-name>`
2. Look for failure status: `gh run view <run-id>`
3. Check workflow logs in Actions tab

**Common Causes**:
- GitHub Actions service outage or delay
- Workflow syntax error
- Permission issue
- Timeout due to long-running task

### OpenCode Agent Not Responding

**Symptom**: Workflow shows "OpenCode execution succeeded" but no PR/issue was created.

**Diagnosis**:
1. Check agent output for errors or warnings
2. Review agent logs in workflow step
3. Check if conditions were met (e.g., "no issues" skip condition)

**Common Causes**:
- Agent couldn't complete task due to missing context
- API rate limiting
- Model timeout or quota exceeded

### PR Creation Failed

**Symptom**: Agent commits to branch but no PR appears.

**Diagnosis**:
1. Check git log: `git log origin/<branch-name>`
2. Check if push succeeded: `git push --dry-run` (to test without actually pushing)
3. Check for push failures in workflow logs

### Branch Conflicts

**Symptom**: Agent reports "Merge conflict or already up to date" but can't proceed.

**Diagnosis**:
1. Check branch status: `git status`
2. Check default branch: `git remote show origin`
3. Manually resolve conflicts if trivial

### CI Check Failures

**Symptom**: A PR fails checks that should pass.

**Diagnosis**:
1. Re-run failed checks locally:
   - `composer test` (if tests)
   - `composer cs-diff` (code style)
   - `composer analyse` (static analysis)
2. Check for environment differences (local vs CI)
3. Check for dependencies not installed
4. Review recent changes that may have broken checks

---

## Workflow Development Guidelines

### When Adding a New Workflow

1. **Choose Purpose**: Define clear objective (e.g., "Run tests on every PR", "Deploy to staging")
2. **Select Minimal Permissions**: Only request permissions needed
3. **Consider Triggers**: Choose appropriate events (schedule, push, pull_request)
4. **Set Timeouts**: Configure appropriate timeout based on task duration
5. **Use Reusable Actions**: Where possible, use GitHub composite actions or existing patterns
6. **Document**: Add comments explaining non-obvious logic

### When Modifying Existing Workflow

1. **Understand Dependencies**: Check which workflows trigger or depend on this one
2. **Test Locally**: Run workflow via `workflow_dispatch` before merging
3. **Update Documentation**: Keep this file in sync
4. **Monitor**: Check first few runs after deployment
5. **Rollback Plan**: Have a revert plan ready if issues arise

### Anti-Patterns

❌ **NEVER DO**:
- Add `id-token: write` permission if not using OIDC
- Use `secrets.GITHUB_TOKEN` for personal operations
- Add excessive permissions "just in case"
- Create workflows without understanding existing system
- Remove concurrency locks without understanding impact

✅ **ALWAYS DO**:
- Use minimal permissions (read-only when possible)
- Document clearly why each permission is needed
- Follow existing patterns (e.g., queue management with turnstyle)
- Consider security implications of changes
- Test before merging to main

### Related Documentation

- [Issue #632](https://github.com/sulhicmz/malnu-backend/issues/632) - Workflow consolidation efforts
- [Issue #611](https://github.com/sulhicmz/malnu-backend/issues/611) - Workflow permission hardening
- [Issue #572](https://github.com/sulhicmz/malnu-backend/issues/572) - PR consolidation
- [Issue #225](https://github.com/sulhicmz/malnu-backend/issues/225) - CI/CD optimization

---

## Notes

### Traditional CI Pipeline Status

⚠️ **Current State**: The repository does NOT have traditional CI pipeline (running tests, code style, static analysis on every commit).

**Alternative**: All CI/CD is handled by OpenCode agents and scheduled workflows.

**To Add Traditional CI**:
- Create `ci.yml` with jobs for:
  - PHP syntax validation
  - PHPUnit tests (`composer test`)
  - Code style checks (`composer cs-diff`)
  - Static analysis (`composer analyse`)
  - Security audit (`composer audit`)
- Trigger on `pull_request` and `push: [main]`
- Set appropriate permissions (read-only for CI checks)

### Future Improvements

1. **Workflow Consolidation**: Consider reducing from 10 workflows to 3-4 focused workflows
2. **Better Logging**: Add structured logging for workflow execution
3. **Metrics**: Track workflow success/failure rates
4. **Self-Healing**: Add ability for workflows to detect and fix their own failures
5. **Developer Controls**: Add manual dispatch options for specific workflow phases

---

## Quick Reference

### Workflow Quick Links

| Workflow | File | Purpose | Trigger |
|-----------|--------|---------|-----------|
| Main Automation | on-push.yml | Orchestrator | push, dispatch |
| PR Automation | on-pull.yml | PR Handler | PR, dispatch |
| Iteration | iterate.yml | Orchestrator | PR, dispatch |
| Monitor | workflow-monitor.yml | Orchestrator | schedule (30 min) |
| Maintainer | oc-maintainer.yml | Maintenance | schedule (daily) |
| PR Handler | oc-pr-handler.yml | PR Review | schedule (15 min), PR |
| Issue Solver | oc-issue-solver.yml | Issues | schedule (30 min), dispatch |
| Problem Finder | oc-problem-finder.yml | Analysis | schedule (hourly), dispatch |
| DevOps | oc-cf-supabase.yml | Deployments | dispatch |

### Common Commands

```bash
# List recent workflow runs
gh run list --workflow <workflow-name>

# View workflow logs
gh run view <run-id>

# Trigger a workflow manually
gh workflow run <workflow-name>

# Check for running workflows
gh run list --json databaseId,name,status --jq '.[] | select(.status == "in_progress" or .status == "queued")'

# List open issues
gh issue list --state open

# List open PRs
gh pr list --state open
```

---

**Document Version**: 1.0  
**Last Updated**: 2026-02-07  
**Maintainer**: Repository maintainers
