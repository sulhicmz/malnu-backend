# OpenAPI/Swagger Documentation for Malnu Backend

This document explains the OpenAPI/Swagger documentation setup for Malnu Backend API.

## Overview

The API is documented using OpenAPI 3.0 annotations with swagger-php library. Annotations are added directly to PHP controller classes and methods.

## Installation

After adding the `zircote/swagger-php` dependency to `composer.json`, install the package:

```bash
composer require --dev zircote/swagger-php
```

## Generating OpenAPI Documentation

To generate the OpenAPI specification file:

```bash
# Using swagger-php generator
vendor/bin/openapi -o public/swagger.json app

# Or if you have configured it through config
php artisan swagger:generate
```

This will create `public/swagger.json` (and optionally `swagger.yaml`) with the API specification.

## Accessing Swagger UI

You can use Swagger UI to interact with the API documentation. There are several ways to set this up:

### Option 1: Online Swagger UI Editor

Visit https://editor.swagger.io/ and load your generated `swagger.json` file.

### Option 2: Local Swagger UI Setup

1. Download Swagger UI:
   ```bash
   cd public
   git clone https://github.com/swagger-api/swagger-ui.git --depth 1 swagger-ui
   ```

2. Configure it to load your spec:
   ```javascript
   // public/swagger-ui/index.html
   const ui = SwaggerUIBundle({
       url: '/swagger.json',
       dom_id: '#swagger-ui',
       presets: [
           SwaggerUIBundle.presets.apis,
           SwaggerUIStandalonePreset
       ]
   });
   ```

3. Access at: `http://localhost:9501/swagger-ui/`

### Option 3: Using Swagger-PHP Generator

Swagger-php includes a built-in viewer. Run:

```bash
vendor/bin/openapi app -o public/docs --viewer
```

This creates an interactive viewer at `public/docs/index.html`.

## API Tags

The following tags are currently documented:

- **Authentication** - User authentication and authorization endpoints
- **Attendance** - Attendance tracking and management endpoints
- **Student** - Student management endpoints
- **Teacher** - Teacher management endpoints

## Authentication

Most endpoints require JWT authentication. To authenticate:

1. Call `/auth/login` with email and password
2. Receive a JWT token in the response
3. Include the token in the Authorization header:
   ```
   Authorization: Bearer <your_jwt_token>
   ```

## Annotation Examples

### Class-Level Annotations

```php
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
}
```

### Method-Level Annotations (GET)

```php
/**
 * @OA\Get(
 *     path="/api/attendance/student/{studentId}",
 *     tags={"Attendance"},
 *     summary="Get student attendance",
 *     description="Retrieve attendance records for a specific student",
 *     security={{"bearerAuth":{}}},
 *     @OA\Parameter(
 *         name="studentId",
 *         in="path",
 *         required=true,
 *         description="Student UUID",
 *         @OA\Schema(type="string", format="uuid")
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Student attendance retrieved successfully",
 *         @OA\JsonContent(...)
 *     )
 * )
 */
public function getStudentAttendance(string $studentId)
{
}
```

### Method-Level Annotations (POST)

```php
/**
 * @OA\Post(
 *     path="/auth/login",
 *     tags={"Authentication"},
 *     summary="User login",
 *     description="Authenticate user with email and password",
 *     @OA\RequestBody(
 *         required=true,
 *         @OA\JsonContent(
 *             required={"email","password"},
 *             @OA\Property(property="email", type="string", format="email"),
 *             @OA\Property(property="password", type="string", format="password")
 *         )
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Login successful",
 *         @OA\JsonContent(...)
 *     )
 * )
 */
public function login()
{
}
```

## Best Practices

1. **Document All Endpoints**: Ensure every public API method has full OpenAPI annotations
2. **Be Specific**: Use appropriate HTTP status codes (200, 201, 400, 401, 403, 404, 422, 500)
3. **Include Examples**: Add example values for all request and response properties
4. **Document Security**: Always include `security={{"bearerAuth":{}}}` for endpoints requiring authentication
5. **Use Correct Types**: Use appropriate JSON Schema types (string, integer, boolean, array, object)
6. **Format Specifiers**: Use format specifiers like `email`, `date`, `uuid`, `password` for validation
7. **Enum Values**: Document allowed values using `enum={...}`

## Configuration

The Swagger configuration is in `config/swagger.php`:

```php
return [
    'scan' => [
        'paths' => [
            'app/Http/Controllers/Api',
        ],
        'exclude' => [
            'vendor',
        ],
    ],
    'output' => [
        'file' => 'public/swagger.json',
        'yaml' => true,
    ],
];
```

## Response Format

All API responses follow a consistent format:

```json
{
  "success": true,
  "message": "Operation successful",
  "data": { ... }
}
```

For validation errors:

```json
{
  "success": false,
  "message": "Validation error",
  "errors": {
    "field_name": ["Error message"]
  }
}
```

## Status Codes

- `200` - OK: Request succeeded
- `201` - Created: Resource created successfully
- `400` - Bad Request: Invalid request data
- `401` - Unauthorized: Authentication required or failed
- `403` - Forbidden: User lacks permission
- `404` - Not Found: Resource not found
- `422` - Unprocessable Entity: Validation failed
- `500` - Internal Server Error: Server error

## Next Steps

1. Add OpenAPI annotations to remaining controllers (AcademicRecords, Inventory, Schedule, etc.)
2. Set up automatic generation in deployment pipeline
3. Consider adding Swagger UI as a permanent feature
4. Add response examples for all endpoints
5. Document error responses more comprehensively

## References

- [OpenAPI 3.0 Specification](https://swagger.io/specification/)
- [Swagger-PHP Library](https://github.com/zircote/swagger-php)
- [OpenAPI Annotations Reference](https://github.com/zircote/swagger-php/tree/master/docs)
- [Swagger UI](https://swagger.io/tools/swagger-ui/)
