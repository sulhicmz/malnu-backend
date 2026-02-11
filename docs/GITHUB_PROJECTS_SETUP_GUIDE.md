# GitHub Projects Setup Guide

**Created**: January 10, 2026
**Repository**: sulhicmz/malnu-backend

---

## Overview

This document outlines the GitHub Projects structure for the malnu-backend repository. Projects help organize, prioritize, and track progress across different areas of the codebase.

---

## Recommended Project Structure

### Project 1: Security & Critical Issues 🔒

**Purpose**: Track all security vulnerabilities and critical blockers that must be resolved immediately.

**Columns**:
- 🚨 **Critical** - Must fix immediately (24-48 hours)
- 🔴 **High Priority** - Fix within 1 week
- 🟡 **In Progress** - Currently being worked on
- ✅ **Resolved** - Fixed and merged
- ❄️ **On Hold** - Blocked by dependencies

**Labels to Track**:
- `critical`
- `security`
- `high-priority`

**Key Issues**:
- #281 - Fix broken authentication system
- #282 - Fix SecurityHeaders middleware
- #347 - Replace MD5 with SHA-256
- #348 - Fix password reset token exposure
- #359 - Implement missing CSRF protection
- #360 - Implement proper RBAC authorization

**Success Metrics**:
- Zero critical security issues
- All security vulnerabilities patched
- Auth and RBAC fully functional

---

### Project 2: Code Quality & Architecture 🏗️

**Purpose**: Improve code maintainability, reduce technical debt, and enforce best practices.

**Columns**:
- 📋 **Backlog** - Issues not yet prioritized
- 🎯 **Ready to Start** - Issues with clear requirements
- 🔨 **In Development** - Currently implementing
- 🧪 **In Review** - Ready for code review
- ✅ **Completed** - Merged to main

**Labels to Track**:
- `code-quality`
- `architecture`
- `refactoring`
- `cleanup`
- `medium-priority`

**Key Issues**:
- #349 - Implement Form Request validation classes
- #350 - Replace direct service instantiation with DI
- #351 - Fix hardcoded configuration values
- #353 - Create generic CRUD base class/trait
- #356 - Standardize error handling across controllers

**Success Metrics**:
- Code quality score > 8/10
- Zero duplicate code violations
- 100% dependency injection compliance
- Consistent error handling patterns

---

### Project 3: Performance & Optimization ⚡

**Purpose**: Optimize application performance, reduce response times, and improve scalability.

**Columns**:
- 📊 **Analysis** - Performance profiling phase
- 🎯 **Planning** - Optimization strategy defined
- 🔧 **Implementing** - Code changes in progress
- ✅ **Testing** - Performance validation
- ✅ **Deployed** - Improvements in production

**Labels to Track**:
- `performance`
- `database`
- `cache`
- `optimization`

**Key Issues**:
- #224 - Implement Redis caching strategy
- #357 - Add missing database indexes
- #358 - Implement request/response logging middleware
- #283 - Enable database services in Docker Compose

**Success Metrics**:
- API response time < 200ms
- Database query time < 50ms
- Cache hit rate > 80%
- Zero N+1 query issues

---

### Project 4: Testing & Quality Assurance 🧪

**Purpose**: Increase test coverage, ensure reliability, and prevent regressions.

**Columns**:
- 📝 **Test Planning** - Test cases being designed
- 🔨 **Writing Tests** - Test implementation phase
- ✅ **Passing** - All tests green
- ❌ **Failing** - Tests need fixing
- ✅ **Coverage Goal Met** - Target coverage achieved

**Labels to Track**:
- `testing`
- `unit-test`
- `integration-test`
- `e2e-test`

**Key Issues**:
- #134 - Fix CI/CD pipeline and add automated testing
- #173 - Add comprehensive test suite

**Success Metrics**:
- Overall test coverage > 80%
- Unit test coverage > 85%
- Integration test coverage > 75%
- CI/CD pipeline green on all PRs

---

