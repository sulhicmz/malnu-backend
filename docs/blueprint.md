# Development Blueprint

## Architecture Standards

### Framework Stack
- **Backend**: HyperVel (Laravel-style with Hyperf/Swoole)
- **PHP**: 8.2+ with strict types
- **Database**: SQLite (dev) / MySQL (prod)
- **Cache**: Redis
- **Frontend**: React with Vite

### Code Standards

#### PHP
- **PSR-12**: Code formatting via PHP CS Fixer
- **Type Safety**: Strict types declaration on all files
- **DocBlocks**: PHPDoc on all public methods
- **Naming**: 
  - Classes: PascalCase
  - Methods: camelCase
  - Variables: camelCase
  - Constants: UPPER_SNAKE_CASE

#### Database
- **Primary Keys**: UUID (CHAR(36)) with default UUID()
- **Timestamps**: `created_at` and `updated_at` DATETIME
- **Soft Deletes**: `deleted_at` DATETIME (nullable)
- **Indexes**: Composite indexes on foreign key pairs
- **Migrations**: Hyperf migration format with `use Hyperf\DbConnection\Db;`

#### API
- **RESTful**: Resource-based routing
- **Versioning**: `/api/v1/` prefix
- **Response**: Standardized JSON format
- **Status Codes**: HTTP standard codes
- **Errors**: Consistent error response structure

### Architecture Patterns

#### Interface-Based Design
- All services must implement interfaces defined in `app/Contracts/`
- Controllers and middleware depend on interfaces, not concrete implementations
- Enables dependency injection and testability
- Follows Dependency Inversion Principle
- **Interface Implementation Pattern**:
  ```php
  // 1. Create interface in app/Contracts/
  interface AttendanceServiceInterface {
      public function markAttendance(array $data): StudentAttendance;
      public function getStudentAttendance(string $studentId): object;
  }

  // 2. Implement interface in service
  class AttendanceService implements AttendanceServiceInterface {
      public function markAttendance(array $data): StudentAttendance {
          // Implementation
      }
  }

  // 3. Inject interface in controller
  class AttendanceController extends BaseController {
      private AttendanceServiceInterface $attendanceService;
      public function __construct(AttendanceServiceInterface $service) {
          $this->attendanceService = $service;
      }
  }
  ```

#### Domain Organization
```
app/Models/
├── SchoolManagement/     # Core school operations
├── ELearning/            # Online learning platform
├── Grading/              # Grade and competency management
├── OnlineExam/           # Examination system
├── DigitalLibrary/       # E-book catalog
├── CareerDevelopment/    # Career guidance
├── Monetization/         # Financial features
├── ParentPortal/         # Parent access
└── System/               # System management
```

#### Layer Architecture
1. **Models**: Eloquent models with relationships
2. **Services**: Business logic (Service pattern)
3. **Controllers**: Request handling and response formatting
4. **Middleware**: Request/response processing
5. **Requests**: Validation classes

### Security Standards

#### Authentication
- JWT token-based authentication
- Role-based access control (RBAC)
- Permission checking on protected routes
- Password hashing with bcrypt

#### Input Validation
- Form request validation classes for all inputs
- SQL injection prevention via Eloquent
- XSS prevention via proper escaping
- File upload validation and scanning

#### Security Headers
- Content Security Policy (CSP)
- X-Frame-Options: DENY
- X-Content-Type-Options: nosniff
- Strict-Transport-Security
- Referrer-Policy

### Performance Standards

#### Caching
- Redis for query result caching (TTL: 5-60 min)
- Route caching in production
- Session storage in Redis
- API response caching for GET endpoints

#### Database Optimization
- Eager loading to prevent N+1 queries
- Composite indexes on frequent query patterns
- Connection pooling via Swoole
- Query optimization with proper indexes

#### Response Times
- API endpoints: <200ms (p95)
- Database queries: <50ms (p95)
- Static assets: <100ms (p95)

### Integration & Resilience Standards

#### Core Principles
- **Resilience First**: External services WILL fail; handle gracefully
- **No Cascading Failures**: Circuit breakers prevent failure propagation
- **Predictable Patterns**: Consistent integration patterns everywhere
- **Backward Compatibility**: Never break consumers without versioning
- **Self-Documenting**: Intuitive, well-documented integrations
- **Idempotency**: Safe operations produce same result

#### Timeout Standards
All external service calls must have timeouts:
- **HTTP Requests**: 30s default, 10s connect timeout
- **Email Sending**: 30s timeout
- **Database Queries**: 50ms timeout (p95 target)
- **Cache Operations**: 100ms timeout
- **External APIs**: Configurable per service

