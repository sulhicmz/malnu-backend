# PR Consolidation Guide

This guide provides best practices for consolidating duplicate pull requests and maintaining a healthy repository PR backlog.

## Problem Statement

The repository has accumulated 50+ open PRs, with multiple PRs targeting the same issues. This creates:
- Review bottlenecks for maintainers
- Wasted reviewer resources on duplicate implementations
- Confusion for contributors about which PR to work on
- Difficulty identifying which PRs are ready to merge

## Duplicate PR Prevention

### Before Creating a PR

1. **Check for Existing PRs**
   ```bash
   # Check for PRs referencing your issue number
   gh pr list --search "<issue-number>"

   # Use the validation script (recommended)
   ./scripts/check-duplicate-pr.sh <issue-number>
   ```

2. **Review Existing PRs**
   - Read existing PR descriptions carefully
   - Check if existing PR adequately addresses the issue
   - If yes, consider contributing to that PR instead of creating a new one

3. **Ask for Clarification**
   - If you're unsure whether to create a new PR, ask in the issue comments
   - Explain what you've found and ask for guidance

### What Constitutes a Duplicate PR

A PR is considered a duplicate if it:
- Targets the same issue number as another open PR
- Implements the same feature or fix
- Has similar changes (same files, same approach)
- Addresses the same problem with the same solution

### When to Create a New PR vs. Contribute

**Create a new PR when:**
- No existing PR exists for the issue
- Existing PR is abandoned (no activity for 14+ days)
- Your approach is significantly different from existing PR
- Existing PR explicitly states it needs help or is incomplete

**Contribute to existing PR when:**
- Existing PR is well-implemented and nearly complete
- Your changes are improvements rather than new implementations
- PR author is actively responding to reviews

## Duplicate PR Identification

### Manual Identification

1. **Search by Issue Number**
   ```bash
   gh pr list --search "#123"
   ```

2. **Search by Keywords**
   ```bash
   gh pr list --search "Form Request validation"
   ```

3. **Review Similar PRs**
   - Compare PR titles for similar wording
   - Check if PRs modify the same files
   - Look at branches for similar naming patterns

### Automated Analysis

Use the provided analysis script:
```bash
php scripts/analyze-pr-consolidation.php
```

This generates `docs/PR_CONSOLIDATION_REPORT.md` with:
- List of all open PRs grouped by target issue
- Duplicate PR sets identified
- Recommendations for which PR to merge
- Close message templates for duplicate PRs

## PR Consolidation Process

### Step 1: Identify Duplicate PRs

Run the analysis script or manually search:
```bash
gh pr list --state open --limit 100 > all-prs.txt
# Review and identify duplicates manually
```

### Step 2: Evaluate Each Duplicate Set

For each set of duplicate PRs:

1. **Check PR Labels**
   - PRs with `ready` or `approved` labels should be prioritized
   - PRs with `wip` (work in progress) may not be complete
   - PRs with `do-not-merge` should be closed

2. **Review Implementation Quality**
   - Read PR descriptions for completeness
   - Check if code changes are correct
   - Verify tests are included
   - Look at review comments for feedback

3. **Check PR Age**
   - Oldest PRs (by date) often represent canonical implementation
   - Newer PRs may be improvements or duplicates
   - PRs with no activity for 14+ days may be abandoned

### Step 3: Select Canonical PR

**Criteria for selecting canonical PR:**

1. **Oldest PR** (by creation date) - default choice
2. **Has `ready` or `approved` label** - overrides age
3. **Most complete implementation** - if clearly better
4. **Has positive reviews** - if maintainers have approved
5. **Has tests included** - prefer PRs with test coverage

**Document the decision:**
- Why this PR was chosen over others
- What criteria were used
- Any improvements from other PRs to incorporate

### Step 4: Close Duplicate PRs

For each duplicate PR:

1. **Close with Explanatory Comment**
   ```markdown
   This PR is being closed as a duplicate of PR #<canonical-number>.

   Multiple PRs were created for the same issue (<issue-number>). To maintain focus
   and reduce reviewer workload, we're consolidating to the canonical implementation.

   **Canonical PR**: #<canonical-number> - <brief description>

   **Please**:
   1. Review the canonical PR for the merged solution
   2. If your PR has improvements not in the canonical one, please comment
      on the canonical PR suggesting those improvements
   3. For future contributions: Check for existing PRs before creating new ones
      (see CONTRIBUTING.md)

   Thank you for your contribution!
   ```

2. **Add Duplicate Label**
   ```bash
   gh pr edit <pr-number> --add-label "duplicate"
   ```

3. **Link to Canonical PR**
   - Reference canonical PR in close comment
   - This helps contributors find the active work

### Step 5: Merge Canonical PR

1. **Final Review**
   - Ensure all tests pass
   - Check code quality standards
   - Verify documentation is complete
   - Run any required CI/CD checks

2. **Merge with Proper Commit Message**
   - Use conventional commit format
   - Reference the issue: `Fixes #<issue-number>`
   - Include brief description of what was merged

3. **Update Issue**
   - Issue should automatically close when PR is merged (via `Fixes #<number>`)
   - If not, manually close issue and link to merged PR
   - Thank contributors who participated

## Closing Stale PRs

### When to Close as Stale

