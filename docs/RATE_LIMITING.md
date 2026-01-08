# Rate Limiting System

This document describes the rate limiting system implemented for the Malnu Backend API.

## Overview

The rate limiting system protects the API from abuse, brute force attacks, and denial of service attempts by limiting the number of requests clients can make within a given time period.

## Implementation

### Middleware

The rate limiting is implemented in `app/Http/Middleware/RateLimitingMiddleware.php`, which:

- Implements PSR-7 `MiddlewareInterface`
- Uses Redis for efficient rate limit storage
- Supports both IP-based and user-based limiting
- Returns appropriate HTTP headers for rate limit information
- Responds with `429 Too Many Requests` when limits are exceeded

### Configuration

The rate limiting configuration is defined in `config/rate-limiting.php`:

```php
'default' => env('RATE_LIMIT_DRIVER', 'redis'),

'limits' => [
    'auth.login' => [
        'max_attempts' => 5,
        'decay_minutes' => 1,
        'key_type' => 'ip',
    ],
    // ... more rules
],
```

### Rate Limit Rules

The following rate limits are applied:

| Rule | Max Attempts | Time Window | Key Type | Applied To |
|------|-------------|-------------|----------|------------|
| `auth.login` | 5 | 1 minute | IP | `POST /auth/login` |
| `auth.register` | 3 | 1 minute | IP | `POST /auth/register` |
| `auth.password.reset` | 3 | 1 minute | IP | `POST /auth/password/reset` |
| `public_api` | 60 | 1 minute | IP | All public auth endpoints |
| `protected_api` | 300 | 1 minute | User | All protected endpoints (JWT required) |

### Environment Variables

Configure rate limiting in your `.env` file:

```env
# Rate Limiting Configuration
RATE_LIMIT_DRIVER=redis
RATE_LIMIT_REDIS_CONNECTION=default
RATE_LIMIT_PREFIX=ratelimit:
```

## Response Headers

When rate limiting is active, the following headers are included in responses:

- `X-RateLimit-Limit`: The maximum number of requests allowed in the time window
- `X-RateLimit-Remaining`: The number of requests remaining in the time window
- `X-RateLimit-Reset`: Unix timestamp when the rate limit window resets
- `Retry-After`: Number of seconds to wait before retrying (only on 429 responses)

## Error Response

When the rate limit is exceeded, the API returns:

```json
{
  "success": false,
  "error": {
    "message": "Too many requests. Please try again later.",
    "code": "TOO_MANY_REQUESTS"
  },
  "timestamp": "2024-01-08T12:00:00+00:00"
}
```

HTTP Status: `429 Too Many Requests`

## IP-Based vs User-Based Limiting

### IP-Based Limiting

Used for public endpoints and authentication routes. Each IP address is tracked separately.

Example: 5 login attempts per minute per IP address.

### User-Based Limiting

Used for protected endpoints after JWT authentication. Each authenticated user is tracked separately, allowing legitimate users to make more requests even if they share an IP address.

Example: 300 requests per minute per authenticated user.

### Combined Limiting

The middleware also supports a `both` key type that limits based on both IP and user ID for enhanced security.

## Redis Storage

Rate limit data is stored in Redis with the following structure:

- Key format: `{prefix}{type}:{identifier}`
- Value: Number of attempts (integer)
- TTL: Time window in seconds (calculated from `decay_minutes`)

Examples:
- `ratelimit:ip:192.168.1.1` → "3" (3 attempts from IP)
- `ratelimit:user:123` → "45" (45 attempts from user ID 123)

## Testing

Tests for the rate limiting system are in `tests/Feature/RateLimitingTest.php`.

To test rate limiting manually:

```bash
# Test login endpoint rate limiting (5 requests per minute)
for i in {1..6}; do
  curl -X POST http://localhost:9501/auth/login \
    -H "Content-Type: application/json" \
    -d '{"email":"test@example.com","password":"password"}'
  echo ""
done

# The 6th request should return 429 Too Many Requests
```

## Monitoring

Monitor Redis rate limit keys to track usage:

```bash
# View all rate limit keys
redis-cli KEYS "ratelimit:*"

# View a specific rate limit key
redis-cli GET "ratelimit:ip:192.168.1.1"

# View TTL for a rate limit key
redis-cli TTL "ratelimit:ip:192.168.1.1"
```

## Security Considerations

1. **Brute Force Protection**: Authentication endpoints have strict limits (3-5 requests per minute)
2. **DoS Protection**: Public endpoints are limited to 60 requests per minute
3. **User Fairness**: Authenticated users have higher limits (300 per minute) to prevent abuse while allowing legitimate usage
4. **Redis Persistence**: Ensure Redis is properly configured for persistence to survive restarts
5. **IP Spoofing**: The middleware checks multiple headers for the real IP address to prevent IP spoofing

## Troubleshooting

### Rate Limiting Not Working

1. Check Redis is running: `redis-cli ping`
2. Verify Redis configuration in `config/rate-limiting.php`
3. Check environment variables are set correctly
4. Verify middleware is applied to routes in `routes/api.php`

### Users Getting 429 Errors

1. Check if limits are too restrictive for legitimate use
2. Consider increasing limits for specific endpoints in `config/rate-limiting.php`
3. Verify the time window (`decay_minutes`) is appropriate
4. Check if IP-based or user-based limiting is causing the issue

### Redis Connection Issues

1. Verify Redis is accessible from the application
2. Check `REDIS_HOST`, `REDIS_PORT`, and `REDIS_AUTH` in `.env`
3. Test Redis connection: `redis-cli -h {host} -p {port} ping`

## Future Enhancements

Potential improvements to consider:

1. Dynamic rate limiting based on user roles or subscription levels
2. Rate limit burst capabilities (allow short bursts within limits)
3. Whitelist trusted IPs or users
4. Rate limit analytics and dashboards
5. Custom rate limit rules per client or API key
6. Distributed rate limiting for multi-instance deployments
