# OpenCode Automated Workflows

This document describes all OpenCode AI agents used in this repository, their purposes, schedules, permissions, and how to work with them.

## Overview

The repository uses 6 OpenCode AI agent workflows to automate development, maintenance, and DevOps tasks. Each agent has a specific focus and runs on different schedules.

**Key Characteristics:**
- All workflows use a global concurrency lock to prevent overlapping runs
- All workflows have a 40-minute timeout (with 20-minute limits per individual OpenCode agent run)
- All workflows use the OpenCode CLI installed via `curl -fsSL https://opencode.ai/install | bash`
- All workflows require the `IFLOW_API_KEY` secret
- All workflows use `GH_TOKEN` for GitHub API access

---

## Agent Details

### 1. oc-issue-solver

**File:** `.github/workflows/oc-issue-solver.yml`

**Schedule:** Every 30 minutes (`0/30 * * * *`)

**Trigger:**
- Scheduled: Every 30 minutes
- Manual: `workflow_dispatch`

**Purpose:**
- Solves GitHub issues end-to-end in a single run
- Selects one issue per run based on priority
- Implements the solution, tests it, and creates a pull request
- Posts plan and completion comments on issues

**Issue Selection Criteria:**
1. Issues labeled with highest priority (P0, P1, critical)
2. Issues assigned to the agent
3. Issues with highest user impact (bugs over features, production issues over minor improvements)
4. Oldest open issues if equivalent

**Capabilities:**
- Deep analysis of chosen issue (read issue, comments, inspect codebase)
- Creates focused, incremental changes
- Runs tests and quality checks
- Links PR to issue with closing keyword (Fixes, Closes, Resolves)
- Updates documentation when behavior changes

**Permissions:**
- Workflow-level: `contents: write`, `pull-requests: write`, `issues: write`, `actions: read`
- Job-level (excessive): `id-token: write`, `contents: write`, `pull-requests: write`, `issues: write`, `actions: write`, `deployments: write`, `packages: write`, `pages: write`, `security-events: write`

**Model:** `opencode/glm-4.7-free`

**Secrets:**
- `IFLOW_API_KEY` - OpenCode API authentication
- `GH_TOKEN` - GitHub API token (automatically provided)

---

### 2. oc-problem-finder

**File:** `.github/workflows/oc-problem-finder.yml`

**Schedule:** Daily at midnight UTC (`0 0 * * *`)

**Trigger:**
- Scheduled: Daily at 00:00 UTC
- Manual: `workflow_dispatch`

**Purpose:**
- Performs deep repository analysis
- Identifies problems, code smells, technical debt, and optimization opportunities
- Creates well-structured issues based on findings
- Focuses on maintaining code health and identifying improvement areas

**Capabilities:**
- Analyzes entire codebase structure, modules, API, workflows, and configuration
- Inspects commit history, PRs, issues, and GitHub Actions logs
- Identifies inconsistencies, errors, and architectural issues
- Creates projects and manages task prioritization
- Generates granular, actionable tasks

**Permissions:**
- Workflow-level: `contents: read`, `issues: write`, `pull-requests: read`
- Job-level (excessive): `id-token: write`, `contents: write`, `pull-requests: write`, `issues: write`, `actions: write`, `deployments: write`, `packages: write`, `pages: write`, `security-events: write`

**Model:** `iflowcn/qwen3-coder-plus`

**Special Features:**
- Uses `step-security/harden-runner@v2` with egress-policy audit
- Prompt is written in Indonesian language

---

### 3. oc-maintainer

**File:** `.github/workflows/oc-maintainer.yml`

**Schedule:** Daily at 3AM UTC (`0 3 * * *`)

**Trigger:**
- Scheduled: Daily at 03:00 UTC
- Manual: `workflow_dispatch`

**Purpose:**
- Keeps repository clean, secure, maintainable, efficient
- Proactively identifies security, CI, code health, and configuration issues
- Addresses problems in a controlled, auditable way via issues and PRs
- Coordinates through issues, PRs, and comments (not silent changes)

**Capabilities:**
- Scans for: dead/unused code, outdated patterns, large unstructured files
- Inspects: dependencies (outdated, deprecated, insecure, unused)
- Reviews: CI/CD workflows, failing/flaky jobs, redundant workflows, missing checks
- Checks: security (hard-coded secrets, overly permissive settings), documentation (outdated, broken links)
- Manages: maintenance-labeled issues, prioritizing by security, correctness, CI stability

**Constraints:**
- Works on ONE maintenance theme per run (prefers small, focused PRs)
- Does NOT change: licensing, ownership, legal metadata
- Does NOT introduce breaking API changes without documentation
- Does NOT disable security features/tests just to make things pass
- Always uses branches and PRs (no direct pushes to main)

