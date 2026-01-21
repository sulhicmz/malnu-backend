# GitHub Projects for Malnu Backend

## Overview

This document describes the GitHub Projects structure for the Malnu Backend School Management System repository. Projects are used to organize work by business domain, track progress, and coordinate efforts across teams and contributors.

## Project Structure

### Project 1: Core Infrastructure

**Description**: Foundation systems, platform capabilities, and technical infrastructure

**Purpose**: Track all infrastructure-level improvements including caching, CI/CD, monitoring, security, and performance optimizations

**Labels**:
- `infrastructure`
- `database`
- `security`
- `performance`
- `ci-cd`
- `monitoring`

**Columns**:
- Backlog
- To Do
- In Progress
- In Review
- Done

**Example Issues**:
- #224: Implement Redis caching strategy
- #134: Fix CI/CD pipeline and add automated testing
- #227: Implement application monitoring and observability
- #254: Implement comprehensive error handling and logging strategy

---

### Project 2: School Management

**Description**: Student, teacher, class, and school administration systems

**Purpose**: Manage all core school administration functionality including student information, teacher records, class management, and school operations

**Labels**:
- `school-management`
- `students`
- `teachers`
- `classes`
- `subjects`
- `schedules`

**Columns**:
- Backlog
- To Do
- In Progress
- In Review
- Done

**Example Issues**:
- #229: Implement comprehensive Student Information System (SIS)
- Class management and scheduling
- Teacher record management
- Subject management

---

### Project 3: Grading & Assessment

**Description**: Academic assessment and reporting systems

**Purpose**: Manage grading, assessments, exams, report cards, transcripts, and academic analytics

**Labels**:
- `grading`
- `assessment`
- `exams`
- `reports`
- `transcripts`

**Columns**:
- Backlog
- To Do
- In Progress
- In Review
- Done

**Example Issues**:
- #231: Implement comprehensive assessment and examination management system
- #259: Implement comprehensive report card and transcript generation system
- Grade management
- Assessment creation and delivery

---

### Project 4: E-Learning & Digital Resources

**Description**: Online learning and digital content management

**Purpose**: Manage virtual classes, learning materials, assignments, quizzes, and digital library resources

**Labels**:
- `e-learning`
- `digital-library`
- `learning-materials`
- `assignments`
- `quizzes`
- `virtual-classes`

**Columns**:
- Backlog
- To Do
- In Progress
- In Review
- Done

**Example Issues**:
- #264: Implement comprehensive library and digital resource management system
- Virtual class management
- Learning material creation and delivery
- Assignment and quiz management

---

### Project 5: Attendance & Leave Management

**Description**: Student and staff attendance tracking

**Purpose**: Manage daily student attendance, staff attendance, leave requests, and substitute management

**Labels**:
- `attendance`
- `leave`
- `staff-attendance`
- `substitutes`

**Columns**:
- Backlog
- To Do
- In Progress
- In Review
- Done

**Example Issues**:
- #108: Add comprehensive leave management and staff attendance system
- Daily student attendance tracking
- Leave request workflows
- Substitute teacher management

---

### Project 6: Communication & Notification

**Description**: Messaging, announcements, and alert systems

**Purpose**: Manage internal communication, parent notifications, announcements, and multi-channel alerts

**Labels**:
- `communication`
- `notification`
- `messaging`
- `announcements`
- `parent-portal`

**Columns**:
- Backlog
- To Do
- In Progress
- In Review
- Done

**Example Issues**:
- #201: Implement comprehensive communication and messaging system
- #232: Implement comprehensive parent engagement and communication portal
- Multi-channel notification delivery
- School-wide announcements

---

### Project 7: Finance & Monetization

**Description**: Financial operations and fee management

**Purpose**: Manage fee structures, invoicing, payments, transactions, and financial reporting

**Labels**:
- `finance`
- `monetization`
- `fees`
- `billing`
- `payments`
- `transactions`

**Columns**:
- Backlog
- To Do
- In Progress
- In Review
- Done

