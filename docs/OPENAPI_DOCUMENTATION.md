# OpenAPI/Swagger API Documentation

## Overview

This project uses **OpenAPI 3.0** annotations with **swagger-php** to generate comprehensive API documentation programmatically. The documentation is generated from PHPDoc annotations in the controller files, ensuring that the API documentation stays in sync with the code.

## Quick Start

### 1. Generate OpenAPI Specification

Run the following command to generate the OpenAPI specification file:

```bash
php artisan openapi:generate
```

This will scan all controllers in `app/Http/Controllers` and generate `public/openapi.json`.

### 2. Generate in YAML Format

To generate YAML instead of JSON:

```bash
php artisan openapi:generate --format=yaml
```

### 3. Generate to Custom Path

To specify a custom output path:

```bash
php artisan openapi:generate --output=/path/to/openapi.json
```

## Accessing the Documentation

### Swagger UI

Access the interactive Swagger UI documentation at:

```
http://localhost:8000/swagger
```

The Swagger UI provides:
- **Interactive API Explorer** - Test endpoints directly from the browser
- **Request/Response Schemas** - See expected data formats
- **Authentication** - Built-in JWT token support
- **Try It Out** - Execute API calls with pre-filled parameters

### OpenAPI Specification

The raw OpenAPI specification is available at:

```
http://localhost:8000/openapi.json
```

This JSON/YAML file can be used to:
- Generate API client SDKs
- Import into Postman/Insomnia
- Create custom documentation
- Integrate with API gateways

## Adding OpenAPI Annotations

### Basic Controller Annotation

Add class-level annotations to define API metadata:

```php
/**
 * @OA\Info(
 *     title="Your API Title",
 *     version="1.0.0",
 *     description="API Description"
 * )
 * @OA\Server(
 *     url="/api",
 *     description="API Base URL"
 * )
 */
class YourController extends BaseController
{
    // ...
}
```

### Endpoint Annotation

Document each public method:

```php
/**
 * Your endpoint description
 *
 * @OA\Post(
 *     path="/your-endpoint",
 *     summary="Brief description",
 *     description="Detailed description",
 *     tags={"Your Tag"},
 *     security={{"BearerAuth":{}}},
 *     @OA\RequestBody(...),
 *     @OA\Response(...)
 * )
 */
public function yourMethod()
{
    // ...
}
```

### Request Body Annotation

Define request schemas:

```php
@OA\RequestBody(
    required=true,
    @OA\MediaType(
        mediaType="application/json",
        @OA\Schema(
            required={"field1", "field2"},
            @OA\Property(property="field1", type="string", example="value1"),
            @OA\Property(property="field2", type="integer", example=123)
        )
    )
)
```

### Response Annotation

Define response schemas:

```php
@OA\Response(
    response=200,
    description="Success description",
    @OA\MediaType(
        mediaType="application/json",
        @OA\Schema(
            @OA\Property(property="success", type="boolean", example=true),
            @OA\Property(property="message", type="string", example="Success message"),
            @OA\Property(property="data", type="object")
        )
    )
)
```

### Parameter Annotation

Define query/path parameters:

```php
@OA\Parameter(
    name="page",
    in="query",
    description="Page number",
    required=false,
    @OA\Schema(type="integer", default=1)
)
```

### Authentication

Define security schemes:

```php
/**
 * @OA\SecurityScheme(
 *     securityScheme="BearerAuth",
 *     type="http",
 *     scheme="bearer",
 *     bearerFormat="JWT",
 *     description="JWT token authentication"
 * )
 */
class AuthController extends BaseController
{
    // ...

    /**
     * @OA\Get(
     *     path="/protected-endpoint",
     *     tags={"Protected"},
     *     security={{"BearerAuth":{}}}
     * )
     */
    public function protectedEndpoint()
    {
        // ...
    }
}
```

## Annotation Reference

### Common Annotations

