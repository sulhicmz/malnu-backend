#!/bin/bash

# PR Analysis and Consolidation Tool
# Analyzes all open PRs, identifies duplicates, and generates recommendations
# Usage: ./scripts/analyze-open-prs.sh [--output FILE] [--format FORMAT]

set -e

# Configuration
OUTPUT_DIR="docs/pr-analysis"
TIMESTAMP=$(date +%Y%m%d_%H%M%S)
OUTPUT_FORMAT="markdown"  # Options: markdown, html, json

# Parse arguments
while [[ $# -gt 0 ]]; do
  case $1 in
    --output)
      OUTPUT_FILE="$2"
      shift 2
      ;;
    --format)
      OUTPUT_FORMAT="$2"
      shift 2
      ;;
    --help)
      echo "Usage: $0 [--output FILE] [--format FORMAT]"
      echo "  --output FILE: Save report to file (default: stdout)"
      echo "  --format FORMAT: Output format - markdown, html, or json (default: markdown)"
      exit 0
      ;;
    *)
      echo "Unknown option: $1"
      echo "Use --help for usage information"
      exit 1
      ;;
  esac
done

echo "🔍 Analyzing open PRs..."
echo ""

# Create output directory if saving to file
if [ -n "$OUTPUT_FILE" ]; then
  mkdir -p "$(dirname "$OUTPUT_FILE")"
fi

# Fetch all open PRs
echo "Fetching all open PRs..."
ALL_PRS=$(gh pr list --state open --limit 500 --json number,title,headRefName,createdAt,updatedAt,body,state,author,url,labels --jq '.[]')
TOTAL_PRS=$(echo "$ALL_PRS" | jq -s 'length')

echo "Found $TOTAL_PRS open PRs"
echo ""

# Extract issue references from PRs
echo "Extracting issue references..."
PR_WITH_ISSUES=$(echo "$ALL_PRS" | jq -r 'select(.body | test("#[0-9]+")) | {number, title, headRefName, createdAt, updatedAt, body, author, url, labels}')

# Group PRs by issue
echo "Grouping PRs by referenced issues..."
ISSUE_GROUPS=$(echo "$PR_WITH_ISSUES" | jq -r -s '
  map(select(.body | test("#[0-9]+"))) |
  map(.issueNumbers = ([.body | scan("#[0-9]+")] | unique)) |
  group_by(.issueNumbers | join(",")) |
  map({
    issues: .[0].issueNumbers,
    prs: map({
      number: .number,
      title: .title,
      branch: .headRefName,
      created: .createdAt,
      updated: .updatedAt,
      author: .author | .login,
      url: .url,
      labels: [.labels[]?.name] | sort
    })
  }) |
  sort_by(.issues | length) | reverse
')

# Identify duplicate PR groups (multiple PRs for same issue)
DUPLICATE_GROUPS=$(echo "$ISSUE_GROUPS" | jq -r '[.[] | select(.prs | length > 1)]')

# Identify potential duplicates based on title similarity
POTENTIAL_DUPES=$(echo "$ALL_PRS" | jq -r -s '
  map({number, title, headRefName, author}) |
  group_by(.title | ascii_downcase | sub(" fix$| refactor$| feat$| feat\\("; "") | ascii_downcase) |
  map({
    normalizedTitle: .[0].title | ascii_downcase | sub(" fix$| refactor$| feat$| feat\\("; "") | ascii_downcase,
    prs: map({number, title, headRefName, author})
  }) |
  map(select(.prs | length > 1))
')

# Identify stale PRs (open > 14 days, no updates)
STALE_PRS=$(echo "$ALL_PRS" | jq -r -s '
  map({
    number,
    title,
    branch: .headRefName,
    created: .createdAt,
    updated: .updatedAt,
    author: .author | .login
  })
')

# Generate report
generate_report() {
  local format=$1
  
  case $format in
    markdown)
      generate_markdown_report
      ;;
    html)
      generate_html_report
      ;;
    json)
      generate_json_report
      ;;
    *)
      echo "Unknown format: $format"
      exit 1
      ;;
  esac
}

