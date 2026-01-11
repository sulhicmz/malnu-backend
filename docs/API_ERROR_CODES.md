# API Error Codes

This document defines the standardized error codes used throughout the Malnu Backend API.

## Error Code Format

Error codes follow the pattern: `[CATEGORY]_[SERIAL_NUMBER]`

- **AUTH**: Authentication and authorization errors
- **VAL**: Input validation errors
- **RES**: Resource-related errors
- **SRV**: Server and infrastructure errors
- **RTL**: Rate limiting errors

## Authentication Errors (AUTH_XXX)

| Code | Description | HTTP Status | Usage |
|------|-------------|-------------|-------|
| AUTH_001 | Invalid credentials | 401 | Login with wrong username/password |
| AUTH_002 | Invalid token | 401 | JWT token format or signature is invalid |
| AUTH_003 | Token expired | 401 | JWT token has expired |
| AUTH_004 | Token blacklisted | 401 | JWT token has been revoked/blacklisted |
| AUTH_005 | Unauthorized | 401 | User not authenticated |
| AUTH_006 | Forbidden | 403 | User lacks permission for this action |
| AUTH_007 | User not found | 404 | User record doesn't exist |
| AUTH_008 | User already exists | 409 | Email/username already registered |
| AUTH_009 | Password reset invalid | 400 | Password reset token is invalid |
| AUTH_010 | Password reset expired | 400 | Password reset token has expired |

## Validation Errors (VAL_XXX)

| Code | Description | HTTP Status | Usage |
|------|-------------|-------------|-------|
| VAL_001 | Validation failed | 422 | General validation failure |
| VAL_002 | Required field | 422 | Missing required field |
| VAL_003 | Invalid format | 422 | Field format is incorrect (e.g., email) |
| VAL_004 | Invalid type | 422 | Field type is incorrect (e.g., string instead of int) |
| VAL_005 | Invalid length | 422 | Field length is too short or too long |
| VAL_006 | Invalid range | 422 | Value is outside allowed range |
| VAL_007 | Invalid date | 422 | Date format or value is invalid |
| VAL_008 | Duplicate entry | 409 | Unique constraint violated |

## Resource Errors (RES_XXX)

| Code | Description | HTTP Status | Usage |
|------|-------------|-------------|-------|
| RES_001 | Not found | 404 | Resource doesn't exist |
| RES_002 | Creation failed | 400 | Failed to create resource |
| RES_003 | Update failed | 400 | Failed to update resource |
| RES_004 | Deletion failed | 400 | Failed to delete resource |
| RES_005 | Locked | 423 | Resource is locked/in-use |
| RES_006 | Insufficient balance | 400 | Not enough balance/credits |
| RES_007 | Already processed | 409 | Resource already processed (e.g., approved) |

## Server Errors (SRV_XXX)

| Code | Description | HTTP Status | Usage |
|------|-------------|-------------|-------|
| SRV_001 | Internal error | 500 | Unhandled server error |
| SRV_002 | Database error | 500 | Database operation failed |
| SRV_003 | External service error | 502 | Third-party service unavailable |
| SRV_004 | Timeout | 504 | Request timeout |
| SRV_005 | Maintenance | 503 | System under maintenance |

## Rate Limiting Errors (RTL_XXX)

| Code | Description | HTTP Status | Usage |
|------|-------------|-------------|-------|
| RTL_001 | Rate limit exceeded | 429 | Too many requests |

## Standard Error Response Format

### Success Response
```json
{
  "success": true,
  "data": { ... },
  "message": "Operation successful",
  "timestamp": "2026-01-08T12:00:00+00:00"
}
```

### Error Response
```json
{
  "success": false,
  "error": {
    "message": "User-friendly error message",
    "code": "AUTH_001",
    "type": "authentication",
    "details": {
      "field": "Specific validation details (if applicable)"
    }
  },
  "timestamp": "2026-01-08T12:00:00+00:00"
}
```

### Validation Error with Details
```json
{
  "success": false,
  "error": {
    "message": "Validation failed. Please check your input.",
    "code": "VAL_001",
    "type": "validation",
    "details": {
      "email": [
        "The email must be a valid email address."
      ],
      "password": [
        "The password must be at least 6 characters."
      ]
    }
  },
  "timestamp": "2026-01-08T12:00:00+00:00"
}
```

## HTTP Status Code Mapping

| Status Code | Error Codes |
|-------------|-------------|
| 400 | AUTH_009, AUTH_010, RES_002, RES_003, RES_004, RES_006 |
| 401 | AUTH_001, AUTH_002, AUTH_003, AUTH_004, AUTH_005 |
| 403 | AUTH_006 |
| 404 | AUTH_007, RES_001 |
| 409 | AUTH_008, VAL_008, RES_007 |
| 422 | VAL_001, VAL_002, VAL_003, VAL_004, VAL_005, VAL_006, VAL_007 |
| 423 | RES_005 |
| 429 | RTL_001 |
| 500 | SRV_001, SRV_002 |
| 502 | SRV_003 |
| 503 | SRV_005 |
| 504 | SRV_004 |

## Error Response Fields

- **success** (boolean): Always `false` for error responses
- **error** (object): Error details
  - **message** (string): User-friendly error message
  - **code** (string): Standardized error code from this document
  - **type** (string): Error category (authentication, validation, authorization, not_found, database, timeout, server)
  - **details** (object, optional): Additional error details (e.g., validation errors)
- **timestamp** (string): ISO 8601 formatted timestamp

## Usage Guidelines

1. **Always use standardized error codes** from `config/error-codes.php`
2. **Include user-friendly messages** that describe what went wrong
3. **Log technical details** on the server, don't expose them to clients
4. **Return appropriate HTTP status codes** based on error type
5. **Use validation errors with details** for form validation failures
6. **Rate limit sensitive endpoints** and return `RTL_001` when exceeded

## Adding New Error Codes

When adding new error codes:
1. Add the error code to `config/error-codes.php`
2. Map it to an appropriate HTTP status code
3. Update this documentation
4. Add unit tests for the new error code
5. Ensure the error message is user-friendly

---

*Last Updated: January 8, 2026*
