# PR Consolidation and Management Tools

This directory contains tools to help manage and consolidate the large number of open PRs in the repository.

## Overview

The repository has 50+ open PRs with many duplicates, causing:
- Review bottleneck for maintainers
- Wasted contributor effort on duplicate work
- Slow progress on issues
- Confusion about which PRs to work on

These tools help address these issues by:
- Analyzing all open PRs and identifying duplicates
- Providing recommendations for PR consolidation
- Helping close duplicate PRs with proper comments
- Maintaining audit trails of actions taken

## Tools

### 1. check-duplicate-pr.sh

**Purpose**: Check for existing PRs before creating a new one (prevention tool)

**Usage**:
```bash
./scripts/check-duplicate-pr.sh <issue_number>
```

**Example**:
```bash
./scripts/check-duplicate-pr.sh 570
```

**Output**:
- Lists existing PRs for the given issue
- Returns exit code 1 if duplicates found (to block PR creation)
- Returns exit code 0 if no duplicates (safe to create PR)

**Integration**:
Can be added to CI/CD pipeline or pre-commit hook to automatically check for duplicates before allowing new PRs.

### 2. analyze-open-prs.sh

**Purpose**: Analyze all open PRs and generate consolidation report

**Usage**:
```bash
./scripts/analyze-open-prs.sh [--format FORMAT] [--output FILE]
```

**Options**:
- `--format FORMAT`: Output format - `markdown`, `html`, or `json` (default: markdown)
- `--output FILE`: Save report to file (default: stdout)

**Examples**:
```bash
# Generate markdown report to console
./scripts/analyze-open-prs.sh

# Save JSON report to file
./scripts/analyze-open-prs.sh --format json --output docs/pr-analysis/report.json

# Generate HTML report
./scripts/analyze-open-prs.sh --format html --output docs/pr-analysis/report.html
```

**Output Includes**:
- Total PR count and analysis summary
- Duplicate PR groups (multiple PRs for same issue)
- Stale PR candidates (>14 days without updates)
- Recommendations for consolidation
- Success metrics and next steps

**Report Example (Markdown)**:
```markdown
# PR Consolidation Analysis Report

**Generated:** 2026-01-21T15:00:00Z
**Total Open PRs:** 83
**Analyzed PRs with Issues:** 45

## Executive Summary

- **Duplicate PR Groups:** 12 groups with 2+ PRs for same issue
- **Stale PRs:** 8 PRs to check for staleness
- **PRs Without Issue References:** 38

## Duplicate PR Groups by Issue

### Issue #570 (4 duplicate PRs)
* **#610** - perf(auth): Fix N+1 query in login()
* **#613** - fix(auth): Replace N+1 query
* **#615** - fix(auth): Fix N+1 query in AuthService
* **#618** - fix(auth): Replace inefficient getAllUsers()
...
```

### 3. bulk-close-duplicates.sh

**Purpose**: Close duplicate PRs with proper comments and references

**Usage**:
```bash
./scripts/bulk-close-duplicates.sh [--dry-run] [--confirm] [--file FILE]
```

**Options**:
- `--dry-run`: Preview actions without executing (default - **always run this first!**)
- `--confirm`: Execute actions (requires confirmation)
- `--file FILE`: Use specific JSON file with PR list

**Examples**:
```bash
# Dry run - review actions before executing
./scripts/bulk-close-duplicates.sh --dry-run

# Execute closures (requires explicit confirmation)
./scripts/bulk-close-duplicates.sh --confirm

# Use specific analysis file
./scripts/bulk-close-duplicates.sh --file docs/pr-analysis/report.json
```

**How It Works**:
1. Generates or loads PR analysis
2. Lists duplicate PR groups with PR counts
3. Prompts for selection of which groups to process
4. Shows PRs that will be closed
5. In dry-run mode: Shows actions without executing
6. In confirm mode: Requires 'yes' confirmation before executing
7. Closes selected PRs with explanatory comments
8. Maintains audit log in `docs/pr-consolidation-audit.log`

**Closure Comment Template**:
```markdown
This PR is being closed as a duplicate of #123.

To help consolidate the 50+ open PRs in this repository, we are identifying and closing duplicate PRs. The canonical PR for this issue is #123.

## Why is this being closed?

1. **Duplicate Work**: Multiple PRs exist for the same issue
2. **Canonical Selection**: PR #123 was chosen as the canonical implementation based on:
   - Recency (most recent activity)
   - Completeness of implementation
   - Test coverage

## Next Steps

If you believe this PR should remain open, please:
1. Comment on this PR explaining why it should remain open
2. Review the canonical PR #123 and contribute there instead
3. Reference this PR in a comment explaining the differences

## References

- Issue #572: PR consolidation and cleanup
- Canonical PR: #123
- Maintainer decision on: [DATE]

Thank you for your contribution! 🙏
```

