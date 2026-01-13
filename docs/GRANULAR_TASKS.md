# Granular Actionable Tasks - January 13, 2026

This document provides detailed, granular tasks for resolving identified issues and completing roadmap items.

---

## Issue #446: HIGH - Fix Database Services Disabled in Docker Compose

### Task Breakdown

#### Task 446.1: Uncomment MySQL Database Service
**File**: `docker-compose.yml:50-61`
**Effort**: 15 minutes
**Acceptance Criteria**:
- [ ] MySQL service block uncommented
- [ ] MySQL image specified (mysql:8.0)
- [ ] Port 3306 exposed
- [ ] Volume `dbdata` defined
- [ ] Volume mounted to `/var/lib/mysql`

#### Task 446.2: Configure MySQL Environment Variables
**File**: `docker-compose.yml:55-60`
**Effort**: 20 minutes
**Acceptance Criteria**:
- [ ] `MYSQL_ROOT_PASSWORD` uses environment variable (not hardcoded)
- [ ] `MYSQL_DATABASE` uses environment variable
- [ ] `MYSQL_USER` uses environment variable
- [ ] `MYSQL_PASSWORD` uses environment variable
- [ ] Comment added warning about production values

#### Task 446.3: Add Database Dependency to App Service
**File**: `docker-compose.yml:42-48`
**Effort**: 10 minutes
**Acceptance Criteria**:
- [ ] Uncomment `depends_on:` section
- [ ] Add `db` to depends_on list
- [ ] App service waits for database to be healthy

#### Task 446.4: Uncomment Volume Definition
**File**: `docker-compose.yml:89-92`
**Effort**: 5 minutes
**Acceptance Criteria**:
- [ ] `dbdata:` volume uncommented
- [ ] Volume defined in volumes section

#### Task 446.5: Update .env.example for Docker
**File**: `.env.example:13-18`
**Effort**: 15 minutes
**Acceptance Criteria**:
- [ ] `DB_HOST` set to `db` (Docker service name)
- [ ] `DB_PORT` set to `3306`
- [ ] `DB_DATABASE` set to `hypervel`
- [ ] `DB_USERNAME` set to `hypervel`
- [ ] `DB_PASSWORD` set to placeholder with warning
- [ ] Comment added about Docker vs local development

#### Task 446.6: Test Database Connectivity
**Command**: `docker-compose up db && docker-compose exec db mysql -u hyperf -p`
**Effort**: 30 minutes
**Acceptance Criteria**:
- [ ] Database container starts successfully
- [ ] Database connection established from app container
- [ ] Database migrations run successfully
- [ ] Seeders execute without errors
- [ ] Application connects to database

**Total Estimated Time**: 1.5-2 hours

---

## Issue #447: HIGH - Fix JWT_SECRET Placeholder in .env.example

### Task Breakdown

#### Task 447.1: Clear JWT_SECRET Value
**File**: `.env.example:66`
**Effort**: 5 minutes
**Acceptance Criteria**:
- [ ] `JWT_SECRET=` is empty (no value after equals sign)
- [ ] No placeholder text remains

#### Task 447.2: Add Warning Comments
**File**: `.env.example:64-69`
**Effort**: 10 minutes
**Acceptance Criteria**:
- [ ] Warning comment added above JWT_SECRET
- [ ] Command to generate secure key documented: `openssl rand -hex 32`
- [ ] Warning about not using placeholder values
- [ ] Warning about production security

**Example**:
```env
# JWT Authentication Configuration
# Generate a secure JWT secret using: openssl rand -hex 32
# WARNING: NEVER use placeholder values in production!
# Always generate a unique, random secret for each environment.
JWT_SECRET=
```