| Annotation | Purpose | Example |
|-----------|---------|---------|
| `@OA\Info` | API metadata | title, version, description |
| `@OA\Server` | API base URL | url, description |
| `@OA\Tag` | Endpoint grouping | name, description |
| `@OA\Get` | GET endpoint | path, summary, tags |
| `@OA\Post` | POST endpoint | path, summary, tags |
| `@OA\Put` | PUT endpoint | path, summary, tags |
| `@OA\Delete` | DELETE endpoint | path, summary, tags |
| `@OA\Parameter` | Request parameter | name, in, schema |
| `@OA\RequestBody` | Request body | required, content |
| `@OA\Response` | Response definition | response, description |
| `@OA\Schema` | Data schema | type, properties |
| `@OA\Property` | Schema property | property, type, format |
| `@OA\SecurityScheme` | Authentication | type, scheme |

### Data Types

Supported data types in `@OA\Property`:

- `string` - Text strings
- `integer` - Whole numbers
- `number` - Decimal numbers
- `boolean` - true/false
- `array` - Arrays (use `@OA\Items`)
- `object` - Objects
- `format="uuid"` - UUID format
- `format="email"` - Email format
- `format="date"` - Date format (YYYY-MM-DD)
- `format="date-time"` - DateTime format (ISO 8601)

### Array Definitions

Use `@OA\Items` for array items:

```php
@OA\Property(
    property="items",
    type="array",
    @OA\Items(
        @OA\Property(property="id", type="string", format="uuid"),
        @OA\Property(property="name", type="string")
    )
)
```

## Best Practices

### 1. Keep Annotations Near Code

Documentation should be close to the implementation for easy maintenance:

```php
/**
 * Create a new user
 *
 * @OA\Post(
 *     path="/users",
 *     summary="Create user"
 * )
 */
public function create()  // Implementation right below
{
    // ...
}
```

### 2. Use Meaningful Descriptions

Provide clear, actionable descriptions:

```php
/**
 * Get user profile by ID
 *
 * @OA\Get(
 *     path="/users/{id}",
 *     summary="Get user profile",
 *     description="Retrieves complete user profile including roles and permissions"
 * )
 */
```

### 3. Include Examples

Add example values for all fields:

```php
@OA\Property(
    property="email",
    type="string",
    format="email",
    example="john@example.com"  // Always include examples
)
```

### 4. Document All Status Codes

Don't forget to document error responses:

```php
@OA\Response(response=200, description="Success"),
@OA\Response(response=400, description="Bad Request"),
@OA\Response(response=401, description="Unauthorized"),
@OA\Response(response=404, description="Not Found"),
@OA\Response(response=422, description="Validation Error"),
@OA\Response(response=500, description="Server Error")
```

### 5. Use Enum Constraints

Document allowed values:

```php
@OA\Property(
    property="status",
    type="string",
    enum={"active", "inactive", "pending"},
    example="active"
)
```

## Regenerating Documentation

Regenerate documentation after:

1. Adding new endpoints
2. Modifying existing endpoints
3. Changing request/response schemas
4. Updating authentication requirements

Run:

```bash
php artisan openapi:generate
```

## Integration with Development Workflow

### Git Hooks (Optional)

Add a pre-commit hook to regenerate documentation:

```bash
#!/bin/bash
# .git/hooks/pre-commit
php artisan openapi:generate
git add public/openapi.json
```

### CI/CD Integration

Add documentation generation to CI pipeline:

```yaml
- name: Generate OpenAPI Documentation
  run: php artisan openapi:generate

- name: Deploy Documentation
  run: |
    scp public/openapi.json deploy-server:/api-docs/
    scp public/swagger/index.html deploy-server:/api-docs/
```

## Client Generation

Use the OpenAPI spec to generate client SDKs:

### OpenAPI Generator

```bash
docker run --rm \
  -v ${PWD}:/local \
  openapitools/openapi-generator-cli generate \
  -i /local/openapi.json \
  -g javascript \
  -o /local/generated-client
```

### TypeScript Client

```bash
npm install -g @openapitools/openapi-generator-cli
openapi-generator-cli generate -i openapi.json -g typescript-axios -o ./client
```

### Postman Import

