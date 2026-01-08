# GitHub Actions Workflow Consolidation

This document describes the consolidation of GitHub Actions workflows to improve maintainability and reduce complexity.

## Overview

The repository previously had 9 complex workflows that relied heavily on automated AI agents, creating maintenance overhead and potential conflicts. These have been consolidated into 3 focused workflows that follow CI/CD best practices.

## Changes

### Removed Workflows

The following workflows have been removed or replaced:

1. **oc-problem-finder.yml** - AI agent for problem detection
2. **oc-pr-handler.yml** - AI agent for PR management
3. **oc-issue-solver.yml** - AI agent for issue resolution
4. **oc-maintainer.yml** - AI agent for repository maintenance
5. **oc-cf-supabase.yml** - AI agent for Cloudflare/Supabase operations
6. **oc-researcher.yml** - AI agent for research tasks (file not found)
7. **openhands.yml** - AI agent for security/performance analysis
8. **on-pull.yml** - Complex PR automation with multiple steps
9. **on-push.yml** - Complex push automation with multiple flows

### New Consolidated Workflows

#### 1. ci.yml

**Purpose**: Combined testing, linting, and security scanning

**Jobs**:
- **PHPUnit Tests**: Runs the full test suite with code coverage reporting
- **PHP CS Fixer**: Checks code style compliance
- **PHPStan**: Performs static analysis
- **Security Audit**: Runs composer audit to detect vulnerabilities

**Triggers**:
- Pull requests to main/master/develop branches
- Pushes to main/master/develop branches

**Benefits**:
- Provides essential quality gates for all changes
- Consistent testing across PHP 8.2
- Code coverage integration with Codecov
- Automated security vulnerability detection

#### 2. pr-automation.yml

**Purpose**: PR management and validation

**Jobs**:
- **Validate PR**: Ensures PR has description and linked issues
- **Auto-label**: Automatically labels PRs based on changed files
- **Check Mergeability**: Detects merge conflicts
- **Check Commit Messages**: Validates commit message format
- **PR Size Check**: Warns on large PRs (>500 lines)

**Triggers**:
- PR opened/synchronized/reopened/edited
- PR review events
- PR target events

**Benefits**:
- Ensures PR quality before review
- Automatic labeling improves organization
- Prevents merge conflicts reaching main branch
- Enforces commit message standards
- Encourages smaller, focused PRs

#### 3. maintenance.yml

**Purpose**: Repository upkeep and dependency management

**Jobs**:
- **Check Dependencies**: Identifies outdated packages and security vulnerabilities
- **Repository Cleanup**: Identifies stale branches (>30 days)
- **Documentation Sync**: Checks for broken links and outdated docs
- **Generate Report**: Creates maintenance report artifact

**Triggers**:
- Scheduled daily at 2:00 AM UTC
- Manual dispatch with task selection

**Benefits**:
- Proactive dependency management
- Automated security vulnerability detection
- Keeps documentation up-to-date
- Provides regular maintenance reports

## Configuration Files

### .github/labeler.yml

Automatic PR labeling based on changed files:
- `docs`: Documentation changes
- `frontend`: Frontend code changes
- `backend`: Backend code changes
- `tests`: Test code changes
- `ci`: CI/CD workflow changes
- `dependencies`: Package manager changes
- `database`: Database/migration changes

### .commitlintrc.js

Commit message linting rules:
- Conventional commits format (type(scope): description
- Supported types: feat, fix, docs, style, refactor, perf, test, build, ci, chore, revert
- Subject max length: 72 characters
- No trailing periods in subject

### .github/link-check-config.json

Documentation link checking configuration:
- Ignores localhost/example.com links
- Accepts redirect status codes (301, 302, etc.)
- Retries on rate limiting (429)
- 5 retry attempts with 10s delay

## Migration Guide

### For Developers

1. **Update your PR descriptions**:
   - Must include a description
   - Must link to an issue using "Fixes #", "Closes #", or "Resolves #"

2. **Follow commit message standards**:
   - Use conventional commits format
   - Keep subject under 72 characters
   - Examples:
     - `feat: Add user authentication`
     - `fix(api): Resolve login timeout issue`
     - `docs: Update API documentation`

3. **PR size guidelines**:
   - Aim for PRs under 500 lines
   - Split large changes into multiple focused PRs
   - Large PRs will trigger warnings

### For Maintainers

1. **Review automated labels**:
   - PRs are automatically labeled based on changed files
   - Add or adjust labels as needed

2. **Check CI status**:
   - All jobs must pass before merging
   - Review failure logs for troubleshooting

3. **Maintenance reports**:
   - Review daily maintenance reports in Actions artifacts
   - Address security vulnerabilities promptly
   - Clean up stale branches periodically

### Manual Triggers

You can manually trigger the maintenance workflow:

```bash
gh workflow run maintenance.yml -f task=dependencies
gh workflow run maintenance.yml -f task=cleanup
gh workflow run maintenance.yml -f task=docs
gh workflow run maintenance.yml -f task=all
```

## Benefits of Consolidation

### 1. Reduced Complexity
- **Before**: 9 complex workflows with overlapping functionality
- **After**: 3 focused workflows with clear responsibilities

### 2. Improved Maintainability
- Standard GitHub Actions patterns
- Clear separation of concerns
- Easier to debug and modify

### 3. Better Resource Usage
- Uses standard Ubuntu runners (no ARM-specific requirements)
- Shorter timeouts (30-40 min vs 40 min +)
- Reduced API calls to external services

### 4. Essential Quality Gates
- Automated testing on all PRs
- Code style enforcement
- Static analysis
- Security vulnerability scanning
- PR quality checks

### 5. Proactive Maintenance
- Daily dependency checks
- Security vulnerability detection
- Documentation validation
- Stale branch identification

## Rollback Plan

If issues arise with the new workflows, you can:

1. **Disable new workflows**:
   ```bash
   gh workflow disable ci.yml
   gh workflow disable pr-automation.yml
   gh workflow disable maintenance.yml
   ```

2. **Restore old workflows** (if backed up):
   - Restore from git history
   - Re-enable old workflows
   - Review and fix issues

3. **Report issues**:
   - Create a GitHub issue describing the problem
   - Include workflow run logs
   - Tag with `ci` label

## Future Improvements

Potential enhancements to consider:

1. **Add deployment workflow**: For staging/production deployments
2. **Performance benchmarking**: Add performance testing to CI
3. **Integration tests**: Add end-to-end testing
4. **Slack/Email notifications**: Add failure notifications
5. **Code quality metrics**: Add code quality tracking
6. **Dependency update automation**: Automated PRs for dependency updates

## References

- [GitHub Actions Documentation](https://docs.github.com/en/actions)
- [Commitlint](https://commitlint.js.org/)
- [Labeler Action](https://github.com/actions/labeler)
- [PHPUnit](https://phpunit.de/)
- [PHP CS Fixer](https://github.com/FriendsOfPHP/PHP-CS-Fixer)
- [PHPStan](https://phpstan.org/)
- [Codecov](https://codecov.io/)