#### Task 447.3: Update JWTService Startup Validation
**File**: `app/Services/JWTService.php`
**Effort**: 20 minutes
**Acceptance Criteria**:
- [ ] Add validation check in constructor
- [ ] Reject empty JWT_SECRET values
- [ ] Reject default/placeholder values (e.g., "your-secret-key-here", "changeme")
- [ ] Throw descriptive exception on invalid JWT_SECRET
- [ ] Log warning if using default value

**Example**:
```php
public function __construct()
{
    $secret = config('jwt.secret', '');
    
    if (empty($secret)) {
        throw new \RuntimeException('JWT_SECRET is empty. Please generate a secure secret using: openssl rand -hex 32');
    }
    
    if (in_array(strtolower($secret), ['your-secret-key-here', 'changeme', 'secret'])) {
        throw new \RuntimeException('JWT_SECRET is using a default value. Please generate a secure secret using: openssl rand -hex 32');
    }
    
    $this->secret = $secret;
    // ... rest of constructor
}
```

#### Task 447.4: Test JWT Secret Validation
**Test File**: Create `tests/Unit/JWTServiceSecretValidationTest.php`
**Effort**: 15 minutes
**Acceptance Criteria**:
- [ ] Test that empty secret throws exception
- [ ] Test that default values throw exception
- [ ] Test that valid secret does not throw exception
- [ ] Test passes in CI/CD

**Total Estimated Time**: 50 minutes (less than 1 hour)

---

## Issue #448: MEDIUM - Update Outdated Documentation

### Task Breakdown

#### Task 448.1: Update APPLICATION_STATUS.md
**File**: `docs/APPLICATION_STATUS.md`
**Effort**: 45 minutes
**Acceptance Criteria**:
- [ ] Update Critical Blockers section (lines 103-111) to remove resolved issues:
  - Remove "RoleMiddleware Always Returns True" (FIXED)
  - Remove "CSRF Not Working" (FIXED)
  - Update status to show these are resolved
- [ ] Update System Health Score (line 32) from 6.5/10 to 8.0/10
- [ ] Update Component Scores table (lines 21-31):
  - Architecture: 75 → 9.5/10
  - Code Quality: 5.0 → 8.5/10
  - Security Config: 40 → 7.75/10
  - Authentication: 40 → 9.0/10
- [ ] Update Critical Issues Summary (lines 96-125) to show actual remaining issues
- [ ] Add new issues #446, #447, #448 to appropriate sections
- [ ] Remove references to MD5, weak passwords, RBAC bypass (all fixed)
- [ ] Update "Recent Progress" section (lines 8-15) to show resolved issues
- [ ] Update "System Health Assessment" (line 208) to 8.0/10
- [ ] Update "Conclusion" section to reflect B grade status

#### Task 448.2: Update ORCHESTRATOR_ANALYSIS_REPORT_v3.md
**File**: `docs/ORCHESTRATOR_ANALYSIS_REPORT_v3.md`
**Effort**: 30 minutes
**Acceptance Criteria**:
- [ ] Add disclaimer at top: "This report is superseded by ORCHESTRATOR_ANALYSIS_REPORT_v4.md"
- [ ] Update Critical Issues Summary to reflect resolved state
- [ ] Update System Health Score to 8.0/10
- [ ] Cross-reference v4 report at end

**Add at top of file**:
```markdown
> **NOTE**: This report has been superseded by [ORCHESTRATOR_ANALYSIS_REPORT_v4.md](ORCHESTRATOR_ANALYSIS_REPORT_v4.md)
> with updated findings as of January 13, 2026.
```

#### Task 448.3: Update ROADMAP.md
**File**: `docs/ROADMAP.md`
**Effort**: 60 minutes
**Acceptance Criteria**:
- [ ] Remove fixed issues from Critical Issues lists (lines 56-65):
  - Remove #281 (Auth fixed)
  - Remove #282 (SecurityHeaders fixed)
  - Remove #347 (MD5 → SHA-256 fixed)
  - Remove #348 (Password reset fixed)
  - Remove #359 (CSRF fixed)
  - Remove #360 (RBAC fixed)
