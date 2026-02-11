# GitHub Projects Setup - Execution Guide (v11)

> **Date**: January 30, 2026
> **Purpose**: Manual setup instructions for GitHub Projects to organize 94 open issues and 93 open PRs
> **Estimated Time**: 2-3 hours

---

## Executive Summary

This document provides **step-by-step manual instructions** for creating 7 GitHub Projects via the GitHub web interface. GitHub CLI does not support project creation, so manual setup is required.

**Current Status**:
- Open Issues: 94
- Open PRs: 93
- GitHub Projects: 0
- Recommendation: Create 7 projects for organization

---

## Prerequisites

1. **Repository Access**: You must have admin or maintainer access to the repository
2. **GitHub Account**: Must be logged into GitHub
3. **Web Browser**: Use Chrome, Firefox, Safari, or Edge

---

## Project 1: Critical Security Fixes

### Purpose

Organize and track critical security issues that require immediate attention.

### Setup Instructions

1. **Create New Project**:
   - Go to https://github.com/sulhicmz/malnu-backend/projects
   - Click "New Project"
   - Select "Table" view
   - Name: "Critical Security Fixes"
   - Description: "Urgent security issues requiring immediate attention"
   - Template: "None (blank)"

2. **Add Columns**:
   - Click "Add column"
   - Column 1: `To Do` (default)
   - Column 2: `In Progress` (add)
   - Column 3: `In Review` (add)
   - Column 4: `Done` (add)

3. **Assign Issues**:
   - **#629** - security(critical): Remove admin merge bypass from on-pull.yml workflow
     - Label: critical, security, high-priority, infrastructure
     - Assign to: `To Do` column
   - **#611** - SECURITY: Apply GitHub workflow permission hardening
     - Label: security, high-priority, maintenance
     - Assign to: `To Do` column
   - **#573** - [SECURITY] Replace direct exec() usage with Symfony Process
     - Label: code-quality, security, medium-priority
     - Assign to: `To Do` column

4. **Set Priority Order**:
   - Move #629 to top of `To Do` column (CRITICAL)
   - Move #611 below #629 (HIGH)
   - Move #573 below #611 (MEDIUM)

---

## Project 2: Performance Optimization

### Purpose

Track performance improvements, query optimizations, and database index additions.

### Setup Instructions

1. **Create New Project**:
   - Name: "Performance Optimization"
   - Description: "Performance improvements, query optimizations, and caching"

2. **Add Columns**:
   - Column 1: `To Do`
   - Column 2: `In Progress`
   - Column 3: `In Review`
   - Column 4: `Done`

3. **Assign Issues**:
   - **#630** - perf(attendance): Fix N+1 query in detectChronicAbsenteeism()
     - Label: bug, testing, medium-priority, performance
     - Assign to: `To Do` column
   - **#635** - perf(attendance): Optimize multiple count queries
     - Label: bug, database, medium-priority, performance
     - Assign to: `To Do` column
   - **#570** - [PERFORMANCE] Fix N+1 query in AuthService login()
     - Label: code-quality, high-priority, performance
     - Assign to: `Done` column (already resolved)
   - **#52** - PERFORMANCE: Implement Redis caching and query optimization
     - Label: high-priority, performance
     - Assign to: `To Do` column
   - **#25** - PERFORMANCE: Database query optimization plan
     - Label: (no labels)
     - Assign to: `To Do` column

4. **Set Priority Order**:
   - Move #630 to top (HIGH priority - N+1 query)
   - Move #635 below #630 (MEDIUM priority - count queries)
   - Move #52 below #635 (HIGH priority - Redis caching)
   - Move #25 below #52 (LOW priority - optimization plan)

---

## Project 3: Code Quality & Refactoring

### Purpose

Track code quality improvements, refactoring tasks, and technical debt reduction.

### Setup Instructions

1. **Create New Project**:
   - Name: "Code Quality & Refactoring"
   - Description: "Code quality improvements, refactoring, and technical debt reduction"

2. **Add Columns**:
   - Column 1: `To Do`
   - Column 2: `In Progress`
   - Column 3: `In Review`
   - Column 4: `Done`

