#!/bin/bash

# Create GitHub Projects for Malnu Backend
# This script creates all projects listed in GITHUB_PROJECTS.md

set -e

echo "Creating GitHub Projects for Malnu Backend..."

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

# Repository name
REPO="sulhicmz/malnu-backend"

# Create Project 1: Core Infrastructure
echo "Creating project: Core Infrastructure..."
gh project create \
  --title "Core Infrastructure" \
  --body "Foundation systems, platform capabilities, and technical infrastructure" \
  --repo "$REPO" || echo "Warning: Core Infrastructure project may already exist"

# Create Project 2: School Management
echo "Creating project: School Management..."
gh project create \
  --title "School Management" \
  --body "Student, teacher, class, and school administration systems" \
  --repo "$REPO" || echo "Warning: School Management project may already exist"

# Create Project 3: Grading & Assessment
echo "Creating project: Grading & Assessment..."
gh project create \
  --title "Grading & Assessment" \
  --body "Academic assessment and reporting systems" \
  --repo "$REPO" || echo "Warning: Grading & Assessment project may already exist"

# Create Project 4: E-Learning & Digital Resources
echo "Creating project: E-Learning & Digital Resources..."
gh project create \
  --title "E-Learning & Digital Resources" \
  --body "Online learning and digital content management" \
  --repo "$REPO" || echo "Warning: E-Learning & Digital Resources project may already exist"

# Create Project 5: Attendance & Leave Management
echo "Creating project: Attendance & Leave Management..."
gh project create \
  --title "Attendance & Leave Management" \
  --body "Student and staff attendance tracking" \
  --repo "$REPO" || echo "Warning: Attendance & Leave Management project may already exist"

# Create Project 6: Communication & Notification
echo "Creating project: Communication & Notification..."
gh project create \
  --title "Communication & Notification" \
  --body "Messaging, announcements, and alert systems" \
  --repo "$REPO" || echo "Warning: Communication & Notification project may already exist"

# Create Project 7: Finance & Monetization
echo "Creating project: Finance & Monetization..."
gh project create \
  --title "Finance & Monetization" \
  --body "Financial operations and fee management" \
  --repo "$REPO" || echo "Warning: Finance & Monetization project may already exist"

# Create Project 8: Health & Medical Records
echo "Creating project: Health & Medical Records..."
gh project create \
  --title "Health & Medical Records" \
  --body "Student health management and medical records" \
  --repo "$REPO" || echo "Warning: Health & Medical Records project may already exist"

# Create Project 9: Alumni Network
echo "Creating project: Alumni Network..."
gh project create \
  --title "Alumni Network" \
  --body "Alumni profiles, career tracking, and engagement" \
  --repo "$REPO" || echo "Warning: Alumni Network project may already exist"

# Create Project 10: Analytics & Reporting
echo "Creating project: Analytics & Reporting..."
gh project create \
  --title "Analytics & Reporting" \
  --body "Data analytics, dashboards, and reporting" \
  --repo "$REPO" || echo "Warning: Analytics & Reporting project may already exist"

# Create Project 11: Compliance & Governance
echo "Creating project: Compliance & Governance..."
gh project create \
  --title "Compliance & Governance" \
  --body "Regulatory compliance, accreditation management, and school governance" \
  --repo "$REPO" || echo "Warning: Compliance & Governance project may already exist"

echo ""
echo "GitHub Projects created successfully!"
echo ""
echo "Next steps:"
echo "1. Go to https://github.com/sulhicmz/malnu-backend/projects"
echo "2. Verify all projects are created"
echo "3. Add standard columns to each project: Backlog, To Do, In Progress, In Review, Done"
echo "4. Add relevant labels to each project for automatic organization"
echo "5. Start adding issues to projects"
echo ""
echo "For detailed information, see: docs/GITHUB_PROJECTS.md"