generate_markdown_report() {
  echo "# PR Consolidation Analysis Report"
  echo ""
  echo "**Generated:** $(date)"
  echo "**Total Open PRs:** $TOTAL_PRS"
  echo "**Analyzed PRs with Issues:** $(echo "$PR_WITH_ISSUES" | jq -s 'length')"
  echo ""
  echo "## Executive Summary"
  echo ""
  echo "- **Duplicate PR Groups:** $(echo "$DUPLICATE_GROUPS" | jq -r 'length') groups with 2+ PRs for same issue"
  echo "- **Stale PRs:** $(echo "$STALE_PRS" | jq -r 'length') PRs to check for staleness"
  echo "- **PRs Without Issue References:** $(($TOTAL_PRS - $(echo "$PR_WITH_ISSUES" | jq -s 'length')))"
  echo ""
  echo "## Duplicate PR Groups by Issue"
  echo ""

  echo "$DUPLICATE_GROUPS" | jq -r -c '.[]' | while read -r group; do
    issues=$(echo "$group" | jq -r '.issues | join(", ")')
    pr_count=$(echo "$group" | jq -r '.prs | length')
    
    echo ""
    echo "### Issue #$issues ($pr_count duplicate PRs)"
    echo ""
    
    echo "$group" | jq -r '.prs | sort_by(.created) | .[] |
      "* **#\(.number)** - \(.title)" +
      "\n  - Branch: `\(.branch)`" +
      "\n  - Created: \(.created)" +
      "\n  - Updated: \(.updated)" +
      "\n  - Author: @\(.author)" +
      "\n  - URL: \(.url)" +
      "\n  - Labels: \([.labels[]] | join(", ") // "none")\n"'
  done

  cat <<'MD'

## Stale PRs (Potential - Check manually for >14 days without updates)

MD

  echo "$STALE_PRS" | jq -r '.[] |
    "* **#\(.number)** - \(.title)" +
    "\n  - Branch: `\(.branch)`" +
    "\n  - Created: \(.created)" +
    "\n  - Updated: \(.updated)" +
    "\n  - Author: @\(.author)" +
    "\n  - URL: \(.url)\n"'

  echo ""
  echo "## Recommendations"
  echo ""
  echo "### Immediate Actions (High Priority)"
  echo ""
  echo "1. **Review and Merge Ready PRs**"
  echo "   - Review PRs from oldest duplicate groups"
  echo "   - Merge the most complete/active PR"
  echo "   - Close duplicates with reference to merged PR"
  echo ""
  echo "2. **Close Stale PRs**"
  echo "   - Contact authors of stale PRs (>14 days)"
  echo "   - If no response, close with comment 'Stale - please reopen if still working on this'"
  echo "   - Document closure in issue comments"
  echo ""
  echo "3. **Prevent Future Duplicates**"
  echo "   - Run \`./scripts/check-duplicate-pr.sh\` before creating new PR"
  echo "   - Update CONTRIBUTING.md with duplicate PR prevention guidance"
  echo "   - Add PR template that requires issue reference"
  echo ""
  echo "### Medium Priority"
  echo ""
  echo "4. **Consolidate by Issue Type**"
  echo "   - Create GitHub Projects (see issue #567)"
  echo "   - Group work by business domain or feature area"
  echo "   - Assign owners to track PR progress"
  echo ""
  echo "5. **Review Cycle**"
  echo "   - Implement weekly PR review meetings"
  echo "   - Assign reviewers to reduce backlog"
  echo "   - Set SLA for PR review time (e.g., 7 days)"
  echo ""
  echo "### Success Metrics"
  echo ""
  echo "- Target: <15 open PRs (down from 50+)"
  echo "- Target: <5% duplicate PR rate"
  echo "- Target: Average PR age <7 days"
  echo "- Target: Merge rate >70%"
  echo ""
  echo "## Next Steps"
  echo ""
  echo "1. Run \`./scripts/bulk-close-duplicates.sh --dry-run\` to review duplicate closures"
  echo "2. Review stale PR list and contact authors"
  echo "3. Merge ready PRs from duplicate groups"
  echo "4. Update documentation with PR consolidation guidelines"
  echo ""
  echo "---"
  echo ""
  echo "**Report generated by:** \`scripts/analyze-open-prs.sh\`"
  echo "**See:** Issue #572 for PR consolidation context"
}

generate_json_report() {
  jq -n \
    --arg generated "$(date -Iseconds)" \
    --argjson totalPrs "$TOTAL_PRS" \
    --argjson duplicateGroups "$DUPLICATE_GROUPS" \
    --argjson stalePrs "$STALE_PRS" \
    '{
      generated: $generated,
      summary: {
        totalPrs: $totalPrs,
        duplicateGroups: ($duplicateGroups | length),
        stalePrs: ($stalePrs | length)
      },
      duplicateGroups: $duplicateGroups,
      stalePrs: $stalePrs,
      recommendations: [
        "Review and merge PRs from oldest duplicate groups",
        "Close stale PRs (>14 days) with author contact",
        "Prevent future duplicates with check-duplicate-pr.sh",
        "Create GitHub Projects for better organization",
        "Implement weekly PR review cycle"
      ]
    }'
}