#### Retry Pattern
- Use `RetryService` for all retryable operations
- **Exponential backoff**: Multiply delay by 2 after each attempt
- **Jitter**: Add ±10% random variation to prevent thundering herd
- **Maximum attempts**: 3 by default (configurable)
- **Maximum delay**: Cap at 10s to prevent excessive waiting
- **Initial delay**: 500ms - 2s depending on service type

**Retry Configuration**:
```php
$retryService->execute(
    $operation,
    [
        'max_attempts' => 3,
        'initial_delay' => 1000,
        'max_delay' => 10000,
        'multiplier' => 2,
        'jitter' => true,
        'retry_on' => [ConnectException::class],
    ]
);
```

#### Circuit Breaker Pattern
- Use `CircuitBreakerService` for all external service dependencies
- **States**: CLOSED (normal), OPEN (failing), HALF_OPEN (testing recovery)
- **Failure threshold**: 3-5 failures before opening (configurable)
- **Recovery timeout**: 30-120s before attempting half-open (configurable)
- **Half-open attempts**: 1-3 successful attempts to close circuit
- **Fallback**: Required for all circuit-breaker-protected calls

**Circuit Breaker Usage**:
```php
$circuitBreaker->call(
    'email-service',
    function () use ($email, $token) {
        return $emailService->sendPasswordReset($email, $token);
    },
    function () use ($email) {
        return $fallbackService->queueEmail($email);
    }
);
```

#### Resilient HTTP Client
- Use `ResilientHttpClientService` for all external HTTP calls
- **Combined protection**: Timeouts + Retries + Circuit Breaker
- **Standard methods**: GET, POST, PUT, PATCH, DELETE
- **Async support**: Request methods for async operations
- **Health monitoring**: `getHealthStatus()` for circuit breaker state

#### Anti-Patterns (NEVER Do)
- ❌ External calls without timeouts
- ❌ Infinite retries without limits
- ❌ No error handling for external services
- ❌ Exposing internal implementation details
- ❌ Breaking changes without versioning
- ❌ Letting external failures cascade to users
- ❌ Inconsistent naming/response formats
- ❌ Ignoring service health status

#### Configuration Files
- `config/circuit-breaker.php` - Circuit breaker settings
- `config/retry.php` - Retry strategy settings
- `config/resilient_http.php` - HTTP client timeouts
- `config/resilient_email.php` - Email service settings

#### Service-Specific Configurations
```php
'email' => [
    'failure_threshold' => 3,
    'recovery_timeout' => 120,
    'retry_attempts' => 3,
    'timeout' => 30,
],
'http' => [
    'failure_threshold' => 5,
    'recovery_timeout' => 60,
    'retry_attempts' => 3,
    'timeout' => 30,
    'connect_timeout' => 10,
],
```

#### Monitoring & Observability
- **Circuit breaker state logging**: All state transitions logged
- **Retry attempt logging**: Every retry logged with delay and error
- **Failure context**: Include service, operation, error details
- **Health endpoints**: `/health` endpoint with circuit breaker states
- **Metrics**: Track failure rates, circuit opens, retry counts

#### Error Codes for Integrations
- `SERVICE_UNAVAILABLE` (503): Circuit breaker open
- `TIMEOUT_ERROR` (504): Service timeout
- `CONNECTION_ERROR` (502): Connection failure
- `MAX_RETRIES_EXCEEDED` (429): Retry limit reached

### Testing Standards

#### Test Coverage
- **Target**: 90%+ coverage
- **Types**:
  - Unit tests: Models and services
  - Feature tests: API endpoints
  - Integration tests: Component interactions
- **Tools**: PHPUnit with RefreshDatabase trait

#### Test Organization
```
tests/
├── Unit/                 # Model and service tests
├── Feature/              # API endpoint tests
├── Integration/          # Component integration tests
└── Database/             # Migration and schema tests
```

### Quality Gates

#### Pre-commit
- PHPStan static analysis (level 5)
- PHP CS Fixer (PSR-12 compliance)
- Unit tests pass (local)

#### Pre-merge
- All tests pass (coverage check)
- No security vulnerabilities (audit)
- Documentation updated

### Development Workflow

#### Git Workflow
- Main branch: `main`
- Development branch: `agent`
- Feature branches: `feature/issue-id-description`
- Commit format: `type(scope): description`
- PR requirement: Approval + tests passing