**Example Issues**:
- #200: Implement comprehensive fee management and billing system
- Fee structure configuration
- Payment processing
- Financial reporting

---

### Project 8: Health & Medical Records

**Description**: Student health management and medical records

**Purpose**: Manage student medical records, immunization tracking, medications, health screenings, and incident reporting

**Labels**:
- `health`
- `medical-records`
- `immunizations`
- `medications`

**Columns**:
- Backlog
- To Do
- In Progress
- In Review
- Done

**Example Issues**:
- #59: Add student health and medical records management system
- #161: Add comprehensive health and medical records management system
- Immunization tracking
- Health incident reporting

---

### Project 9: Alumni Network

**Description**: Alumni profiles, career tracking, and engagement

**Purpose**: Manage alumni profiles, career progression, mentorship programs, alumni events, and donation tracking

**Labels**:
- `alumni`
- `career-tracking`
- `mentorship`
- `donations`
- `alumni-events`

**Columns**:
- Backlog
- To Do
- In Progress
- In Review
- Done

**Example Issues**:
- #262: Implement comprehensive alumni network and tracking system
- Alumni directory
- Career tracking
- Mentorship programs

---

### Project 10: Analytics & Reporting

**Description**: Data analytics, dashboards, and reporting

**Purpose**: Provide learning analytics, performance insights, early warning systems, and comprehensive reporting

**Labels**:
- `analytics`
- `reporting`
- `dashboards`
- `learning-analytics`
- `performance-analytics`

**Columns**:
- Backlog
- To Do
- In Progress
- In Review
- Done

**Example Issues**:
- #61: Add advanced analytics and reporting dashboard
- #179: Implement advanced learning analytics and student performance insights
- Student performance analytics
- Early warning systems

---

### Project 11: Compliance & Governance

**Description**: Regulatory compliance, accreditation management, and school governance

**Purpose**: Manage compliance requirements, accreditation standards, policies, procedures, and institutional governance

**Labels**:
- `compliance`
- `governance`
- `accreditation`
- `policies`
- `auditing`

**Columns**:
- Backlog
- To Do
- In Progress
- In Review
- Done

**Example Issues**:
- #181: Implement comprehensive compliance and regulatory reporting system
- #233: Implement comprehensive school administration and governance module
- Regulatory requirement tracking
- Accreditation management

---

## Setup Instructions

### Option 1: Manual Setup via GitHub Web UI

1. Navigate to: https://github.com/sulhicmz/malnu-backend/projects
2. Click "New Project"
3. For each project listed above:
   - Name: Use the project name
   - Description: Use the project description
   - Columns: Create the standard columns (Backlog, To Do, In Progress, In Review, Done)
4. Add relevant labels to project for automatic organization
5. Set visibility to Public for community transparency
6. Save the project

### Option 2: Automated Setup via GitHub CLI

**Prerequisites**: Install GitHub CLI if not already installed
```bash
# On macOS
brew install gh

# On Linux
# Download from https://cli.github.com/

# On Windows
# Download from https://cli.github.com/
```

