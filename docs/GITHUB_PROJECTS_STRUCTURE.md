# GitHub Projects Setup Guide - January 13, 2026

This document outlines the structure and organization of GitHub Projects for the malnu-backend repository.

---

## Overview

GitHub Projects provide a visual way to organize and track work across issues and pull requests. The following 7 projects are recommended for optimal project management.

## Project Structure

### Project 1: Security & Critical Issues

**Purpose**: Track and prioritize all security vulnerabilities and critical blockers

**Columns**:
1. ⚠️ **Critical Blockers** - Issues preventing production deployment
2. 🔴 **High Priority Security** - Vulnerabilities requiring immediate attention
3. 🟡 **Medium Priority Security** - Security improvements and enhancements
4. ✅ **Fixed & Merged** - Resolved security issues
5. 🔒 **Security Audit** - Ongoing security assessments

**Issues to Include**:
- All critical security issues (if any remain)
- Security-related high priority issues
- Automated security scanning tasks
- JWT authentication issues
- CSRF protection issues
- Rate limiting issues
- Input validation security
- Security headers configuration

**Automations**:
- Auto-add issues with `critical` label
- Auto-add issues with `security` label
- Move issues to "Fixed & Merged" when linked PR is merged

---

### Project 2: Code Quality & Architecture

**Purpose**: Track code quality improvements and architectural enhancements

**Columns**:
1. 📋 **Backlog** - Identified code quality issues
2. 🔄 **In Progress** - Currently being addressed
3. 🧪 **Testing** - Awaiting test validation
4. ✅ **Completed** - Code quality improvements

**Issues to Include**:
- Code quality issues
- Architecture improvements
- Refactoring tasks
- Dependency injection fixes
- Validation code organization
- Generic CRUD base class
- Service interfaces
- Repository pattern implementation
- Technical debt reduction

**Automations**:
- Auto-add issues with `code-quality` label
- Auto-add issues with `architecture` label
- Auto-add issues with `refactor` label
- Move to "Testing" when PR is opened
- Move to "Completed" when PR is merged

---

### Project 3: Performance & Optimization

**Purpose**: Track performance improvements and optimization efforts

**Columns**:
1. 🎯 **Identified Bottlenecks** - Performance issues identified
2. 🔧 **Optimization In Progress** - Currently being optimized
3. 📊 **Benchmarking** - Performance testing
4. ✅ **Optimized** - Completed optimizations

**Issues to Include**:
- Database indexes optimization
- Redis caching implementation
- Query performance improvements
- API response time optimization
- Database query optimization
- Memory usage optimization
- Load balancing improvements
- Connection pooling

**Automations**:
- Auto-add issues with `performance` label
- Auto-add issues with `cache` label
- Move to "Benchmarking" when implementation PR is opened
- Move to "Optimized" when benchmarked

---

### Project 4: Testing & Quality Assurance

**Purpose**: Track test coverage and quality assurance efforts

**Columns**:
1. 📝 **Test Plan** - Tests to be written
2. ✍️ **Writing Tests** - Test implementation in progress
3. 🧪 **Testing** - Tests being executed
4. ✅ **Tested** - Tests passing

**Issues to Include**:
- Comprehensive test suite implementation
- Unit tests for services
- Feature tests for endpoints
- Integration tests
- API contract tests
- End-to-end tests
- CI/CD pipeline improvements
- Test coverage improvement

**Automations**:
- Auto-add issues with `testing` label
- Auto-add issues with `test` label
- Move to "Testing" when PR is opened
- Move to "Tested" when CI passes

---

### Project 5: Documentation & Knowledge Base

**Purpose**: Track documentation updates and knowledge base improvements

**Columns**:
1. 📚 **Documentation Needed** - Docs to be written
2. ✍️ **Drafting** - Documentation in progress
3. 👀 **Review** - Awaiting review
4. ✅ **Published** - Documentation complete

**Issues to Include**:
- API documentation with OpenAPI/Swagger
- Developer onboarding guides
- API error handling docs
- Deployment guides
- Architecture documentation updates
- Database schema documentation
- Security documentation
- Configuration documentation

**Automations**:
- Auto-add issues with `documentation` label
- Auto-add issues with `docs` label
- Move to "Review" when PR is opened
- Move to "Published" when PR is merged

---

### Project 6: Feature Development

**Purpose**: Track feature implementation across all business domains

**Columns**:
1. 💡 **Feature Ideas** - Proposed features
2. 📋 **Planning** - Feature planning and design
3. 🔨 **Development** - Feature implementation
4. 🧪 **Testing** - Feature testing
5. ✅ **Completed** - Features ready for production

