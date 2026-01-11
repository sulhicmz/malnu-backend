# Input Validation Security Enhancements

This document describes the security enhancements made to `InputValidationTrait` to prevent injection attacks and validate user input securely.

## Overview

The `InputValidationTrait` has been enhanced with additional validation methods and security protections to defend against:
- Cross-Site Scripting (XSS)
- SQL Injection
- Command Injection
- File Upload Attacks
- NoSQL Injection
- Rate Limiting Abuse

## Enhanced Methods

### URL Validation
```php
validateUrl(string $url): bool
```

Validates that a string is a properly formatted URL. Uses PHP's `FILTER_VALIDATE_URL` filter.

**Usage:**
```php
if (!$this->validateUrl($userInput['website'])) {
    return $this->errorResponse('Invalid URL format');
}
```

### Phone Number Validation
```php
validatePhone(string $phone): bool
```

Validates phone numbers supporting international formats. Accepts:
- 10-15 digit phone numbers
- Optionally prefixed with `+`

**Usage:**
```php
if (!$this->validatePhone($userInput['phone'])) {
    return $this->errorResponse('Invalid phone number format');
}
```

### IP Address Validation
```php
validateIp(string $ip): bool
```

Validates IPv4 and IPv6 addresses using PHP's `FILTER_VALIDATE_IP` filter.

**Usage:**
```php
if (!$this->validateIp($userInput['ip_address'])) {
    return $this->errorResponse('Invalid IP address');
}
```

### JSON Validation
```php
validateJson(string $json): bool
```

Validates that a string contains valid JSON structure.

**Usage:**
```php
if (!$this->validateJson($userInput['config'])) {
    return $this->errorResponse('Invalid JSON format');
}
```

### Custom Regex Validation
```php
validateRegex(string $value, string $pattern): bool
```

Validates input against a custom regular expression pattern.

**Usage:**
```php
if (!$this->validateRegex($userInput['username'], '/^[a-zA-Z0-9._]{3,20}$/')) {
    return $this->errorResponse('Username must be 3-20 alphanumeric characters');
}
```

## Security Protection Methods

### Command Injection Protection
```php
sanitizeCommand(string $command): string
sanitizeCommandArg(string $arg): string
```

Prevents command injection by escaping shell commands and arguments using:
- `escapeshellcmd()` - Escapes whole command strings
- `escapeshellarg()` - Escapes individual command arguments

**Usage:**
```php
$command = "ls -la {$directory}";
$safeCommand = $this->sanitizeCommand($command);

$arg = $filename;
$safeArg = $this->sanitizeCommandArg($arg);

exec($safeCommand . ' ' . $safeArg, $output);
```

### SQL Injection Protection
```php
escapeSqlIdentifier(string $identifier): string
```

Escapes SQL identifiers (table names, column names) by wrapping in backticks.
Note: For most queries, use Hyperf/Laravel ORM with parameterized queries instead.

**Usage:**
```php
$table = $this->escapeSqlIdentifier($userInput['table']);
$query = "SELECT * FROM {$table} WHERE id = ?";
```

### Filename Sanitization
```php
sanitizeFilename(string $filename): string
```

