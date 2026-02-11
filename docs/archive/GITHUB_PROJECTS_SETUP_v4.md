# GitHub Projects Setup for Malnu Backend

> **Status**: Projects cannot be created via CLI due to API limitations. Manual setup required.
> **Date**: January 23, 2026
> **Orchestrator Version**: v10

---

## Summary

Due to GitHub CLI limitations, Projects must be created manually via the GitHub web interface. This document provides the complete structure and configuration for all 7 recommended projects.

---

## Project Structure

### 1. Critical Security Fixes

**Purpose**: Track urgent security issues requiring immediate attention

**Columns**:
- 🚨 Triage (To Review)
- 🔴 Critical (P0 - Fix Now)
- 🟠 High (P1 - Fix This Week)
- 🟡 In Progress
- ✅ Done

**Target Issues**:
- #629 - security(critical): Remove admin merge bypass from on-pull.yml
- #611 - SECURITY: Apply GitHub workflow permission hardening
- #573 - [SECURITY] Replace direct exec() usage with Symfony Process component

**Automation Rules**:
- Auto-add issues labeled `critical` to "Critical" column
- Auto-add issues labeled `security` to "Triage" column
- Auto-move items from "In Progress" to "Done" when linked PR merges

---

### 2. Performance Optimization

**Purpose**: Track performance-related issues and database query optimizations

**Columns**:
- 📊 Triage (To Review)
- 🚀 High Impact (P0)
- ⚡ Medium Impact (P1)
- 🔍 In Progress
- ✅ Done

**Target Issues**:
- #630 - perf(attendance): Fix N+1 query in detectChronicAbsenteeism()
- #635 - perf(attendance): Optimize multiple count queries in calculateAttendanceStatistics()
- #570 - [PERFORMANCE] Fix N+1 query in AuthService login()
- #224 - HIGH: Implement Redis caching strategy for performance optimization

**Automation Rules**:
- Auto-add issues labeled `performance` to "Triage" column
- Auto-add PRs with `perf:` prefix to "In Progress" column

---

### 3. Code Quality & Refactoring

**Purpose**: Track code quality improvements, refactoring, and cleanup tasks

**Columns**:
- 🔍 Triage (To Review)
- 📝 Low Priority (P3)
- 🔧 Medium Priority (P2)
- ⚙️ In Progress
- ✅ Done

**Target Issues**:
- #633 - code-quality(auth): Remove duplicate password_verify check
- #634 - code-quality(middleware): Standardize error response format
- #571 - [CODE QUALITY] Replace generic Exception usage with custom exception classes
- #569 - [CODE QUALITY] Remove duplicate password validation
- #349 - HIGH: Implement Form Request validation classes

**Automation Rules**:
- Auto-add issues labeled `code-quality` to "Triage" column
- Auto-add PRs with `refactor:` or `fix:` prefix to "In Progress" column

---

### 4. Feature Development

**Purpose**: Track new features and enhancements for school management system

**Columns**:
- 💡 Backlog
- 📋 Planned (Next Sprint)
- 🚧 In Development
- 🧪 In Review
- ✅ Released

**Target Issues**:
- #223 - HIGH: Implement comprehensive API controllers for all 11 business domains
- #258 - HIGH: Implement comprehensive school calendar and event management system
- #259 - HIGH: Implement comprehensive report card and transcript generation system
- #260 - HIGH: Implement comprehensive transportation management system
- #142 - FEATURE: Implement comprehensive learning management system (LMS)
- #139 - FEATURE: Add comprehensive parent portal

**Automation Rules**:
- Auto-add issues labeled `enhancement` or `feature` to "Backlog" column
- Auto-add PRs with `feat:` prefix to "In Development" column

---

### 5. Testing & Quality Assurance

**Purpose**: Track test coverage improvements and quality assurance tasks

**Columns**:
- 📊 Triage
- 🧪 Test Implementation
- 🔍 Code Review
- 🚀 Deployment Testing
- ✅ Passed

**Target Issues**:
- #134 - CRITICAL: Fix CI/CD pipeline and add automated testing
- #104 - Implement comprehensive test suite for all models and relationships

**Automation Rules**:
- Auto-add issues labeled `testing` to "Triage" column
- Auto-add PRs with `test:` prefix to "Test Implementation" column

---

### 6. Infrastructure & CI/CD

**Purpose**: Track infrastructure, workflows, and deployment improvements

