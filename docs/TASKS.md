# Granular Task List for Malnu Backend Development

> ⚠️ **DEPRECATED**: This granular task breakdown has been superseded by [TASK_MANAGEMENT.md](TASK_MANAGEMENT.md) which contains the comprehensive task management system. This document is preserved for historical reference only.

## 🚨 Phase 1: Critical Fixes (Week 1-2)

### Issue #100: Fix Model Relationship Error
**Estimated Time**: 2-4 hours | **Priority**: CRITICAL

#### Tasks:
- [ ] **Task 100.1**: Add missing import to Student.php
  - Add `use App\Models\ParentPortal\ParentOrtu;` to imports
  - Change `return $this->belongsTo(Parent::class);` to `return $this->belongsTo(ParentOrtu::class);`
  - **File**: `app/Models/SchoolManagement/Student.php:55`

- [ ] **Task 100.2**: Test student-parent relationship
  - Create test data with student and parent records
  - Test `$student->parent` relationship returns correct data
  - Verify no fatal errors when accessing relationship

- [ ] **Task 100.3**: Verify no other similar errors
  - Search all models for similar incorrect class references
  - Fix any additional relationship errors found
  - Run full model test suite

### Issue #101: Fix Missing DB Imports in Migrations
**Estimated Time**: 1-2 days | **Priority**: CRITICAL

#### Tasks:
- [ ] **Task 101.1**: Audit all migration files
  - List all 11 migration files in `database/migrations/`
  - Identify files using `DB::raw('(UUID())')` without import
  - Document all instances (46+ total)

- [ ] **Task 101.2**: Add DB imports to all migration files
  - Add `use Hyperf\DbConnection\Db;` to each affected file
  - Ensure imports are at the top after the opening PHP tag
  - **Files**: All migration files using `DB::raw()`

- [ ] **Task 101.3**: Test migration execution
  - Run `php artisan migrate:fresh` successfully
  - Verify all tables created with proper UUID defaults
  - Test rollback functionality with `php artisan migrate:rollback`

- [ ] **Task 101.4**: Update migration documentation
  - Document the standard for UUID usage in migrations
  - Add comment blocks explaining UUID implementation
  - Update any migration templates or examples

### Issue #103: Standardize UUID Implementation
**Estimated Time**: 3-5 days | **Priority**: HIGH

#### Tasks:
- [ ] **Task 103.1**: Create base model standardization
  - Review `app/Models/Model.php` base class
  - Add UUID configuration to base model if not present
  - Define standard UUID properties and methods

- [ ] **Task 103.2**: Audit all model files
  - List all models in domain directories:
    - `app/Models/SchoolManagement/` (6 models)
    - `app/Models/ELearning/` (7 models)
    - `app/Models/OnlineExam/` (5 models)
    - `app/Models/DigitalLibrary/` (4 models)
    - `app/Models/Grading/` (4 models)
    - `app/Models/CareerDevelopment/` (3 models)
    - `app/Models/Monetization/` (3 models)
    - `app/Models/ParentPortal/` (1 model)
    - `app/Models/AIAssistant/` (1 model)
    - `app/Models/Logs/` (1 model)
    - `app/Models/System/` (1 model)
    - Core models (User, Role, Permission, etc.)

- [ ] **Task 103.3**: Update all models with UUID standardization
  - Add `protected $primaryKey = 'id';` where missing
  - Add `protected $keyType = 'string';` where missing
  - Add `public $incrementing = false;` where missing
  - Ensure consistency across all models

- [ ] **Task 103.4**: Test model functionality
  - Test model creation with UUID primary keys
  - Test model relationships work correctly
  - Verify no breaking changes to existing code

### Issue #104: Implement Comprehensive Test Suite
**Estimated Time**: 2-3 weeks | **Priority**: CRITICAL

#### Tasks:
- [ ] **Task 104.1**: Setup testing infrastructure
  - Configure database testing with RefreshDatabase trait
  - Setup test database environment
  - Create base test classes and helpers

- [ ] **Task 104.2**: Create model factories
  - Create factory for User model
  - Create factory for Student model
  - Create factory for Teacher model
  - Create factory for ClassModel model
  - Create factories for other core models

- [ ] **Task 104.3**: Model relationship tests
  - Test Student-Parent relationship
  - Test User-Role relationships
  - Test Class-Subject relationships
  - Test all model relationships systematically

- [ ] **Task 104.4**: Business logic tests
  - Test UUID generation in models
  - Test model validation rules
  - Test custom model methods
  - Test model events and observers

