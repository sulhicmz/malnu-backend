#!/bin/bash
#
# GitHub Workflow Permission Hardening Script
#
# This script applies security hardening to GitHub workflow files by:
# 1. Reducing excessive permissions to minimum required
# 2. Removing duplicate job-level permissions
# 3. Adding security documentation comments
#
# Usage: ./scripts/apply-workflow-permission-hardening.sh
#

set -euo pipefail

# Color output
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
RED='\033[0;31m'
NC='\033[0m' # No Color

# Workflow files to update
WORKFLOW_DIR=".github/workflows"
declare -A WORKFLOWS=(
  ["oc- researcher.yml"]="contents:read, pull-requests:write, issues:write"
  ["oc-maintainer.yml"]="contents:write, pull-requests:write, issues:write"
  ["oc-cf-supabase.yml"]="contents:write, deployments:write"
  ["oc-issue-solver.yml"]="contents:write, pull-requests:write, issues:write"
  ["oc-pr-handler.yml"]="contents:read, pull-requests:write, actions:read"
  ["oc-problem-finder.yml"]="contents:read, issues:write"
)

echo -e "${GREEN}=== GitHub Workflow Permission Hardening ===${NC}"
echo ""
echo "This script will apply security hardening to workflow files."
echo ""

# Check if we're in a git repository
if ! git rev-parse --git-dir > /dev/null 2>&1; then
  echo -e "${RED}Error: Not in a git repository${NC}"
  exit 1
fi

# Check for uncommitted changes
if ! git diff-index --quiet HEAD --; then
  echo -e "${YELLOW}Warning: You have uncommitted changes.${NC}"
  echo "Please commit or stash them before running this script."
  exit 1
fi

# Create backup branch
BACKUP_BRANCH="backup/workflow-permissions-$(date +%Y%m%d-%H%M%S)"
echo -e "${YELLOW}Creating backup branch: $BACKUP_BRANCH${NC}"
git branch "$BACKUP_BRANCH"

echo ""
echo -e "${GREEN}Updating workflow files...${NC}"
echo ""

# Counters
FILES_UPDATED=0
PERMISSIONS_REMOVED=0

# Process each workflow file
for file in "${!WORKFLOWS[@]}"; do
  filepath="$WORKFLOW_DIR/$file"

  if [[ ! -f "$filepath" ]]; then
    echo -e "${YELLOW}Warning: $file not found, skipping${NC}"
    continue
  fi

  echo "Processing: $file"

  # Apply changes using sed
  # Different workflows require different permission sets based on their function

  # For files with comment pattern
  case "$file" in
    "oc- researcher.yml"|"oc-maintainer.yml"|"oc-issue-solver.yml"|"oc-pr-handler.yml"|"oc-problem-finder.yml")
      # Remove id-token, actions, deployments, packages, pages, security-events permissions
      # These are not needed for issue/PR management workflows
      sed -i \
        -e 's/^permissions:$/# Minimum required permissions\npermissions:/' \
        -e '/id-token: write$/d' \
        -e '/actions: write$/d' \
        -e '/deployments: write$/d' \
        -e '/packages: write$/d' \
        -e '/pages: write$/d' \
        -e '/security-events: write$/d' \
        "$filepath"
      ;;

    "oc-cf-supabase.yml")
      sed -i \
        -e 's/^permissions:$/# Minimum required permissions for Cloudflare deployment\npermissions:/' \
        -e '/packages: write$/d' \
        -e '/id-token: write$/d' \
        "$filepath"
      ;;

    *)
      # Remove job-level permissions block entirely
      # Jobs will inherit from top-level permissions
      sed -i \
        -e '/^    permissions:$/,/^      security-events: write$/d' \
        "$filepath"
      ;;
  esac

  # Remove duplicate job-level permissions block (8-10 lines)
  # This pattern matches the entire job-level permissions block
  sed -i \
    -e '/^    permissions:$/,/^      security-events: write$/d' \
    "$filepath"

  FILES_UPDATED=$((FILES_UPDATED + 1))
  PERMISSIONS_REMOVED=$((PERMISSIONS_REMOVED + 1))

  # Add inheritance comment after job declaration
  sed -i \
    -e '/^    timeout-minutes: 40$/a\\n    # Inherits permissions from top-level' \
    "$filepath"

  echo -e "  ${GREEN}✓ Updated${NC}"
done

echo ""
echo -e "${GREEN}Summary:${NC}"
echo "  Files updated: $FILES_UPDATED"
echo "  Permissions blocks removed: $PERMISSIONS_REMOVED"
echo ""

# Show changes
echo -e "${YELLOW}Changes to be committed:${NC}"
git diff --stat "$WORKFLOW_DIR/"

echo ""
echo -e "${GREEN}Next steps:${NC}"
echo "1. Review the changes above"
echo "2. Commit: git add .github/workflows/ && git commit -m 'security: Apply workflow permission hardening'"
echo "3. Push to your fork"
echo "4. Create a pull request"
echo ""
echo -e "${YELLOW}To restore: git checkout $BACKUP_BRANCH${NC}"