**Columns**:
- 🔍 Triage
- 🏗️ Infrastructure Work
- ⚙️ CI/CD Updates
- 🚀 Deployment
- ✅ Completed

**Target Issues**:
- #632 - refactor(ci): Consolidate redundant GitHub workflows (11 → 3-4 workflows)
- #225 - MEDIUM: Consolidate and optimize GitHub Actions workflows
- #567 - [MAINTENANCE] Create GitHub Projects for better issue organization

**Automation Rules**:
- Auto-add issues labeled `infrastructure` or `ci` to "Triage" column
- Auto-add PRs touching `.github/workflows/` to "CI/CD Updates" column

---

### 7. Documentation

**Purpose**: Track documentation improvements and updates

**Columns**:
- 📚 Triage
- ✍️ Writing
- 🔍 Review
- ✅ Published

**Target Issues**:
- #175 - MEDIUM: Update all documentation to reflect current architecture
- #528 - [DOCUMENTATION] Update outdated documentation to reflect January 2026 status

**Automation Rules**:
- Auto-add issues labeled `documentation` or `docs` to "Triage" column
- Auto-add PRs with `docs:` prefix to "Writing" column

---

## Manual Setup Instructions

### Step 1: Create Projects

For each project above:

1. Go to https://github.com/sulhicmz/malnu-backend/projects
2. Click "New Project"
3. Select "Board" template
4. Enter Title and Description from project definition above
5. Click "Create"

### Step 2: Add Columns

For each project, add columns in the exact order specified above.

### Step 3: Add Automation Rules (Optional)

GitHub Projects support automation rules (beta feature). To configure:

1. Open the project
2. Click the "..." menu → "Automation"
3. Add rules as specified in each project definition

### Step 4: Move Existing Items

Use GitHub CLI to batch-move issues to appropriate projects:

```bash
# Move critical security issues
gh issue edit 629 --add-project "Critical Security Fixes"
gh issue edit 611 --add-project "Critical Security Fixes"
gh issue edit 573 --add-project "Critical Security Fixes"

# Move performance issues
gh issue edit 630 --add-project "Performance Optimization"
gh issue edit 635 --add-project "Performance Optimization"
gh issue edit 570 --add-project "Performance Optimization"

# Move code quality issues
gh issue edit 633 --add-project "Code Quality & Refactoring"
gh issue edit 634 --add-project "Code Quality & Refactoring"
gh issue edit 571 --add-project "Code Quality & Refactoring"

# Move feature issues
gh issue edit 223 --add-project "Feature Development"
gh issue edit 258 --add-project "Feature Development"
gh issue edit 259 --add-project "Feature Development"

# Move testing issues
gh issue edit 134 --add-project "Testing & Quality Assurance"

# Move infrastructure issues
gh issue edit 632 --add-project "Infrastructure & CI/CD"
gh issue edit 225 --add-project "Infrastructure & CI/CD"

# Move documentation issues
gh issue edit 175 --add-project "Documentation"
```

---

## Issue-to-Project Mapping

| Label | Target Project | Example Issues |
|--------|----------------|----------------|
| `critical` | Critical Security Fixes | #629 |
| `security` | Critical Security Fixes | #611, #573 |
| `performance` | Performance Optimization | #630, #635, #570 |
| `code-quality` | Code Quality & Refactoring | #633, #634, #571 |
| `enhancement`, `feature` | Feature Development | #223, #258, #259, #260 |
| `testing` | Testing & Quality Assurance | #134, #104 |
| `infrastructure`, `ci` | Infrastructure & CI/CD | #632, #225 |
| `documentation`, `docs` | Documentation | #175, #528 |

---

## Next Steps

1. [ ] Create all 7 projects manually via GitHub web interface
2. [ ] Configure columns for each project
3. [ ] Set up automation rules (if available)
4. [ ] Batch-move existing issues to appropriate projects
5. [ ] Configure workflow triggers to auto-add new issues/PRs to projects

---

## References

- [GitHub Projects Documentation](https://docs.github.com/en/issues/planning-and-tracking-with-projects/creating-projects)
- [Issue #567](https://github.com/sulhicmz/malnu-backend/issues/567) - Original request for GitHub Projects
- [ORCHESTRATOR_ANALYSIS_REPORT_v10.md](ORCHESTRATOR_ANALYSIS_REPORT_v10.md) - Latest orchestrator analysis

---

**Document Created**: January 23, 2026
**Author**: Repository Orchestrator v10
**Status**: Ready for manual implementation
