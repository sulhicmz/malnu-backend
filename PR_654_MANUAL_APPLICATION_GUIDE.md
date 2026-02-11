# Manual Application Instructions for Issue #654

## Problem

The GitHub App lacks the `workflows` permission, which prevents direct pushes of workflow file changes. This PR includes the necessary changes as a patch file that must be manually applied.

## Solution

This PR adds essential CI checks to GitHub workflows via a new `.github/workflows/ci.yml` file.

## Steps to Apply the Fix

### 1. Apply the Patch

From the root of your repository, run:

```bash
git checkout main
git pull origin main
git apply CI_WORKFLOW_PATCH.diff
```

### 2. Verify the Changes

Check that the workflow file was created:

```bash
ls -la .github/workflows/ci.yml
cat .github/workflows/ci.yml
```

### 3. Commit the Changes

```bash
git add .github/workflows/ci.yml
git commit -m "feat(ci): Add essential CI checks to GitHub workflow

- Add dedicated ci.yml workflow with essential checks
- Run on all pull requests and pushes to main branch
- Include PHP syntax validation
- Run unit tests (composer test)
- Check code style (composer cs-diff)
- Run static analysis (composer analyse)
- Use composer caching for faster builds
- Minimal permissions (contents: read) for security

Fixes #654"
```

### 4. Push and Merge

```bash
git push origin feat/654-add-essential-ci-checks
```

Then create or update the PR to include the actual workflow file changes.

## What the CI Workflow Does

The new `.github/workflows/ci.yml` file includes:

1. **PHP Syntax Validation** - Checks all PHP files for syntax errors
2. **Unit Tests** - Runs the full test suite via `composer test`
3. **Code Style Checks** - Validates code style via `composer cs-diff`
4. **Static Analysis** - Runs PHPStan analysis via `composer analyse`

### Triggers

- Runs on every pull request
- Runs on every push to the main branch

### Benefits

- **Early Bug Detection** - Catches issues before they reach production
- **Code Quality** - Ensures code style and type safety
- **Faster Reviews** - Reduces manual review burden
- **Prevents Regressions** - Catches issues in new code

## Testing

After applying the patch:

1. Create a test PR to verify the workflow runs
2. Check the Actions tab in GitHub to see CI results
3. Verify all checks pass on the main branch

## Questions?

If you encounter any issues applying the patch, please:
1. Check that you're on the main branch
2. Ensure you have the latest changes: `git pull origin main`
3. Verify the patch file exists in the repository root
4. Check for any merge conflicts: `git apply --check CI_WORKFLOW_PATCH.diff`

## Related

- Issue: #654 - feat(ci): Add essential CI checks to GitHub workflows
- Partially addresses: #134 - CI/CD pipeline and automated testing