### Project 5: Documentation & Knowledge Base 📚

**Purpose**: Maintain comprehensive, up-to-date documentation for developers and users.

**Columns**:
- 📝 **Drafting** - Content being written
- 👀 **Review** - Content under review
- ✅ **Published** - Documentation live
- 🔄 **Outdated** - Needs updating
- 🗑️ **Deprecated** - No longer relevant

**Labels to Track**:
- `documentation`
- `api`
- `guide`

**Key Issues**:
- #21/#354 - Add comprehensive API documentation
- #255 - Create developer onboarding guide
- #361 - Add SECURITY.md and CODEOWNERS governance files
- #175 - Update all documentation to reflect current architecture

**Success Metrics**:
- All API endpoints documented
- Developer onboarding time < 2 days
- Documentation accuracy > 95%
- Zero outdated sections

---

### Project 6: Feature Development 🚀

**Purpose**: Track new feature implementation across all business domains.

**Columns**:
- 💡 **Ideation** - Feature concept phase
- 📋 **Specification** - Requirements defined
- 🎨 **Design** - UI/UX and API design
- 🔨 **Implementation** - Code being written
- 🧪 **Testing** - Feature testing
- ✅ **Ready** - Ready for release

**Labels to Track**:
- `enhancement`
- `feature`
- `api`
- `medium-priority`
- `low-priority`

**Key Issues**:
- #223 - Implement comprehensive API controllers for all 11 business domains
- #229 - Implement comprehensive student information system (SIS)
- #231 - Implement comprehensive assessment and examination management system
- #230 - Implement comprehensive timetable and scheduling system
- #257 - Implement comprehensive notification and alert system
- #259 - Implement comprehensive report card and transcript generation system

**Success Metrics**:
- All 11 business domains have API controllers
- Feature delivery on schedule
- Zero critical bugs in new features
- User satisfaction > 90%

---

### Project 7: Infrastructure & DevOps 🔧

**Purpose**: Manage CI/CD, deployment, monitoring, and infrastructure improvements.

**Columns**:
- 📊 **Monitoring** - Infrastructure health checks
- 🔧 **Maintenance** - Regular maintenance tasks
- 🚀 **Deployment** - Deployment process
- ✅ **Operational** - Systems running smoothly
- 🚨 **Incidents** - Issues requiring immediate attention

**Labels to Track**:
- `infrastructure`
- `devops`
- `deployment`
- `ci-cd`
- `monitoring`

**Key Issues**:
- #134 - Fix CI/CD pipeline and add automated testing
- #225 - Consolidate GitHub Actions workflows
- #227 - Implement application monitoring and observability system
- #265 - Implement comprehensive data backup and disaster recovery system

**Success Metrics**:
- 99.9% uptime target met
- Deployment time < 10 minutes
- Zero failed deployments in production
- All monitoring alerts configured

---

## Project Metadata

### Priority Levels
- **Critical** (🚨): Must fix immediately, system broken
- **High** (🔴): Fix within 1 week, major impact
- **Medium** (🟡): Fix within 2-4 weeks
- **Low** (🔵): Fix when time permits, minor impact

### Size Estimates
- **XS**: < 1 day effort
- **S**: 1-3 days effort
- **M**: 1-2 weeks effort
- **L**: 2-4 weeks effort
- **XL**: > 1 month effort

### Custom Fields

#### Complexity Score (1-10)
- 1-3: Simple, straightforward implementation
- 4-6: Moderate complexity, some challenges
- 7-8: Complex, requires careful planning
- 9-10: Very complex, high risk

#### Risk Level
- **Low**: Minimal risk of breaking changes
- **Medium**: Some risk, testing required
- **High**: High risk, extensive testing and rollback plan needed

#### Dependencies
- **Blocked By**: Issues that must be completed first
- **Blocking**: Issues that depend on this one
- **Related**: Issues that are connected but not blocking

---

## Automation Rules

### Auto-Assign Issues to Projects

