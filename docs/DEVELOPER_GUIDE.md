# Developer Guide

## Quick Start (New to the Project?)

Welcome to Malnu Backend! If you're new to this project, start here:

1. **Read the Project Overview** - 5 minutes
   - [Project Structure](PROJECT_STRUCTURE.md) - High-level overview
   - [Architecture](ARCHITECTURE.md) - System design and patterns

2. **Understand the Framework** - 10 minutes
   - [HyperVel Framework Guide](HYPERVEL_FRAMEWORK_GUIDE.md) - Learn HyperVel concepts
   - Key differences from Laravel: Swoole coroutines, annotations, async operations

3. **Explore Business Domains** - 15 minutes
   - [Business Domains Guide](BUSINESS_DOMAINS_GUIDE.md) - 11 domain overview
   - Understand how domains interact and which one you'll work on

4. **Set Up Your Environment** - 20 minutes
   - Follow the [Prerequisites](#prerequisites) below
   - Complete the [Setup Development Environment](#setup-development-environment) steps

5. **Learn the Workflow** - 10 minutes
   - Review [Development Workflow](#development-workflow)
   - Understand [Coding Standards](#coding-standards)

6. **Start Coding!** 🚀
   - Pick an issue from GitHub Issues
   - Create a feature branch
   - Follow [Best Practices](#best-practices)
   - Submit a Pull Request

**Need help?** See [Getting Help](#getting-help) section below.

---

## Getting Started

### Prerequisites

Before you begin, ensure you have the following installed:

- **PHP 8.2+** - Required by HyperVel framework
- **Composer 2.x** - PHP dependency manager
- **Node.js 18+** - Required for frontend
- **npm/yarn** - JavaScript package manager
- **Docker & Docker Compose** - For containerized development
- **Redis** - For caching and sessions
- **Git** - Version control
- **Make** (optional) - For running make commands

### Setup Development Environment

#### 1. Clone the Repository

```bash
git clone https://github.com/sulhicmz/malnu-backend.git
cd malnu-backend
```

#### 2. Install Backend Dependencies

```bash
composer install
```

#### 3. Install Frontend Dependencies

```bash
cd frontend
npm install
cd ..
```

#### 4. Configure Environment Variables

```bash
# Option 1: Use automated setup script (RECOMMENDED)
./scripts/setup-env.sh

# Option 2: Manual setup
cp .env.example .env
php artisan key:generate
```

**Security Note**: The setup script automatically generates secure random values for `APP_KEY` and `JWT_SECRET`. For production deployments, always regenerate these secrets using:
```bash
openssl rand -base64 32  # For APP_KEY
openssl rand -hex 32     # For JWT_SECRET
```

Never use example secrets in production!

Edit `.env` file with your configuration:

```env
APP_NAME="Malnu Backend"
APP_ENV=local
APP_DEBUG=true
APP_URL=http://localhost:9501

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=malnu_backend
DB_USERNAME=root
DB_PASSWORD=your_password

 REDIS_HOST=127.0.0.1
 REDIS_PASSWORD=null
 REDIS_PORT=6379

 # CRITICAL: Generate your own secure JWT secret
 # Generate using: openssl rand -hex 32
 # WARNING: NEVER use placeholder values in production!
 JWT_SECRET=
```

#### 5. Start Docker Services

```bash
docker-compose up -d mysql redis
```

#### 6. Run Database Migrations

```bash
php artisan migrate
```

#### 7. Run Database Seeders

```bash
php artisan db:seed
```

#### 8. Start Backend Server

```bash
php artisan start
```

The application will be available at `http://localhost:9501`

#### 9. Start Frontend Development Server

```bash
cd frontend
npm run dev
```

The frontend will be available at `http://localhost:5173`

## Project Structure

### Backend Structure

```
app/
├── Console/Commands/       # Artisan commands
├── Contracts/              # Service interfaces
├── Events/                 # Event classes
├── Exceptions/             # Exception handlers
├── Http/
│   ├── Controllers/         # Request handlers
│   │   ├── Api/          # API controllers
│   │   ├── Attendance/    # Attendance controllers
│   │   ├── Calendar/      # Calendar controllers
│   │   └── admin/        # Admin controllers
│   ├── Middleware/        # Request middleware
│   └── Requests/        # Form request validation
├── Models/              # Eloquent models
│   ├── AIAssistant/     # AI assistant models
│   ├── Attendance/      # Attendance models
│   ├── Calendar/        # Calendar models
│   ├── CareerDevelopment/ # Career development models
│   ├── DigitalLibrary/  # Digital library models
│   ├── ELearning/       # E-Learning models
│   ├── Grading/        # Grading models
│   ├── Logs/           # Logging models
│   ├── Monetization/    # Monetization models
│   ├── OnlineExam/      # Online exam models
│   ├── PPDB/          # PPDB models
│   ├── ParentPortal/   # Parent portal models
│   ├── SchoolManagement/ # School management models
│   ├── System/        # System models
│   ├── User.php       # User model
│   ├── Role.php       # Role model
│   └── Permission.php # Permission model
├── Providers/         # Service providers
├── Services/          # Business logic services
└── Traits/           # Reusable traits

config/              # Configuration files
database/
├── factories/        # Model factories
├── migrations/       # Database migrations
└── seeders/         # Database seeders

routes/
├── api.php          # API routes
├── channels.php      # Broadcast channels
├── console.php      # Console routes
└── web.php         # Web routes

tests/              # Test files
```

### Frontend Structure

```
frontend/src/
├── components/      # Reusable React components
├── pages/          # Page components
│   ├── Dashboard.tsx
│   ├── Analytics.tsx
│   ├── school/     # School management pages
│   └── elearning/  # E-Learning pages
├── hooks/          # Custom React hooks
├── services/       # API services
└── App.tsx        # Main app component
```

## Development Workflow

### 1. Create a Feature Branch

```bash
git checkout -b feature/your-feature-name
```

Branch naming conventions:
- `feature/feature-name` - New features
- `fix/bug-description` - Bug fixes
- `refactor/refactor-description` - Code refactoring
- `docs/documentation-update` - Documentation updates

### 2. Make Changes

Follow the coding standards and implement your changes.

### 3. Run Tests

```bash
# Backend tests
composer test

# Frontend tests (if any)
cd frontend
npm run test
```

### 4. Run Code Style Checks

```bash
# Backend code style
composer cs-diff

# Fix code style
composer cs-fix

# Static analysis
composer analyse

# Frontend linting
cd frontend
npm run lint
```

### 5. Commit Changes

```bash
git add .
git commit -m "feat: add new feature"
```

Commit message format:
- `feat:` - New feature
- `fix:` - Bug fix
- `docs:` - Documentation changes
- `style:` - Code style changes (formatting)
- `refactor:` - Code refactoring
- `test:` - Adding or updating tests
- `chore:` - Maintenance tasks

### 6. Push and Create Pull Request

```bash
git push origin feature/your-feature-name
```

Create a pull request on GitHub with:
- Clear description of changes
- Related issue numbers (e.g., "Fixes #123")
- Screenshots for UI changes
- Test results

### 7. Code Review and Merge

- Request code review from team members
- Address review comments
- Ensure all CI checks pass
- Merge PR when approved

## Coding Standards

### PHP (Backend)

- Follow PSR-12 coding standard
- Use strict types where possible
- Add PHPDoc blocks for all public methods
- Use type hints for parameters and return values
- Use named arguments in PHP 8.0+ features
- Follow Model-View-Controller pattern
- Use dependency injection

Example:

```php
<?php

namespace App\Http\Controllers;

use App\Services\AuthService;
use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Annotation\PostMapping;

/**
 * Authentication Controller
 */
#[Controller]
class AuthController
{
    public function __construct(
        private readonly AuthService $authService
    ) {}

    /**
     * User login
     *
     * @param LoginRequest $request
     * @return array
     */
    #[PostMapping('/auth/login')]
    public function login(LoginRequest $request): array
    {
        $token = $this->authService->login(
            $request->input('email'),
            $request->input('password')
        );

        return [
            'success' => true,
            'data' => [
                'token' => $token,
                'type' => 'Bearer'
            ]
        ];
    }
}
```

### TypeScript/React (Frontend)

- Use functional components with hooks
- Define TypeScript interfaces for all props
- Use proper TypeScript types (avoid `any`)
- Follow React best practices
- Use Tailwind CSS for styling
- Implement proper error handling

Example:

```typescript
interface StudentDataProps {
  studentId: string;
  name: string;
  email: string;
  onEdit: (id: string) => void;
}

export const StudentData: React.FC<StudentDataProps> = ({
  studentId,
  name,
  email,
  onEdit
}) => {
  return (
    <div className="p-4 bg-white rounded-lg shadow">
      <h2 className="text-lg font-semibold">{name}</h2>
      <p className="text-gray-600">{email}</p>
      <button
        onClick={() => onEdit(studentId)}
        className="mt-2 px-4 py-2 bg-blue-500 text-white rounded"
      >
        Edit
      </button>
    </div>
  );
};
```

## Testing

### Writing Tests

#### Backend Tests (PHPUnit)

Create tests in `tests/Feature/` or `tests/Unit/`:

```php
<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;

class AuthControllerTest extends TestCase
{
    public function test_user_can_login()
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => bcrypt('password123')
        ]);

        $response = $this->post('/auth/login', [
            'email' => 'test@example.com',
            'password' => 'password123'
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'token',
                    'type'
                ]
            ]);
    }

    public function test_login_fails_with_invalid_credentials()
    {
        $response = $this->post('/auth/login', [
            'email' => 'invalid@example.com',
            'password' => 'wrongpassword'
        ]);

        $response->assertStatus(401);
    }
}
```

#### Frontend Tests

Create tests for React components (TBD).

### Running Tests

```bash
# Backend tests
composer test

# Backend tests with coverage
composer test -- --coverage-html

# Run specific test
composer test -- filter=test_user_can_login
```

## Common Development Tasks

### Create a New Controller

```bash
php artisan make:controller Api/YourController
```

### Create a New Model

```bash
php artisan make:model YourModel
```

### Create a New Migration

```bash
php artisan make:migration create_table_name
```

### Run Migrations

```bash
php artisan migrate
php artisan migrate:rollback
php artisan migrate:fresh
php artisan migrate:fresh --seed
```

### Create a Factory

```bash
php artisan make:factory ModelFactory
```

### Generate a JWT Secret

```bash
php artisan jwt:secret
```

### Clear Cache

```bash
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear
```

## Debugging

### Enable Debug Mode

Set `APP_DEBUG=true` in `.env` file.

### View Logs

```bash
tail -f storage/logs/hyperf.log
```

### Database Debugging

```bash
# Check database connection
php artisan db:show

# Run specific query
php artisan tinker
```

### Frontend Debugging

- Use browser DevTools (F12)
- Check browser console for errors
- Use React DevTools extension
- Check network tab for API calls

## Common Issues & Solutions

### Issue: Database connection fails

**Solution:**
1. Check Docker services are running: `docker-compose ps`
2. Verify database credentials in `.env`
3. Ensure database migrations have run: `php artisan migrate`

### Issue: JWT authentication fails

**Solution:**
1. Verify `JWT_SECRET` is set in `.env`
2. Ensure token is sent in Authorization header: `Bearer {token}`
3. Check token expiration time in `config/jwt.php`

### Issue: Redis connection fails

**Solution:**
1. Ensure Redis is running: `docker-compose ps`
2. Check Redis configuration in `.env`
3. Test Redis connection: `redis-cli ping`

### Issue: Frontend can't connect to backend

**Solution:**
1. Ensure backend server is running: `php artisan start`
2. Check CORS settings in backend
3. Verify API base URL in frontend services

## Best Practices

### Security

**Critical Security Requirements:**

1. **Never commit secrets** - Always use environment variables
   - Never commit `.env` files
   - Use `.env.example` for template
   - Generate secure secrets with `php artisan key:generate` and `php artisan jwt:secret`
   - Review [Security Analysis](SECURITY_ANALYSIS.md) for security best practices

2. **Validate all input** - Use Form Request validation
   - Always validate user input on the server side
   - Use Form Request classes for complex validation
   - Never trust client-side validation alone
   - Sanitize and escape output to prevent XSS

3. **Sanitize output** - Prevent XSS attacks
   - Use proper encoding when rendering user content
   - Never output untrusted data without sanitization
   - Use Content Security Policy headers
   - Escape HTML, JavaScript, and CSS contexts

4. **Use HTTPS** - In production environments
   - Force HTTPS redirects
   - Use secure cookies (HttpOnly, Secure, SameSite)
   - Configure proper TLS certificates
   - Disable old TLS versions and weak ciphers

5. **Implement rate limiting** - Prevent abuse
   - Use `RateLimitingMiddleware` on API endpoints
   - Configure appropriate limits per endpoint type
   - Implement IP-based and user-based limits
   - Log rate limit violations

6. **Authentication & Authorization**
   - Always use JWT for API authentication
   - Validate tokens on every protected request
   - Use Role-Based Access Control (RBAC) properly
   - Never rely on client-side role checks
   - Implement proper session management

7. **SQL Injection Prevention**
   - Use Eloquent ORM or parameterized queries
   - Never concatenate user input into SQL queries
   - Use query builder methods with binding
   - Sanitize all database inputs

8. **Password Security**
   - Always hash passwords using `bcrypt` or `Argon2`
   - Never store passwords in plain text
   - Implement password complexity requirements
   - Use secure password reset flows (no token exposure)
   - Enforce password expiration policies

9. **File Upload Security**
   - Validate file types and sizes
   - Sanitize filenames
   - Store uploads outside web root
   - Use virus scanning if possible
   - Implement access controls for uploaded files

10. **Cross-Site Request Forgery (CSRF)**
    - Use CSRF tokens for state-changing operations
    - Validate tokens on every POST/PUT/DELETE request
    - Use SameSite cookie attribute
    - Implement referrer checking

11. **Logging and Monitoring**
    - Log all authentication attempts (success and failure)
    - Log authorization failures
    - Monitor suspicious activities
    - Set up alerts for security events
    - Regularly review access logs

12. **Keep dependencies updated** - Run security audits regularly
    - Regularly update Composer dependencies
    - Use `composer audit` to check for vulnerabilities
    - Monitor security advisories for dependencies
    - Apply security patches promptly
    - Review dependency licenses

**Additional Security Guidelines:**

- See [Security Analysis](SECURITY_ANALYSIS.md) for comprehensive security assessment
- Review [API Error Handling](API_ERROR_HANDLING.md) for secure error responses
- Follow [Rate Limiting](RATE_LIMITING.md) configuration guidelines
- Review [Deployment Guide](DEPLOYMENT.md) for production security setup

**Common Security Mistakes to Avoid:**

- ❌ Hardcoding credentials in code
- ❌ Trusting client-side validation
- ❌ Returning sensitive data in API responses
- ❌ Using `md5()` or `sha1()` for password hashing
- ❌ Concatenating user input into SQL queries
- ❌ Exposing stack traces in production
- ❌ Storing sensitive data in URLs
- ❌ Using default or weak passwords
- ❌ Disabling security features for convenience

### Performance

1. **Use eager loading** - Prevent N+1 queries
2. **Implement caching** - Cache expensive operations
3. **Add database indexes** - Optimize query performance
4. **Use pagination** - Prevent large result sets
5. **Optimize images** - Compress and use WebP format

### Code Quality

1. **Write tests first** - Test-driven development
2. **Keep functions small** - Single responsibility principle
3. **DRY principle** - Don't repeat yourself
4. **Meaningful names** - Clear, descriptive names
5. **Comment complex logic** - Explain why, not what

## Contributing

1. Read this guide thoroughly
2. Check existing issues before creating new ones
3. Work on issues assigned to you
4. Follow the development workflow
5. Write tests for your changes
6. Update documentation as needed
7. Submit pull requests with clear descriptions

## Getting Help

- **Documentation:**
  - [Developer Guide](DEVELOPER_GUIDE.md) - This guide
  - [HyperVel Framework Guide](HYPERVEL_FRAMEWORK_GUIDE.md) - Framework-specific concepts
  - [Business Domains Guide](BUSINESS_DOMAINS_GUIDE.md) - Domain overview
  - [Architecture](ARCHITECTURE.md) - System architecture
  - [API Documentation](API.md) - API reference
  - [Security Analysis](SECURITY_ANALYSIS.md) - Security best practices
- **Issues:** Search GitHub issues
- **Discussions:** Ask in GitHub Discussions
- **Team:** Contact team members for help

## Next Steps for New Developers

After completing the Quick Start, you're ready to:

1. **Choose Your First Issue**
   - Browse [GitHub Issues](https://github.com/sulhicmz/malnu-backend/issues)
   - Look for issues labeled `good first issue` or with domain expertise
   - Comment on the issue that you're taking it

2. **Join Team Discussions**
   - Participate in GitHub Discussions
   - Review pull requests to learn patterns
   - Ask questions in Slack/Discord (if available)

3. **Read Domain-Specific Documentation**
   - Identify the domain you'll work on
   - Read the [Business Domains Guide](BUSINESS_DOMAINS_GUIDE.md)
   - Review domain-specific models and services

4. **Start with Small Contributions**
   - Fix bugs or add small features
   - Improve documentation
   - Add tests

5. **Graduate to Complex Features**
   - Take on larger features
   - Work across multiple domains
   - Participate in architectural discussions

6. **Become a Domain Expert**
   - Master one or more domains
   - Review PRs in your domain
   - Mentor new developers

Remember: Every contribution counts! Start small, learn continuously, and don't hesitate to ask questions.

---

*Last Updated: February 23, 2026*
