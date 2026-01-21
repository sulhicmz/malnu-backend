#!/bin/bash

# Bulk Duplicate PR Closure Tool
# Helps close duplicate PRs with proper comments and references
# Usage: ./scripts/bulk-close-duplicates.sh [--dry-run] [--confirm] [--file FILE]

set -e

# Configuration
DRY_RUN=true
CONFIRM=false
INPUT_FILE=""
PR_LOG="docs/pr-consolidation-audit.log"

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Parse arguments
while [[ $# -gt 0 ]]; do
  case $1 in
    --dry-run)
      DRY_RUN=true
      shift
      ;;
    --confirm)
      CONFIRM=true
      shift
      ;;
    --file)
      INPUT_FILE="$2"
      shift 2
      ;;
    --help)
      echo "Usage: $0 [--dry-run] [--confirm] [--file FILE]"
      echo ""
      echo "Options:"
      echo "  --dry-run     Preview actions without executing (default)"
      echo "  --confirm      Execute actions (requires confirmation)"
      echo "  --file FILE    Use specific JSON file with PR list"
      echo "  --help         Show this help message"
      echo ""
      echo "This script helps close duplicate PRs with proper comments."
      echo "Always run with --dry-run first to review actions."
      exit 0
      ;;
    *)
      echo -e "${RED}Unknown option: $1${NC}"
      echo "Use --help for usage information"
      exit 1
      ;;
  esac
done

# Create log directory
mkdir -p "$(dirname "$PR_LOG")"

echo -e "${BLUE}🔍 PR Duplicate Closure Tool${NC}"
echo ""

# Generate PR analysis if no input file
if [ -z "$INPUT_FILE" ]; then
  echo "Generating PR analysis..."
  ANALYSIS_OUTPUT=$(mktemp)
  ./scripts/analyze-open-prs.sh --format json > "$ANALYSIS_OUTPUT"
  
  # Extract duplicate groups
  DUPLICATE_GROUPS=$(jq '.duplicateGroups' "$ANALYSIS_OUTPUT")
  
  # Clean up
  rm "$ANALYSIS_OUTPUT"
else
  echo "Using input file: $INPUT_FILE"
  if [ ! -f "$INPUT_FILE" ]; then
    echo -e "${RED}Error: Input file not found: $INPUT_FILE${NC}"
    exit 1
  fi
  DUPLICATE_GROUPS=$(jq '.duplicateGroups' "$INPUT_FILE")
fi

# Check if there are duplicates
DUPLICATE_COUNT=$(echo "$DUPLICATE_GROUPS" | jq 'length')

if [ "$DUPLICATE_COUNT" -eq 0 ]; then
  echo -e "${GREEN}✅ No duplicate PRs found!${NC}"
  exit 0
fi

echo -e "${YELLOW}Found $DUPLICATE_COUNT groups of duplicate PRs${NC}"
echo ""

# Ask user to select which groups to process
echo "Duplicate PR Groups:"
echo "$DUPLICATE_GROUPS" | jq -r '.[] | "\(.issues | join(", ")) - \(.prs | length) PRs"' | nl
echo ""

echo "Which groups to process?"
echo "  - Enter comma-separated numbers (e.g., 1,3,5)"
echo "  - Enter 'all' to process all groups"
echo "  - Enter 'quit' to cancel"
echo ""
read -p "Selection: " SELECTION

if [ "$SELECTION" = "quit" ]; then
  echo "Cancelled by user."
  exit 0
fi

# Build list of PRs to close
PRS_TO_CLOSE=()

if [ "$SELECTION" = "all" ]; then
  GROUPS_TO_PROCESS=$(seq 1 $DUPLICATE_COUNT)
else
  GROUPS_TO_PROCESS=$(echo "$SELECTION" | tr ',' '\n')
fi

for group_num in $GROUPS_TO_PROCESS; do
  if [ "$group_num" -lt 1 ] || [ "$group_num" -gt "$DUPLICATE_COUNT" ]; then
    echo -e "${RED}Invalid group number: $group_num${NC}"
    exit 1
  fi
  
  # Get PRs in this group (keeping newest, closing others)
  GROUP_DATA=$(echo "$DUPLICATE_GROUPS" | jq ".[$group_num-1]")
  PRS_IN_GROUP=$(echo "$GROUP_DATA" | jq -r '.prs | sort_by(.updated) | reverse | .[1:] | .[]')
  
  # Add to list
  PRS_TO_CLOSE+=("$PRS_IN_GROUP")
done