## Workflow

### For Maintainers

**Step 1: Analyze Open PRs**
```bash
./scripts/analyze-open-prs.sh --format markdown --output docs/pr-analysis/latest-report.md
```

**Step 2: Review the Report**
- Open `docs/pr-analysis/latest-report.md`
- Review duplicate PR groups
- Review stale PR candidates
- Review recommendations

**Step 3: Preview Closure Actions**
```bash
./scripts/bulk-close-duplicates.sh --dry-run
```

**Step 4: Execute Closures** (if approved)
```bash
./scripts/bulk-close-duplicates.sh --confirm
```

**Step 5: Follow Up**
- Review audit log: `docs/pr-consolidation-audit.log`
- Update related issues with PR status
- Monitor for new duplicate PRs

### For Contributors

**Before Creating a PR**:
```bash
./scripts/check-duplicate-pr.sh <issue_number>
```

If duplicates exist:
1. Review existing PRs for the issue
2. Contribute to an existing PR instead
3. Only create a new PR if existing PRs are stale or inadequate

## Best Practices

### Preventing Duplicates

1. **Always check for existing PRs** before creating a new one
2. **Comment on existing PRs** if you want to contribute improvements
3. **Reference issues** in all PR descriptions
4. **Check PR age** - if a PR is >14 days old, ask before creating a new one

### Reviewing Duplicate PRs

When choosing which PR to keep as canonical:
1. **Recency** - Prefer PRs with recent activity
2. **Completeness** - Choose PRs with complete implementation
3. **Test Coverage** - Prefer PRs with comprehensive tests
4. **Code Quality** - Prefer PRs passing all checks
5. **Community Interest** - Consider PRs with more reviews/comments

### Communicating with Contributors

When closing duplicate PRs:
- Be respectful and appreciative of contributions
- Clearly explain the reason for closure
- Provide actionable next steps
- Encourage contribution to the canonical PR
- Keep PR open if there are valid objections

## Maintenance

### Files Created by Tools

1. `docs/pr-analysis/` - Directory for analysis reports
   - `latest-report.md` - Most recent analysis (markdown)
   - `report.json` - Analysis data (JSON)
   - `report.html` - Analysis report (HTML)

2. `docs/pr-consolidation-audit.log` - Audit trail of all closure actions

### Log Format

```
2026-01-21T15:30:00Z - DRY RUN - Would close PRs: 610 613 615
2026-01-21T15:35:00Z - CLOSED #610 - Duplicate of #618
2026-01-21T15:35:01Z - CLOSED #613 - Duplicate of #618
2026-01-21T15:35:02Z - CLOSED #615 - Duplicate of #618
```

### Regular Maintenance

**Weekly**:
- Run `analyze-open-prs.sh` to check for new duplicates
- Review stale PR candidates
- Merge ready PRs from duplicate groups

**Monthly**:
- Review audit logs and adjust consolidation strategy
- Update PR consolidation guidelines based on feedback
- Communicate progress with community

## Troubleshooting

### Tool Not Executable

```bash
chmod +x scripts/analyze-open-prs.sh
chmod +x scripts/bulk-close-duplicates.sh
```

### GitHub CLI Not Installed

Install GitHub CLI: https://cli.github.com/

```bash
# On macOS
brew install gh

# On Linux
sudo apt install gh

# On Windows
winget install --id GitHub.cli
```

### jq Not Found

Install `jq` (JSON processor):

```bash
# On macOS
brew install jq

# On Linux
sudo apt install jq

# On Windows
chocolatey install jq
```

### GitHub CLI Not Authenticated

```bash
gh auth login
```

### Rate Limiting

GitHub API has rate limits. Tools include automatic delays, but if you see errors:
- Wait a few minutes and retry
- Consider using GitHub token with higher rate limits
- Break up large operations into smaller batches

## Success Metrics

Track the following metrics to measure PR consolidation success:

- **Open PR Count**: Target <15 (down from 50+)
- **Duplicate PR Rate**: Target <5%
- **Average PR Age**: Target <7 days
- **Merge Rate**: Target >70%
- **PR Review Time**: Target <7 days

## Related Issues

- **Issue #572**: PR consolidation and cleanup
- **Issue #545**: Duplicate PR prevention
- **Issue #567**: Create GitHub Projects for better organization

## Contributing to These Tools

If you have ideas for improving these tools:

1. Fork the repository
2. Make your changes
3. Add tests for new functionality
4. Update this README with changes
5. Create a PR (checking for duplicates first!)

## License

These tools are part of the Malnu Backend project and follow the same license terms.