Removes dangerous characters from filenames to prevent:
- Path traversal attacks (`../`, `./`)
- Command injection attempts (`;`, `&`, `|`, `` `)
- Invalid characters

**Usage:**
```php
$safeFilename = $this->sanitizeFilename($userFile['name']);
move_uploaded_file($userFile['tmp_name'], $safeFilename);
```

### Enhanced File Upload Validation
```php
validateSecureFileUpload(mixed $file, array $allowedMimes = [], ?int $maxSizeBytes = null): array
```

Comprehensive file upload security checks:
1. **Required Check** - File must be provided
2. **Uploaded File Check** - Verifies using `is_uploaded_file()`
3. **Size Validation** - Optional max file size in bytes
4. **MIME Type Validation** - Uses `finfo_file()` to detect actual file type
5. **Filename Sanitization** - Removes dangerous characters
6. **Extension Validation** - Blocks dangerous file extensions (`.php`, `.sh`, `.exe`, etc.)

**Usage:**
```php
$allowedTypes = ['image/jpeg', 'image/png', 'application/pdf'];
$maxSize = 5 * 1024 * 1024; // 5MB

$errors = $this->validateSecureFileUpload(
    $_FILES['avatar'],
    $allowedTypes,
    $maxSize
);

if (!empty($errors)) {
    return $this->errorResponse($errors[0]);
}
```

## Security Best Practices

### 1. Always Validate Input
Never trust user input. Always validate:
- Data types (string, integer, boolean, array)
- Data formats (email, URL, phone, date)
- Data ranges (min/max length, numeric ranges)
- Business rules (unique values, allowed values)

### 2. Use Framework ORM for Database Queries
Prefer Hyperf/Laravel ORM with parameterized queries over raw SQL:

```php
// GOOD - Parameterized query (safe from SQL injection)
User::where('email', $email)->first();

// BAD - Raw SQL with interpolation (vulnerable)
DB::select("SELECT * FROM users WHERE email = '{$email}'");
```

### 3. Never Use Exec/Shell Functions with User Input
Always sanitize commands and arguments before using `exec()`, `shell_exec()`, `system()`:

```php
// GOOD - Sanitized
exec($this->sanitizeCommand('ls'), $output);

// BAD - Direct execution
exec('ls ' . $userInput['directory'], $output);
```

### 4. Implement Defense in Depth
Use multiple layers of security:
1. Input validation
2. Parameterized queries
3. Prepared statements
4. Application-level validation
5. Framework security features

### 5. Use Content Security Headers
Set appropriate CSP headers to prevent XSS:

```php
return response()
    ->withHeader('Content-Security-Policy', "default-src 'self'; script-src 'self' 'unsafe-inline' 'unsafe-eval'")
    ->withHeader('X-Content-Type-Options', 'nosniff')
    ->withHeader('X-Frame-Options', 'DENY');
```

### 6. Implement Rate Limiting
Protect endpoints from brute force and abuse using rate limiting:
- Limit requests per time window
- Use sliding window or token bucket algorithms
- Return appropriate headers (`X-RateLimit-Limit`, `X-RateLimit-Remaining`)

### 7. Validate File Uploads Thoroughly
- Check MIME type with `finfo_file()` (not just extension)
- Scan file content for malicious signatures
- Sanitize filenames
- Set reasonable file size limits
- Restrict dangerous extensions

### 8. Keep Security Dependencies Updated
- Use `htmlspecialchars()` for XSS protection
- Keep PHP and framework updated
- Review security advisories for dependencies

### 9. Log Security Events
Log failed validation attempts, suspicious input, and security violations for monitoring:
```php
Log::warning('Potential XSS attack detected', [
    'input' => $sanitizedInput,
    'original' => $rawInput
]);
```

### 10. Test Security Features
Use `tests/Feature/InputValidationSecurityTest.php` to test:
- All validation methods with valid/invalid inputs
- Sanitization functions with malicious inputs
- File upload security checks
- Error handling

## Testing

Run the security validation tests:

```bash
vendor/bin/phpunit tests/Feature/InputValidationSecurityTest.php
```

All tests should pass:
- URL validation with various formats
- Phone number validation
- IP address validation (IPv4 and IPv6)
- JSON structure validation
- Regex pattern validation
- Command and argument sanitization
- SQL identifier escaping
- Filename sanitization
- Secure file upload validation

## Breaking Changes

None. All enhancements are additive and backward compatible.

## Related Documentation

- [API.md](API.md) - API endpoint documentation
- [SECURITY_ANALYSIS.md](SECURITY_ANALYSIS.md) - Security analysis
- [InputValidationTrait.php](../app/Traits/InputValidationTrait.php) - Trait implementation
