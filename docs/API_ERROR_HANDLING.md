# API Error Handling and Response Standardization

This implementation provides comprehensive API error handling and response standardization for the HyperVel/Laravel-style application.

## Features

### 1. Standardized API Controller
- `App\Http\Controllers\Api\BaseController` provides standardized response methods
- Consistent response format across all API endpoints
- Proper error logging and handling

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

## Benefits

- **Consistency**: All API responses follow the same format
- **Developer Experience**: Predictable response structure
- **Error Handling**: Proper error logging and standardized messages
- **Maintainability**: Centralized response logic
- **Security**: Controlled error information exposure