A PR should be considered stale if:
- No activity for 14+ days
- No reviewer comments for 14+ days
- Author hasn't responded to review comments for 14+ days
- PR has `do-not-merge` or `abandoned` label

### Stale PR Close Process

1. **Add Warning Label First**
   ```bash
   gh pr edit <pr-number> --add-label "stale"
   ```

2. **Comment with Warning**
   ```markdown
   This PR has been inactive for 14+ days and is being marked as stale.

   **If you're still working on this**, please:
   1. Remove the `stale` label
   2. Add a comment with an update
   3. Respond to any outstanding review feedback

   If no activity occurs within 7 days, this PR will be closed.

   Thank you for your contribution!
   ```

3. **Close if Still Inactive After 7 Days**
   ```markdown
   This PR is being closed due to inactivity.

   No updates have been made for 21+ days. If you'd like to continue this work:

   1. Re-open this PR or create a new one
   2. Reference this PR for context
   3. Address any previous review feedback

   Thank you for your interest in contributing to this project!
   ```

## Prevention Strategies

### 1. Use PR Templates

The repository has PR templates that should:
- Ask for linked issue numbers
- Prompt contributors to check for existing PRs
- Require description of changes
- Ask about testing done

### 2. Add Issue to Branch Names

Encourage branch naming like:
- `feature/issue-123-description`
- `fix/issue-456-bug-description`

This makes it easier to identify which issue a PR addresses.

### 3. Automate Duplicate Detection

Consider adding automated checks:
- GitHub Actions workflow to detect duplicates on PR creation
- Comment on new PR if duplicate exists
- Block or warn on potential duplicates

### 4. Clear Communication

Maintainers should:
- Respond to PRs promptly
- Clearly communicate which PRs should be worked on
- Close duplicates with clear explanations
- Update issues with PR status

### 5. Regular PR Reviews

Schedule regular PR review sessions:
- Weekly review of new PRs
- Identify duplicates early
- Provide clear feedback
- Close stale PRs promptly

## Metrics to Track

### PR Health Metrics

- **Open PR Count**: Target < 15 (currently 50+)
- **Average PR Age**: Target < 7 days
- **Duplicate PR Rate**: Target < 5%
- **Merge Rate**: Target > 70% of PRs merged
- **Stale PR Count**: PRs with no activity for 14+ days

### Review Metrics

- **Time to First Review**: Average time to first reviewer comment
- **Time to Merge**: Average time from creation to merge
- **Review Response Time**: Maintainer response time

### Action Items

Track progress on consolidation:
- Issues with duplicate PRs: [ ] / [ ]
- PRs closed as duplicates: [ ] / [ ]
- PRs marked as stale: [ ] / [ ]

## Examples

### Example: Duplicate PR Resolution

**Issue #570 (N+1 Query Fix)**

PRs found: #576, #591, #595, #596, #598, #599, #602, #606, #610, #613, #615, #618

**Analysis**:
- All PRs implement the same fix (replace `getAllUsers()` with direct queries)
- PR #606 was created first (2026-01-20T21:49:13Z)
- PRs #610, #613, #615, #618 were created later with identical changes
- PR #606 has reviewer requested (sulhicmz)

**Decision**:
- **Canonical PR**: #606
- **Reason**: First submission, reviewer engaged
- **Action**: Merge #606, close others as duplicates

### Example: Stale PR Handling

**Issue #284 (Input Validation)**

PRs found: #290 (created 2026-01-07T19:00:55Z)

**Analysis**:
- PR #290 created 14+ days ago
- No review comments
- No activity from author in 14+ days
- Implementation appears complete

**Decision**:
- **Action**: Mark as stale first
- **Follow-up**: If no response in 7 days, close as stale
- **Alternative**: If implementation is good, review and merge instead

## References

- [CONTRIBUTING.md](../CONTRIBUTING.md) - Repository contribution guidelines
- [Issue #572](../../issues/572) - Main consolidation issue
- [Issue #545](../../issues/545) - Duplicate PR prevention
- [GitHub PR Best Practices](https://docs.github.com/en/pull-requests/collaborating-with-pull-requests/proposing-changes-to-your-work-with-pull-requests)
- [Managing Large PR Backlogs](https://github.blog/2015-11-03-working-with-large-pull-requests/)

## Quick Reference

### Common Commands

```bash
# Search for PRs by issue number
gh pr list --search "#123"

# Search for PRs by keyword
gh pr list --search "validation"

# List all open PRs
gh pr list --state open --limit 100

# Close a PR with comment
gh pr close <pr-number> --comment "Reason for closing..."

# Add label to PR
gh pr edit <pr-number> --add-label "duplicate"

# Remove label from PR
gh pr edit <pr-number> --remove-label "stale"
```

### Close Message Templates

**For Duplicates**:
```markdown
Closed as duplicate of PR #<number> <title>

See issue #<issue-number> for details. If your PR has improvements
not in the canonical one, please comment there.

Thank you for your contribution!
```

**For Stale PRs**:
```markdown
Closed due to inactivity (21+ days).

To continue this work:
1. Re-open this PR or create a new one
2. Reference this PR for context
3. Address any previous review feedback

Thank you for your interest!
```

---

**Last Updated**: 2026-01-21
**Maintainers**: @sulhicmz
**Related**: Issue #572
