# CrudOperationsTrait

The `CrudOperationsTrait` provides generic CRUD (Create, Read, Update, Delete) operations for controllers, eliminating code duplication and promoting the DRY (Don't Repeat Yourself) principle.

## Overview

This trait provides a complete set of CRUD operations that can be easily configured through class properties and customized through hook methods. It's designed to work with controllers extending from `BaseController`.

## Features

- **Generic CRUD operations**: `index()`, `store()`, `show()`, `update()`, `destroy()`
- **Configurable**: Easy to customize through protected properties
- **Flexible**: Hook methods for custom logic
- **Validation**: Built-in validation and unique field checking
- **Filtering**: Query parameter filtering support
- **Search**: Full-text search across specified fields
- **Pagination**: Automatic pagination with configurable limits
- **Relationships**: Support for eager loading relationships

## Usage

### Basic Example

```php
<?php

namespace App\Http\Controllers\Api\Example;

use App\Http\Controllers\Api\BaseController;
use App\Models\Example;
use App\Traits\CrudOperationsTrait;

class ExampleController extends BaseController
{
    use CrudOperationsTrait;

    protected string $model = Example::class;
    protected string $resourceName = 'Example';
}
```

### Configured Example

```php
<?php

namespace App\Http\Controllers\Api\SchoolManagement;

use App\Http\Controllers\Api\BaseController;
use App\Models\SchoolManagement\Student;
use App\Traits\CrudOperationsTrait;

class StudentController extends BaseController
{
    use CrudOperationsTrait;

    protected string $model = Student::class;
    protected string $resourceName = 'Student';
    protected array $relationships = ['class'];
    protected array $uniqueFields = ['nisn', 'email'];
    protected array $allowedFilters = ['class_id', 'status'];
    protected array $searchFields = ['name', 'nisn'];
    protected array $validationRules = [
        'required' => ['name', 'nisn', 'class_id', 'enrollment_year', 'status'],
        'email' => 'email',
    ];
}
```

## Properties

### Required Properties

| Property | Type | Description |
|----------|------|-------------|
| `$model` | string | The fully qualified model class name (e.g., `Student::class`) |
| `$resourceName` | string | Human-readable name for the resource (used in messages) |

### Optional Properties

| Property | Type | Default | Description |
|----------|------|---------|-------------|
| `$relationships` | array | `[]` | Eager load relationships (e.g., `['class', 'teacher']`) |
| `$uniqueFields` | array | `[]` | Fields to check for uniqueness |
| `$allowedFilters` | array | `[]` | Query parameters allowed for filtering (e.g., `['status', 'class_id']`) |
| `$searchFields` | array | `[]` | Fields to search in when `search` query param is provided |
| `$defaultOrderBy` | string | `'id'` | Default field for ordering results |
| `$defaultOrderDirection` | string | `'asc'` | Default sort direction (`'asc'` or `'desc'`) |
| `$defaultPerPage` | int | `15` | Default number of items per page |
| `$validationRules` | array | `[]` | Validation rules for the resource |

## Validation Rules

The `$validationRules` array supports the following keys:

### Required Fields

```php
protected array $validationRules = [
    'required' => ['name', 'email', 'status'],
];
```

### Email Validation

```php
protected array $validationRules = [
    'email' => 'email',
];
```

## Hook Methods

The trait provides several hook methods that you can override in your controller to add custom logic:

### beforeIndex($query)

Modify the query before fetching the index results.

```php
protected function beforeIndex($query)
{
    $user = $this->request->getAttribute('user_id');
    
    return $query->where('user_id', $user);
}
```

### beforeStore(array $data): array

Process or validate data before creating a record.

```php
protected function beforeStore(array $data): array
{
    $data['created_by'] = $this->request->getAttribute('user_id');
    
    return $data;
}
```

### afterStore(Model $model): void

Perform actions after creating a record.

```php
protected function afterStore(Model $model): void
{
    // Send notification
    Notification::send($model->user, new CreatedNotification($model));
}
```

### beforeUpdate(array $data, Model $model): array

Process or validate data before updating a record.

```php
protected function beforeUpdate(array $data, Model $model): array
{
    $data['updated_by'] = $this->request->getAttribute('user_id');
    
    return $data;
}
```

### afterUpdate(Model $model): void

Perform actions after updating a record.

```php
protected function afterUpdate(Model $model): void
{
    // Log the update
    Log::info("Updated {$this->resourceName}", ['id' => $model->id]);
}
```

### beforeDestroy(Model $model)

Check conditions before deleting a record. Return `false` to prevent deletion.

```php
protected function beforeDestroy(Model $model)
{
    if ($model->leaveRequests()->count() > 0) {
        return false;
    }
    
    return true;
}
```

### afterDestroy(Model $model): void

Perform actions after deleting a record.

```php
protected function afterDestroy(Model $model): void
{
    // Clean up related records
    $model->related()->delete();
}
```

## API Endpoints

When using this trait, the following endpoints are automatically available:

### GET /{resource}
List all records with filtering and pagination.

**Query Parameters:**
- `page` - Page number (default: 1)
- `limit` - Items per page (default: 15)
- `search` - Search term (searches in `$searchFields`)
- `{filter}` - Filter by field name (must be in `$allowedFilters`)

**Example:**
```
GET /api/school/students?status=active&search=John&page=2&limit=10
```

### POST /{resource}
Create a new record.

**Request Body:**
```json
{
    "name": "John Doe",
    "nisn": "1234567890",
    "class_id": "class-1",
    "status": "active"
}
```

**Response (201 Created):**
```json
{
    "success": true,
    "data": { ... },
    "message": "Student created successfully"
}
```

### GET /{resource}/{id}
Retrieve a single record by ID.

**Response (200 OK):**
```json
{
    "success": true,
    "data": { ... },
    "message": "Student retrieved successfully"
}
```

**Response (404 Not Found):**
```json
{
    "success": false,
    "error": {
        "message": "Student not found",
        "code": "NOT_FOUND"
    }
}
```

### PUT /{resource}/{id}
Update a record by ID.

**Request Body:**
```json
{
    "name": "Updated Name",
    "status": "inactive"
}
```

**Response (200 OK):**
```json
{
    "success": true,
    "data": { ... },
    "message": "Student updated successfully"
}
```

### DELETE /{resource}/{id}
Delete a record by ID.

**Response (200 OK):**
```json
{
    "success": true,
    "message": "Student deleted successfully"
}
```

## Error Responses

### Validation Error (422)
```json
{
    "success": false,
    "error": {
        "message": "Validation failed",
        "code": "VALIDATION_ERROR",
        "details": {
            "name": ["The name field is required."],
            "email": ["The email must be a valid email address."]
        }
    }
}
```

### Duplicate Field Error (400)
```json
{
    "success": false,
    "error": {
        "message": "The nisn has already been taken.",
        "code": "STUDENT_CREATION_ERROR"
    }
}
```

## Examples

### StudentController

```php
class StudentController extends BaseController
{
    use CrudOperationsTrait;

    protected string $model = Student::class;
    protected string $resourceName = 'Student';
    protected array $relationships = ['class'];
    protected array $uniqueFields = ['nisn', 'email'];
    protected array $allowedFilters = ['class_id', 'status'];
    protected array $searchFields = ['name', 'nisn'];
    protected array $validationRules = [
        'required' => ['name', 'nisn', 'class_id', 'enrollment_year', 'status'],
        'email' => 'email',
    ];
}
```

### TeacherController

```php
class TeacherController extends BaseController
{
    use CrudOperationsTrait;

    protected string $model = Teacher::class;
    protected string $resourceName = 'Teacher';
    protected array $relationships = ['subject', 'class'];
    protected array $uniqueFields = ['nip', 'email'];
    protected array $allowedFilters = ['subject_id', 'class_id', 'status'];
    protected array $searchFields = ['name', 'nip'];
    protected array $validationRules = [
        'required' => ['name', 'nip', 'subject_id', 'join_date'],
        'email' => 'email',
    ];
}
```

## Best Practices

1. **Keep controllers simple**: Use the trait for basic CRUD and only override what's necessary
2. **Use hook methods**: Instead of completely overriding methods, use hooks for custom logic
3. **Configure relationships**: Always eager load relationships to prevent N+1 queries
4. **Use filters and search**: Leverage the built-in filtering and search capabilities
5. **Validate at the right level**: Use the trait's validation for simple rules, consider form requests for complex validation

## Migration Guide

If you have an existing controller with CRUD operations, here's how to migrate:

### Before (Duplicate Code)

```php
class ExampleController extends BaseController
{
    public function index()
    {
        try {
            $query = Example::with(['relation']);
            $page = (int) $this->request->query('page', 1);
            $limit = (int) $this->request->query('limit', 15);
            
            $results = $query->orderBy('name', 'asc')
                ->paginate($limit, ['*'], 'page', $page);

            return $this->successResponse($results, 'Examples retrieved successfully');
        } catch (\Exception $e) {
            return $this->serverErrorResponse($e->getMessage());
        }
    }
    
    public function store()
    {
        // ... 50 lines of code
    }
    
    public function show(string $id)
    {
        // ... 30 lines of code
    }
    
    public function update(string $id)
    {
        // ... 60 lines of code
    }
    
    public function destroy(string $id)
    {
        // ... 30 lines of code
    }
}
```

### After (Using CrudOperationsTrait)

```php
class ExampleController extends BaseController
{
    use CrudOperationsTrait;

    protected string $model = Example::class;
    protected string $resourceName = 'Example';
    protected array $relationships = ['relation'];
    protected array $allowedFilters = ['status'];
    protected array $searchFields = ['name'];
    protected array $validationRules = [
        'required' => ['name'],
    ];
}
```

**Result**: Reduced from ~170 lines to ~15 lines of code!

## Limitations

- Requires model to follow Eloquent conventions
- Uses HTTP status codes from `BaseController` response methods
- Email validation is currently limited to simple format checking
- Assumes use of `$this->request` and `$this->response` from `BaseController`

## Contributing

When adding new CRUD controllers, consider using this trait to maintain consistency and reduce code duplication.
