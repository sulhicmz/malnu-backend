# GitHub Projects Setup - Phase 2

> **Date**: January 22, 2026  
> **Orchestrator Version**: v9  
> **Purpose**: Create and organize GitHub Projects for better issue management

---

## Executive Summary

This document outlines the GitHub Projects structure for the malnu-backend repository, organized by business domains and development phases.

## Project Structure

### Main Project: Malnu Backend Development

**Status**: To be created  
**Repository**: sulhicmz/malnu-backend  
**Visibility**: Public

---

## Project Boards (5)

### 1. Critical Issues & Security

**Purpose**: Track critical and security issues requiring immediate attention

**Columns**:
1. **🔴 Backlog** - New critical/security issues
2. **🔴 In Progress** - Currently being worked on
3. **🔴 In Review** - Pending review
4. **✅ Done** - Resolved issues

**Auto-add Labels**:
- `critical`
- `security`
- `high-priority`

**Key Issues to Add**:
- #629: Remove admin merge bypass from on-pull.yml
- #630: Fix getAllUsers() loading all users for login

---

### 2. Performance Optimization

**Purpose**: Track performance improvements and optimizations

**Columns**:
1. **📊 Backlog** - New performance issues
2. **📊 Analyzing** - Performance analysis in progress
3. **📊 Fixing** - Implementation in progress
4. **📊 Testing** - Performance testing
5. **✅ Done** - Optimizations completed

**Auto-add Labels**:
- `performance`
- `database`
- `cache`

**Key Issues to Add**:
- #631: Fix N+1 query in detectChronicAbsenteeism()
- #635: Optimize multiple count queries in calculateAttendanceStatistics()

---

### 3. Code Quality & Refactoring

**Purpose**: Track code quality improvements and refactoring efforts

**Columns**:
1. **🔧 Backlog** - New code quality issues
2. **🔧 Planning** - Refactoring plan being created
3. **🔧 In Progress** - Refactoring in progress
4. **🔧 Code Review** - Pending review
5. **✅ Done** - Refactoring completed

**Auto-add Labels**:
- `code-quality`
- `refactor`
- `quality`
- `bug`

**Key Issues to Add**:
- #633: Remove duplicate password_verify check
- #634: Standardize error response format across middleware
- #635: Implement proper global exception handler

---

### 4. CI/CD & Infrastructure

**Purpose**: Track CI/CD pipeline, infrastructure, and deployment improvements

**Columns**:
1. **🚀 Backlog** - New infrastructure issues
2. **🚀 Planning** - Infrastructure planning
3. **🚀 Implementing** - Implementation in progress
4. **🚀 Testing** - Testing in progress
5. **✅ Done** - Infrastructure improvements completed

**Auto-add Labels**:
- `infrastructure`
- `docker`
- `ci`
- `maintenance`

**Key Issues to Add**:
- #632: Consolidate redundant GitHub workflows (11 → 3-4)

---

### 5. Feature Development

**Purpose**: Track new feature development by business domain

**Columns**:
1. **📝 Backlog** - New feature requests
2. **📝 Planning** - Feature planning and design
3. **📝 In Progress** - Feature development
4. **📝 Testing** - Feature testing
5. **📝 Code Review** - Pending review
6. **✅ Done** - Features completed

**Auto-add Labels**:
- `feature`
- `enhancement`
- `api`

**Key Issues to Add**:
- #623: Parent Portal API
- #628: Remove redundant OpenCode workflows
- #627: Learning Management System
- #616: Calendar event management
- #608: Financial management system

---

## Issue Categorization Rules

### Critical Issues (Priority 1)

**Criteria**:
- Security vulnerabilities
- Data loss risks
- Production outages
- Performance bottlenecks affecting users

**Auto-add to**: Critical Issues & Security project

**Labels**: `critical`, `security`

### High Priority Issues (Priority 2)

**Criteria**:
- Performance issues impacting UX
- Bug fixes affecting core functionality
- High-value feature requests

**Auto-add to**: Relevant project based on category

**Labels**: `high-priority`

### Medium Priority Issues (Priority 3)

**Criteria**:
- Code quality improvements
- Refactoring
- Medium-value features
- Documentation updates

**Auto-add to**: Relevant project based on category

**Labels**: `medium-priority`

### Low Priority Issues (Priority 4)

**Criteria**:
- Nice-to-have features
- Minor code improvements
- Documentation tweaks

**Auto-add to**: Relevant project based on category

**Labels**: `low-priority`

---

## Implementation Steps

### Step 1: Create Main Project

