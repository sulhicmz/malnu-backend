# API Error Handling and Response Standardization

This implementation provides comprehensive API error handling and response standardization for the HyperVel/Laravel-style application.

## Features

### 1. Standardized API Controller
- `App\Http\Controllers\Api\BaseController` provides standardized response methods
- Consistent response format across all API endpoints
- Proper error logging and handling

### 2. Controllers Using Standardized Error Handling

All controllers in the application now use standardized error handling:

#### Authentication (app/Http/Controllers/Api/)
- **AuthController** - User registration, login, logout, token refresh, password reset

#### School Management (app/Http/Controllers/Api/SchoolManagement/)
- **StudentController** - Student CRUD operations
- **TeacherController** - Teacher CRUD operations

#### Attendance (app/Http/Controllers/Attendance/)
- **StaffAttendanceController** - Staff attendance tracking and management
- **LeaveRequestController** - Leave request management
- **LeaveTypeController** - Leave type management

#### Calendar (app/Http/Controllers/Calendar/)
- **CalendarController** - Calendar and event management

All controllers extend `BaseController` and use standardized response methods, ensuring consistent API responses throughout the application.

### 2. Standard Response Formats

#### Success Response
```json
{
    "success": true,
    "data": {...},
    "message": "Operation successful",
    "timestamp": "2025-11-22T10:30:00+00:00"
}
```

#### Error Response
```json
{
    "success": false,
    "error": {
        "message": "Error description",
        "code": "ERROR_CODE",
        "details": {...}
    },
    "timestamp": "2025-11-22T10:30:00+00:00"
}
```

### 3. Standardized Response Methods

- `successResponse($data, $message, $statusCode)` - Standard success response
- `errorResponse($message, $errorCode, $details, $statusCode)` - Standard error response
- `validationErrorResponse($errors)` - Validation error response
- `notFoundResponse($message)` - Not found error response
- `unauthorizedResponse($message)` - Unauthorized error response
- `forbiddenResponse($message)` - Forbidden error response
- `serverErrorResponse($message)` - Server error response

### 4. Error Handling Middleware
- `App\Http\Middleware\ApiErrorHandlingMiddleware` provides global exception handling
- Catches unhandled exceptions and returns standardized error responses
- Logs error details for debugging

## Usage

To use the standardized API responses in your controllers:

```php
<?php

namespace App\Http\Controllers\YourNamespace;

use App\Http\Controllers\Api\BaseController;

class YourController extends BaseController
{
    public function index()
    {
        $data = YourModel::all();
        return $this->successResponse($data, 'Data retrieved successfully');
    }
    
    public function store()
    {
        try {
            // Your logic here
            $data = YourModel::create($request->all());
            return $this->successResponse($data, 'Data created successfully', 201);
        } catch (\Exception $e) {
            return $this->serverErrorResponse('Failed to create data');
        }
    }
    
    public function show($id)
    {
        $data = YourModel::find($id);
        if (!$data) {
            return $this->notFoundResponse('Data not found');
        }
        return $this->successResponse($data);
    }
}
```

## Migration Guide

If you have existing controllers that use manual `response()->json()` calls, follow these steps to migrate to standardized error handling:

1. **Update Parent Class**
   ```php
   // Before
   use App\Http\Controllers\Controller;
   class YourController extends Controller
   
   // After
   use App\Http\Controllers\Api\BaseController;
   class YourController extends BaseController
   ```

2. **Update Constructor**
   ```php
   public function __construct(
       RequestInterface $request,
       ResponseInterface $response,
       ContainerInterface $container
   ) {
       parent::__construct($request, $response, $container);
   }
   ```

3. **Replace Response Calls**
   ```php
   // Before
   return response()->json([
       'success' => true,
       'data' => $data
   ]);
   
   // After
   return $this->successResponse($data);
   ```

4. **Replace Error Response Calls**
   ```php
   // Before
   return response()->json([
       'success' => false,
       'message' => 'Resource not found'
   ], 404);
   
   // After
   return $this->notFoundResponse('Resource not found');
   ```

## Recent Updates

### Issue #355: Standardize Error Handling Across All Controllers

Completed standardization of error handling for all controllers:

- **StaffAttendanceController**: Migrated from manual `response()->json()` to BaseController methods
- **LeaveTypeController**: Migrated from manual `response()->json()` to BaseController methods

Both controllers now:
- Extend `BaseController` instead of `Controller`
- Use Hyperf contracts (RequestInterface, ResponseInterface, ContainerInterface)
- Include proper constructor with dependency injection
- Return standardized responses with timestamps
- Log errors automatically with context information
- Use standardized error codes for better debugging

## Benefits
 
- **Consistency**: All API responses follow the same format
- **Developer Experience**: Predictable response structure
- **Error Handling**: Proper error logging and standardized messages
- **Maintainability**: Centralized response logic
- **Security**: Controlled error information exposure
- **Observability**: Automatic error logging with request context