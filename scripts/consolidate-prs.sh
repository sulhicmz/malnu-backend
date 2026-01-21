#!/bin/bash

# PR Consolidation Analyzer
# Fetches and analyzes all open pull requests to identify:
# - Duplicate PRs for the same issue
# - Ready-to-merge PRs
# - Stale PRs (no activity for 14+ days)
# - PRs needing review

set -e

echo "PR Consolidation Analyzer"
echo "====================="
echo ""

# Check if GitHub CLI is installed
if ! command -v gh &> /dev/null; then
    echo "Error: GitHub CLI (gh) is not installed."
    echo "Please install GitHub CLI from https://cli.github.com/"
    exit 1
fi

# Check if authenticated
if ! gh auth status &> /dev/null; then
    echo "Error: Not authenticated to GitHub CLI."
    echo "Please run: gh auth login"
    exit 1
fi

# Check if PHP is installed
if ! command -v php &> /dev/null; then
    echo "Error: PHP is not installed."
    exit 1
fi

# Run the PHP analyzer
SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"

echo "Running PR analysis..."
echo ""

php "$SCRIPT_DIR/analyze-prs.php"

echo ""
echo "====================="
echo "Analysis complete!"
echo ""
echo "Report generated: PR_CONSOLIDATION_REPORT.md"
echo ""
echo "Next steps:"
echo "1. Review PR_CONSOLIDATION_REPORT.md"
echo "2. Merge recommended PRs"
echo "3. Close duplicate PRs with appropriate comments"
echo "4. Request changes for PRs needing review"
echo ""
echo "View report: cat PR_CONSOLIDATION_REPORT.md"