```yaml
# Security Issues
If issue has label 'critical' or 'security':
  Add to "Security & Critical Issues" project

# Code Quality
If issue has label 'code-quality' or 'architecture':
  Add to "Code Quality & Architecture" project

# Performance
If issue has label 'performance' or 'cache':
  Add to "Performance & Optimization" project

# Testing
If issue has label 'testing':
  Add to "Testing & Quality Assurance" project

# Documentation
If issue has label 'documentation':
  Add to "Documentation & Knowledge Base" project

# Features
If issue has label 'enhancement' or 'feature':
  Add to "Feature Development" project

# Infrastructure
If issue has label 'infrastructure' or 'devops':
  Add to "Infrastructure & DevOps" project
```

---

## View Templates

### Board View (Kanban)
- Best for: Daily standups, sprint planning, visual workflow tracking
- Shows: Issues organized by status columns

### Table View
- Best for: Issue tracking, prioritization, bulk editing
- Shows: Issues with metadata fields in spreadsheet format

### Timeline View
- Best for: Release planning, dependency management
- Shows: Issues with due dates and dependencies

### Roadmap View
- Best for: Long-term planning, stakeholder communication
- Shows: High-level milestones and features

---

## Sprint Planning Template

### Sprint Duration: 2 Weeks

**Focus**: Security & Critical Issues (Week 1-2)

| Issue | Priority | Assignee | Est. Days | Status |
|-------|----------|-----------|------------|--------|
| #281 | Critical | @developer1 | 2-3 | In Progress |
| #282 | Critical | @developer2 | 1-2 | Ready |
| #347 | Critical | @security | 0.5 | Ready |
| #348 | Critical | @developer1 | 1-2 | Ready |
| #359 | Critical | @developer2 | 2-3 | Blocked |
| #360 | Critical | @developer1 | 3-5 | Blocked |

**Sprint Goals**:
1. Fix all critical authentication issues
2. Implement missing CSRF protection
3. Complete RBAC authorization
4. Achieve zero critical security vulnerabilities

---

## Project Permissions

| Role | Read | Edit | Admin |
|------|------|------|-------|
| Maintainers | ✅ | ✅ | ✅ |
| Contributors | ✅ | ✅ | ❌ |
| Public | ✅ | ❌ | ❌ |

---

## Integration with Workflows

### Automated Workflows

1. **New Issue Created**:
   - Auto-assign to appropriate project based on labels
   - Set default priority based on criticality
   - Assign to maintainer for review

2. **PR Merged**:
   - Move related issues to appropriate column
   - Notify project maintainers
   - Update progress metrics

3. **Issue Closed**:
   - Move to "Completed" or "Resolved" column
   - Calculate time-to-resolution
   - Update success metrics

---

## Reporting & Metrics

### Weekly Reports

**Metrics to Track**:
- Issues created vs. closed
- Average time to resolution
- Backlog growth rate
- PR merge rate
- Test coverage percentage

### Dashboards

**Key Dashboards**:
1. **Security Status**: Open critical vulnerabilities by severity
2. **Development Velocity**: Issues completed per week
3. **Quality Metrics**: Code quality scores, test coverage
4. **Project Health**: On-time delivery, blockers

---

## Next Steps

1. [ ] Create the 7 GitHub Projects using the structure above
2. [ ] Configure automation rules for issue assignment
3. [ ] Set up custom fields for each project
4. [ ] Migrate existing issues to appropriate projects
5. [ ] Create views for different team roles
6. [ ] Configure notifications and workflows
7. [ ] Train team members on project usage

---

## References

- [GitHub Projects Documentation](https://docs.github.com/en/issues/planning-and-tracking-with-projects)
- [Issue Labels](docs/TASK_MANAGEMENT.md)
- [Roadmap](docs/ROADMAP.md)
- [Application Status](docs/APPLICATION_STATUS.md)

---

**Document Created**: January 10, 2026
**Last Updated**: January 10, 2026
**Status**: Ready for Implementation
