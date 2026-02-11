# HyperVel Framework Guide

## Overview

HyperVel is a Laravel-style PHP framework built on top of Hyperf and powered by Swoole, providing high-performance capabilities through native coroutine support. This guide explains HyperVel-specific concepts, patterns, and best practices for developers familiar with Laravel.

## HyperVel vs Laravel

While HyperVel follows Laravel conventions, there are important differences:

| Aspect | Laravel | HyperVel |
|--------|---------|----------|
| Runtime | PHP-FPM | Swoole (coroutine-based) |
| Performance | Standard | Ultra-high (10-100x faster) |
| Request Handling | Traditional | Asynchronous, non-blocking |
| Annotations | Optional | Primary method for configuration |
| Dependency Injection | Service Container | Hyperf DI Container |
| Async/Await | Limited | Native coroutines |

## Key HyperVel Concepts

### 1. Swoole Coroutines

HyperVel leverages Swoole's coroutines for high-performance concurrent operations.

**Basic Coroutine Usage:**

```php
use Hyperf\Coroutine\Coroutine;

// Simple coroutine
Coroutine::create(function () {
    // This runs concurrently
    echo "Running in coroutine\n";
});
```

**Cooperation with Database:**

```php
// Queries run asynchronously when in coroutine context
$users = User::where('status', 'active')->get(); // Non-blocking
```

**Important Notes:**
- Most code runs automatically in coroutine context during HTTP requests
- Avoid blocking operations (sleep, file I/O) in coroutines
- Use coroutine-safe alternatives (Co::sleep instead of sleep)

### 2. Annotations

HyperVel uses PHP 8 attributes (annotations) extensively for configuration instead of Laravel's config files.

**Route Definitions:**

```php
use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Annotation\GetMapping;
use Hyperf\HttpServer\Annotation\PostMapping;

#[Controller]
class UserController extends BaseController
{
    #[GetMapping('/users')]
    public function index(): array
    {
        return ['users' => User::all()];
    }

    #[PostMapping('/users')]
    public function store(): array
    {
        // Store user logic
    }
}
```

**Middleware Registration:**

```php
use Hyperf\HttpServer\Annotation\Middleware;
use App\Http\Middleware\AuthMiddleware;

#[Controller]
#[Middleware(AuthMiddleware::class)]
class ProtectedController extends BaseController
{
    // All methods protected by AuthMiddleware
}
```

**Route-Level Middleware:**

```php
#[PostMapping('/users/create')]
#[Middleware(AuthMiddleware::class)]
public function create(): array
{
    // Only this method protected
}
```

**Controller Auto-Discovery:**

- Controllers in `app/Http/Controllers/` are automatically discovered
- Routes defined with attributes are automatically registered
- No need to manually register routes in a routes file for controller-based routes

### 3. Dependency Injection

HyperVel uses a powerful DI container based on Hyperf.

**Constructor Injection:**

```php
use App\Services\UserService;
use Hyperf\Di\Annotation\Inject;

#[Controller]
class UserController extends BaseController
{
    private readonly UserService $userService;

    public function __construct(UserService $userService)
    {
        $this->userService = $userService;
    }

    public function index(): array
    {
        return $this->userService->getAllUsers();
    }
}
```

**@Inject Annotation:**

```php
#[Controller]
class UserController extends BaseController
{
    #[Inject]
    private UserService $userService;
}
```

**Service Provider Pattern:**

```php
use Hyperf\ServiceGovernance\ServiceManager;

class UserService
{
    // Auto-registered as a service
    public function getUsers(): array
    {
        return User::all()->toArray();
    }
}
```

### 4. Event System

HyperVel provides a powerful event system using annotations.

**Defining Events:**

```php
use Hyperf\Event\Annotation\Listener;

class UserRegistered
{
    public function __construct(
        public readonly User $user
    ) {}
}
```

**Creating Listeners:**

```php
use Hyperf\Event\Annotation\Listener;

#[Listener]
class SendWelcomeEmail
{
    public function listen(): array
    {
        return [UserRegistered::class];
    }

    public function process(object $event): void
    {
        // Send welcome email
    }
}
```

**Dispatching Events:**

```php
use Hyperf\Event\EventDispatcher;

$eventDispatcher = make(EventDispatcher::class);
$eventDispatcher->dispatch(new UserRegistered($user));
```

### 5. Queue & Async Tasks

HyperVel supports asynchronous task processing.

**Job Definition:**

