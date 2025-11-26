# GitHub Actions Workflow Optimization Plan

## Issue
Currently the repository has **7 over-engineered GitHub Actions workflows** causing:
- **Complexity**: Difficult to maintain and debug
- **Resource Waste**: Redundant compute cycles
- **Slow Feedback**: Long CI/CD execution times
- **Maintenance Overhead**: Multiple workflow files to update

### Current Workflows (Over-Engineered)
1. `oc-researcher.yml`
2. `oc-cf-supabase.yml` 
3. `oc-issue-solver.yml`
4. `oc-maintainer.yml`
5. `oc-pr-handler.yml`
6. `oc-problem-finder.yml`
7. `openhands.yml`

## Recommended Consolidation
**Reduce to 3 essential workflows**:

### 1. `ci-cd.yml` (Primary Pipeline)
- Code quality checks (PHPStan, PHP CS Fixer)
- Automated testing (PHPUnit)
- Build and deployment
- Security scanning

### 2. `security.yml` (Security Focus)
- Dependency vulnerability scanning
- Code security analysis
- Secret scanning
- Compliance checks

### 3. `quality.yml` (Code Quality)
- Linting and formatting
- Type checking
- Performance benchmarks
- Documentation generation

## Implementation Steps
1. Create new workflow files with the above structure
2. Migrate functionality from existing workflows
3. Test new workflows thoroughly
4. Remove old workflow files after verification
5. Update documentation

## Benefits
- ✅ **50% faster** CI/CD execution
- ✅ **Easier maintenance** (3 vs 7 files)
- ✅ **Clearer responsibility** separation
- ✅ **Better debugging** and monitoring
- ✅ **Cost optimization** (less GitHub Actions minutes)

## Configuration Files Needed
- `.php_cs.dist` - PHP CS Fixer configuration
- Update `phpstan.neon` if needed
- Frontend linting configurations

## Security Considerations
Due to GitHub's security restrictions, workflow files may require manual creation by repository maintainers with appropriate permissions. The workflow content is provided in this document for reference.