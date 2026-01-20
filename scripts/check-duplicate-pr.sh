#!/bin/bash

# Check for existing PRs for a given issue before creating a new one
# Enhanced version: Shows PR details, checks for duplicates, provides guidance
# Usage: ./scripts/check-duplicate-pr.sh <issue_number>

set -e

ISSUE_NUMBER=$1

if [ -z "$ISSUE_NUMBER" ]; then
  echo "Error: Issue number is required"
  echo "Usage: $0 <issue_number>"
  exit 1
fi

echo "=================================="
echo "Checking for existing PRs for issue #$ISSUE_NUMBER"
echo "=================================="
echo ""

# Check for existing PRs that reference this issue
EXISTING_PRS=$(gh pr list --state open --json number,title,body,headRefName,state,createdAt --jq ".[] | select(.body | contains(\"#$ISSUE_NUMBER\") or contains(\"Fixes #$ISSUE_NUMBER\") or contains(\"Closes #$ISSUE_NUMBER\") or contains(\"Resolves #$ISSUE_NUMBER\")) | \"\(.number) - \(.title) (state: \(.state)) (created: \(.createdAt)) (branch: \(.headRefName))\"")

if [ -z "$EXISTING_PRS" ]; then
  echo "✅ No existing open PRs found for issue #$ISSUE_NUMBER"
  echo ""
  echo "It is safe to create a new PR for this issue."
  echo ""
  echo "Next steps:"
  echo "  1. Create feature branch: git checkout -b feature/issue-$ISSUE_NUMBER"
  echo "  2. Implement your changes"
  echo "  3. Commit changes: git add . && git commit -m 'feat: ...'"
  echo "  4. Push branch: git push -u origin feature/issue-$ISSUE_NUMBER"
  echo "  5. Create PR: gh pr create --title 'feat: ... --body 'Fixes #$ISSUE_NUMBER'"
  exit 0
else
  echo "⚠️  Found existing open PR(s) for issue #$ISSUE_NUMBER:"
  echo ""
  echo "$EXISTING_PRS"
  echo ""
  
  # Count PRs
  PR_COUNT=$(echo "$EXISTING_PRS" | wc -l)
  echo "Total: $PR_COUNT existing open PR(s)"
  echo ""
  
  # Check if any PR is actually ready to merge
  echo "Recommendations:"
  echo ""
  
  FIRST_PR=$(echo "$EXISTING_PRS" | head -n1)
  FIRST_PR_NUM=$(echo "$FIRST_PR" | grep -oP '#[0-9]*' | grep -oP '[0-9]*' || echo "Unknown")
  
  echo "1. Review the existing PR(s) above"
  echo "2. Check if any PR addresses your needs"
  echo "3. Consider contributing to an existing PR instead of creating a new one"
  echo "4. If existing PRs are incomplete/outdated, add a comment explaining what's missing"
  echo ""
  
  echo "To view details of a specific PR:"
  echo "  gh pr view <pr_number>"
  echo ""
  echo "Example: gh pr view $FIRST_PR_NUM"
  echo ""
  
  # Ask user what they want to do
  echo "What would you like to do?"
  echo "  1) Create a new PR anyway (not recommended)"
  echo "  2) View details of first PR"
  echo "  3) Cancel and exit"
  echo ""
  read -p "Enter your choice (1-3): " CHOICE
  
  case $CHOICE in
    1)
      echo "Proceeding with new PR creation..."
      echo "⚠️  Warning: This will create a duplicate PR!"
      exit 0
      ;;
    2)
      echo "Viewing PR #$FIRST_PR_NUM..."
      gh pr view "$FIRST_PR_NUM"
      exit 0
      ;;
    3)
      echo "Cancelled. No action taken."
      exit 1
      ;;
    *)
      echo "Invalid choice. Exiting."
      exit 1
      ;;
  esac
fi
