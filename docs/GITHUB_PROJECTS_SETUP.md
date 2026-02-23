# GitHub Projects Setup

Since GitHub Projects are not enabled for this repository, we need to organize work using labels and milestones as an alternative project management system.

## Labels as Project Columns

Use labels to categorize and prioritize issues:

### Priority Labels (Columns)
- **critical** - P0 - Critical blockers, must fix immediately
- **high-priority** - P1 - High priority issues
- **medium-priority** - P2 - Medium priority issues
- **low-priority** - P3 - Low priority, nice to have

### Domain Labels (Swimlanes)
- **api** - API development issues
- **frontend** - Frontend development issues
- **database** - Database-related issues
- **security** - Security issues and vulnerabilities
- **infrastructure** - Infrastructure and deployment
- **documentation** - Documentation improvements
- **code-quality** - Code quality and refactoring
- **testing** - Testing and test coverage
- **performance** - Performance optimization
- **architecture** - Architecture and design

### Type Labels
- **bug** - Bug fixes
- **enhancement** - New features
- **feature** - Feature requests
- **maintenance** - Maintenance tasks
- **cleanup** - Code cleanup

## Milestones as Phases

Use milestones to track phases:

### M1: Foundation & Infrastructure
- **Target:** January 31, 2026
- **Issues:** #283, #301, #225, #302, #307, #304, #303, #305, #306, #308
- **Status:** In Progress

### M2: Core API Development
- **Target:** February 28, 2026
- **Issues:** #223, #226
- **Status:** Not Started

### M3: Additional Domains
- **Target:** March 31, 2026
- **Issues:** [To be determined - depends on M1/M2 completion]
- **Status:** Not Started

### M4: Frontend Development
- **Target:** April 30, 2026
- **Issues:** #304, #309
- **Status:** Not Started

### M5: Integration & Testing
- **Target:** May 31, 2026
- **Issues:** #173, #254
- **Status:** Not Started

### M6: Production Deployment
- **Target:** June 30, 2026
- **Issues:** #265, #277, #310
- **Status:** Not Started

## Workflow

1. **Create Issue:** Use appropriate labels for priority, domain, and type
2. **Assign Milestone:** Link issue to appropriate milestone phase
3. **Work on Issue:** Developers work on issues in priority order
4. **Update Progress:** Move issue labels or update comments with progress
5. **Close Issue:** When complete, close issue and link to PR

## Views

### Current Sprint
- Filter by: milestone=M1 and labels=critical,high-priority
- Sort by: priority

### Backlog
- Filter by: milestone is empty
- Sort by: priority

### Blocked Issues
- Filter by: body contains "Blocked by:"

### Ready for Review
- Filter by: state=open and labels=pr-ready

## Integration with CI/CD

When PR is merged:
- Automatically close linked issues
- Update milestone progress
- Notify team members

## Board View Alternative

Since we can't use GitHub Projects board, use:
- GitHub Issue filters and saved views
- Labels for organization
- Milestones for phases
- Projects in project management tools (linear.dev, Trello, etc.)