- [ ] Add new issues #446, #447, #448 to appropriate sections
- [ ] Update Current Repository Status table:
  - System Status: "CRITICAL (0/100)" → "POOR (80/100)"
  - Security Config: "Partial (75/100)" → "Good (78/100)"
  - Authentication: "Critical (0/100)" → "Excellent (90/100)"
- [ ] Update Phase 1 tasks to reflect actual current state
- [ ] Update Success Metrics tables with current targets
- [ ] Remove references to resolved issues from dependencies

#### Task 448.4: Cross-Reference All Documentation Files
**Files**: All files in `docs/` directory
**Effort**: 45 minutes
**Acceptance Criteria**:
- [ ] Search all docs for references to fixed issues (#281, #282, #347, #348, #359, #360)
- [ ] Update references to point to v4 analysis report
- [ ] Update ORCHESTRATOR_ANALYSIS_REPORT.md with link to v4
- [ ] Update INDEX.md to reference v4 report
- [ ] Ensure consistency across all documentation files

#### Task 448.5: Create SUMMARY.md for Quick Reference
**File**: `docs/SUMMARY_v4.md` (new file)
**Effort**: 30 minutes
**Acceptance Criteria**:
- [ ] Create executive summary of v4 analysis
- [ ] List key achievements (8 major issues resolved)
- [ ] List remaining issues (3 HIGH, 3 MEDIUM)
- [ ] Provide system health score (8.0/10)
- [ ] List immediate next steps
- [ ] Include link to full v4 report

**Content Structure**:
```markdown
# Repository Summary - January 13, 2026

## Quick Stats
- System Health: 8.0/10 (B Grade)
- Total Issues: 450+ (124 closed, 326 open)
- New Issues: 3 (#446, #447, #448)

## Key Achievements
✅ SHA-256 hashing (was MD5)
✅ Complex password validation
✅ Proper RBAC authorization
✅ No DI violations
✅ No $_ENV access
✅ CSRF protection
✅ No code smells

## Remaining Issues
🔴 HIGH: Database services disabled (#446)
🔴 HIGH: JWT_SECRET placeholder (#447)
🟡 MEDIUM: Documentation outdated (#448)
🟡 MEDIUM: Low test coverage
🟡 MEDIUM: Incomplete API implementation

## Next Steps
1. Fix database services (2-3 hours)
2. Fix JWT_SECRET placeholder (1 hour)
3. Update documentation (2-3 hours)

**See**: [ORCHESTRATOR_ANALYSIS_REPORT_v4.md](ORCHESTRATOR_ANALYSIS_REPORT_v4.md) for full details
```

**Total Estimated Time**: 3.5 hours

---

## Test Coverage Improvement Tasks

### Task: Increase Test Coverage from 25% to 40%

#### Task T1.1: Add Service Unit Tests
**Files to Create** (estimated 7 files):
- `tests/Unit/CalendarServiceTest.php`
- `tests/Unit/LeaveManagementServiceTest.php`
- `tests/Unit/RolePermissionServiceTest.php`
- `tests/Unit/FileUploadServiceTest.php`
- `tests/Unit/BackupServiceTest.php`
- `tests/Unit/EmailServiceTest.php`

**Effort per file**: 2-3 hours
**Total Effort**: 14-21 hours

**Acceptance Criteria per Test File**:
- [ ] Test all public methods
- [ ] Test happy path and error cases
- [ ] Test edge cases
- [ ] Mock dependencies appropriately
- [ ] Assert on both success and failure outcomes

#### Task T1.2: Add Controller Feature Tests
**Files to Create** (estimated 13 files):
- `tests/Feature/Api/StudentControllerTest.php`
- `tests/Feature/Api/TeacherControllerTest.php`
- `tests/Feature/Api/InventoryControllerTest.php`
- `tests/Feature/Api/CalendarControllerTest.php`
- `tests/Feature/Attendance/StaffAttendanceControllerTest.php`
- `tests/Feature/Attendance/LeaveRequestControllerTest.php`
- `tests/Feature/Attendance/LeaveTypeControllerTest.php`
- Plus 7 more for existing controllers

**Effort per file**: 2-3 hours
**Total Effort**: 26-39 hours

**Acceptance Criteria per Test File**:
- [ ] Test all API endpoints
- [ ] Test authentication required
- [ ] Test authorization (roles)
- [ ] Test validation errors
- [ ] Test successful responses
- [ ] Test 404, 401, 403, 422, 500 status codes

#### Task T1.3: Add Integration Tests
**Files to Create** (estimated 10 files):
- `tests/Integration/AuthFlowTest.php`
- `tests/Integration/StudentManagementFlowTest.php`
- `tests/Integration/AttendanceFlowTest.php`
- `tests/Integration/CalendarEventFlowTest.php`
- Plus 6 more for critical flows

**Effort per file**: 3-4 hours
**Total Effort**: 30-40 hours

**Acceptance Criteria per Test File**:
- [ ] Test complete user flows
- [ ] Test multi-step operations
- [ ] Test database persistence
- [ ] Test rollback on errors
- [ ] Clean up test data after each test

#### Task T1.4: Setup Coverage Reporting
**File**: `phpunit.xml.dist` (update)
**Effort**: 1 hour
**Acceptance Criteria**:
- [ ] Coverage threshold set to 40%
- [ ] Coverage report generated in XML format
- [ ] Coverage report generated in HTML format
- [ ] Coverage excludes vendor, tests, config directories

**Update phpunit.xml.dist**:
```xml
<coverage processUncoveredFiles="true">
    <include>
        <directory suffix=".php">./app</directory>
    </include>
    <exclude>
        <directory suffix=".php">./vendor</directory>
        <directory suffix=".php">./tests</directory>
        <directory>./config</directory>
    </exclude>
    <report>
        <html outputDirectory="build/coverage/html"/>
        <clover outputFile="build/coverage/clover.xml"/>
    </report>
</coverage>
```

**Total Estimated Time**: 75-105 hours (2-3 weeks)

---

## API Controller Implementation Tasks

### Task: Implement 15 Priority API Controllers

#### Task A1.1: Create PPDBController
**File**: `app/Http/Controllers/Api/SchoolManagement/PPDBController.php`
**Effort**: 8-12 hours
**Acceptance Criteria**:
- [ ] Create controller extending BaseController
- [ ] Inject PPDBService via constructor
- [ ] Implement index() - list all registrations
- [ ] Implement show() - show single registration
- [ ] Implement store() - create new registration
- [ ] Implement update() - update registration
- [ ] Implement destroy() - delete registration
- [ ] Add proper validation using InputValidationTrait
- [ ] Add error handling using BaseController methods
- [ ] Add tests (feature tests)

#### Task A1.2: Create AssessmentController
**File**: `app/Http/Controllers/Api/SchoolManagement/AssessmentController.php`
**Effort**: 10-15 hours
**Acceptance Criteria**:
- [ ] Create controller extending BaseController
- [ ] Inject AssessmentService via constructor
- [ ] Implement CRUD methods for assessments
- [ ] Implement grade calculation endpoint
- [ ] Implement report card generation endpoint
- [ ] Add validation for all inputs
- [ ] Add proper error handling
- [ ] Add tests

#### Task A1.3: Create FeeController
**File**: `app/Http/Controllers/Api/SchoolManagement/FeeController.php`
**Effort**: 8-10 hours
**Acceptance Criteria**:
- [ ] Create controller extending BaseController
- [ ] Inject FeeService via constructor
- [ ] Implement CRUD methods for fees
- [ ] Implement payment processing endpoint
- [ ] Implement payment history endpoint
- [ ] Implement overdue fees report endpoint
- [ ] Add validation
- [ ] Add error handling
- [ ] Add tests

#### Task A1.4 through A1.15: Additional Controllers
**Repeat pattern for**:
- CommunicationController
- ReportCardController
- HealthRecordController
- TransportationController
- ELearningController
- DigitalLibraryController
- OnlineExamController
- CafeteriaController
- HostelController
- AlumniController
- BehaviorController
- SchoolAdminController

**Effort per controller**: 8-12 hours
**Total Effort for all 15**: 120-180 hours (3-4.5 weeks)

**Per-Controller Checklist**:
- [ ] Controller created in correct namespace
- [ ] Extends BaseController
- [ ] Service injected via constructor
- [ ] All CRUD methods implemented
- [ ] Validation using InputValidationTrait
- [ ] Error handling using BaseController methods
- [ ] Routes added to api.php
- [ ] Role middleware added where needed
- [ ] Feature tests created (index, show, store, update, destroy)
- [ ] Unit tests created for related service

---

## Priority Matrix for Task Execution

### Week 1 (Jan 13-20) - IMMEDIATE
**Must Complete**:
- ✅ Task 446.1 through 446.6 (Database Services) - 2 hours
- ✅ Task 447.1 through 447.4 (JWT_SECRET) - 1 hour
- ✅ Task 448.1 through 448.5 (Documentation) - 3.5 hours

**Total Week 1 Effort**: 6.5 hours

### Week 2-3 (Jan 21-Feb 3) - FOUNDATION
**Must Complete**:
- Task T1.4: Coverage reporting setup - 1 hour
- Task T1.1: Add Service Unit Tests (at least 4 services) - 8-12 hours
- Task T1.2: Add Controller Feature Tests (at least 4 controllers) - 8-12 hours

**Total Week 2-3 Effort**: 17-25 hours

### Week 4-7 (Feb 4-28) - API CORE
**Must Complete**:
- Task A1.1: PPDBController - 8-12 hours
- Task A1.2: AssessmentController - 10-15 hours
- Task A1.3: FeeController - 8-10 hours
- Task A1.4: CommunicationController - 8-12 hours

**Total Week 4-7 Effort**: 34-49 hours

---

## Success Criteria Tracking

### Phase 1 Completion Checklist
- [ ] Database services enabled and functional
- [ ] JWT_SECRET placeholder removed with validation
- [ ] All documentation updated and accurate
- [ ] System health score: 8.5/10
- [ ] 0 HIGH priority issues remaining

### Phase 2 Completion Checklist
- [ ] Test coverage: 40% (from 25%)
- [ ] Coverage reporting configured in CI/CD
- [ ] All services have unit tests
- [ ] All controllers have feature tests
- [ ] System health score: 8.5/10

### Phase 3 Completion Checklist
- [ ] 20 API controllers implemented (from 5)
- [ ] All new controllers have tests
- [ ] API coverage: 33% (from 8.3%)
- [ ] All new routes documented
- [ ] System health score: 8.5/10

---

## Task Dependencies

### Critical Path
```
446 (Database) → All database-dependent tasks
                → A1.1-A1.15 (API Controllers)
                → T1.2-T1.3 (Controller Tests)

447 (JWT_SECRET) → Production readiness

448 (Documentation) → Developer onboarding

T1.4 (Coverage) → CI/CD enforcement → Test confidence → PR merging
```

### Parallel Tracks
**Track 1**: 446, 447, 448 (can be done in parallel - Week 1)
**Track 2**: T1.1, T1.2, T1.3, T1.4 (can be done in parallel - Week 2-3)
**Track 3**: A1.1 through A1.15 (can be done in parallel after Week 3)

---

**Created**: January 13, 2026
**Status**: Ready for Implementation
**Owner**: Repository Orchestrator
**Version**: 1.0
**Next Review**: Weekly (Fridays)
