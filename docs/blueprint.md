# Development Blueprint

## Architecture Standards

### Framework Stack
- **Backend**: HyperVel (Laravel-style with Hyperf/Swoole)
- **PHP**: 8.2+ with strict types
- **Database**: MySQL 8.0 (Docker) / SQLite (local dev)
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
- **Errors**: Consistent error response structure with standardized codes (see `docs/API_ERROR_CODES.md`)

#### Error Code Standards
- **Format**: `[CATEGORY]_[SERIAL_NUMBER]` (e.g., AUTH_001, VAL_001, RES_001, SRV_001)
- **Categories**:
  - `AUTH`: Authentication and authorization errors
  - `VAL`: Input validation errors
  - `RES`: Resource-related errors
  - `SRV`: Server and infrastructure errors
  - `RTL`: Rate limiting errors
- **Configuration**: All error codes defined in `config/error-codes.php`
- **Documentation**: See `docs/API_ERROR_CODES.md` for complete reference

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

#### API Integration Patterns
- **Controller Inheritance**: All API controllers must extend `BaseController`
- **Response Methods**: Use standardized response methods:
  - `successResponse($data, $message, $statusCode)` - Success responses
  - `errorResponse($message, $errorCode, $details, $statusCode)` - Generic errors
  - `validationErrorResponse($errors)` - Validation errors (422)
  - `notFoundResponse($message)` - 404 errors
  - `unauthorizedResponse($message)` - 401 errors
  - `forbiddenResponse($message)` - 403 errors
  - `serverErrorResponse($message)` - 500 errors
- **Error Handling**: Global `ApiErrorHandlingMiddleware` catches and classifies exceptions
- **Logging**: All errors logged with context (IP, user agent, URI, method)
- **Rate Limiting**: Redis-based rate limiting with proper headers
- **Input Sanitization**: All inputs sanitized via `InputSanitizationMiddleware`
- **Error Classification**: Exception types mapped to appropriate error codes and types

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

## Frontend UI/UX Standards

### Accessibility Requirements
- **WCAG 2.1 AA Compliance**: All interfaces must meet WCAG 2.1 Level AA
- **Keyboard Navigation**: All interactive elements must be keyboard accessible
- **Screen Reader Support**: Proper ARIA labels and semantic HTML for screen readers
- **Focus Management**: Visible focus indicators and logical focus flow
- **Color Contrast**: Minimum 4.5:1 for text, 3:1 for larger text

### Frontend Framework & Stack
- **Framework**: React with TypeScript
- **Build Tool**: Vite
- **Styling**: Tailwind CSS with design tokens
- **Icons**: Lucide React
- **Charts**: Recharts

### Component Standards
- **Semantic HTML**: Use appropriate HTML elements (nav, main, article, button)
- **ARIA Attributes**: Enhance (not replace) semantic HTML with ARIA
- **TypeScript**: All components must have proper TypeScript interfaces
- **Responsive Design**: Mobile-first approach with breakpoints
- **Loading States**: All async actions must show loading indicators
- **Error Handling**: User-friendly error messages with proper announcements

### Accessibility Best Practices

#### Forms
- All form fields must have associated labels (visible or sr-only)
- Provide inline validation with aria-invalid and aria-describedby
- Error messages must be announced via aria-live regions
- Focus management on form submission errors

#### Navigation
- Skip-to-content link for keyboard users
- Proper heading hierarchy (h1, h2, h3...)
- Breadcrumbs for navigation hierarchy
- Current page indication with aria-current="page"

#### Tables
- Proper table caption for context
- Scope attributes on headers
- Responsive table handling for mobile
- Keyboard navigation for sorting/filtering

#### Images & Media
- Meaningful alt text for all images
- Decorative images marked with aria-hidden="true"
- Controls for auto-playing media
- Captions for video content

### Design Tokens (Tailwind)
- **Primary Colors**: Blue (#3b82f6) for primary actions
- **Success Colors**: Green (#22c55e) for positive states
- **Warning Colors**: Orange (#f59e0b) for warnings
- **Danger Colors**: Red (#ef4444) for destructive actions
- **Spacing**: Consistent 0.25rem (4px) scale
- **Typography**: System fonts with clear hierarchy
- **Border Radius**: Consistent rounded-md (6px) for most elements

### Component Library
- **Button**: Reusable with variants (primary, secondary, success, warning, danger)
- **Card**: Container component with header, title, content, footer
- **Form**: Input, Select, Checkbox with validation states
- **Feedback**: Loading spinners, error alerts, success toasts
- **Navigation**: Breadcrumb, Pagination, Tabs components

### Performance Standards
- **Lighthouse Score**: 90+ for Performance, Accessibility, Best Practices
- **Bundle Size**: Optimize code splitting and lazy loading
- **Image Optimization**: WebP format with lazy loading
- **Animation**: GPU-accelerated transforms only
- **Render Performance**: Avoid unnecessary re-renders

## Update Frequency

- **Blueprint**: Reviewed quarterly, updated as needed
- **Architecture**: Updated with major changes
- **Standards**: Updated when new patterns emerge

---

*Last Updated: January 8, 2026*
*Owner: Principal Product Strategist*
