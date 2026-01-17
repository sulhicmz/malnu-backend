# Form Request Validation Best Practices

This guide explains how to use Form Request validation classes in the Malnu Backend application following Hyperf/Laravel conventions.

## Overview

Form Request validation classes provide a clean, reusable way to handle validation logic for API requests. They:

- **Separate validation logic from controllers** - Keep controllers focused on business logic
- **Centralize validation rules** - Define rules once, reuse across multiple endpoints
- **Provide custom error messages** - Clear, user-friendly validation errors
- **Support authorization** - Control who can submit requests
- **Follow DRY principle** - Don't Repeat Yourself - eliminate code duplication

## Standard Pattern

### 1. Create Form Request Class

Create a Form Request class in `app/Http/Requests/{Module}/`:

```php
<?php

declare(strict_types=1);

namespace App\Http\Requests\SchoolManagement;

use Hyperf\Foundation\Http\FormRequest;

class StoreStudent extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
            'status' => 'required|in:active,inactive',
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'The name field is required.',
            'email.email' => 'The email must be a valid email address.',
            'status.in' => 'The status must be active or inactive.',
        ];
    }
}
```

### 2. Type-Hint Form Request in Controller

Update your controller to use the Form Request class:

```php
<?php

namespace App\Http\Controllers\Api\SchoolManagement;

use App\Http\Controllers\Api\BaseController;
use App\Http\Requests\SchoolManagement\StoreStudent;
use App\Models\SchoolManagement\Student;

class StudentController extends BaseController
{
    public function store(StoreStudent $request)
    {
        $validated = $request->validated();

        $student = Student::create($validated);

        return $this->successResponse($student, 'Student created successfully', 201);
    }
}
```

### 3. Form Request Automatically Handles Validation

When you type-hint a Form Request in your controller method:

1. **Automatic validation** - Hyperf validates the request before your controller method runs
2. **Automatic error responses** - Validation errors return 422 status automatically
3. **Access validated data** - Use `$request->validated()` to get sanitized input

## Validation Rules Reference

### Common Validation Rules

| Rule | Description | Example |
|-------|-------------|---------|
| `required` | Field must be present and not empty | `'name' => 'required'` |
| `nullable` | Field can be null | `'email' => 'nullable\|email'` |
| `string` | Must be a string | `'name' => 'string'` |
| `integer` | Must be an integer | `'age' => 'integer'` |
| `email` | Must be valid email format | `'email' => 'email'` |
| `date` | Must be valid date | `'join_date' => 'date'` |
| `min:value` | Minimum value/length | `'age' => 'min:18'` |
| `max:value` | Maximum value/length | `'name' => 'max:255'` |
| `in:val1,val2` | Must be one of listed values | `'status' => 'in:active,inactive'` |
| `exists:table,column` | Must exist in database | `'class_id' => 'exists:classes,id'` |
| `unique:table,column` | Must be unique in database | `'email' => 'unique:students,email'` |
| `after:date` | Must be after specified date | `'end_date' => 'after:start_date'` |
| `before:date` | Must be before specified date | `'join_date' => 'before:today'` |

### Store vs Update Requests

For **store** operations:
```php
public function rules(): array
{
    return [
        'email' => 'required|email|unique:students,email',
        'nisn' => 'required|unique:students,nisn',
    ];
}
```

For **update** operations - exclude current record from unique check:
```php
public function rules(): array
{
    $id = $this->route('id');

    return [
        'email' => 'nullable|email|unique:students,email,' . $id,
        'nisn' => 'required|unique:students,nisn,' . $id,
    ];
}
```

## Authorization

Control who can submit requests using the `authorize()` method:

```php
public function authorize(): bool
{
    $user = $this->request->getAttribute('user');

    if ($this->route('id') !== $user->id) {
        return false;
    }

    return true;
}
```

If `authorize()` returns `false`, a 403 Forbidden response is returned automatically.

## Custom Error Messages

Override the `messages()` method for custom error messages:

```php
public function messages(): array
{
    return [
        'email.required' => 'We need your email address.',
        'password.min' => 'Password must be at least 8 characters.',
        'terms.accepted' => 'You must accept our terms.',
    ];
}
```

## Examples in This Project

### Student Validation

**Store Request** - `app/Http/Requests/SchoolManagement/StoreStudent.php`
**Update Request** - `app/Http/Requests/SchoolManagement/UpdateStudent.php`

### Teacher Validation

**Store Request** - `app/Http/Requests/SchoolManagement/StoreTeacher.php`
**Update Request** - `app/Http/Requests/SchoolManagement/UpdateTeacher.php`

### Leave Request Validation

**Store Request** - `app/Http/Requests/Attendance/StoreLeaveRequest.php`

## Migration from Manual Validation

### Old Pattern (Deprecated)

**Controller with manual validation:**
```php
class StudentController extends BaseController
{
    use CrudOperationsTrait;

    protected array $validationRules = [
        'required' => ['name', 'email'],
    ];

    public function store()
    {
        $data = $this->request->all();
        $errors = $this->validateStoreData($data);

        if (! empty($errors)) {
            return $this->validationErrorResponse($errors);
        }

        Student::create($data);
    }
}
```

**Problems:**
- Validation logic in controller (not reusable)
- Manual error handling
- Code duplication across controllers
- Harder to test validation in isolation

### New Pattern (Recommended)

**Controller with Form Request:**
```php
class StudentController extends BaseController
{
    use CrudOperationsTrait;

    public function store(StoreStudent $request)
    {
        $validated = $request->validated();

        Student::create($validated);
    }
}
```

**Benefits:**
- Validation logic in separate class (reusable)
- Automatic error handling
- No code duplication
- Easy to test in isolation
- Follows Hyperf/Laravel conventions

## Testing Form Requests

Create tests to verify validation rules:

```php
<?php

namespace Tests\Feature;

use Tests\TestCase;

class StudentValidationTest extends TestCase
{
    public function test_student_store_validation_fails_with_missing_required_fields()
    {
        $response = $this->postJson('/api/school/students', [
            'name' => 'John Doe',
        ]);

        $response->assertStatus(422)
                 ->assertJson([
                     'success' => false,
                     'error' => [
                         'code' => 'VALIDATION_ERROR'
                     ]
                 ]);
    }
}
```

## Best Practices

1. **Always type-hint Form Request classes** in controller methods
2. **Use specific validation rules** (e.g., `exists:students,email`) not generic checks
3. **Provide clear error messages** in the `messages()` method
4. **Handle `nullable` fields** appropriately for updates vs stores
5. **Exclude current record from unique checks** in update operations
6. **Test validation rules** with both valid and invalid data
7. **Use Form Requests for all user input validation**, not manual validation
8. **Don't use `$validationRules` in controllers** - create Form Request classes instead

## References

- [Hyperf Validation Documentation](https://hyperf.wiki/3.0/en/en/validation)
- [Laravel Validation Documentation](https://laravel.com/docs/validation)
- [Form Request Validation Pattern](https://laravel.com/docs/validation#form-request-validation)
