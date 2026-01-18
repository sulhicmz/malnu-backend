#!/bin/bash

# Check for existing PRs for a given issue before creating a new one
# Usage: ./scripts/check-duplicate-pr.sh <issue_number>

set -e

ISSUE_NUMBER=$1

if [ -z "$ISSUE_NUMBER" ]; then
  echo "Error: Issue number is required"
  echo "Usage: $0 <issue_number>"
  exit 1
fi

# Check for existing PRs that reference this issue
EXISTING_PRS=$(gh pr list --state open --json number,title,body,headRefName --jq ".[] | select(.body | contains(\"$ISSUE_NUMBER\")) | \"\(.number) - \(.title) - \(.headRefName)\"")

if [ -n "$EXISTING_PRS" ]; then
  echo "Found existing PRs for issue #$ISSUE_NUMBER:"
  echo "$EXISTING_PRS"
  echo ""
  echo "Please review these PRs before creating a new one."
  exit 1
else
  echo "No existing PRs found for issue #$ISSUE_NUMBER"
  exit 0
fi
