# Developer Guide

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
cp .env.example .env
php artisan key:generate
```

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

# CRITICAL: Generate your own secure JWT secret using: openssl rand -hex 32
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

1. **Never commit secrets** - Always use environment variables
2. **Validate all input** - Use Form Request validation
3. **Sanitize output** - Prevent XSS attacks
4. **Use HTTPS** - In production environments
5. **Implement rate limiting** - Prevent abuse
6. **Keep dependencies updated** - Run security audits regularly

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

- **Documentation:** Check `docs/` folder
- **Issues:** Search GitHub issues
- **Discussions:** Ask in GitHub Discussions
- **Team:** Contact team members for help

---

*Last Updated: January 8, 2026*
