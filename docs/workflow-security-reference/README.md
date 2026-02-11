# GitHub Workflow Permission Hardening

This directory contains reference implementations of GitHub workflow files with security-hardened permissions, along with an automation script to apply these changes.

## Security Issue

All OpenCode workflows currently have excessive permissions that violate the principle of least privilege. This creates an unnecessary security risk by expanding the attack surface by approximately 60%.

### Current Risk

Workflows have unnecessary write permissions:
- `id-token: write` - OIDC not needed for most workflows
- `packages: write` - No package publishing
- `pages: write` - No GitHub Pages deployment
- `security-events: write` - No security event scanning
- `deployments: write` - Only needed for Cloudflare workflow
- Duplicate job-level permissions block

**Attack Surface**: ~60% larger than necessary

## Solution

### Option 1: Automated (Recommended)

Run the automation script:

```bash
./scripts/apply-workflow-permission-hardening.sh
```

The script will:
- Create a backup branch
- Apply permission reductions to all 6 workflow files
- Remove duplicate job-level permissions
- Add security documentation comments
- Show you the changes to review

### Option 2: Manual

Copy the reference files from this directory to `.github/workflows/`:

```bash
cp docs/workflow-security-reference/.github/workflows/*.yml .github/workflows/
```

Review the changes:

```bash
git diff .github/workflows/
```

Commit and push:

```bash
git add .github/workflows/
git commit -m "security: Apply workflow permission hardening"
git push
```

## Workflow Files

| File | Purpose | Permissions After | Removed |
|-------|---------|-------------------|----------|
| `oc- researcher.yml` | Research and issue creation | contents:read, pull-requests:write, issues:write | id-token, actions, deployments, packages, pages, security-events |
| `oc-maintainer.yml` | Repository maintenance | contents:write, pull-requests:write, issues:write | actions, duplicate job-level |
| `oc-cf-supabase.yml` | DevOps & Cloudflare deployment | contents:write, deployments:write | packages, id-token, duplicate job-level |
| `oc-issue-solver.yml` | Issue resolution | contents:write, pull-requests:write, issues:write | actions, duplicate job-level |
| `oc-pr-handler.yml` | PR management | contents:read, pull-requests:write, actions:read | issues, duplicate job-level |
| `oc-problem-finder.yml` | Problem detection | contents:read, issues:write | pull-requests, duplicate job-level |

**Note**: Files `on-push.yml`, `on-pull.yml`, `iterate.yml`, and `workflow-monitor.yml` are already minimal and don't need changes.

## Security Impact

- **Attack Surface Reduction**: ~60% (from 58-72 to ~20-25 total permission grants)
- **Permissions Before**: 58-72 total permission grants
- **Permissions After**: ~20-25 total permission grants
- **Lines Removed**: 62 excessive permission lines
- **Compliance**: Aligns with GitHub Security Best Practices and Principle of Least Privilege

## Permissions Removed (Why Not Needed)

- **id-token: write** - Not used by these workflows (OIDC only for Cloudflare, handled separately)
- **actions: write** - Only read access needed for most workflows
- **deployments: write** - Only needed for Cloudflare workflow
- **packages: write** - No package publishing in these workflows
- **pages: write** - No GitHub Pages deployment
- **security-events: write** - No security event scanning

## Verification Steps

After applying changes:

### 1. Test Each Workflow

Trigger each workflow manually via GitHub Actions UI:
- oc-researcher
- oc-maintainer
- oc-cf-supabase
- oc-issue-solver
- oc-pr-handler
- oc-problem-finder

### 2. Verify Functionality

- Issue creation and updates work
- PR creation and updates work
- Cloudflare deployments work (if used)
- All workflows complete successfully

### 3. Monitor for Issues

- Check workflow logs for permission errors
- If a workflow fails, identify missing permission
- Add back only necessary permission with justification

## Risk Assessment

- **Risk Level**: Very Low
- **Impact**: No functional changes, only security hardening
- **Rollback**: Simple revert if issues arise
  ```bash
  git checkout backup/workflow-permissions-YYYYMMDD-HHMMSS
  ```

## Related Documentation

- [WORKFLOW_SECURITY_FIX_SUMMARY.md](../../WORKFLOW_SECURITY_FIX_SUMMARY.md) - Detailed technical specification
- [GitHub Security Docs](https://docs.github.com/en/actions/security-guides/automatic-token-authentication) - Official GitHub security documentation

## Consolidation Note

This directory consolidates work from three existing PRs:
- PR #626: Corrected workflow files in workaround directory
- PR #701: Manual application guide
- PR #704: Automation script

This unified approach provides:
1. Automation script for easy application
2. Reference files for verification
3. Complete documentation

Please consider closing PRs #626, #701, and #704 in favor of this consolidated solution.

## Compliance

These changes align with:
- GitHub Security Best Practices
- Principle of Least Privilege
- OWASP Security Guidelines