generate_html_report() {
  cat <<'HTML'
<!DOCTYPE html>
<html>
<head>
  <title>PR Consolidation Analysis Report</title>
  <style>
    body { font-family: Arial, sans-serif; max-width: 1200px; margin: 0 auto; padding: 20px; }
    h1 { color: #333; border-bottom: 2px solid #0366d6; padding-bottom: 10px; }
    h2 { color: #444; margin-top: 30px; }
    h3 { color: #555; }
    .summary { background: #f6f8fa; padding: 15px; border-radius: 5px; margin: 20px 0; }
    .metric { display: inline-block; margin: 0 20px 10px 0; }
    .metric-value { font-size: 24px; font-weight: bold; color: #0366d6; }
    .metric-label { font-size: 12px; color: #666; }
    .duplicate-group, .stale-pr { background: #fff; border: 1px solid #ddd; padding: 15px; margin: 10px 0; border-radius: 5px; }
    .pr-item { padding: 10px; border-left: 3px solid #0366d6; margin: 10px 0; background: #f9f9f9; }
    .recommendations { background: #d4edda; padding: 20px; border-radius: 5px; margin: 20px 0; }
    .priority-high { color: #d9534f; font-weight: bold; }
  </style>
</head>
<body>
HTML

  echo "<h1>📊 PR Consolidation Analysis Report</h1>"
  echo "<p><strong>Generated:</strong> $(date)</p>"
  echo "<p><strong>Total Open PRs:</strong> $TOTAL_PRS</p>"
  
  echo "<div class='summary'>"
  echo "<div class='metric'><div class='metric-value'>$(echo "$DUPLICATE_GROUPS" | jq -r 'length')</div><div class='metric-label'>Duplicate Groups</div></div>"
  echo "<div class='metric'><div class='metric-value'>$(echo "$STALE_PRS" | jq -r 'length')</div><div class='metric-label'>Stale PRs (>14 days)</div></div>"
  echo "<div class='metric'><div class='metric-value'>$(($TOTAL_PRS - $(echo "$PR_WITH_ISSUES" | jq -r 'length')))</div><div class='metric-label'>PRs Without Issue Ref</div></div>"
  echo "</div>"
  
  echo "<h2>Duplicate PR Groups</h2>"
  echo "$DUPLICATE_GROUPS" | jq -r -c '.[]' | while read -r group; do
    issues=$(echo "$group" | jq -r '.issues | join(", ")')
    echo "<div class='duplicate-group'>"
    echo "<h3>Issue #$issues</h3>"
    echo "$group" | jq -r '.prs | .[] |
      "<div class=\"pr-item\"><strong>#\(.number)</strong> - \(.title)<br>" +
      "<small>Branch: <code>\(.branch)</code> | Created: \(.created) | Author: @\(.author) | <a href=\"\(.url)\">View</a></small></div>"'
    echo "</div>"
  done
  
  echo "<h2>Stale PRs</h2>"
  echo "$STALE_PRS" | jq -r '.[] |
    "<div class=\"stale-pr\"><strong>#\(.number)</strong> - \(.title)<br>" +
    "<small>\(.daysSinceUpdate) days since update | \(.daysOpen) days open | Author: @\(.author) | <a href=\"\(.url)\">View</a></small></div>"'
  
  echo "<div class='recommendations'>"
  echo "<h2>📋 Recommendations</h2>"
  echo "<h3 class='priority-high'>Immediate Actions</h3>"
  echo "<ol><li>Review and merge PRs from oldest duplicate groups</li>"
  echo "<li>Close stale PRs (>14 days) with author contact</li>"
  echo "<li>Prevent future duplicates with check-duplicate-pr.sh</li></ol>"
  echo "<h3>Medium Priority</h3>"
  echo "<ol><li>Create GitHub Projects for better organization</li>"
  echo "<li>Implement weekly PR review cycle</li>"
  echo "<li>Assign owners to track PR progress</li></ol>"
  echo "</div>"
  
  echo "</body></html>"
}

# Generate and output report
if [ "$OUTPUT_FORMAT" = "json" ]; then
  generate_json_report
elif [ "$OUTPUT_FORMAT" = "html" ]; then
  generate_html_report
else
  generate_markdown_report
fi | tee "$OUTPUT_FILE" 2>/dev/null || generate_markdown_report

echo ""
echo "✅ Analysis complete!"
echo ""
echo "Next steps:"
echo "  1. Review the report above"
echo "  2. Run: ./scripts/bulk-close-duplicates.sh --dry-run"
echo "  3. Take action on duplicates and stale PRs"
