# Input Validation Security Enhancements

This document describes security enhancements made to `InputValidationTrait` to prevent injection attacks and validate user input securely.

## Overview

The `InputValidationTrait` has been enhanced with additional validation methods and security protections to defend against:
- Cross-Site Scripting (XSS)
- SQL Injection
- Command Injection
- File Upload Attacks
- LDAP Injection
- Path Traversal

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

**Examples:**
- ✅ Valid: `https://example.com`, `http://example.com/path`
- ❌ Invalid: `not-a-url`, `javascript:alert(1)`

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

**Examples:**
- ✅ Valid: `1234567890`, `+1234567890`, `123-456-7890`
- ❌ Invalid: `123` (too short), `1234567890123456` (too long)

### IP Address Validation

```php
validateIp(string $ip): bool
```

Validates IPv4 and IPv6 addresses using PHP's `FILTER_VALIDATE_IP` filter.

**Usage:**
```php
if (!$this->validateIp($userInput['ip'])) {
    return $this->errorResponse('Invalid IP address');
}
```

**Examples:**
- ✅ Valid: `192.168.1.1`, `8.8.8.8`, `::1`
- ❌ Invalid: `256.256.256.256`, `not-an-ip`

### JSON Validation

```php
validateJson(string $json): bool
```

Validates JSON structure.

**Usage:**
```php
if (!$this->validateJson($userInput['jsonData'])) {
    return $this->errorResponse('Invalid JSON format');
}
```

**Examples:**
- ✅ Valid: `{"key":"value"}`, `["item1","item2"]`, `123`
- ❌ Invalid: `{"key":value}`, `{invalid}`

### Regex Pattern Validation

```php
validateRegex(string $value, string $pattern): bool
```

Validates input against custom regex pattern.

**Usage:**
```php
if (!$this->validateRegex($userInput['username'], '/^[a-zA-Z0-9_]{3,20}$/')) {
    return $this->errorResponse('Username must be 3-20 alphanumeric characters');
}
```

### Command Injection Protection

```php
sanitizeCommand(string $command): string
sanitizeCommandArg(string $arg): string
```

Escapes shell commands and arguments to prevent command injection.

**Usage:**
```php
$safeCommand = $this->sanitizeCommand('ls');
$safeArg = $this->sanitizeCommandArg($filename);
shell_exec($safeCommand . ' ' . $safeArg);
```

**Important:**
- Use `escapeshellcmd()` for commands
- Use `escapeshellarg()` for command arguments
- Always validate input before using in shell commands

### SQL Identifier Escaping

```php
escapeSqlIdentifier(string $identifier): string
```

Wraps SQL identifiers (table/column names) in backticks to prevent SQL injection.

**Usage:**
```php
$column = $this->escapeSqlIdentifier($userInput['column']);
$query = "SELECT {$column} FROM table WHERE id = ?";
```

**Important:**
- Only use for table/column names, not data values
- For data values, use parameterized queries (ORM prepared statements)
- Hyperf/Laravel ORM automatically handles parameterized queries for most cases

### Filename Sanitization

```php
sanitizeFilename(string $filename): string
```

Removes dangerous characters from filenames to prevent path traversal and command injection.

**Usage:**
```php
$safeFilename = $this->sanitizeFilename($userFile['name']);
move_uploaded_file($userFile['tmp_name'], '/uploads/' . $safeFilename);
```

**Sanitization:**
- Removes: `..`, `/`, `\`, and special characters
- Keeps: alphanumeric, `_`, `.`, `-`
- Trims: leading/trailing `.` and `-`
- Returns: `'file'` if empty after sanitization

### Enhanced File Upload Validation

```php
validateSecureFileUpload(mixed $file, array $allowedMimes = [], ?int $maxSizeBytes = null): array
```

Enhanced file upload validation with comprehensive security checks.

**Parameters:**
- `$file` - File upload array (from `$_FILES`)
- `$allowedMimes` - Array of allowed MIME types (e.g., `['image/jpeg', 'image/png']`)
- `$maxSizeBytes` - Maximum file size in bytes (null = no limit)

**Returns:**
- Array of error messages (empty if valid)

**Security Checks:**
1. File existence verification using `is_uploaded_file()`
2. File size validation
3. MIME type detection using `finfo_file()` (prevents extension spoofing)
4. Filename sanitization (prevents path traversal)
5. Dangerous extension blocking (`.php`, `.sh`, `.exe`, `.bat`, etc.)

**Dangerous Extensions Blocked:**
- PHP: `php`, `phtml`, `php3`, `php4`, `php5`, `php7`, `phps`
- Server scripts: `shtml`, `sh`, `cgi`, `pl`
- Executables: `exe`, `dll`, `bat`, `cmd`

**Usage:**
```php
$file = $_FILES['avatar'] ?? null;
$errors = $this->validateSecureFileUpload(
    $file,
    ['image/jpeg', 'image/png', 'image/gif'],
    5242880 // 5MB
);