**Create Projects Script**:
```bash
#!/bin/bash

# Create GitHub Projects for Malnu Backend
# This script creates all projects listed in GITHUB_PROJECTS.md

echo "Creating GitHub Projects for Malnu Backend..."

# Project 1: Core Infrastructure
gh project create \
  --title "Core Infrastructure" \
  --body "Foundation systems, platform capabilities, and technical infrastructure" \
  --repo sulhicmz/malnu-backend

# Project 2: School Management
gh project create \
  --title "School Management" \
  --body "Student, teacher, class, and school administration systems" \
  --repo sulhicmz/malnu-backend

# Project 3: Grading & Assessment
gh project create \
  --title "Grading & Assessment" \
  --body "Academic assessment and reporting systems" \
  --repo sulhicmz/malnu-backend

# Project 4: E-Learning & Digital Resources
gh project create \
  --title "E-Learning & Digital Resources" \
  --body "Online learning and digital content management" \
  --repo sulhicmz/malnu-backend

# Project 5: Attendance & Leave Management
gh project create \
  --title "Attendance & Leave Management" \
  --body "Student and staff attendance tracking" \
  --repo sulhicmz/malnu-backend

# Project 6: Communication & Notification
gh project create \
  --title "Communication & Notification" \
  --body "Messaging, announcements, and alert systems" \
  --repo sulhicmz/malnu-backend

# Project 7: Finance & Monetization
gh project create \
  --title "Finance & Monetization" \
  --body "Financial operations and fee management" \
  --repo sulhicmz/malnu-backend

# Project 8: Health & Medical Records
gh project create \
  --title "Health & Medical Records" \
  --body "Student health management and medical records" \
  --repo sulhicmz/malnu-backend

# Project 9: Alumni Network
gh project create \
  --title "Alumni Network" \
  --body "Alumni profiles, career tracking, and engagement" \
  --repo sulhicmz/malnu-backend

# Project 10: Analytics & Reporting
gh project create \
  --title "Analytics & Reporting" \
  --body "Data analytics, dashboards, and reporting" \
  --repo sulhicmz/malnu-backend

# Project 11: Compliance & Governance
gh project create \
  --title "Compliance & Governance" \
  --body "Regulatory compliance, accreditation management, and school governance" \
  --repo sulhicmz/malnu-backend

echo "GitHub Projects created successfully!"
```

Save the script as `scripts/setup-github-projects.sh` and run:
```bash
chmod +x scripts/setup-github-projects.sh
./scripts/setup-github-projects.sh
```

**Note**: GitHub CLI project creation may require additional permissions. If `gh project create` command is not available, use the web UI method instead.

## Adding Issues to Projects

### Manual Addition

1. Open an issue
2. Click the "Projects" dropdown in the top navigation
3. Select the appropriate project
4. Click "Add cards" button
5. Select issues to add to the project
6. Save changes

### Automatic Organization via Labels

Issues are automatically organized into projects based on their labels. When an issue is created or updated:

1. The issue's labels are matched against project label mappings
2. The issue is automatically added to the appropriate project
3. If an issue has multiple labels, it can be added to multiple projects (primary project based on first matching label)

**Label to Project Mapping**:
- `infrastructure`, `database`, `security`, `performance`, `ci-cd`, `monitoring` → Core Infrastructure
- `school-management`, `students`, `teachers`, `classes`, `subjects`, `schedules` → School Management
- `grading`, `assessment`, `exams`, `reports`, `transcripts` → Grading & Assessment
- `e-learning`, `digital-library`, `learning-materials`, `assignments`, `quizzes`, `virtual-classes` → E-Learning & Digital Resources
- `attendance`, `leave`, `staff-attendance`, `substitutes` → Attendance & Leave Management
- `communication`, `notification`, `messaging`, `announcements`, `parent-portal` → Communication & Notification
- `finance`, `monetization`, `fees`, `billing`, `payments`, `transactions` → Finance & Monetization
- `health`, `medical-records`, `immunizations`, `medications` → Health & Medical Records
- `alumni`, `career-tracking`, `mentorship`, `donations`, `alumni-events` → Alumni Network
- `analytics`, `reporting`, `dashboards`, `learning-analytics`, `performance-analytics` → Analytics & Reporting
- `compliance`, `governance`, `accreditation`, `policies`, `auditing` → Compliance & Governance

## Maintaining Projects

### Regular Maintenance Tasks

**Weekly**:
- Review project status and move completed items to Done
- Update project progress in relevant issues
- Ensure new issues are properly labeled and added to projects

**Monthly**:
- Review project descriptions and update if scope changes
- Evaluate project effectiveness and restructure if needed
- Review labels and ensure they're being used consistently
- Archive completed projects or merge related projects

**Quarterly**:
- Comprehensive review of all projects
- Update GITHUB_PROJECTS.md documentation
- Gather feedback from contributors on project organization
- Adjust project structure based on changing priorities

### Adding Labels to Issues

