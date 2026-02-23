# Malnu Backend - AI Agent Configuration Reference

> This document provides comprehensive documentation for all AI agents configured in this repository.
> Referenced by `opencode.json` as part of agent instructions.

## Overview

This repository uses **OpenCode CLI** as the primary AI agent framework with multiple specialized agents for different development tasks. Agents are configured in:

- **opencode.json** - Primary agent definitions and custom commands
- **.opencode/oh-my-opencode.json** - Plugin configuration with additional agents and skills
- **.github/workflows/** - Automated GitHub Actions agents

---

## Primary Agents (opencode.json)

### cmz - Automation Specialist

| Property | Value |
|----------|-------|
| Mode | Primary |
| Model | `opencode/kimi-k2.5-free` |
| Temperature | 0.2 |
| Description | CMZ - Self-healing, self-learning, self-evolving automation specialist |

**Capabilities:**
- Full read/write/edit access
- Web search and code search enabled
- Composer, PHP artisan, Git, npm/yarn, pip, gh CLI access
- OpenCode CLI execution allowed

**Best For:** Complex automation tasks, self-healing workflows, repository management

---

### build - Full Development Agent

| Property | Value |
|----------|-------|
| Mode | Primary |
| Model | `opencode/kimi-k2.5-free` |
| Temperature | 0.3 |
| Description | Full development agent for HyperVel/Laravel projects |

**Capabilities:**
- Full read/write/edit access
- Composer, PHP artisan, Git access
- Web fetch enabled

**Best For:** Feature implementation, bug fixes, general development tasks

---

### plan - Planning & Analysis (Read-Only)

| Property | Value |
|----------|-------|
| Mode | Primary |
| Model | `opencode/glm-4.7-free` |
| Temperature | 0.1 |
| Description | Planning and analysis agent for code review and architecture |

**Capabilities:**
- Read-only access (no write/edit/bash)
- Web fetch enabled for research

**Best For:** Code review, architecture analysis, planning, documentation review

---

## Subagents (opencode.json)

### php-specialist

| Property | Value |
|----------|-------|
| Mode | Subagent |
| Model | `opencode/kimi-k2.5-free` |
| Temperature | 0.2 |
| Description | PHP/HyperVel specialist for complex PHP tasks and optimization |

**Capabilities:**
- Full read/write/edit access
- Composer, PHP artisan, PHPStan, PHP-CS-Fixer access

**Best For:** Complex PHP tasks, code optimization, PSR-12 compliance

---

### database-specialist

| Property | Value |
|----------|-------|
| Mode | Subagent |
| Model | `opencode/glm-4.7-free` |
| Temperature | 0.1 |
| Description | Database specialist for migrations, queries, and optimization |

**Capabilities:**
- Full read/write/edit access
- PHP artisan migrate, db: commands, composer test access

**Best For:** Database migrations, query optimization, schema design

---

### testing-specialist

| Property | Value |
|----------|-------|
| Mode | Subagent |
| Model | `opencode/minimax-m2.1-free` |
| Temperature | 0.2 |
| Description | Testing specialist for PHPUnit and feature tests |

**Capabilities:**
- Full read/write/edit access
- Composer test, php artisan test, phpunit access

**Best For:** Writing tests, test coverage improvement, test debugging

---

### security-auditor (Read-Only)

| Property | Value |
|----------|-------|
| Mode | Subagent |
| Model | `opencode/kimi-k2.5-free` |
| Temperature | 0.1 |
| Description | Security specialist for vulnerability assessment and security best practices |

**Capabilities:**
- Read-only access (no write/edit/bash)
- Web fetch enabled for security research

**Best For:** Security audits, vulnerability assessment, security recommendations

---

### performance-optimizer

| Property | Value |
|----------|-------|
| Mode | Subagent |
| Model | `opencode/glm-4.7-free` |
| Temperature | 0.2 |
| Description | Performance optimization specialist for HyperVel/Swoole applications |

**Capabilities:**
- Full read/write/edit access
- Composer analyse, php artisan start, composer test access

**Best For:** Performance analysis, Swoole optimization, coroutine tuning

---

## Oh-My-OpenCode Agents (.opencode/oh-my-opencode.json)

### sisyphus - Orchestrator

| Property | Value |
|----------|-------|
| Model | `opencode/kimi-k2.5-free` |
| Temperature | 1.0 |

**Role:** Powerful orchestration agent with delegation capabilities

---

### oracle - Consultant (Read-Only)

| Property | Value |
|----------|-------|
| Model | `opencode/glm-4.7-free` |
| Temperature | 0.1 |

**Role:** Read-only consultation for architecture, debugging, and complex logic

---

### frontend-ui-ux-engineer

| Property | Value |
|----------|-------|
| Model | `opencode/minimax-m2.1-free` |
| Temperature | 0.3 |

**Role:** Frontend, UI/UX, design, styling, and animation specialist

---

### librarian - Reference Researcher

| Property | Value |
|----------|-------|
| Model | `opencode/kimi-k2.5-free` |
| Temperature | 1.0 |

**Role:** External reference search for docs, OSS, and API documentation

---

### explore - Contextual Search

| Property | Value |
|----------|-------|
| Model | `opencode/glm-4.7-free` |
| Temperature | 0.1 |

**Role:** Codebase exploration and pattern discovery

---

## Custom Commands (opencode.json)

| Command | Agent | Description |
|---------|-------|-------------|
| `/test` | testing-specialist | Run tests with coverage |
| `/analyse` | php-specialist | Run static analysis |
| `/migrate` | database-specialist | Run database migrations |
| `/cs-fix` | php-specialist | Fix code style (PSR-12) |
| `/security-check` | security-auditor | Security audit |
| `/performance-check` | performance-optimizer | Performance analysis |
| `/api-endpoint` | php-specialist | Create API endpoint |
| `/model` | database-specialist | Create model with migration |

---

## Skills (oh-my-opencode.json)

| Skill | Enabled | Description |
|-------|---------|-------------|
| git-master | Yes | Git operations, commits, rebase, history |
| debugging-strategies | Yes | Systematic debugging approaches |
| systematic-debugging | Yes | Structured problem-solving |
| git-commit-message | Yes | Auto-generate conventional commits |
| playwright | No | Browser automation (disabled) |

---

## MCP Tools (oh-my-opencode.json)

| Tool | Enabled | Description |
|------|---------|-------------|
| websearch | Yes | Web search for current information |
| context7 | Yes | Documentation lookup |
| grep_app | Yes | GitHub code search |

---

## GitHub Workflow Agents

See [docs/OPENCODE_AGENTS.md](docs/OPENCODE_AGENTS.md) for full documentation of automated GitHub Actions agents:

| Agent | Schedule | Purpose |
|-------|----------|---------|
| oc-issue-solver | Every 30 min | Solve GitHub issues end-to-end |
| oc-problem-finder | Daily midnight | Find code problems/technical debt |
| oc-maintainer | Daily 3AM | Repository maintenance |
| oc-researcher | Daily 1AM | Research features |
| oc-pr-handler | 3x daily | Handle PR reviews |
| oc-cf-supabase | Manual only | DevOps (Cloudflare/Supabase) |

---

## Parallel Specialist Roles (parallel.yml)

The parallel workflow runs these 13 specialist roles:

1. **system-architect** - System design and architecture
2. **RnD** - Research and development
3. **Product-Architect** - Product strategy and features
4. **ai-agent-engineer** - AI/ML engineering and agent improvements
5. **backend-engineer** - Backend development
6. **frontend-engineer** - Frontend development
7. **ui-ux-engineer** - UI/UX design
8. **platform-engineer** - Platform infrastructure
9. **security-engineer** - Security engineering
10. **quality-assurance** - QA and testing
11. **DX-engineer** - Developer experience
12. **technical-writer** - Documentation
13. **user-story-engineer** - User story implementation

---

## Usage Guidelines

### Selecting the Right Agent

| Task Type | Recommended Agent |
|-----------|-------------------|
| Feature implementation | build, php-specialist |
| Bug fixing | build, debugging-strategies skill |
| Database changes | database-specialist |
| Test writing | testing-specialist |
| Security review | security-auditor |
| Performance optimization | performance-optimizer |
| Code review/analysis | plan, oracle |
| Frontend work | frontend-ui-ux-engineer |
| Research | librarian, explore |
| Orchestration | sisyphus |

### Delegation Pattern

When working as an orchestrator (sisyphus), delegate to specialists:

```
delegate_task(
  category="visual-engineering",
  load_skills=["frontend-ui-ux"],
  description="Implement responsive navbar",
  run_in_background=true
)
```

### Read-Only Agents

The following agents are **read-only** and cannot modify files:
- `plan` - Use for analysis only
- `security-auditor` - Use for security recommendations
- `oracle` - Use for consultation

---

## Related Documentation

- [Contributing Guide](CONTRIBUTING.md) - Contribution guidelines
- [Developer Guide](docs/DEVELOPER_GUIDE.md) - Development setup
- [OpenCode Agents](docs/OPENCODE_AGENTS.md) - GitHub workflow agents
- [Architecture](docs/ARCHITECTURE.md) - System architecture
