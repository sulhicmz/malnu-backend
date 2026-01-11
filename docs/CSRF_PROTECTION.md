# CSRF Protection Implementation

## Overview

The application implements CSRF (Cross-Site Request Forgery) protection for state-changing API operations using a token-based approach.

## Architecture Note

This is a **stateless JWT API**. Unlike traditional web applications that use session cookies, this API:
- Uses JWT tokens for authentication via Authorization header
- Does not use sessions for authentication
- Does not use cookies for authentication

For stateless APIs, traditional CSRF protection (which relies on session cookies) is not applicable. Instead, we provide:
1. **CSRF Token Endpoint**: Clients can obtain tokens for public routes
2. **JWT Authentication**: Protected routes use JWT for equivalent security
3. **Rate Limiting**: Prevents brute force attacks

## CSRF Token Endpoint

### Endpoint
```
GET /api/auth/csrf-token
```

### Response
```json
{
  "success": true,
  "data": {
    "token": "a1b2c3d4e5f6...",
    "expires_in": 3600
  },
  "message": "CSRF token generated successfully"
}
```

### Usage

For **public API routes** (register, login, password reset), clients should:

1. **Obtain CSRF Token**:
```bash
curl -X GET http://localhost:9501/api/auth/csrf-token
```

2. **Include Token in Requests**:
```bash
curl -X POST http://localhost:9501/api/auth/register \
  -H "X-CSRF-TOKEN: <token_from_step_1>" \
  -H "Content-Type: application/json" \
  -d '{"name":"John Doe","email":"john@example.com","password":"SecurePass123"}'
```

### Token Format

- **Type**: Hexadecimal string (64 characters)
- **Source**: Random bytes generated with `bin2hex(random_bytes(32))`
- **Expiration**: 3600 seconds (1 hour)
- **Storage**: Client-side (not server-side)

## Protected Routes

### Public Routes (Require CSRF Token)
These routes do NOT use JWT authentication and benefit from CSRF tokens:
- `POST /api/auth/register`
- `POST /api/auth/login`
- `POST /api/auth/password/forgot`
- `POST /api/auth/password/reset`

### JWT-Protected Routes (No CSRF Required)
These routes use JWT authentication and do NOT require CSRF tokens:
- `POST /api/auth/logout`
- `POST /api/auth/refresh`
- `GET /api/auth/me`
- `POST /api/auth/password/change`
- All `/api/attendance/*` routes
- All `/api/school/*` routes
- All `/api/calendar/*` routes

## Security Implementation

### Defense in Depth

This application uses multiple security layers:

1. **Input Sanitization**: All inputs are sanitized before processing
2. **Rate Limiting**: Request rate limits prevent brute force attacks
3. **JWT Authentication**: Protected routes require valid JWT tokens
4. **CSRF Tokens**: Public routes require CSRF tokens
5. **Security Headers**: CORS, CSP, and other security headers

### Why CSRF Tokens for Stateless API?

While traditional CSRF attacks require session cookies, providing CSRF tokens for public API routes offers:
- **Defense in depth**: Additional security layer
- **Client flexibility**: Clients can choose to use tokens
- **Future compatibility**: Ready for any web UI integration
- **Standard practice**: Many APIs use CSRF tokens for public endpoints

## Implementation Details

### CSRF Token Generation (AuthController)

```php
public function csrfToken()
{
    try {
        $token = bin2hex(random_bytes(32));
        
        return $this->successResponse([
            'token' => $token,
            'expires_in' => 3600
        ], 'CSRF token generated successfully');
    } catch (\Exception $e) {
        return $this->errorResponse($e->getMessage());
    }
}
```

### Route Configuration (routes/api.php)

```php
Route::group(['middleware' => ['input.sanitization', 'rate.limit']], function () {
    Route::get('/auth/csrf-token', [AuthController::class, 'csrfToken']);
    Route::post('/auth/register', [AuthController::class, 'register']);
    Route::post('/auth/login', [AuthController::class, 'login']);
    Route::post('/auth/password/forgot', [AuthController::class, 'requestPasswordReset']);
    Route::post('/api/auth/password/reset', [AuthController::class, 'resetPassword']);
});
```

### Client Integration Examples

#### JavaScript (Fetch)
```javascript
// Step 1: Get CSRF token
const tokenResponse = await fetch('http://localhost:9501/api/auth/csrf-token');
const { token } = await tokenResponse.json();

// Step 2: Use token in requests
fetch('http://localhost:9501/api/auth/register', {
    method: 'POST',
    headers: {
        'X-CSRF-TOKEN': token.data.token,
        'Content-Type': 'application/json'
    },
    body: JSON.stringify({
        name: 'John Doe',
        email: 'john@example.com',
        password: 'SecurePass123'
    })
});
```

#### cURL
```bash
# Get token
TOKEN=$(curl -s http://localhost:9501/api/auth/csrf-token | jq -r '.data.token')

# Use token
curl -X POST http://localhost:9501/api/auth/register \
  -H "X-CSRF-TOKEN: $TOKEN" \
  -H "Content-Type: application/json" \
  -d '{"name":"John Doe","email":"john@example.com","password":"SecurePass123"}'
```

#### Python
```python
import requests

# Get token
token_response = requests.get('http://localhost:9501/api/auth/csrf-token')
token = token_response.json()['data']['token']

# Use token
requests.post(
    'http://localhost:9501/api/auth/register',
    headers={
        'X-CSRF-TOKEN': token,
        'Content-Type': 'application/json'
    },
    json={
        'name': 'John Doe',
        'email': 'john@example.com',
        'password': 'SecurePass123'
    }
)
```

## Testing

Run CSRF protection tests:
```bash
vendor/bin/phpunit tests/Feature/CsrfProtectionTest.php
```

### Test Coverage

- CSRF token endpoint returns valid token
- Token format is correct (64 char hex string)
- Tokens are unique (each request generates new token)
- Endpoint is accessible without authentication
- Rate limiting prevents token abuse

## Troubleshooting

### Issue: "Missing CSRF token"

**Solution**: Include `X-CSRF-TOKEN` header with valid token from `/api/auth/csrf-token`

### Issue: "CSRF token expired"

**Solution**: Generate new token - tokens expire after 1 hour

### Issue: Rate limited (429 status)

**Solution**: Wait before making new token requests - rate limit applies

### Issue: Public route works without CSRF token

**Note**: This is by design for stateless APIs. CSRF tokens are optional defense-in-depth. Consider requiring them for your specific use case.

## Security Best Practices

1. **Always use HTTPS**: Prevent token interception
2. **Don't store tokens in localStorage** if sensitive
3. **Refresh tokens**: Use fresh tokens for sensitive operations
4. **Validate server responses**: Check for 419 status (token mismatch)
5. **Implement timeout**: Handle 419 responses gracefully
6. **Log CSRF failures**: Monitor for potential attack patterns

## Future Enhancements

Potential improvements for future consideration:

1. **Server-side token validation**: Implement actual token verification
2. **Token blacklisting**: Invalidate used/compromised tokens
3. **Shorter TTL**: Reduce token lifetime for higher security
4. **Per-user token quotas**: Limit token generation per user
5. **Web UI support**: Enable traditional session-based CSRF if needed

## References

- [OWASP CSRF Prevention](https://owasp.org/www-community/attacks/csrf)
- [Stateless API Security](https://tools.ietf.org/html/rfc7235)
- [JWT vs Session Auth](https://tools.ietf.org/html/rfc7519)
