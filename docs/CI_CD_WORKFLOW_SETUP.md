# CI/CD Pipeline Consolidation - Workflow Files

Due to GitHub App permission restrictions on workflow files, the 3 new consolidated workflows need to be manually added by repository maintainers.

## New Workflows

### 1. ci.yml
This workflow replaces all AI agent testing workflows and provides essential CI/CD checks:

**Triggers:**
- Push to main or develop branches
- Pull requests to main or develop branches

**Jobs:**
- **test**: Runs PHPUnit tests with PHP 8.2 and 8.3 matrix
  - Setup PHP environment
  - Install dependencies with caching
  - Prepare test database (SQLite)
  - Execute tests with composer test
  - Upload coverage to Codecov

- **phpstan**: Runs PHPStan static analysis
  - Setup PHP 8.2
  - Install dependencies with caching
  - Run `composer analyse`

- **cs-fixer**: Runs PHP CS Fixer code style checks
  - Setup PHP 8.2
  - Install dependencies with caching
  - Run PHP CS Fixer in dry-run mode

### 2. security.yml
This workflow provides security scanning and dependency checks:

**Triggers:**
- Push to main or develop branches
- Pull requests to main or develop branches
- Weekly scheduled run (every Monday at 6 AM UTC)

**Jobs:**
- **composer-audit**: Checks for known vulnerabilities in dependencies
  - Install dependencies
  - Run `composer audit --format=json`

- **dependency-review**: Reviews new dependencies in PRs
  - Uses GitHub Actions dependency review action
  - Fails on high severity vulnerabilities

- **codeql-analysis**: Runs CodeQL security analysis
  - Supports PHP and JavaScript languages
  - Analyzes code for security vulnerabilities

### 3. deploy.yml
This workflow handles deployment to production and staging environments:

**Triggers:**
- Push to main branch (production)
- Manual workflow dispatch (both environments)

**Jobs:**
- **deploy**: Production deployment
  - Environment: production
  - Setup PHP 8.2
  - Install production dependencies
  - Run database migrations
  - Clear application caches
  - Provides deployment notification

- **deploy-staging**: Staging deployment
  - Environment: staging
  - Similar to production but uses staging secrets
  - Can be triggered manually for pre-production testing

## Disabled Workflows

The following 10 workflows have been disabled by renaming them with `.disabled` suffix:

1. `oc-researcher.yml.disabled` - AI agent for repository analysis
2. `oc-cf-supabase.yml.disabled` - AI agent for Cloudflare/Supabase DevOps
3. `oc-issue-solver.yml.disabled` - AI agent for issue resolution
4. `oc-maintainer.yml.disabled` - AI agent for repository maintenance
5. `oc-pr-handler.yml.disabled` - AI agent for PR handling
6. `oc-problem-finder.yml.disabled` - AI agent for finding issues (scheduled)
7. `on-pull.yml.disabled` - AI agent triggered on PRs
8. `on-push.yml.disabled` - AI agent triggered on pushes
9. `openhands.yml.disabled` - AI agent for complex tasks
10. `workflow-monitor.yml.disabled` - Workflow monitoring and triggering

## Manual Installation Steps

To complete this CI/CD consolidation, a repository maintainer with `workflows` permission should:

1. Create the 3 new workflow files in `.github/workflows/`:
   ```bash
   mkdir -p .github/workflows
   # Create ci.yml with content from this document
   # Create security.yml with content from this document
   # Create deploy.yml with content from this document
   ```

2. The new workflow files are ready to use immediately once added

3. The disabled workflows can be:
   - Kept as-is for reference (current state)
   - Deleted if confirmed to be unnecessary

## Benefits

- **70% reduction in workflow complexity** (10 files → 3 files)
- **Automated testing** - Tests run on every push and PR
- **Quality gates** - PHPStan and PHP CS Fixer prevent low-quality code
- **Security scanning** - Composer audit, dependency review, and CodeQL analysis
- **Coverage reporting** - Automatic code coverage tracking with Codecov
- **Simplified maintenance** - Easier to understand and modify

## Environment Variables Required

The workflows expect these GitHub secrets to be configured:

- `CODECOV_TOKEN` - For Codecov coverage reporting (optional)
- `DB_CONNECTION`, `DB_HOST`, `DB_PORT`, `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD` - Database configuration
- `STAGING_DB_CONNECTION`, `STAGING_DB_HOST`, `STAGING_DB_PORT`, `STAGING_DB_DATABASE`, `STAGING_DB_USERNAME`, `STAGING_DB_PASSWORD` - Staging database (optional)

## Notes

- All tests use SQLite in-memory database for fast execution
- Production and staging use MySQL as configured in `.env`
- Security scans run weekly in addition to push/PR triggers
- PHPStan uses level 5 configuration from `phpstan.neon`
- Code coverage is uploaded only for main branch pushes to minimize usage