#### Code Review
- All code must be reviewed
- Minimum 1 approval required
- Review checklist:
  - [ ] Standards compliance
  - [ ] Tests pass and coverage adequate
  - [ ] Documentation updated
  - [ ] No security issues
  - [ ] Performance impact considered

## Anti-Patterns

### Never Do
- ❌ Hardcode credentials or secrets
- ❌ Mix business logic in controllers
- ❌ Direct database queries without Eloquent
- ❌ Skip validation on user input
- ❌ Commit sensitive data
- ❌ Break backward compatibility without deprecation
- ❌ Ignore or suppress errors
- ❌ Duplicate code - extract to common utility
- ❌ Modify database schema without migration
- ❌ Push broken code to main

## Deprecation Policy

### Version Support
- Current version: Full support
- Previous version: Security patches only
- Older versions: No support

### Feature Deprecation
1. Mark as deprecated in documentation
2. Add deprecation warning in code
3. Provide migration guide
4. Remove after 2 releases

## Documentation Standards

### Required Documentation
- All public methods: PHPDoc
- Complex logic: Inline comments
- API endpoints: OpenAPI/Swagger spec
- Breaking changes: Migration guide
- New features: Feature spec in feature.md

### DocBlock Format
```php
/**
 * Brief description
 *
 * Detailed description
 *
 * @param Type $param Description
 * @return ReturnType Description
 * @throws ExceptionClass Condition
 */
```

## Update Frequency

- **Blueprint**: Reviewed quarterly, updated as needed
- **Architecture**: Updated with major changes
- **Standards**: Updated when new patterns emerge

## DevOps Standards

### CI/CD Workflow Architecture

#### Workflow Permissions

**Critical Principle**: Workflow files define their own permissions. When a workflow needs to trigger another workflow via `workflow_dispatch`, it requires `actions: write` permission.

**Permissions Matrix**:
```yaml
# For workflows that TRIGGER other workflows:
permissions:
  actions: write    # Required for gh workflow run
  contents: write    # For git operations

# For workflows that DON'T trigger other workflows:
permissions:
  contents: write    # Minimum required for git operations
```

#### Workflow File Modifications

**Security Constraint**: The GITHUB_TOKEN used by GitHub Actions has restricted permissions by design:
- Cannot modify workflow files without `workflows: write` permission
- This prevents infinite loops and unauthorized modifications
- Maintains security by requiring manual approval for workflow changes

**When Modifying Workflows**:
1. Identify the specific permission requirement (read vs write)
2. Update permissions in the workflow file itself
3. **Manual application required** - cannot be automated via GITHUB_TOKEN
4. Repository maintainer must approve and push workflow changes

#### Workflow Monitoring

**Critical Workflows**:
- `workflow-monitor` - Monitors and triggers on-push/on-pull (every 30 min)
- `on-push` - Main CI pipeline for code pushed to main
- `on-pull` - PR validation and automated fixes

**Monitoring Checklist**:
- [ ] All workflows completing successfully
- [ ] No permission errors (403: Resource not accessible)
- [ ] No timeout issues
- [ ] Workflow dispatch events working correctly

### CI Health Monitoring

#### Daily Checklist

1. **Build Status**
   - All workflows passing?
   - No flaky tests?
   - Build time reasonable?

2. **Security Scans**
   - No new vulnerabilities?
   - Dependency audits passing?
   - Sensitive data not committed?

3. **Pipeline Health**
   - No stuck jobs?
   - Cache working correctly?
   - Artifacts generated successfully?

#### Weekly Review

1. **Performance**
   - Build time trends
   - Resource utilization
   - Cache hit rates

2. **Stability**
   - Flaky test identification
   - Failure rate analysis
   - Rollback events

#### Incident Response Protocol

**Priority Levels**:
- 🔴 P0: CI failures - ONLY priority
- 🟡 P1: Flaky tests, pipeline timeouts
- 🟢 P2: Performance optimization

**When CI Fails**:
1. Immediately stop non-CI work
2. Identify failing workflow
3. Check logs for errors
4. Fix or document blocker
5. Verify fix in next run

### Anti-Patterns (CI/CD)

- ❌ Ignore failing CI builds
- ❌ Commit secrets or credentials
- ❌ Modify production without CI
- ❌ Skip workflow permission validation
- ❌ Assume GITHUB_TOKEN has all permissions
- ❌ Push workflow changes without manual review
- ❌ Let flaky tests remain unaddressed

---

*Last Updated: January 7, 2026*
*Owner: Principal Product Strategist*