When creating new issues:
1. Apply relevant labels from the list above
2. Issue will be automatically sorted into appropriate project
3. If unsure which project, use the primary business domain label

Best practices for labeling:
- Use specific labels over generic ones (e.g., `database` instead of `enhancement`)
- Combine priority labels (`high-priority`, `medium-priority`, `low-priority`) with domain labels
- Use type labels (`bug`, `feature`, `enhancement`, `refactor`, `documentation`)

### Project Cleanup

When a project is completed:
1. Move all remaining issues to other appropriate projects
2. Close the project by moving all cards to Done
3. Archive the project to maintain a clean project board

## Integration with Repository

### ISSUE_TEMPLATE Updates

Consider updating issue templates to encourage proper labeling:
```markdown
---
**Labels**
Select labels for this issue:

**Domain/Module** (select all that apply):
- [ ] school-management
- [ ] grading
- [ ] assessment
- [ ] e-learning
- [ ] digital-library
- [ ] attendance
- [ ] leave
- [ ] communication
- [ ] notification
- [ ] finance
- [ ] monetization
- [ ] health
- [ ] alumni
- [ ] analytics
- [ ] compliance
- [ ] infrastructure
- [ ] database
- [ ] security

**Priority**:
- [ ] high-priority
- [ ] medium-priority
- [ ] low-priority

**Type**:
- [ ] bug
- [ ] feature
- [ ] enhancement
- [ ] refactor
- [ ] documentation
- [ ] testing
```

### CI/CD Integration

Add a GitHub Actions workflow that automatically organizes issues into projects based on labels:

```yaml
name: Organize Issues into Projects

on:
  issues:
    types: [labeled]
  issue_comment:
    types: [created, edited]

jobs:
  organize-issues:
    runs-on: ubuntu-latest
    steps:
      - name: Auto-organize issues
        env:
          GITHUB_TOKEN: ${{ secrets.GITHUB_TOKEN }}
        run: |
          # Script to add issues to appropriate projects based on labels
          echo "Issues are automatically organized via labels - no action needed"
```

**Note**: GitHub doesn't currently support adding issues to projects via API in the same way as manual addition. This workflow is for documentation purposes only.

## Benefits of GitHub Projects

### For Contributors
- **Clear visibility** into what's being worked on across the repository
- **Better prioritization** with visual project boards
- **Reduced context switching** with organized work areas
- **Improved collaboration** with shared project understanding
- **Easier onboarding** for new contributors

### For Maintainers
- **Better tracking** of progress across business domains
- **Identified dependencies** between projects
- **Resource allocation** based on project needs
- **Performance metrics** per business domain
- **Strategic planning** based on project status

### For Stakeholders
- **Transparency** into what's being worked on
- **Progress tracking** across all business domains
- **Issue prioritization** visibility
- **Delivery timeline** understanding

## Troubleshooting

### Issue Not Appearing in Project

**Possible Causes**:
1. Missing or incorrect labels
2. Label not mapped to a project
3. Issue filter settings on project

**Solutions**:
1. Verify issue has appropriate labels
2. Check label mappings in this document
3. Check project settings for label filters

### Project Not Visible

**Possible Causes**:
1. Project set to private
2. User doesn't have read permissions

**Solutions**:
1. Set project visibility to Public
2. Ensure team members have appropriate permissions

### GitHub CLI Commands Not Working

**Possible Causes**:
1. GitHub CLI not installed
2. Not authenticated to GitHub
3. GitHub CLI version too old
4. Repository permissions issue

**Solutions**:
1. Verify GitHub CLI installation: `gh --version`
2. Authenticate: `gh auth login`
3. Update GitHub CLI: `gh upgrade`
4. Check repository permissions

## References

- [GitHub Projects Documentation](https://docs.github.com/en/projects)
- [GitHub CLI Documentation](https://cli.github.com/manual/)
- [Issue Management Best Practices](https://docs.github.com/en/issues)
- [Label Management](https://docs.github.com/en/issues/managing-your-workflow-with-issues/labels)
