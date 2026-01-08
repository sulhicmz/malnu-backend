# Malnu Backend API - Developer Guide

## Table of Contents
1. [Quick Start](#quick-start)
2. [Authentication](#authentication)
3. [API Conventions](#api-conventions)
4. [Error Handling](#error-handling)
5. [Rate Limiting](#rate-limiting)
6. [Code Examples](#code-examples)
7. [Best Practices](#best-practices)
8. [Troubleshooting](#troubleshooting)

---

## Quick Start

### Base URL
- **Development**: `http://localhost:9501/api`
- **Production**: `https://api.malnu.edu/api`

### Making Your First Request

1. **Register a new user**:
```bash
curl -X POST http://localhost:9501/api/auth/register \
  -H "Content-Type: application/json" \
  -d '{
    "name": "John Doe",
    "email": "john.doe@example.com",
    "password": "securepassword123"
  }'
```

2. **Login to get JWT token**:
```bash
curl -X POST http://localhost:9501/api/auth/login \
  -H "Content-Type: application/json" \
  -d '{
    "email": "john.doe@example.com",
    "password": "securepassword123"
  }'
```

3. **Use the token for authenticated requests**:
```bash
curl -X GET http://localhost:9501/api/school/students \
  -H "Authorization: Bearer YOUR_JWT_TOKEN_HERE"
```

---

## Authentication

### JWT-Based Authentication

The API uses JSON Web Tokens (JWT) for authentication. Most endpoints require a valid JWT token in the `Authorization` header.

### Obtaining a Token

**Register** a new account or **login** with existing credentials to receive a JWT token:

```json
{
  "success": true,
  "data": {
    "token": "eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9...",
    "user": {
      "id": 1,
      "name": "John Doe",
      "email": "john.doe@example.com"
    }
  },
  "message": "Login successful",
  "timestamp": "2024-01-01T00:00:00+00:00"
}
```

### Using the Token

Include the token in the `Authorization` header with the `Bearer` scheme:

```http
Authorization: Bearer eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9...
```

### Token Refresh

JWT tokens have an expiration time. Refresh your token before it expires:

```bash
curl -X POST http://localhost:9501/api/auth/refresh \
  -H "Authorization: Bearer YOUR_JWT_TOKEN_HERE"
```

### Logout

To invalidate your token and logout:

```bash
curl -X POST http://localhost:9501/api/auth/logout \
  -H "Authorization: Bearer YOUR_JWT_TOKEN_HERE"
```

### Password Reset

1. **Request a reset**:
```bash
curl -X POST http://localhost:9501/api/auth/password/forgot \
  -H "Content-Type: application/json" \
  -d '{"email": "john.doe@example.com"}'
```

2. **Reset with token** (received via email):
```bash
curl -X POST http://localhost:9501/api/auth/password/reset \
  -H "Content-Type: application/json" \
  -d '{
    "token": "reset_token_from_email",
    "password": "newpassword123"
  }'
```

---

## API Conventions

### Request Format

All API requests should use:
- **Content-Type**: `application/json`
- **Accept**: `application/json`
- **Body**: JSON-formatted data in request body

### Response Format

All API responses follow a consistent structure:

#### Success Response

```json
{
  "success": true,
  "data": {
    // Response data
  },
  "message": "Operation successful",
  "timestamp": "2024-01-01T00:00:00+00:00"
}
```

#### Error Response

```json
{
  "success": false,
  "error": {
    "message": "Error message here",
    "code": "ERROR_CODE",
    "details": {
      // Additional error details (for validation errors)
    }
  },
  "timestamp": "2024-01-01T00:00:00+00:00"
}
```

### HTTP Status Codes

| Code | Description | Usage |
|------|-------------|-------|
| 200 | OK | Successful GET, PUT, DELETE |
| 201 | Created | Successful POST (resource created) |
| 400 | Bad Request | Validation error, invalid input |
| 401 | Unauthorized | Missing or invalid authentication |
| 403 | Forbidden | Insufficient permissions |
| 404 | Not Found | Resource does not exist |
| 422 | Unprocessable Entity | Validation error |
| 500 | Internal Server Error | Server-side error |

### Pagination

List endpoints support pagination with these query parameters:

| Parameter | Type | Default | Description |
|-----------|------|---------|-------------|
| `page` | integer | 1 | Page number |
| `limit` | integer | 15 | Items per page |

**Example**:
```bash
curl -X GET "http://localhost:9501/api/school/students?page=2&limit=25"
```

### Filtering

Many list endpoints support filtering via query parameters:

```bash
curl -X GET "http://localhost:9501/api/school/students?status=active&class_id=5"
```

### Sorting

Sort results using query parameters where supported:

```bash
curl -X GET "http://localhost:9501/api/school/students?sort=name&order=asc"
```

---

## Error Handling

### Error Response Structure

All errors follow this structure:

```json
{
  "success": false,
  "error": {
    "message": "Error description",
    "code": "ERROR_CODE",
    "details": {
      // Field-specific errors for validation failures
      "email": ["The email field is required."]
    }
  },
  "timestamp": "2024-01-01T00:00:00+00:00"
}
```

### Common Error Codes

| Code | HTTP Status | Description |
|------|-------------|-------------|
| `VALIDATION_ERROR` | 422 | Request validation failed |
| `UNAUTHORIZED` | 401 | Authentication required or failed |
| `FORBIDDEN` | 403 | Insufficient permissions |
| `NOT_FOUND` | 404 | Resource not found |
| `SERVER_ERROR` | 500 | Internal server error |
| `INSUFFICIENT_BALANCE` | 400 | Insufficient leave balance |
| `REGISTRATION_ERROR` | 400 | User registration failed |
| `STUDENT_CREATION_ERROR` | 400 | Student creation failed |

### Handling Errors

Always check the `success` field and handle errors appropriately:

```javascript
const response = await fetch('/api/school/students', {
  headers: {
    'Authorization': `Bearer ${token}`
  }
});

const data = await response.json();

if (!data.success) {
  // Handle error
  console.error('Error:', data.error.message, data.error.code);
  if (data.error.details) {
    // Handle validation errors
    console.log('Validation errors:', data.error.details);
  }
  return;
}

// Process successful response
console.log('Data:', data.data);
```

---

## Rate Limiting

API requests are rate limited to prevent abuse and ensure fair usage.

### Rate Limit Headers

Check response headers for rate limit information:

```http
X-RateLimit-Limit: 100
X-RateLimit-Remaining: 95
X-RateLimit-Reset: 1704067200
```

| Header | Description |
|--------|-------------|
| `X-RateLimit-Limit` | Maximum requests per window |
| `X-RateLimit-Remaining` | Remaining requests in current window |
| `X-RateLimit-Reset` | Unix timestamp when limit resets |

### Handling Rate Limits

When you exceed the rate limit, you'll receive a `429 Too Many Requests` response:

```json
{
  "success": false,
  "error": {
    "message": "Too many requests. Please try again later.",
    "code": "RATE_LIMIT_EXCEEDED"
  },
  "timestamp": "2024-01-01T00:00:00+00:00"
}
```

Implement exponential backoff or retry-after logic:

```javascript
async function makeRequest(url) {
  let retries = 3;
  let delay = 1000; // Start with 1 second

  while (retries > 0) {
    try {
      const response = await fetch(url);
      if (response.status === 429) {
        const resetTime = response.headers.get('X-RateLimit-Reset');
        const retryAfter = response.headers.get('Retry-After');

        // Use Retry-After header or calculated delay
        const waitTime = retryAfter ? parseInt(retryAfter) * 1000 : delay;
        await new Promise(resolve => setTimeout(resolve, waitTime));

        delay *= 2; // Exponential backoff
        retries--;
        continue;
      }

      return await response.json();
    } catch (error) {
      if (retries === 1) throw error;
      await new Promise(resolve => setTimeout(resolve, delay));
      delay *= 2;
      retries--;
    }
  }
}
```

---

## Code Examples

### JavaScript/Fetch

#### Authentication
```javascript
// Login
const login = async (email, password) => {
  const response = await fetch('http://localhost:9501/api/auth/login', {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json',
    },
    body: JSON.stringify({ email, password })
  });
  return await response.json();
};

// Use with authentication
const getData = async (token) => {
  const response = await fetch('http://localhost:9501/api/school/students', {
    headers: {
      'Authorization': `Bearer ${token}`
    }
  });
  return await response.json();
};
```

#### Create Student
```javascript
const createStudent = async (token, studentData) => {
  const response = await fetch('http://localhost:9501/api/school/students', {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json',
      'Authorization': `Bearer ${token}`
    },
    body: JSON.stringify(studentData)
  });
  return await response.json();
};
```

### Python/Requests

```python
import requests

BASE_URL = "http://localhost:9501/api"

# Login
def login(email, password):
    response = requests.post(
        f"{BASE_URL}/auth/login",
        json={"email": email, "password": password}
    )
    return response.json()

# Get students with token
def get_students(token):
    headers = {"Authorization": f"Bearer {token}"}
    response = requests.get(f"{BASE_URL}/school/students", headers=headers)
    return response.json()

# Create leave request
def create_leave_request(token, data):
    headers = {
        "Content-Type": "application/json",
        "Authorization": f"Bearer {token}"
    }
    response = requests.post(
        f"{BASE_URL}/attendance/leave-requests",
        json=data,
        headers=headers
    )
    return response.json()
```

### PHP/cURL

```php
<?php

class ApiClient {
    private $baseUrl;
    private $token;

    public function __construct($baseUrl) {
        $this->baseUrl = $baseUrl;
    }

    public function login($email, $password) {
        $ch = curl_init($this->baseUrl . '/auth/login');
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode([
            'email' => $email,
            'password' => $password
        ]));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json'
        ]);

        $response = curl_exec($ch);
        curl_close($ch);

        $data = json_decode($response, true);
        $this->token = $data['data']['token'] ?? null;
        return $data;
    }

    public function get($endpoint) {
        $ch = curl_init($this->baseUrl . $endpoint);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: Bearer ' . $this->token
        ]);

        $response = curl_exec($ch);
        curl_close($ch);

        return json_decode($response, true);
    }

    public function post($endpoint, $data) {
        $ch = curl_init($this->baseUrl . $endpoint);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Authorization: Bearer ' . $this->token
        ]);

        $response = curl_exec($ch);
        curl_close($ch);

        return json_decode($response, true);
    }
}

// Usage
$api = new ApiClient('http://localhost:9501/api');
$api->login('user@example.com', 'password');
$students = $api->get('/school/students');
```

---

## Best Practices

### Security

1. **Never expose tokens** in client-side code
2. **Use HTTPS** in production environments
3. **Validate and sanitize** all input data
4. **Implement proper error handling** without exposing sensitive information
5. **Use environment variables** for sensitive configuration
6. **Implement rate limiting** on your application side

### Authentication

1. **Store tokens securely** (HttpOnly cookies, secure storage)
2. **Refresh tokens before expiration**
3. **Implement proper logout** to invalidate tokens
4. **Use strong passwords** and encourage users to do the same
5. **Implement multi-factor authentication** for sensitive operations

### API Usage

1. **Handle all response codes** appropriately
2. **Implement retry logic** with exponential backoff
3. **Use pagination** for large datasets
4. **Cache responses** where appropriate (GET requests)
5. **Use filtering** to reduce data transfer
6. **Validate responses** before processing

### Error Handling

1. **Always check the `success` field**
2. **Log errors** for debugging
3. **Display user-friendly error messages**
4. **Implement graceful degradation**
5. **Monitor error rates** to identify issues

### Performance

1. **Minimize payload sizes**
2. **Use compression** (gzip)
3. **Implement caching** strategies
4. **Use connection pooling**
5. **Optimize database queries**

---

## Troubleshooting

### Common Issues

#### 401 Unauthorized

**Problem**: API returns 401 error

**Solutions**:
1. Check that the `Authorization` header is present
2. Verify the token format: `Authorization: Bearer TOKEN`
3. Ensure the token hasn't expired
4. Refresh the token if needed

#### 422 Validation Error

**Problem**: API returns 422 with validation errors

**Solutions**:
1. Check the `error.details` field for specific errors
2. Ensure all required fields are present
3. Verify field formats (email, dates, etc.)
4. Check field length constraints

#### 429 Rate Limit Exceeded

**Problem**: API returns 429 rate limit error

**Solutions**:
1. Implement exponential backoff
2. Check `Retry-After` header
3. Reduce request frequency
4. Implement caching to reduce duplicate requests

#### 500 Internal Server Error

**Problem**: API returns 500 error

**Solutions**:
1. Check server logs for details
2. Ensure request format is correct
3. Try the request again after a delay
4. Contact API support if issue persists

#### Network Timeout

**Problem**: Requests timeout

**Solutions**:
1. Increase timeout duration
2. Check network connectivity
3. Verify API URL is correct
4. Implement retry logic

### Debug Mode

For development, enable debug mode in your environment configuration:

```bash
# In .env file
APP_ENV=local
APP_DEBUG=true
```

### Getting Help

If you encounter issues not covered here:
1. Check the interactive API documentation (Swagger UI)
2. Review the OpenAPI specification
3. Check server logs for detailed error information
4. Contact API support at support@malnu.edu

---

## Additional Resources

- **OpenAPI Specification**: `/docs/api/openapi.yaml`
- **Interactive Documentation**: `/api/docs` (Swagger UI)
- **Repository**: https://github.com/sulhicmz/malnu-backend
- **Issues**: Report bugs or request features via GitHub Issues

---

## Version History

| Version | Date | Changes |
|---------|------|---------|
| 1.0.0 | 2024-01-01 | Initial API documentation |