3. **Assign Issues**:
   - **#633** - code-quality(auth): Remove duplicate password_verify check
     - Label: bug, duplicate, code-quality, low-priority
     - Assign to: `To Do` column
   - **#634** - code-quality(middleware): Standardize error response format
     - Label: bug, code-quality, low-priority, middleware
     - Assign to: `To Do` column
   - **#571** - [CODE QUALITY] Replace generic Exception usage
     - Label: code-quality, medium-priority, architecture
     - Assign to: `To Do` column
   - **#569** - [CODE QUALITY] Remove duplicate password validation
     - Label: code-quality, medium-priority, cleanup
     - Assign to: `To Do` column
   - **#349** - HIGH: Implement Form Request validation classes
     - Label: code-quality, high-priority, cleanup, validation
     - Assign to: `To Do` column
   - **#103** - CODE QUALITY: Standardize UUID implementation
     - Label: code-quality, medium-priority
     - Assign to: `To Do` column
   - **#353** - MEDIUM: Implement soft deletes for critical models
     - Label: database, medium-priority, quality
     - Assign to: `To Do` column

4. **Set Priority Order**:
   - Move #633 to top (QUICK WIN - 15 min)
   - Move #634 below #633 (1 hour)
   - Move #571 below #634 (MEDIUM priority)
   - Move #569 below #571 (MEDIUM priority)
   - Move #349 below #569 (HIGH priority)
   - Move #103 below #349 (MEDIUM priority)
   - Move #353 below #103 (MEDIUM priority)

---

## Project 4: Feature Development

### Purpose

Track new feature development across 11 business domains.

### Setup Instructions

1. **Create New Project**:
   - Name: "Feature Development"
   - Description: "New features and enhancements across all business domains"

2. **Add Columns**:
   - Column 1: `Backlog`
   - Column 2: `Planned`
   - Column 3: `In Development`
   - Column 4: `Testing`
   - Column 5: `Ready for Review`
   - Column 6: `Done`

