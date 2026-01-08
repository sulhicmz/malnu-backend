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

**Implementation Status (January 8, 2026)**:
✅ All 13 services have corresponding interfaces
✅ All services implement their respective interfaces
✅ Controllers use interfaces for dependency injection

**Available Interfaces**:
- AuthServiceInterface
- JWTServiceInterface
- TokenBlacklistServiceInterface
- CacheServiceInterface
- CircuitBreakerInterface
- RetryServiceInterface
- TimeoutServiceInterface
- FileUploadServiceInterface
- FileTypeDetectorInterface
- LeaveManagementServiceInterface
- RolePermissionServiceInterface
- CalendarServiceInterface
- BackupServiceInterface

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

#### Model Standardization

All models must inherit from `App\Models\Model` which provides:
- **Primary Key**: UUID string (`id`)
- **Key Type**: String (not incrementing)
- **Incrementing**: False (UUID-based)

**Best Practices**:
- Never manually set `$primaryKey`, `$keyType`, or `$incrementing` in individual models
- Use `UsesUuid` trait for automatic UUID generation during `create()`
- All models automatically inherit UUID configuration from base Model
- Migrations use `DB::raw('(UUID())')` for default UUID values

**Example**:
```php
// Correct - inherits UUID config
class User extends Authenticatable
{
    use UsesUuid;

    protected array $fillable = [...];
}

// Incorrect - redundant configuration
class User extends Authenticatable
{
    protected string $primaryKey = 'id';
    protected string $keyType = 'string';
    public bool $incrementing = false;
}
```

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

#### Integration Standards

#### Resilience Patterns

The application implements the following resilience patterns to ensure reliability and availability:

**Rate Limiting**
- Protects API endpoints from overload and abuse
- Middleware: `App\Http\Middleware\RateLimitMiddleware`
- Configuration: `RATE_LIMIT_MAX_ATTEMPTS`, `RATE_LIMIT_DECAY_SECONDS`
- Different limits for different endpoint types (auth endpoints stricter)
- Headers: `X-RateLimit-Limit`, `X-RateLimit-Remaining`, `X-RateLimit-Reset`

**Timeouts**
- All external service calls must have timeouts configured
- Database timeout: 10 seconds (config/database.php)
- External service timeout: 30 seconds (configurable)
- Circuit breaker timeout: 60 seconds
- Prevents cascading failures from hung operations

**Retries with Exponential Backoff**
- Service: `App\Services\RetryService`
- Automatic retry for transient failures
- Exponential backoff: base_delay * (2^(attempt-1))
- Jitter added to prevent thundering herd
- Max 3 attempts by default
- Retryable exceptions: RuntimeException, PDOException, network errors

**Circuit Breaker**
- Service: `App\Services\CircuitBreaker`
- States: CLOSED, OPEN, HALF_OPEN
- Failure threshold: 5 consecutive failures
- Recovery timeout: 60 seconds
- Success threshold: 2 consecutive successes in HALF_OPEN
- Prevents calling failing services repeatedly
- Supports fallback functions for degraded functionality

**Fallback Mechanisms**
- Graceful degradation when services fail
- Fallback functions for all external service calls
- Cached responses for GET endpoints (CacheResponse middleware)
- Default responses for non-critical features
- User-friendly error messages

#### Error Response Standardization

**Error Codes**
- Centralized in `App\Enums\ErrorCode`
- Standardized codes: `STUDENT_NOT_FOUND`, `AUTH_TOKEN_EXPIRED`, etc.
- Grouped by domain: 1xxx (General), 2xxx (Auth), 3xxx (Registration), etc.
- Automatic HTTP status code mapping via `ErrorCode::getStatusCode()`

**Error Response Format**
```json
{
  "success": false,
  "error": {
    "message": "Human-readable error message",
    "code": "ERROR_CODE_CONSTANT",
    "details": { "field": ["Error message"] }
  },
  "timestamp": "2026-01-07T12:00:00+00:00"
}
```

**Using Error Codes**
```php
use App\Enums\ErrorCode;

return $this->errorResponse(
    $message,
    ErrorCode::STUDENT_NOT_FOUND,
    $details,
    ErrorCode::getStatusCode(ErrorCode::STUDENT_NOT_FOUND)
);
```

#### Integration Best Practices

**External API Calls**
1. Always use `RetryService` for network calls
2. Wrap with `CircuitBreaker` for external services
3. Provide fallback functions for critical operations
4. Log failures with context (service, attempt count, exception)
5. Set reasonable timeouts (never infinite)

**Database Operations**
1. Use `RetryService` for transaction conflicts
2. Configure connection timeouts in database config
3. Use proper indexes to prevent slow queries
4. Leverage caching for frequently accessed data
5. Monitor query performance

**Cache Management**
1. Use `CacheService` for all cache operations
2. Set appropriate TTLs (short for volatile, long for static)
3. Implement cache invalidation on data changes
4. Use cache warming for high-traffic endpoints
5. Monitor cache hit rates

**Error Handling**
1. Use centralized `ErrorCode` constants
2. Provide meaningful error messages
3. Include relevant details for debugging
4. Log errors with full context
5. Never expose sensitive information in error messages

**Controller Standardization**
All API controllers must follow these standards:
- Extend `App\Http\Controllers\Api\BaseController`
- Use `App\Enums\ErrorCode` for all error responses
- Use BaseController response methods:
  - `successResponse($data, $message, $statusCode)`
  - `errorResponse($message, $errorCode, $details, $statusCode)`
  - `validationErrorResponse($errors)`
  - `notFoundResponse($message)`
  - `unauthorizedResponse($message)`
  - `forbiddenResponse($message)`
  - `serverErrorResponse($message)`
- Never use direct `$this->response->json()` calls
- Always wrap database operations in try-catch blocks
- Use appropriate Hyperf framework imports (not Laravel)
- Apply proper validation for all inputs

**Rate Limiting**
All API routes must be protected with rate limiting:
- Public routes: `Route::group(['middleware' => ['input.sanitization', 'rate_limit']])`
- Protected routes: `Route::group(['middleware' => ['jwt', 'rate_limit']])`
- Rate limits configured via environment variables
- Headers: `X-RateLimit-Limit`, `X-RateLimit-Remaining`, `X-RateLimit-Reset`

#### Service Integration Examples

**Circuit Breaker + Retry + Timeout**
```php
$circuitBreaker = new CircuitBreaker('external_api');
$retryService = new RetryService();
$timeoutService = new TimeoutService();

$result = $circuitBreaker->call(
    fn() => $retryService->call(
        fn() => $timeoutService->call(
            fn() => $this->makeApiCall(),
            timeoutMs: 5000
        )
    ),
    fallback: fn() => ['data' => 'cached_response']
);
```

**Database Operation with Retry**
```php
$retryService = new RetryService();

$student = $retryService->call(fn() => Student::create($data));
```

**Cached API Response**
```php
$cacheService = new CacheService();
$key = $cacheService->generateKey('students:list', $params);

$students = $cacheService->remember($key, fn() => Student::paginate(), 300);
```

## Quality Gates

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

---

*Last Updated: January 7, 2026*
*Owner: Principal Product Strategist*