# Flatten PR numbers
PR_NUMBERS=()
for pr_json in "${PRS_TO_CLOSE[@]}"; do
  pr_num=$(echo "$pr_json" | jq -r '.number')
  PR_NUMBERS+=("$pr_num")
done

if [ "${#PR_NUMBERS[@]}" -eq 0 ]; then
  echo -e "${YELLOW}No PRs selected for closure.${NC}"
  exit 0
fi

echo ""
echo -e "${BLUE}PRs to close:${NC}"
for pr_num in "${PR_NUMBERS[@]}"; do
  echo "  - #$pr_num"
done
echo ""

# Ask for confirmation
if [ "$DRY_RUN" = true ]; then
  echo -e "${YELLOW}📋 DRY RUN MODE - No actions will be taken${NC}"
  echo ""
  echo "To execute these closures, run:"
  echo "  $0 --confirm"
  echo ""
  
  # Log dry run
  echo "$(date) - DRY RUN - Would close PRs: ${PR_NUMBERS[*]}" >> "$PR_LOG"
  
  echo "✅ Dry run complete. Review actions above."
  exit 0
fi

if [ "$CONFIRM" = false ]; then
  echo -e "${YELLOW}⚠️  Confirm mode not enabled. Add --confirm flag to execute.${NC}"
  echo ""
  echo "Re-run with: $0 --confirm"
  exit 1
fi

# Final confirmation
echo -e "${RED}⚠️  WARNING: This will close ${#PR_NUMBERS[@]} PRs!${NC}"
echo ""
read -p "Type 'yes' to confirm: " FINAL_CONFIRM

if [ "$FINAL_CONFIRM" != "yes" ]; then
  echo "Cancelled by user."
  exit 0
fi

echo ""
echo -e "${BLUE}Processing PR closures...${NC}"
echo ""

# Close PRs
SUCCESS_COUNT=0
FAILED_COUNT=0

for pr_num in "${PR_NUMBERS[@]}"; do
  echo -n "Closing #$pr_num... "
  
  # Get canonical PR (newest in group)
  CANONICAL_PR=$(echo "$DUPLICATE_GROUPS" | jq -r "[.[] | select(.prs[].number | tostring | contains(\"$pr_num\"))][0].prs[0].number")
  
  # Generate closure comment
  CLOSURE_COMMENT="This PR is being closed as a duplicate of #${CANONICAL_PR}.

To help consolidate the 50+ open PRs in this repository, we are identifying and closing duplicate PRs. The canonical PR for this issue is #${CANONICAL_PR}.

## Why is this being closed?

1. **Duplicate Work**: Multiple PRs exist for the same issue
2. **Canonical Selection**: PR #${CANONICAL_PR} was chosen as the canonical implementation based on:
   - Recency (most recent activity)
   - Completeness of implementation
   - Test coverage

## Next Steps

If you believe this PR should remain open, please:
1. Comment on this PR explaining why it should remain open
2. Review the canonical PR #${CANONICAL_PR} and contribute there instead
3. Reference this PR in a comment explaining the differences

## References

- Issue #572: PR consolidation and cleanup
- Canonical PR: #${CANONICAL_PR}
- Maintainer decision on: $(date)

Thank you for your contribution! 🙏"

  # Close PR with comment
  if gh pr close "$pr_num" --comment "$CLOSURE_COMMENT" --delete-branch; then
    echo -e "${GREEN}✓${NC}"
    SUCCESS_COUNT=$((SUCCESS_COUNT + 1))
    
    # Log action
    echo "$(date) - CLOSED #$pr_num - Duplicate of #$CANONICAL_PR" >> "$PR_LOG"
  else
    echo -e "${RED}✗${NC}"
    FAILED_COUNT=$((FAILED_COUNT + 1))
    
    # Log failure
    echo "$(date) - FAILED to close #$pr_num" >> "$PR_LOG"
  fi
  
  # Rate limiting delay (GitHub API limit)
  sleep 1
done

echo ""
echo -e "${BLUE}📊 Summary${NC}"
echo "  PRs closed successfully: $SUCCESS_COUNT"
echo "  PRs failed to close: $FAILED_COUNT"
echo ""

if [ $FAILED_COUNT -gt 0 ]; then
  echo -e "${RED}⚠️  Some PRs failed to close. Check $PR_LOG for details.${NC}"
  exit 1
fi

echo -e "${GREEN}✅ All PRs closed successfully!${NC}"
echo ""
echo "Audit log saved to: $PR_LOG"
echo ""
echo "Next steps:"
echo "  1. Review canonical PRs and merge when ready"
echo "  2. Update related issues with PR status"
echo "  3. Monitor for new duplicate PRs"