**Permissions:**
- Workflow-level: `contents: write`, `pull-requests: write`, `issues: write`, `actions: write`
- Job-level (excessive): `id-token: write`, `contents: write`, `pull-requests: write`, `issues: write`, `actions: write`, `deployments: write`, `packages: write`, `pages: write`, `security-events: write`

**Model:** `opencode/glm-4.7-free`

---

### 4. oc- researcher

**File:** `.github/workflows/oc- researcher.yml` (note: filename contains a space)

**Schedule:** Daily at 1AM UTC (`0 1 * * *`)

**Trigger:**
- Scheduled: Daily at 01:00 UTC
- Manual: `workflow_dispatch`

**Purpose:**
- Understands repository and existing features in depth
- Discovers optimization opportunities and feature connections
- Uses web research to find standard features for similar products
- Identifies missing features and improvements
- Creates non-duplicated, well-structured issues

**Capabilities:**
- Analyzes project purpose and domain
- Inventories existing features from pages, routes, components, modules, APIs
- Identifies feature quality issues (clarity, modularity, reusability, performance)
- Researches standard features via web for similar product categories
- Compares repository features vs industry standards
- Creates improvement/refactor/new feature issues based on findings

**Duplicate Checking:**
- Searches existing issues by keywords, terms, and categories
- Searches open and closed pull requests for existing implementations
- Only creates new issue if no existing issue or PR covers the same work
- Adds comments to existing issues/PRs with additional insights from research

**Permissions:**
- Workflow-level: `id-token: write`, `contents: write`, `pull-requests: write`, `issues: write`, `actions: write`, `deployments: write`, `packages: write`, `pages: write`, `security-events: write`
- Job-level (excessive): Same as workflow-level

**Model:** `iflowcn/glm-4.6`

**Constraints:**
- Never relies on internal training data - always uses web to verify
- Always asks focused questions when something is unclear
- Never provides examples in responses
- Follows GitHub's standard conventions for issues
- Prefers existing repository conventions over inventing new ones

---

### 5. oc-pr-handler

**File:** `.github/workflows/oc-pr-handler.yml`

**Schedule:** Three times daily (`0 9,15,21 * * *` - 9AM, 3PM, 9PM UTC)

**Trigger:**
- Scheduled: 9AM, 3PM, 9PM UTC
- Manual: `workflow_dispatch`
- Event: `pull_request` (opened, synchronize, reopened)

**Purpose:**
- Maintains and improves existing pull requests
- Handles PR review comments and suggestions
- Applies requested changes when appropriate
- Ensures PRs are merge-ready and then merges them
- Focuses on quality over new features

**Selection Criteria:**
- PRs explicitly labeled or assigned for bot/automation handling
- PRs with requested changes and unresolved review comments
- PRs with failing required checks that appear fixable
- Otherwise, PRs closest to merge-ready (fewest conversations, green checks)

**Capabilities:**
- Reads: PR title, description, all commits, full diff, all comments, CI status
- Identifies: PR goal, intended behavior, acceptance criteria, requested changes
- Inspects: relevant modules, files, tests, existing patterns
- Considers: backwards compatibility, security, performance impact

**PR Merge Criteria:**
- All required checks and builds are passing
- No unresolved change requests or blocking reviews
- PR branch is up to date with protected target branch
- Repository policies allow merges from this workflow's credentials

**Git Identity:**
- Uses `maskom_team` as Git author/committer email: `maskom_team@ma-malnukananga.sch.id`

**Permissions:**
- Workflow-level: `contents: write`, `pull-requests: write`, `issues: write`, `actions: read`
- Job-level (excessive): `id-token: write`, `contents: write`, `pull-requests: write`, `issues: write`, `actions: write`, `deployments: write`, `packages: write`, `pages: write`, `security-events: write`

**Model:** `opencode/glm-4.7-free`

**Secrets:**
- `IFLOW_API_KEY`
- `GH_TOKEN`
- `CLOUDFLARE_ACCOUNT_ID`
- `CLOUDFLARE_API_TOKEN`

---

### 6. oc-cf-supabase

**File:** `.github/workflows/oc-cf-supabase.yml`

**Schedule:** Manual only (`workflow_dispatch`)

**Trigger:**
- Manual: `workflow_dispatch` only (no scheduled runs)

**Purpose:**
- DevOps specialist for Cloudflare and Supabase infrastructure
- Manages and improves deployments on Cloudflare (Workers, Pages, DNS, KV, R2, D1)
- Designs and maintains integration with Supabase (database schema, migrations, auth, storage)
- Proposes small incremental features within free-tier limits

