# GitHub Projects Setup Guide - January 18, 2026

This guide provides step-by-step instructions for creating and configuring GitHub Projects for the malnu-backend repository.

---

## Overview

GitHub Projects will help organize issues and pull requests by business domain, priority, and workflow status. This guide outlines 7 recommended projects for comprehensive organization.

---

## Recommended Projects Structure

### Project 1: Infrastructure & DevOps
**Purpose**: Manage infrastructure, CI/CD, and DevOps-related tasks

**Columns**:
- Backlog
- To Do
- In Progress
- In Review
- Done
- Blocked

**Labels to Track**:
- `infrastructure`
- `devops`
- `ci-cd`
- `docker`
- `deployment`
- `monitoring`
- `backup`

**Sample Issues**:
- Consolidate GitHub Actions workflows (#225)
- Fix database services disabled in Docker Compose (#446 - CLOSED)
- Implement automated security scanning (#197)
- Set up application monitoring and observability (#227)

---

### Project 2: API Development
**Purpose**: Track API controller implementation and API-related tasks

**Columns**:
- Backlog
- Planning
- In Progress
- Code Review
- Testing
- Done
- Blocked

**Labels to Track**:
- `api`
- `controller`
- `restful`
- `endpoint`
- `documentation`

**Sample Issues**:
- Implement comprehensive API controllers for all 11 business domains (#223)
- Implement proper RESTful API controllers (#102)
- Add OpenAPI/Swagger documentation (#354)

---

### Project 3: Security & Authentication
**Purpose**: Manage security, authentication, and authorization tasks

**Columns**:
- Backlog
- To Do
- In Progress
- Security Review
- Testing
- Done
- Blocked

**Labels to Track**:
- `security`
- `authentication`
- `authorization`
- `jwt`
- `rbac`
- `vulnerability`

**Sample Issues**:
- Implement multi-factor authentication (MFA) (#177)
- Enhance input validation and prevent injection attacks (#284)
- Implement rate limiting per user (subtask of existing issues)

---

### Project 4: Testing & Quality Assurance
**Purpose**: Track testing, code quality, and QA-related tasks

**Columns**:
- Backlog
- To Do
- In Progress
- Review
- Done
- Blocked

**Labels to Track**:
- `testing`
- `test-coverage`
- `unit-test`
- `integration-test`
- `code-quality`
- `refactor`

**Sample Issues**:
- Implement comprehensive test suite for all models and relationships (#104)
- Fix CI/CD pipeline and add automated testing (#134)
- Standardize UUID implementation across all models (#103)

---

### Project 5: Documentation & Communication
**Purpose**: Manage documentation, guides, and communication tasks

**Columns**:
- Backlog
- To Do
- In Progress
- Review
- Done
- Blocked

**Labels to Track**:
- `documentation`
- `guide`
- `api-docs`
- `readme`
- `communication`

**Sample Issues**:
- Update all documentation to reflect current architecture (#175)
- Add comprehensive API documentation (#354)
- Close duplicate issues (#527 - CLOSED)

---

### Project 6: Feature Implementation
**Purpose**: Track new feature implementation across all business domains

**Columns**:
- Backlog
- Requirements
- Design
- In Progress
- Code Review
- Testing
- Done
- Blocked

**Labels to Track**:
- `enhancement`
- `feature`
- `business-domain`
- Domain-specific labels:
  - `attendance`
  - `calendar`
  - `grading`
  - `e-learning`
  - `inventory`
  - `library`
  - `transportation`
  - `cafeteria`
  - `hostel`
  - `alumni`
  - `parent-portal`

**Sample Issues**:
- Implement comprehensive school calendar and event management system (#258)
- Implement comprehensive assessment and examination management system (#231)
- Implement comprehensive fee management and billing system (#200)

---

### Project 7: Bug Fixes & Maintenance
**Purpose**: Track bug fixes, maintenance tasks, and code improvements

**Columns**:
- Backlog
- To Do
- In Progress
- Review
- Testing
- Done
- Blocked
- Won't Fix

**Labels to Track**:
- `bug`
- `maintenance`
- `cleanup`
- `refactor`
- `performance`
- `optimization`

**Sample Issues**:
- Implement soft deletes for critical models (#353)
- Consolidate and optimize GitHub Actions workflows (#225)
- Fix CI/CD pipeline and add automated testing (#134)

---

## Setup Instructions

### Step 1: Create Projects

For each of the 7 projects:

1. Go to the repository page
2. Click on the "Projects" tab
3. Click "New Project"
4. Select "Table" view
5. Enter project name (see above)
6. Click "Create"

### Step 2: Configure Columns

For each project:

1. Click the "⋮" menu (three dots) in the top right
2. Select "Settings"
3. Scroll to "Columns"
4. Add/remove columns as specified above
5. Drag and drop to reorder as needed
6. Click "Save changes"

### Step 3: Configure Labels

Ensure all required labels exist in the repository:

1. Go to repository "Issues" tab
2. Click "Labels"
3. Create any missing labels from the lists above
4. Use consistent colors:
   - `critical` / `high-priority`: Red
   - `medium-priority`: Yellow
   - `low-priority`: Blue
   - `bug`: Red
   - `enhancement` / `feature`: Green
   - `documentation`: Purple
   - `testing`: Orange
   - `security`: Red
   - `infrastructure`: Gray

### Step 4: Migrate Existing Issues

For each project:

1. Open the project
2. Click "Add items" (or type `/` and select "Issues and pull requests")
3. Search for relevant issues using labels
4. Add issues to appropriate columns
5. Assign priorities and due dates as needed

**Migration Priority**:
1. Infrastructure & DevOps (critical for operations)
2. API Development (core functionality)
3. Security & Authentication (critical for production)
4. Testing & Quality Assurance (blocks feature work)
5. Documentation & Communication (enables onboarding)
6. Feature Implementation (business value)
7. Bug Fixes & Maintenance (maintenance)

### Step 5: Configure Automation (Optional)

GitHub Projects supports automation using GitHub Actions. Set up rules:

**Example Automation Rules**:

1. **Auto-move PRs to "In Review"**:
   - When a PR is created, move to "In Review"
   - When a PR is marked as "Ready for review", move to "In Review"

2. **Auto-move issues to "Done"**:
   - When an issue is closed, move to "Done"

3. **Auto-assign by label**:
   - Issues with `security` label → Security & Authentication project
   - Issues with `api` label → API Development project
   - Issues with `infrastructure` label → Infrastructure & DevOps project

4. **Auto-move stale issues**:
   - Issues in "In Progress" for 7 days → Add `needs-attention` label
   - Issues in "Review" for 5 days → Add `needs-review` label

---

## Issue Prioritization Guidelines

### Priority Levels

**Critical**:
- Security vulnerabilities
- Production outages
- Data loss risks
- Blocks deployment

**High**:
- Important features
- Performance issues
- Major bugs
- Blocks other work

**Medium**:
- Standard features
- Minor bugs
- Code quality improvements
- Documentation updates

**Low**:
- Nice-to-have features
- Minor improvements
- Refactoring
- Nice-to-have enhancements

### Assigning Priorities

When adding issues to projects:

1. **Impact**: How many users affected?
2. **Urgency**: How soon is it needed?
3. **Dependencies**: What does this block?
4. **Effort**: How long will it take?

Use the **RICE prioritization method**:
- **R**each: How many users will it benefit?
- **I**mpact: How much will it benefit each user?
- **C**onfidence: How confident are we in the estimates?
- **E**ffort: How much effort is required?

---

## Workflow Management

### Issue Lifecycle

```
Backlog → Planning → In Progress → Review → Testing → Done
                     ↓
                  Blocked
```

### Pull Request Lifecycle

```
Draft → In Progress → Code Review → Testing → Merged → Done
               ↓
          Changes Requested
```

### Best Practices

1. **One issue, one PR**: Each PR should address a single issue
2. **Link PRs to issues**: Use `#issue-number` in PR description
3. **Update issues from PRs**: When PR is merged, update linked issue
4. **Move issues as they progress**: Keep project board up to date
5. **Review and prioritize weekly**: Reassess backlog priorities regularly
6. **Close resolved issues**: Move to "Done" column when resolved
7. **Archive old issues**: Move completed items to "Done" after closing

---

## Project Metrics

### Track These Metrics

**Velocity**:
- Issues completed per week
- PRs merged per week
- Average time in "In Progress"
- Average time in "Review"

**Quality**:
- Test coverage percentage
- Bugs reported per release
- Code review comments per PR
- CI/CD pass rate

**Progress**:
- % of backlog completed
- % of high-priority issues resolved
- % of features delivered
- % of documentation updated

**Efficiency**:
- Average issue resolution time
- Average PR review time
- Time from issue creation to PR merge
- Reopen rate (issues reopened after closing)

---

## Maintenance

### Weekly Tasks

- [ ] Review and prioritize backlog
- [ ] Move issues between columns as needed
- [ ] Update project metrics
- [ ] Identify and close duplicate issues
- [ ] Review stalled issues (in "In Progress" too long)
- [ ] Review PRs needing review

### Monthly Tasks

- [ ] Review and archive old issues
- [ ] Update project structure if needed
- [ ] Review and update automation rules
- [ ] Generate and review project reports
- [ ] Adjust priorities based on business needs

---

## Integration with Other Tools

### GitHub Actions

- Update project boards based on CI/CD status
- Auto-assign reviewers based on project
- Create issues from failed builds
- Update issue status based on deployment

### Documentation

- Link project items to relevant documentation
- Update docs when features are completed
- Create docs for new features
- Archive outdated docs

### Communication

- Use project discussions for team updates
- Link project items to Slack/Teams notifications
- Weekly project status updates
- Sprint planning based on project backlog

---

## Troubleshooting

### Common Issues

**Issue not appearing in project**:
- Check that the issue has the correct label
- Verify the issue is not already in another project
- Refresh the project board

**Automation not working**:
- Check GitHub Actions logs
- Verify automation rules are enabled
- Ensure permissions are correct

**Too many issues in backlog**:
- Review and prioritize backlog
- Close or defer low-priority items
- Break down large issues into smaller tasks
- Archive completed items

---

## Conclusion

GitHub Projects provide powerful organization and tracking capabilities. By implementing these 7 projects, the malnu-backend repository will have better visibility into progress, clearer priorities, and more efficient workflow management.

**Next Steps**:
1. Create the 7 projects
2. Configure columns and labels
3. Migrate existing issues
4. Set up automation rules
5. Train team on usage
6. Review and adjust as needed

---

**Created**: January 18, 2026
**Maintained By**: Repository Orchestrator
**Version**: 1.0
**Last Updated**: January 18, 2026

---

## References

- [ORCHESTRATOR_ANALYSIS_REPORT_v6.md](ORCHESTRATOR_ANALYSIS_REPORT_v6.md) - Latest analysis
- [GITHUB_PROJECTS_STRUCTURE.md](GITHUB_PROJECTS_STRUCTURE.md) - Project structure details
- [TASK_MANAGEMENT.md](TASK_MANAGEMENT.md) - Task management workflows
- [ROADMAP.md](ROADMAP.md) - Development roadmap
