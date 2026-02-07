# API Controller Implementation Guide

This guide provides comprehensive documentation for implementing API controllers in the Malnu Backend application. It covers patterns, conventions, and best practices to ensure consistency across all controllers.

## Table of Contents

- [Controller Structure](#controller-structure)
- [Standard Endpoints](#standard-endpoints)
- [Validation Pattern](#validation-pattern)
- [Service Layer Pattern](#service-layer-pattern)
- [Error Handling](#error-handling)
- [Testing Guide](#testing-guide)
- [Route Registration](#route-registration)
- [Documentation Updates](#documentation-updates)
- [Checklist for New Controllers](#checklist-for-new-controllers)

---

## Controller Structure

### Basic Controller Template

All API controllers should extend `BaseController` and follow this structure:

```php
<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Contracts\YourServiceInterface;
use App\Traits\InputValidationTrait;

/**
 * @OA\Tag(
 *     name="Your Domain",
 *     description="Your domain endpoints description"
 * )
 */
class YourDomainController extends BaseController
{
    use InputValidationTrait;

    private YourServiceInterface $yourService;

    public function __construct(YourServiceInterface $yourService)
    {
        parent::__construct();
        $this->yourService = $yourService;
    }

    // Controller methods here
}
```

### Key Requirements

1. **Strict Types**: Always declare `declare(strict_types=1);` at the top
2. **Namespace**: Use `App\Http\Controllers\Api` namespace
3. **BaseController**: Extend `BaseController` for consistent response methods
4. **InputValidationTrait**: Use for input validation and sanitization
5. **Dependency Injection**: Inject services via constructor
6. **OpenAPI Annotations**: Add `@OA\Tag` documentation for API docs

### Example: AuthController

See `app/Http/Controllers/Api/AuthController.php` for a complete implementation example:

```php
<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Contracts\AuthServiceInterface;
use App\Traits\InputValidationTrait;

/**
 * @OA\Info(
 *     title="Malnu Backend API",
 *     version="1.0.0",
 *     description="API endpoints for Malnu School Management System"
 * )
 * @OA\Tag(
 *     name="Authentication",
 *     description="User authentication and authorization endpoints"
 * )
 */
class AuthController extends BaseController
{
    use InputValidationTrait;

    private AuthServiceInterface $authService;

    public function __construct(AuthServiceInterface $authService)
    {
        parent::__construct();
        $this->authService = $authService;
    }

    // Controller methods...
}
```

---

## Standard Endpoints

### CRUD Operations

Follow RESTful conventions for standard CRUD operations:

| Method | Endpoint | Purpose | HTTP Method |
|---------|----------|---------|-------------|
| `index()` | `/api/your-domain` | List all resources | GET |
| `show($id)` | `/api/your-domain/{id}` | Show single resource | GET |
| `store()` | `/api/your-domain` | Create new resource | POST |
| `update($id, $data)` | `/api/your-domain/{id}` | Update existing resource | PUT/PATCH |
| `destroy($id)` | `/api/your-domain/{id}` | Delete resource | DELETE |

### Example: Standard CRUD Methods

```php
/**
 * List all resources
 *
 * @OA\Get(
 *     path="/api/your-domain",
 *     tags={"Your Domain"},
 *     summary="List all resources",
 *     description="Retrieve a paginated list of resources",
 *     security={{"bearerAuth":{}}},
 *     @OA\Parameter(
 *         name="page",
 *         in="query",
 *         required=false,
 *         description="Page number",
 *         @OA\Schema(type="integer", default=1)
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Resources retrieved successfully",
 *         @OA\JsonContent(
 *             @OA\Property(property="success", type="boolean", example=true),
 *             @OA\Property(property="message", type="string"),
 *             @OA\Property(property="data", type="object")
 *         )
 *     )
 * )
 */
public function index(RequestInterface $request)
{
    $validator = validator($request->all(), [
        'page' => 'nullable|integer|min:1',
        'per_page' => 'nullable|integer|min:1|max:100',
    ]);

    if ($validator->fails()) {
        return $this->validationErrorResponse($validator->errors());
    }

    $page = $request->input('page', 1);
    $perPage = $request->input('per_page', 15);

    $result = $this->yourService->getAll($page, $perPage);

    return $this->successResponse($result, 'Resources retrieved successfully');
}

/**
 * Show single resource
 *
 * @OA\Get(
 *     path="/api/your-domain/{id}",
 *     tags={"Your Domain"},
 *     summary="Show single resource",
 *     description="Retrieve a specific resource by ID",
 *     security={{"bearerAuth":{}}},
 *     @OA\Parameter(
 *         name="id",
 *         in="path",
 *         required=true,
 *         description="Resource UUID",
 *         @OA\Schema(type="string", format="uuid")
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Resource retrieved successfully"
 *     ),
 *     @OA\Response(
 *         response=404,
 *         description="Resource not found"
 *     )
 * )
 */
public function show(string $id)
{
    $result = $this->yourService->getById($id);

    if (!$result) {
        return $this->notFoundResponse('Resource not found');
    }

    return $this->successResponse($result, 'Resource retrieved successfully');
}

/**
 * Create new resource
 *
 * @OA\Post(
 *     path="/api/your-domain",
 *     tags={"Your Domain"},
 *     summary="Create new resource",
 *     description="Create a new resource",
 *     security={{"bearerAuth":{}}},
 *     @OA\RequestBody(
 *         required=true,
 *         @OA\JsonContent(
 *             required={"name"},
 *             @OA\Property(property="name", type="string", example="Example")
 *         )
 *     ),
 *     @OA\Response(
 *         response=201,
 *         description="Resource created successfully"
 *     ),
 *     @OA\Response(
 *         response=422,
 *         description="Validation error"
 *     )
 * )
 */
public function store(RequestInterface $request)
{
    $data = $this->sanitizeInput($request->all());

    $validator = validator($data, [
        'name' => 'required|string|max:255',
        // Add other field validations
    ]);

    if ($validator->fails()) {
        return $this->validationErrorResponse($validator->errors());
    }

    $result = $this->yourService->create($data);

    return $this->successResponse($result, 'Resource created successfully');
}

/**
 * Update existing resource
 *
 * @OA\Put(
 *     path="/api/your-domain/{id}",
 *     tags={"Your Domain"},
 *     summary="Update resource",
 *     description="Update an existing resource",
 *     security={{"bearerAuth":{}}},
 *     @OA\Parameter(
 *         name="id",
 *         in="path",
 *         required=true,
 *         @OA\Schema(type="string", format="uuid")
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Resource updated successfully"
 *     ),
 *     @OA\Response(
 *         response=404,
 *         description="Resource not found"
 *     ),
 *     @OA\Response(
 *         response=422,
 *         description="Validation error"
 *     )
 * )
 */
public function update(RequestInterface $request, string $id)
{
    $data = $this->sanitizeInput($request->all());

    $validator = validator($data, [
        'name' => 'nullable|string|max:255',
        // Add other field validations
    ]);

    if ($validator->fails()) {
        return $this->validationErrorResponse($validator->errors());
    }

    $result = $this->yourService->update($id, $data);

    return $this->successResponse($result, 'Resource updated successfully');
}

/**
 * Delete resource
 *
 * @OA\Delete(
 *     path="/api/your-domain/{id}",
 *     tags={"Your Domain"},
 *     summary="Delete resource",
 *     description="Delete a resource",
 *     security={{"bearerAuth":{}}},
 *     @OA\Parameter(
 *         name="id",
 *         in="path",
 *         required=true,
 *         @OA\Schema(type="string", format="uuid")
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Resource deleted successfully"
 *     ),
 *     @OA\Response(
 *         response=404,
 *         description="Resource not found"
 *     )
 * )
 */
public function destroy(string $id)
{
    $this->yourService->delete($id);

    return $this->successResponse(null, 'Resource deleted successfully');
}
```

---

## Validation Pattern

### Using InputValidationTrait

The `InputValidationTrait` provides reusable validation methods:

```php
use App\Traits\InputValidationTrait;

class YourDomainController extends BaseController
{
    use InputValidationTrait;

    public function store(RequestInterface $request)
    {
        $data = $this->sanitizeInput($request->all());

        // Validate required fields
        $requiredFields = ['name', 'email'];
        $errors = $this->validateRequired($data, $requiredFields);

        // Validate specific fields
        if (isset($data['email']) && !$this->validateEmail($data['email'])) {
            $errors['email'] = ['The email must be a valid email address.'];
        }

        if (isset($data['name']) && !$this->validateStringLength($data['name'], 3)) {
            $errors['name'] = ['The name must be at least 3 characters.'];
        }

        if (isset($data['password'])) {
            $passwordErrors = $this->validatePasswordComplexity($data['password']);
            if (!empty($passwordErrors)) {
                $errors['password'] = $passwordErrors;
            }
        }

        if (!empty($errors)) {
            return $this->validationErrorResponse($errors);
        }

        // Process validated data...
    }
}
```

### Using Hyperf Validator

For more complex validation, use Hyperf's built-in validator:

```php
public function store(RequestInterface $request)
{
    $validator = validator($request->all(), [
        'name' => 'required|string|max:255',
        'email' => 'required|email|unique:users,email',
        'age' => 'nullable|integer|min:1|max:120',
        'status' => 'required|in:active,inactive,pending',
        'start_date' => 'nullable|date|after:today',
        'end_date' => 'nullable|date|after:start_date',
    ]);

    if ($validator->fails()) {
        return $this->validationErrorResponse($validator->errors());
    }

    // Process validated data...
}
```

### Available Validation Methods in InputValidationTrait

| Method | Purpose |
|---------|---------|
| `validateRequired(array $input, array $requiredFields)` | Validate required fields |
| `sanitizeInput(array $input)` | Sanitize input recursively |
| `validateEmail(string $email)` | Validate email format |
| `validatePasswordComplexity(string $password)` | Validate password strength |
| `validateStringLength(string $value, ?int $min, ?int $max)` | Validate string length |
| `validateDate(string $date, string $format)` | Validate date format |
| `validateDateRange(string $startDate, string $endDate)` | Validate date range |
| `validateInteger(mixed $value)` | Validate integer |
| `validateBoolean(mixed $value)` | Validate boolean |
| `validateUuid(string $value)` | Validate UUID format |
| `validateIn(mixed $value, array $allowedValues)` | Validate value in array |
| `validatePattern(string $value, string $pattern)` | Validate regex pattern |

---

## Service Layer Pattern

### Service Layer Philosophy

- **Controllers** handle HTTP concerns only (request/response)
- **Services** contain business logic and domain operations
- **Models** handle data persistence and relationships

### Service Interface Pattern

Define service interfaces for better testability and dependency injection:

```php
<?php

declare(strict_types=1);

namespace App\Contracts;

interface YourServiceInterface
{
    public function getAll(int $page = 1, int $perPage = 15): array;
    public function getById(string $id): ?array;
    public function create(array $data): array;
    public function update(string $id, array $data): array;
    public function delete(string $id): bool;
}
```

### Service Implementation

```php
<?php

declare(strict_types=1);

namespace App\Services;

use App\Contracts\YourServiceInterface;
use App\Models\YourModel;

class YourService implements YourServiceInterface
{
    public function getAll(int $page = 1, int $perPage = 15): array
    {
        // Business logic here
        return YourModel::paginate($perPage, ['*'], 'page', $page)->toArray();
    }

    public function getById(string $id): ?array
    {
        $model = YourModel::find($id);

        if (!$model) {
            throw new \Exception('Resource not found');
        }

        return $model->toArray();
    }

    public function create(array $data): array
    {
        // Business logic and validation
        $model = new YourModel($data);
        $model->save();

        return $model->toArray();
    }

    public function update(string $id, array $data): array
    {
        $model = YourModel::find($id);

        if (!$model) {
            throw new \Exception('Resource not found');
        }

        $model->fill($data);
        $model->save();

        return $model->toArray();
    }

    public function delete(string $id): bool
    {
        $model = YourModel::find($id);

        if (!$model) {
            throw new \Exception('Resource not found');
        }

        return $model->delete();
    }
}
```

### Service Dependency Injection in Controller

```php
class YourDomainController extends BaseController
{
    private YourServiceInterface $yourService;

    public function __construct(YourServiceInterface $yourService)
    {
        parent::__construct();
        $this->yourService = $yourService;
    }

    public function show(string $id)
    {
        try {
            $result = $this->yourService->getById($id);
            return $this->successResponse($result, 'Resource retrieved successfully');
        } catch (\Exception $e) {
            return $this->notFoundResponse($e->getMessage());
        }
    }
}
```

---

## Error Handling

### Standard Response Methods

`BaseController` provides standardized response methods:

```php
// Success response
return $this->successResponse($data, 'Operation successful', 200);

// Error response
return $this->errorResponse('Error message', 'ERROR_CODE', null, 400);

// Validation error response
return $this->validationErrorResponse($errors);

// Not found response
return $this->notFoundResponse('Resource not found');

// Unauthorized response
return $this->unauthorizedResponse('Unauthorized');

// Forbidden response
return $this->forbiddenResponse('Forbidden');

// Server error response
return $this->serverErrorResponse('Internal server error');
```

### Response Format

#### Success Response
```json
{
    "success": true,
    "data": {
        "id": "123e4567-e89b-12d3-a456-426614174000",
        "name": "Example Resource"
    },
    "message": "Operation successful",
    "timestamp": "2026-02-07T09:25:53+00:00"
}
```

#### Error Response
```json
{
    "success": false,
    "error": {
        "message": "Error message",
        "code": "ERROR_CODE",
        "details": {
            "additional": "error details"
        }
    },
    "timestamp": "2026-02-07T09:25:53+00:00"
}
```

### Try-Catch Pattern

```php
public function store(RequestInterface $request)
{
    try {
        $data = $this->sanitizeInput($request->all());

        $validator = validator($data, [
            // validation rules
        ]);

        if ($validator->fails()) {
            return $this->validationErrorResponse($validator->errors());
        }

        $result = $this->yourService->create($data);

        return $this->successResponse($result, 'Resource created successfully');
    } catch (\Exception $e) {
        $this->logger->error('Failed to create resource', [
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString(),
        ]);

        return $this->serverErrorResponse('Failed to create resource');
    }
}
```

### Custom Exceptions

Define domain-specific exceptions:

```php
<?php

declare(strict_types=1);

namespace App\Exceptions;

class ResourceNotFoundException extends \Exception
{
    public function __construct(string $resource = 'Resource')
    {
        parent::__construct("{$resource} not found");
    }
}

class UnauthorizedException extends \Exception
{
    public function __construct(string $message = 'Unauthorized')
    {
        parent::__construct($message);
    }
}
```

Use custom exceptions in services:

```php
// In Service
if (!$model) {
    throw new ResourceNotFoundException('Student');
}

// In Controller
try {
    $result = $this->service->getById($id);
    return $this->successResponse($result);
} catch (ResourceNotFoundException $e) {
    return $this->notFoundResponse($e->getMessage());
} catch (UnauthorizedException $e) {
    return $this->unauthorizedResponse($e->getMessage());
}
```

---

## Testing Guide

### Unit Tests

Test service methods in isolation:

```php
<?php

declare(strict_types=1);

namespace Tests\Unit;

use Tests\TestCase;
use App\Services\YourService;
use App\Models\YourModel;

class YourServiceTest extends TestCase
{
    private YourService $yourService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->yourService = new YourService();
    }

    public function test_can_create_resource()
    {
        $data = [
            'name' => 'Test Resource',
            'email' => 'test@example.com',
        ];

        $result = $this->yourService->create($data);

        $this->assertIsArray($result);
        $this->assertArrayHasKey('id', $result);
        $this->assertEquals('Test Resource', $result['name']);
        $this->assertEquals('test@example.com', $result['email']);
    }

    public function test_get_by_id_returns_resource()
    {
        $model = YourModel::factory()->create([
            'name' => 'Test Resource',
        ]);

        $result = $this->yourService->getById($model->id);

        $this->assertIsArray($result);
        $this->assertEquals($model->id, $result['id']);
        $this->assertEquals('Test Resource', $result['name']);
    }

    public function test_get_by_id_throws_exception_for_invalid_id()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Resource not found');

        $this->yourService->getById('invalid-uuid');
    }

    public function test_can_update_resource()
    {
        $model = YourModel::factory()->create(['name' => 'Old Name']);

        $result = $this->yourService->update($model->id, ['name' => 'New Name']);

        $this->assertIsArray($result);
        $this->assertEquals('New Name', $result['name']);
    }

    public function test_can_delete_resource()
    {
        $model = YourModel::factory()->create();

        $result = $this->yourService->delete($model->id);

        $this->assertTrue($result);
        $this->assertDatabaseMissing('your_table', ['id' => $model->id]);
    }
}
```

### Feature Tests

Test API endpoints end-to-end:

```php
<?php

declare(strict_types=1);

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Services\JWTService;

class YourDomainControllerTest extends TestCase
{
    private string $token;

    protected function setUp(): void
    {
        parent::setUp();

        $user = User::factory()->create();
        $jwtService = new JWTService();
        $this->token = $jwtService->generateToken($user->id, $user->email, $user->role);
    }

    public function test_can_list_resources()
    {
        $response = $this->get('/api/your-domain', [
            'Authorization' => 'Bearer ' . $this->token,
        ]);

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'success',
            'message',
            'data',
            'timestamp',
        ]);
    }

    public function test_can_create_resource()
    {
        $response = $this->post('/api/your-domain', [
            'name' => 'Test Resource',
            'email' => 'test@example.com',
        ], [
            'Authorization' => 'Bearer ' . $this->token,
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'message' => 'Resource created successfully',
        ]);
        $response->assertJsonStructure([
            'success',
            'message',
            'data' => [
                'id',
                'name',
                'email',
            ],
            'timestamp',
        ]);
    }

    public function test_validates_required_fields()
    {
        $response = $this->post('/api/your-domain', [], [
            'Authorization' => 'Bearer ' . $this->token,
        ]);

        $response->assertStatus(422);
        $response->assertJson([
            'success' => false,
            'message' => 'Validation error',
        ]);
    }

    public function test_validates_email_format()
    {
        $response = $this->post('/api/your-domain', [
            'name' => 'Test Resource',
            'email' => 'invalid-email',
        ], [
            'Authorization' => 'Bearer ' . $this->token,
        ]);

        $response->assertStatus(422);
        $response->assertJson([
            'success' => false,
        ]);
    }

    public function test_can_show_single_resource()
    {
        $model = YourModel::factory()->create();

        $response = $this->get('/api/your-domain/' . $model->id, [
            'Authorization' => 'Bearer ' . $this->token,
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'data' => [
                'id' => $model->id,
            ],
        ]);
    }

    public function test_returns_404_for_non_existent_resource()
    {
        $response = $this->get('/api/your-domain/non-existent-id', [
            'Authorization' => 'Bearer ' . $this->token,
        ]);

        $response->assertStatus(404);
    }

    public function test_unauthorized_access_without_token()
    {
        $response = $this->get('/api/your-domain');

        $response->assertStatus(401);
    }

    public function test_can_update_resource()
    {
        $model = YourModel::factory()->create(['name' => 'Old Name']);

        $response = $this->put('/api/your-domain/' . $model->id, [
            'name' => 'New Name',
        ], [
            'Authorization' => 'Bearer ' . $this->token,
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'message' => 'Resource updated successfully',
            'data' => [
                'id' => $model->id,
                'name' => 'New Name',
            ],
        ]);
    }

    public function test_can_delete_resource()
    {
        $model = YourModel::factory()->create();

        $response = $this->delete('/api/your-domain/' . $model->id, [], [
            'Authorization' => 'Bearer ' . $this->token,
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'message' => 'Resource deleted successfully',
        ]);
        $this->assertDatabaseMissing('your_table', ['id' => $model->id]);
    }
}
```

### Running Tests

```bash
# Run all tests
composer test

# Run specific test file
vendor/bin/co-phpunit tests/Feature/YourDomainControllerTest.php

# Run specific test method
vendor/bin/co-phpunit tests/Feature/YourDomainControllerTest.php --filter test_can_create_resource

# Run with code coverage
vendor/bin/co-phpunit --coverage-html coverage
```

---

## Route Registration

### Adding Routes to `routes/api.php`

Add routes to `routes/api.php` following this pattern:

```php
<?php

declare(strict_types=1);

use App\Http\Controllers\Api\YourDomainController;
use Hyperf\Support\Facades\Route;

// Public routes (no authentication)
Route::group(['middleware' => ['input.sanitization', 'rate.limit']], function () {
    // Add public routes here
});

// Protected routes (JWT authentication required)
Route::group(['middleware' => ['jwt', 'rate.limit']], function () {
    Route::prefix('your-domain')->group(function () {
        Route::get('/', [YourDomainController::class, 'index']);
        Route::get('/{id}', [YourDomainController::class, 'show']);
        Route::post('/', [YourDomainController::class, 'store']);
        Route::put('/{id}', [YourDomainController::class, 'update']);
        Route::delete('/{id}', [YourDomainController::class, 'destroy']);
    });
});

// Role-based routes (specific roles required)
Route::group(['middleware' => ['jwt', 'rate.limit', 'role:Admin|Teacher']], function () {
    // Add role-based routes here
});
```

### Route Parameters

```php
// Simple parameter
Route::get('/your-domain/{id}', [YourDomainController::class, 'show']);

// Optional parameter
Route::get('/your-domain/{id?}', [YourDomainController::class, 'show']);

// Multiple parameters
Route::get('/your-domain/{id}/items/{itemId}', [YourDomainController::class, 'showItem']);
```

### Query Parameters

Query parameters are automatically available via `$request->input()`:

```php
public function index(RequestInterface $request)
{
    $page = $request->input('page', 1);
    $perPage = $request->input('per_page', 15);
    $search = $request->input('search');
    $sort = $request->input('sort', 'created_at');
    $order = $request->input('order', 'desc');

    // Process parameters...
}
```

---

## Documentation Updates

### OpenAPI/Swagger Annotations

Add comprehensive OpenAPI annotations to controller methods:

```php
/**
 * Create new resource
 *
 * @OA\Post(
 *     path="/api/your-domain",
 *     tags={"Your Domain"},
 *     summary="Create new resource",
 *     description="Create a new resource with the provided data",
 *     security={{"bearerAuth":{}}},
 *     @OA\RequestBody(
 *         required=true,
 *         @OA\JsonContent(
 *             required={"name","email"},
 *             @OA\Property(property="name", type="string", example="Example Resource"),
 *             @OA\Property(property="email", type="string", format="email", example="example@test.com"),
 *             @OA\Property(property="description", type="string", example="Optional description")
 *         )
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Resource created successfully",
 *         @OA\JsonContent(
 *             @OA\Property(property="success", type="boolean", example=true),
 *             @OA\Property(property="message", type="string", example="Resource created successfully"),
 *             @OA\Property(property="data", type="object",
 *                 @OA\Property(property="id", type="string", format="uuid"),
 *                 @OA\Property(property="name", type="string"),
 *                 @OA\Property(property="email", type="string", format="email")
 *             )
 *         )
 *     ),
 *     @OA\Response(
 *         response=401,
 *         description="Unauthorized - authentication required"
 *     ),
 *     @OA\Response(
 *         response=422,
 *         description="Validation error",
 *         @OA\JsonContent(
 *             @OA\Property(property="success", type="boolean", example=false),
 *             @OA\Property(property="message", type="string", example="Validation error"),
 *             @OA\Property(property="errors", type="object")
 *         )
 *     )
 * )
 */
public function store(RequestInterface $request)
{
    // Implementation...
}
```

### Update `docs/API.md`

Add new endpoint documentation to `docs/API.md`:

```markdown
## Your Domain

### Create Resource

**Endpoint**: `POST /api/your-domain`

**Authentication**: Required (Bearer Token)

**Request Body**:

```json
{
  "name": "Example Resource",
  "email": "example@test.com",
  "description": "Optional description"
}
```

**Response** (200 OK):

```json
{
  "success": true,
  "message": "Resource created successfully",
  "data": {
    "id": "123e4567-e89b-12d3-a456-426614174000",
    "name": "Example Resource",
    "email": "example@test.com",
    "description": "Optional description",
    "created_at": "2026-02-07T09:25:53+00:00",
    "updated_at": "2026-02-07T09:25:53+00:00"
  },
  "timestamp": "2026-02-07T09:25:53+00:00"
}
```

**Error Response** (422 Unprocessable Entity):

```json
{
  "success": false,
  "message": "Validation error",
  "error": {
    "message": "Validation error",
    "code": "VALIDATION_ERROR",
    "details": {
      "name": ["The name field is required."],
      "email": ["The email must be a valid email address."]
    }
  },
  "timestamp": "2026-02-07T09:25:53+00:00"
}
```
```

---

## Checklist for New Controllers

Before completing a new API controller, ensure all items are checked:

### Structure & Setup
- [ ] Controller extends `BaseController`
- [ ] Uses `InputValidationTrait` for validation
- [ ] Uses `declare(strict_types=1);` at the top
- [ ] Namespace is `App\Http\Controllers\Api`
- [ ] Service dependency injected via constructor
- [ ] `parent::__construct()` called in constructor

### Endpoints
- [ ] Standard CRUD methods implemented (index, show, store, update, destroy)
- [ ] Each endpoint has OpenAPI/Swagger annotations
- [ ] Each endpoint validates input data
- [ ] Each endpoint sanitizes input data
- [ ] Each endpoint returns standardized response format

### Validation
- [ ] Required fields validated
- [ ] Field types validated
- [ ] Field lengths validated
- [ ] Business rules validated
- [ ] Validation errors returned via `validationErrorResponse()`

### Error Handling
- [ ] Try-catch blocks for exception handling
- [ ] Errors logged appropriately
- [ ] Appropriate HTTP status codes returned
- [ ] Error messages are user-friendly
- [ ] Sensitive information not exposed in errors

### Testing
- [ ] Unit tests for service methods
- [ ] Feature tests for API endpoints
- [ ] Test success scenarios
- [ ] Test failure scenarios
- [ ] Test validation errors
- [ ] Test authentication/authorization
- [ ] All tests passing

### Documentation
- [ ] OpenAPI/Swagger annotations complete
- [ ] `docs/API.md` updated with new endpoints
- [ ] Request/response examples provided
- [ ] Authentication requirements documented
- [ ] Error responses documented

### Code Quality
- [ ] Code follows PSR-12 standard
- [ ] PHPStan analysis passes
- [ ] PHP CS Fixer passes
- [ ] No deprecated methods used
- [ ] Code is readable and maintainable
- [ ] Comments for complex logic added

### Security
- [ ] Input sanitization applied
- [ ] SQL injection prevented (parameterized queries)
- [ ] XSS prevention applied
- [ ] Authentication middleware applied where needed
- [ ] Authorization checks where needed
- [ ] Sensitive data not logged

### Routes
- [ ] Routes registered in `routes/api.php`
- [ ] Appropriate middleware applied
- [ ] Route naming follows convention
- [ ] Route groups organized logically

---

## Related Resources

### Documentation
- [API Documentation](docs/API.md) - Complete API reference
- [Architecture](docs/ARCHITECTURE.md) - System architecture
- [Business Domains Guide](docs/BUSINESS_DOMAINS_GUIDE.md) - 11 business domains overview
- [Security Policy](SECURITY.md) - Security guidelines

### Existing Controllers
- `app/Http/Controllers/Api/AuthController.php` - Complete authentication controller
- `app/Http/Controllers/Api/AttendanceController.php` - Attendance management
- `app/Http/Controllers/Api/PasswordChangeController.php` - Password operations

### Testing
- `tests/Feature/AuthServiceTest.php` - Service testing example
- `tests/Feature/ApiEndpointTest.php` - API endpoint testing example
- `tests/README.md` - Test suite documentation

### Framework
- [Hyperf Documentation](https://hyperf.wiki/) - Official Hyperf framework docs
- [Swoole Documentation](https://www.swoole.com/) - High-performance PHP framework

---

## Common Patterns & Best Practices

### Pagination

```php
public function index(RequestInterface $request)
{
    $page = (int) $request->input('page', 1);
    $perPage = min((int) $request->input('per_page', 15), 100);

    $result = YourModel::paginate($perPage, ['*'], 'page', $page)->toArray();

    return $this->successResponse($result, 'Resources retrieved successfully');
}
```

### Filtering & Search

```php
public function index(RequestInterface $request)
{
    $query = YourModel::query();

    // Apply filters
    if ($request->has('status')) {
        $query->where('status', $request->input('status'));
    }

    if ($request->has('search')) {
        $search = $request->input('search');
        $query->where(function ($q) use ($search) {
            $q->where('name', 'like', "%{$search}%")
              ->orWhere('email', 'like', "%{$search}%");
        });
    }

    // Apply sorting
    $sort = $request->input('sort', 'created_at');
    $order = $request->input('order', 'desc');
    $query->orderBy($sort, $order);

    // Paginate
    $page = (int) $request->input('page', 1);
    $perPage = min((int) $request->input('per_page', 15), 100);

    $result = $query->paginate($perPage, ['*'], 'page', $page)->toArray();

    return $this->successResponse($result, 'Resources retrieved successfully');
}
```

### Bulk Operations

```php
public function bulkUpdate(RequestInterface $request)
{
    $validator = validator($request->all(), [
        'items' => 'required|array',
        'items.*.id' => 'required|exists:your_table,id',
        'items.*.name' => 'nullable|string|max:255',
    ]);

    if ($validator->fails()) {
        return $this->validationErrorResponse($validator->errors());
    }

    $updated = 0;
    $failed = [];

    foreach ($request->input('items', []) as $item) {
        try {
            $this->yourService->update($item['id'], $item);
            $updated++;
        } catch (\Exception $e) {
            $failed[] = [
                'id' => $item['id'],
                'error' => $e->getMessage(),
            ];
        }
    }

    return $this->successResponse([
        'updated' => $updated,
        'failed' => count($failed),
        'errors' => $failed,
    ], 'Bulk update completed');
}
```

---

This guide provides a comprehensive reference for implementing API controllers. For questions or clarifications, refer to existing controller implementations in the codebase or consult with the team.