**Capabilities:**
- **Cloudflare:**
  - Makes default branch reliably deployable (fix build/deploy scripts, wrangler config)
  - Improves deployment pipeline (GitHub Actions, CLI commands)
  - Adds observability, health checks, configuration improvements
  - Ensures repeatable, scriptable deployment processes

- **Supabase:**
  - Implements or refines integration (client usage, environment variables, connection handling)
  - Manages migrations (safe, backward compatible)
  - Adds indexes for frequently queried columns
  - Implements basic RLS (Row Level Security) policies

**Scope per Run:**
- Completes exactly ONE DevOps task per run, such as:
  - A concrete deployment improvement for Cloudflare
  - A concrete integration improvement for Supabase
  - A small new feature leveraging Cloudflare/Supabase within free-tier constraints
  - A repair task (fix failing CI, broken deploy, broken schema)

**Constraints:**
- Never prints or commits secrets/tokens
- Uses environment variables for credentials
- Avoids destructive operations (deleting zones, wiping KV, dropping DBs) without explicit intention
- Prefers configuration and code changes over touching critical DNS records
- Stays within free-tier limits for all operations

**Git Identity:**
- Uses `maskom_team` as Git author/committer email: `maskom_team@ma-malnukananga.sch.id`

**Permissions:**
- Workflow-level: `contents: write`, `deployments: write`, `packages: write`, `id-token: write`
- Job-level (excessive): All workflow-level permissions plus `pull-requests: write`, `issues: write`, `actions: write`, `pages: write`, `security-events: write`

**Model:** `iflowcn/glm-4.6`

**Secrets:**
- `IFLOW_API_KEY`
- `GH_TOKEN`
- `CLOUDFLARE_ACCOUNT_ID`
- `CLOUDFLARE_API_TOKEN`
- Optional Supabase secrets: `SUPABASE_URL`, `SUPABASE_ANON_KEY`, `SUPABASE_SERVICE_ROLE_KEY`, `SUPABASE_DB_PASSWORD` (if configured)

**Special Features:**
- Uses `ubuntu-slim` runner (smaller than ubuntu-24.04-arm)
- Does NOT use `step-security/harden-runner` (unlike oc-problem-finder and oc-pr-handler)

---

## Agent Interaction with Issues

### Plan Comments

Before implementing changes, agents post a plan comment on issues that includes:

1. **Confirmation** - Taking the issue
2. **Understanding** - Summary of the problem/request
3. **Plan** - Concrete, ordered steps to implement
4. **Assumptions & Risks** - Any assumptions or trade-offs
5. **Questions** - Clarifying questions if something is unclear

The plan is:
- High-level but actionable
- Broken down into small, logical steps
- Feasible to implement in a single PR

### Completion Comments

After creating a PR, agents post a final comment that:

1. **References the opened PR**
2. **States implementation is complete**
3. **Summarizes main changes** at a high level
4. **Provides important notes** - Migration steps, configuration changes, follow-up tasks

### Closing Keywords

Agents use standard GitHub closing keywords in PRs:
- `Fixes #123`
- `Closes #456`
- `Resolves #789`

This ensures GitHub automatically links the PR to the issue and closes it when the PR is merged.

---

## Permissions & Security

### Current Permission State

Most workflows have **excessive permissions** at the job level:

**Unnecessary permissions used (not needed by most agents):**
- `id-token: write` - OIDC tokens not needed for most workflows
- `deployments: write` - Only oc-cf-supabase actually needs this
- `packages: write` - No package publishing in this repository
- `pages: write` - No GitHub Pages deployment
- `security-events: write` - No security event scanning