```bash
# Create project
gh project create "Malnu Backend Development" \
  --owner sulhicmz \
  --repo sulhicmz/malnu-backend \
  --format json
```

### Step 2: Create Project Boards

```bash
# Board 1: Critical Issues & Security
gh project create "Critical Issues & Security" \
  --owner sulhicmz \
  --format json

# Board 2: Performance Optimization
gh project create "Performance Optimization" \
  --owner sulhicmz \
  --format json

# Board 3: Code Quality & Refactoring
gh project create "Code Quality & Refactoring" \
  --owner sulhicmz \
  --format json

# Board 4: CI/CD & Infrastructure
gh project create "CI/CD & Infrastructure" \
  --owner sulhicmz \
  --format json

# Board 5: Feature Development
gh project create "Feature Development" \
  --owner sulhicmz \
  --format json
```

### Step 3: Create Columns for Each Board

```bash
# For each board, create columns
# Example for Critical Issues & Security
gh project item-create <project-id> --title "🔴 Backlog"
gh project item-create <project-id> --title "🔴 In Progress"
gh project item-create <project-id> --title "🔴 In Review"
gh project item-create <project-id> --title "✅ Done"
```

### Step 4: Add Issues to Projects

```bash
# Add critical issues
gh project item-add <project-id> --issue 629
gh project item-add <project-id> --issue 630

# Add performance issues
gh project item-add <project-id> --issue 631
gh project item-add <project-id> --issue 635

# Add code quality issues
gh project item-add <project-id> --issue 633
gh project item-add <project-id> --issue 634

# Add infrastructure issues
gh project item-add <project-id> --issue 632

# Add feature requests
gh project item-add <project-id> --issue 623
gh project item-add <project-id> --issue 628
gh project item-add <project-id> --issue 627
gh project item-add <project-id> --issue 616
gh project item-add <project-id> --issue 608
```

### Step 5: Set Up Automation

Use GitHub Actions to auto-add labeled issues to projects:

```yaml
# .github/workflows/project-automation.yml
name: Project Automation

on:
  issues:
    types: [labeled, opened]

jobs:
  add-to-project:
    runs-on: ubuntu-latest
    steps:
      - name: Add critical/security issues
        if: contains(github.event.issue.labels.*.name, 'critical') || contains(github.event.issue.labels.*.name, 'security')
        run: |
          gh project item-add <critical-project-id> --issue ${{ github.event.issue.number }}
```

---

## Project Views

### Sprint View

**Purpose**: View issues for current sprint

**Filter**: `milestone:<current-sprint>`

**Sort**: Priority (critical > high > medium > low)

### Backlog View

**Purpose**: View all unassigned issues

**Filter**: `no:assignee`

**Sort**: Created date (newest first)

### My Issues View

**Purpose**: View issues assigned to me

**Filter**: `assignee:@me`

**Sort**: Priority

---

## Metrics & KPIs

### Project Health Metrics

| Metric | Target | Current |
|--------|--------|---------|
| Critical Issues | 0 | 1 |
| High Priority Issues | <10 | TBD |
| Medium Priority Issues | <20 | TBD |
| Cycle Time | <7 days | TBD |
| Lead Time | <14 days | TBD |

### Velocity Tracking

- **Issues Completed per Sprint**: Track per 2-week sprint
- **Story Points per Sprint**: Track per 2-week sprint
- **Code Review Turnaround**: Average time from PR to merge
- **Deployment Frequency**: Number of deployments per week

---

## Maintenance

### Daily

- Review new issues and assign to appropriate project
- Update project statuses based on issue progress

### Weekly

- Review project backlog and prioritize
- Update sprint goals and milestones
- Review and close stale issues

### Monthly

- Review project structure and adjust as needed
- Update automation rules
- Review metrics and KPIs
- Archive completed projects

---

## References

- [GITHUB_PROJECTS_SETUP_GUIDE.md](GITHUB_PROJECTS_SETUP_GUIDE.md) - Original setup guide
- [GITHUB_PROJECTS_STRUCTURE.md](GITHUB_PROJECTS_STRUCTURE.md) - Structure documentation
- [ORCHESTRATOR_ANALYSIS_REPORT_v9.md](ORCHESTRATOR_ANALYSIS_REPORT_v9.md) - Latest orchestrator analysis
- [ROADMAP.md](ROADMAP.md) - Development roadmap

---

**Document Created**: January 22, 2026  
**Orchestrator Version**: v9  
**Total Projects**: 5  
**Total Boards**: 5  