- [ ] **Task 104.5**: API endpoint tests (when controllers exist)
  - Test authentication endpoints
  - Test CRUD operations for all resources
  - Test error handling and edge cases
  - Test API response formats

## 🏗️ Phase 2: Core Architecture (Week 3-6)

### Issue #102: Implement RESTful API Controllers
**Estimated Time**: 2-3 weeks | **Priority**: CRITICAL

#### Tasks:
- [ ] **Task 102.1**: Setup controller foundation
  - Create base API controller class
  - Implement standard CRUD methods
  - Setup response formatting and error handling

- [ ] **Task 102.2**: Authentication controllers
  - Create `AuthController` with login/register/logout
  - Implement JWT token generation and validation
  - Add password reset functionality

- [ ] **Task 102.3**: User management controllers
  - Create `UserController` for user CRUD
  - Create `RoleController` for role management
  - Create `PermissionController` for permission management

- [ ] **Task 102.4**: School management controllers
  - Create `StudentController` for student CRUD
  - Create `TeacherController` for teacher CRUD
  - Create `ClassController` for class management
  - Create `SubjectController` for subject management
  - Create `ScheduleController` for schedule management

- [ ] **Task 102.5**: Academic controllers
  - Create `GradeController` for grade management
  - Create `AttendanceController` for attendance tracking
  - Create `ExamController` for exam management

- [ ] **Task 102.6**: Request validation classes
  - Create validation classes for each controller
  - Implement input sanitization and validation rules
  - Add custom validation messages

- [ ] **Task 102.7**: API resource transformers
  - Create resource classes for consistent JSON responses
  - Implement data transformation and filtering
  - Add relationship loading and pagination

### Issue #14: Authentication & Authorization
**Estimated Time**: 2 weeks | **Priority**: CRITICAL

#### Tasks:
- [ ] **Task 14.1**: JWT authentication implementation
  - Install and configure JWT package
  - Create JWT middleware for route protection
  - Implement token refresh mechanism

- [ ] **Task 14.2**: Role-based access control
  - Implement permissions system
  - Create role assignment functionality
  - Add permission checking middleware

- [ ] **Task 14.3**: Security enhancements
  - Implement rate limiting for API endpoints
  - Add login attempt tracking
  - Implement session management

## 📚 Phase 3: Documentation & Organization

### Issue #51: Documentation Organization
**Estimated Time**: 1-2 days | **Priority**: MEDIUM

#### Tasks:
- [ ] **Task 51.1**: Organize docs folder structure
  - Review current documentation files
  - Create proper folder structure in `docs/`
  - Move documentation files to appropriate folders

- [ ] **Task 51.2**: Update documentation content
  - Update README.md with current project status
  - Update CONTRIBUTING.md with new guidelines
  - Create API documentation structure

- [ ] **Task 51.3**: Create developer documentation
  - Setup guide for new developers
  - Architecture documentation
  - Coding standards and best practices

## 🔍 Issue Management Strategy

### Open Issues Analysis
- **Total Open Issues**: 39 issues
- **Critical Issues**: 4 newly identified (#100, #101, #102, #104)
- **High Priority**: 15+ issues requiring attention
- **Medium Priority**: 10+ feature requests
- **Low Priority**: 5+ enhancement requests

### Issue Triage Process
1. **Immediate Action**: Critical issues (#100, #101) - Fix within 48 hours
2. **Week 1 Priority**: Foundation issues (#103, #104) - Complete in Week 1
3. **Week 2-3 Priority**: Core architecture (#102, #14) - Complete in Week 2-3
4. **Ongoing**: Feature requests and enhancements - Address as capacity allows

### Issue Dependencies
- #102 (Controllers) depends on #100, #101, #103 being fixed
- #104 (Testing) depends on controllers being implemented
- Feature requests depend on core architecture being complete

## 📊 Progress Tracking

### Weekly Goals
- **Week 1**: Fix all critical issues (#100, #101, #103)
- **Week 2**: Complete testing infrastructure (#104)
- **Week 3-4**: Implement authentication and core controllers
- **Week 5-6**: Complete RESTful API and business logic

### Success Metrics
- **Critical Issues**: 0 remaining
- **Test Coverage**: 80%+ for models and controllers
- **API Endpoints**: 20+ functional endpoints
- **Documentation**: 100% coverage for implemented features

---

*This task list will be updated weekly based on progress and new discoveries.*