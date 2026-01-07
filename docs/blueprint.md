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

---

*Last Updated: January 7, 2026*
*Owner: Principal Product Strategist*