**Issues to Include**:
- API controller implementations
- Student information system
- Teacher management system
- Assessment and examination management
- Calendar and event management
- Attendance tracking
- Report card generation
- Notification system
- Fee management
- Communication and messaging
- PPDB (school admission)
- E-Learning platform
- Digital library
- Online exam system
- Health and medical records
- Transportation management
- Cafeteria management
- Hostel and dormitory management
- Alumni network tracking
- Behavior and discipline management
- School administration module

**Automations**:
- Auto-add issues with `feature` label
- Auto-add issues with `enhancement` label
- Move to "Development" when implementation starts
- Move to "Testing" when implementation PR is opened
- Move to "Completed" when PR is merged

---

### Project 7: Infrastructure & DevOps

**Purpose**: Track infrastructure, deployment, and DevOps improvements

**Columns**:
1. 🔧 **Infrastructure Needed** - Infrastructure tasks identified
2. 🚧 **In Progress** - Infrastructure changes in progress
3. 🧪 **Testing** - Infrastructure testing
4. 🚀 **Deployed** - Infrastructure changes deployed

**Issues to Include**:
- Database services configuration
- Backup and disaster recovery
- GitHub Actions consolidation
- Workflow permissions optimization
- Application monitoring setup
- Automated security scanning
- Docker environment improvements
- CI/CD pipeline automation
- Deployment automation
- Infrastructure as code

**Automations**:
- Auto-add issues with `infrastructure` label
- Auto-add issues with `docker` label
- Auto-add issues with `ci` label
- Auto-add issues with `deployment` label
- Move to "Testing" when PR is opened
- Move to "Deployed" when PR is merged

---

## Implementation Steps

### Step 1: Create Projects Manually

Since GitHub Projects cannot be created programmatically via CLI without additional permissions, create them manually through the GitHub UI:

1. Navigate to: https://github.com/sulhicmz/malnu-backend/projects
2. Click "New Project"
3. Select "Board" template
4. Create each of the 7 projects listed above
5. Set up columns as specified

### Step 2: Configure Labels

Ensure all required labels exist in the repository:
- `critical` - Critical issues
- `security` - Security-related
- `code-quality` - Code quality issues
- `architecture` - Architecture improvements
- `performance` - Performance issues
- `cache` - Caching related
- `testing` - Testing related
- `test` - Test files
- `documentation` - Documentation
- `docs` - Docs files
- `feature` - New features
- `enhancement` - Enhancements
- `infrastructure` - Infrastructure
- `docker` - Docker related
- `ci` - CI/CD
- `deployment` - Deployment

### Step 3: Add Automation Rules

For each project, add automation rules using GitHub's built-in automation:

1. Click on the project
2. Go to "..." menu → "Automation"
3. Add rules as specified in each project section
4. Save automations

### Step 4: Migrate Existing Issues

Move existing issues to appropriate projects:

**Security Issues** (#446, #447):
- Move to Project 1: Security & Critical Issues

**Code Quality Issues** (#349, #350, #351, #352, #353):
- Move to Project 2: Code Quality & Architecture

**Performance Issues** (#357, #224):
- Move to Project 3: Performance & Optimization

**Testing Issues** (#173, #356):
- Move to Project 4: Testing & Quality Assurance

**Documentation Issues** (#354, #448):
- Move to Project 5: Documentation & Knowledge Base

**Feature Issues** (#223, #229, #231, #257, #258, #259, #261, #260, #264, #262, #263, #232, #233, #199, #265):
- Move to Project 6: Feature Development

**Infrastructure Issues** (#283, #134, #182, #197):
- Move to Project 7: Infrastructure & DevOps

### Step 5: Update Workflow Automation

Update `.github/workflows/on-push.yml` and `.github/workflows/on-pull.yml` to auto-assign issues to projects:

```yaml
# Example automation in issue creation
- name: Assign to Project
  if: github.event_name == 'issues'
  run: |
    ISSUE_NUMBER=${{ github.event.issue.number }}
    ISSUE_LABELS=${{ github.event.issue.labels.*.name }}
    
    # Assign to appropriate project based on labels
    if [[ "$ISSUE_LABELS" == *"security"* ]] || [[ "$ISSUE_LABELS" == *"critical"* ]]; then
      # Assign to Security & Critical Issues project
      gh api graphql -f query='...'
    fi
```

---

## Benefits

1. **Clear Prioritization**: Issues are organized by type and priority
2. **Better Visibility**: Stakeholders can see progress across all areas
3. **Automated Workflows**: Less manual work with automation rules
4. **Improved Tracking**: Easy to see what's in progress, blocked, or completed
5. **Better Planning**: Can plan sprints and releases based on project status

---

## Maintenance

- Review project assignments weekly
- Update automation rules as needed
- Archive completed projects quarterly
- Create new projects for major releases

---

**Created**: January 13, 2026
**Status**: Ready for Implementation
**Owner**: Repository Orchestrator