if (!empty($errors)) {
    return $this->errorResponse('File upload failed: ' . implode(', ', $errors));
}

$safeFilename = $this->sanitizeFilename($file['name']);
move_uploaded_file($file['tmp_name'], '/avatars/' . $safeFilename);
```

**Examples of Invalid Uploads:**
- ❌ Missing file: `'File is required'`
- ❌ Too large: `'File size exceeds maximum allowed size'`
- ❌ Wrong type: `'File type not allowed'`
- ❌ Invalid characters: `'Filename contains invalid characters'`
- ❌ Dangerous extension: `'File extension is not allowed'`

## Security Best Practices

### 1. Defense in Depth

Use multiple layers of validation and sanitization:

```php
// Layer 1: Validate type
if (!$this->validateUrl($url)) {
    return error('Invalid URL');
}

// Layer 2: Sanitize
$cleanUrl = $this->sanitizeString($url);

// Layer 3: Whitelist (when possible)
$allowedDomains = ['example.com', 'trusted.com'];
if (!in_array(parse_url($cleanUrl, PHP_URL_HOST), $allowedDomains)) {
    return error('Domain not allowed');
}
```

### 2. Use ORM Parameterized Queries

Never concatenate user input into SQL queries manually:

```php
// ❌ BAD - SQL injection vulnerable
$query = "SELECT * FROM users WHERE email = '" . $email . "'";

// ✅ GOOD - Safe parameterized query
$user = User::where('email', $email)->first();

// ✅ GOOD - For custom queries with identifiers
$column = $this->escapeSqlIdentifier($columnName);
$query = "SELECT {$column} FROM users WHERE email = ?";
DB::select($query, [$email]);
```

### 3. Validate and Sanitize Commands

Always validate and sanitize before shell execution:

```php
// ❌ BAD - Command injection vulnerable
shell_exec('convert ' . $_POST['filename'] . ' output.jpg');

// ✅ GOOD - Safe shell execution
if (!$this->validateRegex($filename, '/^[a-zA-Z0-9._-]+$/')) {
    return error('Invalid filename');
}

$safeFilename = $this->sanitizeFilename($filename);
$safeArg = $this->sanitizeCommandArg($safeFilename);
shell_exec('convert ' . $safeArg . ' output.jpg');
```

### 4. File Upload Security

Implement multiple layers of file upload protection:

```php
$errors = $this->validateSecureFileUpload(
    $file,
    ['image/jpeg', 'image/png'], // Whitelist allowed types
    5242880 // Max 5MB
);

if (!empty($errors)) {
    return error('Upload failed');
}

$safeFilename = $this->sanitizeFilename($file['name']);
$uniqueFilename = uniqid() . '_' . $safeFilename;

// Store outside web root
$uploadPath = storage_path('app/uploads/' . $uniqueFilename);

if (!move_uploaded_file($file['tmp_name'], $uploadPath)) {
    return error('Failed to save file');
}
```

### 5. Input Validation Order

Follow this validation order for security:

1. **Type Validation** - Is the input the right type?
2. **Format Validation** - Does it match expected format?
3. **Range Validation** - Is it within acceptable bounds?
4. **Sanitization** - Clean the input
5. **Business Logic Validation** - Does it make sense for your application?

```php
// Example: Email validation
if (!is_string($email)) { // Type
    return error('Email must be a string');
}

if (!$this->validateEmail($email)) { // Format
    return error('Invalid email format');
}

if (!$this->validateStringLength($email, 5, 255)) { // Range
    return error('Email must be 5-255 characters');
}

$cleanEmail = $this->sanitizeString($email); // Sanitize

if (in_array($cleanEmail, $blockedEmails)) { // Business logic
    return error('Email is blocked');
}
```

## Testing

Run security validation tests:

```bash
# Run all input validation tests
vendor/bin/co-phpunit tests/Feature/InputValidationSecurityTest.php

# Run specific test
vendor/bin/co-phpunit tests/Feature/InputValidationSecurityTest.php --filter test_validates_url_correctly
```

## Migration Notes

No database migrations or configuration changes required. The new validation methods can be used immediately in controllers or services.

## Breaking Changes

None. All enhancements are additive and backward compatible with existing code.

## References

- [PHP Filter Functions](https://www.php.net/manual/en/filter.filters.php)
- [OWASP Input Validation](https://owasp.org/www-community/attacks/Input_Validation)
- [CWE-20: Improper Input Validation](https://cwe.mitre.org/data/definitions/20.html)
- [PHP File Upload Security](https://www.php.net/manual/en/features.file-upload.security.php)