1. Open Postman
2. Click "Import"
3. Select "Link" tab
4. Enter: `http://localhost:8000/openapi.json`
5. Click "Import"

## Troubleshooting

### Annotation Not Appearing

If an endpoint doesn't appear in Swagger UI:

1. Check that the method is `public`
2. Verify annotation syntax (closing braces, quotes)
3. Ensure annotation is directly above the method
4. Regenerate: `php artisan openapi:generate`
5. Clear cache if needed

### Schema Validation Errors

If swagger-php reports validation errors:

1. Check for missing required fields
2. Verify data types match actual values
3. Ensure all `@OA\Property` have `property` attribute
4. Validate enum values are correct

### Performance Considerations

For large codebases:

1. Limit scan directories if needed:
   ```php
   Generator::scan([
       BASE_PATH . '/app/Http/Controllers/Api',
   ]);
   ```

2. Cache generated spec in production
3. Use YAML format for smaller file size

## Additional Resources

- [OpenAPI Specification](https://swagger.io/specification/)
- [swagger-php Documentation](https://zircote.github.io/swagger-php/)
- [Swagger UI](https://swagger.io/tools/swagger-ui/)
- [OpenAPI Generator](https://openapi-generator.tech/docs/generators)

## Example: Complete Endpoint

```php
/**
 * Create a new student
 *
 * Creates a new student record with the provided data.
 * Validates required fields and checks for duplicates.
 *
 * @OA\Post(
 *     path="/school/students",
 *     summary="Create a new student",
 *     description="Registers a new student in the school system with all required information",
 *     tags={"Students"},
 *     security={{"BearerAuth":{}}},
 *     @OA\RequestBody(
 *         required=true,
 *         description="Student data to create",
 *         @OA\MediaType(
 *             mediaType="application/json",
 *             @OA\Schema(
 *                 required={"name", "nisn", "class_id", "enrollment_year", "status"},
 *                 @OA\Property(
 *                     property="name",
 *                     type="string",
 *                     minLength=3,
 *                     maxLength=255,
 *                     example="John Doe",
 *                     description="Full name of the student"
 *                 ),
 *                 @OA\Property(
 *                     property="nisn",
 *                     type="string",
 *                     example="1234567890",
 *                     description="National Student Identification Number"
 *                 ),
 *                 @OA\Property(
 *                     property="class_id",
 *                     type="string",
 *                     format="uuid",
 *                     example="550e8400-e29b-41d4-a716-446655440000",
 *                     description="ID of the class the student is enrolled in"
 *                 ),
 *                 @OA\Property(
 *                     property="enrollment_year",
 *                     type="integer",
 *                     example=2025,
 *                     description="Year of enrollment"
 *                 ),
 *                 @OA\Property(
 *                     property="status",
 *                     type="string",
 *                     enum={"active", "inactive"},
 *                     example="active",
 *                     description="Student enrollment status"
 *                 )
 *             )
 *         )
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Student created successfully",
 *         @OA\MediaType(
 *             mediaType="application/json",
 *             @OA\Schema(
 *                 @OA\Property(property="success", type="boolean", example=true),
 *                 @OA\Property(property="message", type="string", example="Student created successfully"),
 *                 @OA\Property(
 *                     property="data",
 *                     @OA\Property(
 *                         property="id",
 *                         type="string",
 *                         format="uuid",
 *                         example="550e8400-e29b-41d4-a716-446655440000"
 *                     )
 *                 )
 *             )
 *         )
 *     ),
 *     @OA\Response(
 *         response=401,
 *         description="Unauthorized - Invalid or missing JWT token"
 *     ),
 *     @OA\Response(
 *         response=422,
 *         description="Validation Error - Invalid input data"
 *     ),
 *     @OA\Response(
 *         response=500,
 *         description="Server Error - Internal server error occurred"
 *     )
 * )
 */
public function store()
{
    // Implementation...
}
```

## Support

For issues or questions about OpenAPI documentation:
- Check [swagger-php documentation](https://zircote.github.io/swagger-php/)
- Review existing annotations in controller files
- Run `php artisan openapi:generate --help` for command options