```php
use Hyperf\AsyncQueue\Annotation\AsyncQueueMessage;

#[AsyncQueueMessage]
class SendEmailJob
{
    public function __construct(
        public readonly string $email,
        public readonly string $message
    ) {}

    public function handle(): void
    {
        // Send email asynchronously
        Mail::send($this->email, $this->message);
    }
}
```

**Dispatching Jobs:**

```php
use Hyperf\AsyncQueue\Driver\DriverFactory;

$driver = make(DriverFactory::class)->get('default');
$driver->push(new SendEmailJob('user@example.com', 'Hello!'));
```

## HyperVel-Specific Conventions

### File Structure

```
app/
├── Http/
│   ├── Controllers/
│   │   ├── Api/          # API controllers
│   │   ├── Attendance/    # Domain-specific controllers
│   │   └── Middleware/   # Middleware
├── Services/              # Business logic services
├── Models/               # Eloquent models
├── Contracts/            # Service interfaces
├── Events/              # Event classes
└── Exceptions/           # Exception handlers
```

### Route Organization

Routes are defined using annotations on controllers:

**API Routes (routes/api.php):**

```php
// This file is minimal in HyperVel
// Most routes defined via controller attributes
```

**Controller-Based Routes:**

```php
use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Annotation\GetMapping;

#[Controller(prefix: '/api/v1')]
class UserController extends BaseController
{
    #[GetMapping('/users')]
    public function index(): array { }

    #[GetMapping('/users/{id}')]
    public function show(int $id): array { }
}
```

### Middleware Registration

Middleware is registered using annotations:

```php
use Hyperf\HttpServer\Annotation\Middleware;
use App\Http\Middleware\AuthMiddleware;
use App\Http\Middleware\CorsMiddleware;

#[Controller]
#[Middleware(CorsMiddleware::class)]
class ApiController extends BaseController
{
    #[GetMapping('/public/data')]
    public function publicData(): array { }

    #[GetMapping('/protected/data')]
    #[Middleware(AuthMiddleware::class)]
    public function protectedData(): array { }
}
```

## Common HyperVel Patterns

### 1. Service Layer Pattern

Separate business logic from controllers:

```php
// Service
namespace App\Services;

class UserService
{
    public function createUser(array $data): User
    {
        $user = User::create($data);
        $this->sendWelcomeEmail($user);
        return $user;
    }

    private function sendWelcomeEmail(User $user): void
    {
        // Email logic
    }
}

// Controller
use App\Services\UserService;

#[Controller]
class UserController extends BaseController
{
    private readonly UserService $userService;

    public function __construct(UserService $userService)
    {
        $this->userService = $userService;
    }

    public function store(Request $request): array
    {
        $user = $this->userService->createUser($request->all());
        return ['user' => $user];
    }
}
```

### 2. Form Request Validation

Use form request classes for validation:

```php
namespace App\Http\Requests;

use Hyperf\Validation\Request\FormRequest;

class CreateUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users',
            'password' => 'required|min:8|confirmed',
        ];
    }
}

// Controller
public function store(CreateUserRequest $request): array
{
    // Validation already passed
    $user = User::create($request->validated());
    return ['user' => $user];
}
```

### 3. Resource Transformation

Transform data for API responses:

```php
namespace App\Http\Resources;

use Hyperf\Resource\Json\JsonResource;

class UserResource extends JsonResource
{
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'created_at' => $this->created_at->format('Y-m-d H:i:s'),
        ];
    }
}

// Controller
use App\Http\Resources\UserResource;

public function index(): array
{
    $users = User::all();
    return UserResource::collection($users)->toArray();
}
```

### 4. Pagination

HyperVel supports pagination similar to Laravel:

```php
// Controller
public function index(): array
{
    $users = User::paginate(15);
    return [
        'data' => $users->items(),
        'pagination' => [
            'current_page' => $users->currentPage(),
            'per_page' => $users->perPage(),
            'total' => $users->total(),
            'last_page' => $users->lastPage(),
        ]
    ];
}
```

## Performance Optimization

### 1. Database Query Optimization

**Use Eager Loading:**

```php
// BAD - N+1 query problem
$users = User::all();
foreach ($users as $user) {
    echo $user->posts->title; // Additional query for each user
}

// GOOD - Eager loading
$users = User::with('posts')->get();
foreach ($users as $user) {
    echo $user->posts->title; // No additional queries
}
```

**Select Only Needed Columns:**

```php
$users = User::select(['id', 'name', 'email'])->get();
```

### 2. Caching

Use Redis for caching:

```php
use Hyperf\Cache\Annotation\Cacheable;

class UserService
{
    #[Cacheable(prefix: 'users', ttl: 3600)]
    public function getAllUsers(): array
    {
        return User::all()->toArray();
    }
}
```

### 3. Connection Pooling

HyperVel automatically manages database connection pools in coroutine context.

## Testing in HyperVel

### Feature Tests

```php
namespace Tests\Feature;

use Hyperf\Testing\Client;
use Tests\TestCase;

class UserTest extends TestCase
{
    public function test_can_create_user()
    {
        $client = make(Client::class);

        $response = $client->post('/api/users', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password123',
        ]);

        $this->assertEquals(201, $response->getStatusCode());
        $this->assertArrayHasKey('user', $response->json());
    }
}
```

### Unit Tests

```php
namespace Tests\Unit;

use App\Services\UserService;
use Tests\TestCase;

class UserServiceTest extends TestCase
{
    public function test_creates_user()
    {
        $userService = new UserService();

        $user = $userService->createUser([
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);

        $this->assertInstanceOf(User::class, $user);
        $this->assertEquals('Test User', $user->name);
    }
}
```

## Common Pitfalls & Solutions

### 1. Blocking Operations in Coroutines

**Problem:**
```php
// BAD - Blocks coroutine
sleep(5); // Blocks entire Swoole server
```

**Solution:**
```php
// GOOD - Non-blocking
use Hyperf\Coroutine\Coroutine;
Coroutine::sleep(5); // Yields control
```

### 2. Static State Across Requests

**Problem:**
```php
// BAD - Static state persists
static $counter = 0;
```

**Solution:**
```php
// GOOD - Use dependency injection
class CounterService
{
    private int $counter = 0;

    public function increment(): int
    {
        return ++$this->counter;
    }
}
```

### 3. Database Transactions in Coroutines

**Problem:**
```php
// BAD - Transaction may leak across requests
DB::beginTransaction();
// Coroutine context switch here
DB::commit();
```

**Solution:**
```php
// GOOD - Use closure-based transaction
DB::transaction(function () {
    // All operations here
    User::create([...]);
});
```

### 4. File I/O in Coroutines

**Problem:**
```php
// BAD - Blocking file operations
file_get_contents('large-file.txt');
```

**Solution:**
```php
// GOOD - Use coroutine-safe I/O
use Hyperf\Coroutine\Channel;
use Swoole\Coroutine\File;

$file = new File('large-file.txt', 'r');
$content = $file->read();
```

## Best Practices

### 1. Use Dependency Injection

Always use DI container instead of `new` keyword:

```php
// BAD
$service = new UserService();

// GOOD
use App\Services\UserService;
private readonly UserService $userService;

public function __construct(UserService $userService)
{
    $this->userService = $userService;
}
```

### 2. Leverage Annotations

Use annotations for configuration:

```php
// BAD - Manual route registration (if applicable)
// Use controller attributes instead

// GOOD
#[GetMapping('/users/{id}')]
public function show(int $id): array { }
```

### 3. Async When Possible

Use async operations for I/O:

```php
use Hyperf\Guzzle\ClientFactory;

$http = make(ClientFactory::class)->create();
// HTTP request is async in coroutine context
$response = $http->get('https://api.example.com/data');
```

### 4. Proper Error Handling

Use try-catch and logging:

```php
use Hyperf\Logger\LoggerFactory;

$logger = make(LoggerFactory::class)->get();

try {
    $user = User::findOrFail($id);
} catch (ModelNotFoundException $e) {
    $logger->error("User not found: {$id}");
    throw new UserNotFoundException($id);
}
```

## Debugging HyperVel Applications

### Enable Debug Mode

```env
# .env
APP_DEBUG=true
```

### View Logs

```bash
tail -f runtime/logs/hyperf.log
```

### Use Tinker

```bash
php bin/hyperf.php
```

### Check Swoole Status

```bash
# Check if Swoole server is running
ps aux | grep swoole
```

## Additional Resources

- [Hyperf Documentation](https://hyperf.wiki/)
- [Swoole Documentation](https://www.swoole.com/docs/)
- [PHP 8 Attributes](https://www.php.net/manual/en/language.attributes.overview.php)
- [Malnu Backend Architecture](ARCHITECTURE.md)
- [Developer Guide](DEVELOPER_GUIDE.md)

---

**Last Updated:** January 9, 2026
**Framework Version:** HyperVel (Hyperf + Swoole)