3. **Assign High-Priority Features**:
   - **#223** - HIGH: Implement comprehensive API controllers for all 11 business domains
     - Label: enhancement, high-priority, api
     - Assign to: `In Development` column
   - **#258** - HIGH: Implement comprehensive school calendar and event management
     - Label: enhancement, database, high-priority, performance, api
     - Assign to: `Planned` column
   - **#259** - HIGH: Implement comprehensive report card and transcript generation
     - Label: enhancement, security, database, high-priority, performance, api
     - Assign to: `Planned` column
   - **#260** - HIGH: Implement comprehensive transportation management
     - Label: enhancement, security, database, high-priority, api
     - Assign to: `Planned` column
   - **#231** - HIGH: Implement comprehensive assessment and examination
     - Label: enhancement, database, high-priority, performance, api
     - Assign to: `Planned` column
   - **#200** - FEATURE: Implement comprehensive fee management and billing
     - Label: enhancement, database, high-priority
     - Assign to: `Planned` column
   - **#201** - FEATURE: Implement comprehensive communication and messaging
     - Label: enhancement, database, high-priority
     - Assign to: `Planned` column
   - **#141** - FEATURE: Add comprehensive financial management
     - Label: enhancement, database, high-priority
     - Assign to: `In Development` column (PR #650 exists)
   - **#139** - FEATURE: Add comprehensive parent portal
     - Label: enhancement, database, high-priority
     - Assign to: `In Development` column (PR #623 exists)
   - **#142** - FEATURE: Implement comprehensive LMS integration
     - Label: enhancement, database, high-priority
     - Assign to: `In Development` column (PR #627 exists)

4. **Assign Medium-Priority Features**:
   - **#232** - MEDIUM: Implement comprehensive parent engagement
     - Label: enhancement, database, medium-priority, api
     - Assign to: `Backlog` column
   - **#263** - MEDIUM: Implement comprehensive hostel and dormitory
     - Label: enhancement, security, database, medium-priority, api
     - Assign to: `Backlog` column
   - **#264** - MEDIUM: Implement comprehensive library management
     - Label: enhancement, database, medium-priority, api
     - Assign to: `Backlog` column
   - **#262** - MEDIUM: Implement comprehensive alumni network
     - Label: enhancement, database, medium-priority, api
     - Assign to: `Backlog` column
   - **#233** - MEDIUM: Implement comprehensive school administration
     - Label: enhancement, database, medium-priority, architecture, api
     - Assign to: `Backlog` column
   - **#202** - FEATURE: Implement comprehensive behavior and discipline
     - Label: enhancement, database, medium-priority
     - Assign to: `Backlog` column

5. **Assign Low-Priority Features**:
   - **#112** - FEATURE: Add school club and extracurricular
     - Label: enhancement, low-priority
     - Assign to: `Backlog` column
   - **#111** - FEATURE: Add school cafeteria and meal management
     - Label: enhancement, low-priority
     - Assign to: `Backlog` column

---

## Project 5: Testing & Quality Assurance

### Purpose

Track test coverage improvements and quality assurance tasks.

### Setup Instructions

1. **Create New Project**:
   - Name: "Testing & Quality Assurance"
   - Description: "Test coverage improvements and quality assurance tasks"

2. **Add Columns**:
   - Column 1: `To Test`
   - Column 2: `Testing`
   - Column 3: `In Review`
   - Column 4: `Passed`
   - Column 5: `Failed`

3. **Assign Issues**:
   - **#134** - CRITICAL: Fix CI/CD pipeline and add automated testing
     - Label: critical, high-priority, testing
     - Assign to: `To Test` column
   - **#104** - TESTING: Implement comprehensive test suite
     - Label: high-priority, testing
     - Assign to: `Testing` column
   - **#50** - TESTING: Replace placeholder tests with comprehensive test suite
     - Label: high-priority, testing
     - Assign to: `To Test` column
   - **#22** - TEST: Implement comprehensive testing strategy
     - Label: enhancement
     - Assign to: `To Test` column
   - **#8** - TEST: Set up automated testing with PHPUnit
     - Label: (no labels)
     - Assign to: `Passed` column (already implemented)

4. **Set Priority Order**:
   - Move #134 to top (CRITICAL - CI/CD pipeline)
   - Move #104 below #134 (HIGH priority - comprehensive tests)
   - Move #50 below #104 (HIGH priority - replace placeholders)
   - Move #22 below #50 (MEDIUM priority - testing strategy)

---

## Project 6: Infrastructure & CI/CD

### Purpose

Track infrastructure improvements, CI/CD workflow consolidation, and deployment tasks.

### Setup Instructions

1. **Create New Project**:
   - Name: "Infrastructure & CI/CD"
   - Description: "Infrastructure improvements, CI/CD workflows, and deployment"

2. **Add Columns**:
   - Column 1: `To Do`
   - Column 2: `In Progress`
   - Column 3: `In Review`
   - Column 4: `Done`

3. **Assign Issues**:
   - **#632** - refactor(ci): Consolidate redundant GitHub workflows (11 → 3-4)
     - Label: medium-priority, maintenance, infrastructure, refactor
     - Assign to: `To Do` column
   - **#134** - CRITICAL: Fix CI/CD pipeline and add automated testing
     - Label: critical, high-priority, testing
     - Assign to: `To Do` column
   - **#225** - MEDIUM: Consolidate and optimize GitHub Actions workflows
     - Label: medium-priority, maintenance
     - Assign to: `To Do` column
   - **#28** - DEVOPS: Create Docker development environment
     - Label: (no labels)
     - Assign to: `Done` column (already implemented)
   - **#11** - CI/CD: Set up GitHub Actions with automated testing
     - Label: (no labels)
     - Assign to: `In Progress` column
   - **#23** - INFRA: Set up database backup and recovery strategy
     - Label: (no labels)
     - Assign to: `To Do` column
   - **#227** - MEDIUM: Implement application monitoring and observability
     - Label: enhancement, medium-priority
     - Assign to: `To Do` column
   - **#254** - HIGH: Implement comprehensive error handling and logging
     - Label: enhancement, high-priority
     - Assign to: `To Do` column

4. **Set Priority Order**:
   - Move #134 to top (CRITICAL - CI/CD pipeline)
   - Move #632 below #134 (HIGH priority - workflow consolidation)
   - Move #225 below #632 (MEDIUM priority - workflow optimization)
   - Move #23 below #225 (MEDIUM priority - backup strategy)
   - Move #227 below #23 (MEDIUM priority - monitoring)
   - Move #254 below #227 (HIGH priority - error handling)

---

## Project 7: Documentation

### Purpose

Track documentation improvements and updates.

### Setup Instructions

1. **Create New Project**:
   - Name: "Documentation"
   - Description: "Documentation improvements and updates"

2. **Add Columns**:
   - Column 1: `To Write`
   - Column 2: `In Review`
   - Column 3: `Approved`
   - Column 4: `Published`

3. **Assign Issues**:
   - **#175** - MEDIUM: Update all documentation to reflect current architecture
     - Label: documentation, medium-priority
     - Assign to: `To Write` column
   - **#354** - API Documentation (not explicitly listed as issue)
     - Label: (check existing issues)
     - Assign to: `In Review` column (PR #497 exists)

4. **Set Priority Order**:
   - Move #175 to top (MEDIUM priority - update all docs)

---

## Automation Setup (Optional)

### Configure Automation Rules

GitHub Projects supports automation rules to automatically move items based on status.

For each project, you can add automation rules:

1. Click the three dots (...) in the top-right of the project
2. Select "Automation"
3. Add rules such as:
   - "When an issue is closed, move it to 'Done'"
   - "When a PR is merged, move it to 'Done'"
   - "When an issue is assigned, move it to 'In Progress'"

---

## Assigning PRs to Projects

PRs can also be added to projects:

1. Open any PR
2. Click the "Projects" dropdown in the sidebar
3. Select the appropriate project and column

**Recommended PR Assignments**:
- **Project 1 (Critical Security)**: PRs fixing #629, #611, #573
- **Project 2 (Performance)**: PRs fixing #630, #635, #570, #52
- **Project 3 (Code Quality)**: PRs fixing #633, #634, #571, #569, #349
- **Project 4 (Feature Development)**: PRs implementing features (#650, #623, #627, etc.)
- **Project 5 (Testing)**: PRs related to testing (#104, #50, #22)
- **Project 6 (Infrastructure)**: PRs related to CI/CD (#632, #134, #225)
- **Project 7 (Documentation)**: PRs updating documentation (#175, #497, etc.)

---

## Summary Checklist

After completing all projects, verify:

- [ ] 7 projects created
- [ ] All 94 open issues assigned to appropriate projects
- [ ] All 93 open PRs assigned to appropriate projects
- [ ] All issues and PRs have proper labels
- [ ] Priority ordering set for each project
- [ ] Automation rules configured (optional)

---

## Expected Outcome

After completing this setup:

- **94 open issues** will be organized across 7 projects
- **93 open PRs** will be organized across 7 projects
- **Clear visibility** into what needs to be done
- **Better prioritization** with column-based workflow
- **Reduced confusion** about task ownership

---

## Time Estimate

- **Project 1 (Critical Security)**: 30 minutes
- **Project 2 (Performance)**: 30 minutes
- **Project 3 (Code Quality)**: 45 minutes
- **Project 4 (Feature Development)**: 45 minutes
- **Project 5 (Testing)**: 30 minutes
- **Project 6 (Infrastructure)**: 30 minutes
- **Project 7 (Documentation)**: 15 minutes

**Total Estimated Time**: 3 hours and 15 minutes

---

## Next Steps

After creating all projects:

1. **Announce to team** via issue comment or discussion
2. **Update CONTRIBUTING.md** to reference projects
3. **Update ROADMAP.md** to reflect project-based organization
4. **Schedule weekly reviews** for each project

---

**Document Created**: January 30, 2026
**Orchestrator Version**: v11
**Status**: Ready for manual execution
**Total Time Estimate**: 3-4 hours

---

## References

- [ORCHESTRATOR_ANALYSIS_REPORT_v11.md](ORCHESTRATOR_ANALYSIS_REPORT_v11.md) - Latest analysis
- [GITHUB_PROJECTS_SETUP_v4.md](GITHUB_PROJECTS_SETUP_v4.md) - Previous setup guide
- [PR_CONSOLIDATION_ACTION_PLAN_v2.md](PR_CONSOLIDATION_ACTION_PLAN_v2.md) - PR consolidation plan
- [CONTRIBUTING.md](CONTRIBUTING.md) - Contribution guidelines