**Risk:**
The attack surface is ~60% larger than necessary due to excessive permissions. See [Issue #611](https://github.com/sulhicmz/malnu-backend/issues/611) for ongoing security hardening work.

### Secrets

All workflows use:
- **`IFLOW_API_KEY`** - OpenCode API key (required for all agents)
- **`GH_TOKEN`** - GitHub token (automatically provided as `${{ github.token }}`)

Additional secrets:
- **oc-pr-handler & oc-cf-supabase:** `CLOUDFLARE_ACCOUNT_ID`, `CLOUDFLARE_API_TOKEN`
- **oc-cf-supabase (optional):** Supabase-related secrets

### Concurrency

All workflows use a global concurrency lock:

```yaml
concurrency:
  group: ${{ github.repository }}-global-workflow
  cancel-in-progress: false
```

This ensures only ONE instance of ANY workflow runs at a time across all events. If a workflow is already running, the new trigger waits.

---

## Troubleshooting

### Checking Agent Logs

1. Go to the **Actions** tab in the GitHub repository
2. Click on the workflow run you're interested in
3. Click on the specific job (e.g., "OC")
4. Expand the logs section to view output

### Common Failure Scenarios

#### Agent Timeout (40 minutes)

**Symptoms:** Workflow runs for 40 minutes then fails with timeout error.

**Causes:**
- Agent is processing a complex task that exceeds time limit
- Agent is stuck in an infinite loop or long-running operation

**Solutions:**
- Check agent output for long-running operations
- If the issue is too complex, consider breaking it down into smaller sub-issues
- Reduce test scope or skip non-essential tests

#### No Open Issues (oc-issue-solver)

**Symptoms:** Workflow runs but skips with message "No open issues, skipping workflow."

**Expected:** This is normal when there are no open issues to solve.

**Action Required:** None - this is expected behavior.

#### Secret Not Found

**Symptoms:** Workflow fails with error about missing secret `IFLOW_API_KEY`.

**Solutions:**
1. Go to Repository Settings → Secrets and variables
2. Check that `IFLOW_API_KEY` is configured
3. If missing, add the secret (obtain from OpenCode platform)
4. Re-run the workflow

#### CI/Test Failures

**Symptoms:** Agent completes but PR fails required checks (tests, lint, build).

**Solutions:**
1. Review PR comments to see what failed
2. Agent should have addressed the failures in a follow-up commit
3. If not, re-run the workflow or manually fix the issues

---

## How to Manage Agents

### Manually Triggering a Workflow

1. Go to the **Actions** tab in GitHub
2. Click on the workflow name (e.g., "oc - issue solver")
3. Click the **"Run workflow"** button
4. Optionally add input parameters if the workflow supports them

### Disabling an Agent Temporarily

**Option 1: Disable scheduling**
1. Go to `.github/workflows/`
2. Comment out or remove the `schedule:` section
3. Commit and push

**Option 2: Disable via UI**
1. Go to the **Actions** tab
2. Click on the workflow
3. Click **..." (three dots)**
4. Select **"Disable workflow"**

### Permanently Removing an Agent

1. Delete the workflow file: `.github/workflows/oc-{name}.yml`
2. Commit and push the deletion
3. Open an issue documenting why the agent was removed

---

## Models Used

Different agents use different AI models:

| Agent | Model | Purpose |
|---------|--------|---------|
| oc-issue-solver | opencode/glm-4.7-free | Issue solving |
| oc-problem-finder | iflowcn/qwen3-coder-plus | Problem identification |
| oc-maintainer | opencode/glm-4.7-free | Maintenance |
| oc- researcher | iflowcn/glm-4.6 | Research |
| oc-pr-handler | opencode/glm-4.7-free | PR handling |
| oc-cf-supabase | iflowcn/glm-4.6 | DevOps |

---

## Best Practices for Working with Agents

### For Maintainers

1. **Review agent contributions** - Agents can make mistakes; review all PRs created by bots
2. **Provide feedback** - Give clear, actionable feedback on agent PRs
3. **Monitor for patterns** - Watch for recurring issues or mistakes agents make
4. **Update constraints** - If agents consistently do something wrong, update their prompts

### For Developers

1. **Read agent plan comments** - Understand what the agent intends to do
2. **Challenge unclear decisions** - If something seems wrong, comment on the issue before the agent proceeds
3. **Provide feedback** - Review agent PRs and give constructive feedback
4. **Learn from patterns** - Observe how agents work to understand the codebase better

---

## Related Documentation

- [Contributing Guide](CONTRIBUTING.md) - Guidelines for contributing to the repository
- [API Documentation](API.md) - API endpoints and usage
- [OpenCode Documentation](https://opencode.ai/docs) - Official OpenCode platform documentation
- [Issue #611](https://github.com/sulhicmz/malnu-backend/issues/611) - Workflow security hardening
- [Issue #632](https://github.com/sulhicmz/malnu-backend/issues/632) - Workflow consolidation

---

## Summary Table

| Agent | Schedule | Purpose | Permissions | Model |
|---------|-----------|----------|------------|--------|
| oc-issue-solver | Every 30 min | Solve issues | glm-4.7-free |
| oc-problem-finder | Daily (00:00 UTC) | Find problems | qwen3-coder-plus |
| oc-maintainer | Daily (03:00 UTC) | Repository maintenance | glm-4.7-free |
| oc- researcher | Daily (01:00 UTC) | Research features | glm-4.6 |
| oc-pr-handler | 3x daily (09:00, 15:00, 21:00 UTC) | Handle PRs | glm-4.7-free |
| oc-cf-supabase | Manual only | DevOps (Cloudflare/Supabase) | glm-4.6 |
