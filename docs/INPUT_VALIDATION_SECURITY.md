# Input Validation Security

This document describes the enhanced input validation security features implemented in the `InputValidationTrait` to protect against injection attacks and malicious input.

## Overview

The `InputValidationTrait` provides comprehensive input validation methods to protect against:
- **Cross-Site Scripting (XSS)** - HTML sanitization
- **SQL Injection** - Identifier escaping
- **Command Injection** - Shell command and argument sanitization
- **File Upload Vulnerabilities** - Enhanced file validation
- **Input Validation** - URL, phone, IP, JSON, and regex validation
- **Password Security** - Complexity validation and common password blacklist

## Security Features

### 1. XSS Protection (Existing)

```php
protected function sanitizeString(?string $value): ?string
{
    return htmlspecialchars(trim($value), ENT_QUOTES, 'UTF-8');
}
```

**Usage:**
```php
$cleanInput = $this->sanitizeString($userInput);
```

**What it prevents:**
- `<script>alert('XSS')</script>` becomes `&lt;script&gt;alert(&apos;XSS&apos;)&lt;/script&gt;`
- HTML tag injection
- JavaScript execution via input

### 2. URL Validation (New)

```php
protected function validateUrl(string $url): bool
```

**Validates:**
- URL format correctness
- Protocol restrictions (http/https only)
- Prevents dangerous protocols (javascript:, file:, etc.)

**Usage:**
```php
if (!$this->validateUrl($userUrl)) {
    throw new ValidationException('Invalid URL format');
}
```

**Examples:**
- ✅ `https://example.com` - Valid
- ✅ `http://sub.domain.com/path` - Valid
- ❌ `javascript:alert(1)` - Invalid (dangerous protocol)
- ❌ `not-a-url` - Invalid (bad format)
- ❌ `ftp://example.com` - Invalid (ftp protocol not allowed)

### 3. Phone Number Validation (New)

```php
protected function validatePhone(string $phone): bool
```

**Validates:**
- International phone number format (10-15 digits)
- Optional + prefix for country codes

**Usage:**
```php
if (!$this->validatePhone($userPhone)) {
    throw new ValidationException('Invalid phone number');
}
```

**Examples:**
- ✅ `1234567890` - Valid (10 digits)
- ✅ `+1234567890` - Valid (with country code)
- ✅ `+11234567890123` - Valid (international)
- ❌ `123` - Invalid (too short)
- ❌ `1234567890123456` - Invalid (too long)

### 4. IP Address Validation (New)

```php
protected function validateIp(string $ip): bool
```

**Validates:**
- IPv4 addresses (e.g., `192.168.1.1`)
- IPv6 addresses (e.g., `2001:0db8:85a3:0000:0000:8a2e:0370:7334`)

**Usage:**
```php
if (!$this->validateIp($userIp)) {
    throw new ValidationException('Invalid IP address');
}
```

**Examples:**
- ✅ `192.168.1.1` - Valid IPv4
- ✅ `10.0.0.1` - Valid IPv4
- ✅ `2001:0db8::1` - Valid IPv6
- ✅ `::1` - Valid IPv6 (loopback)
- ❌ `256.256.256.256` - Invalid (octet > 255)
- ❌ `not-an-ip` - Invalid (bad format)

### 5. JSON Validation (New)

```php
protected function validateJson(string $json): bool
```

**Validates:**
- JSON syntax correctness
- Proper structure

**Usage:**
```php
if (!$this->validateJson($jsonData)) {
    throw new ValidationException('Invalid JSON format');
}
```

**Examples:**
- ✅ `{"key": "value"}` - Valid
- ✅ `{"nested": {"array": [1, 2, 3]}}` - Valid
- ✅ `[]` - Valid (empty array)
- ❌ `{"key": value}` - Invalid (missing quotes)
- ❌ `{not json}` - Invalid (bad format)

### 6. Regex Pattern Validation (New)

```php
protected function validateRegex(string $value, string $pattern): bool
```

**Validates:**
- Custom regex patterns
- Flexible for specific business rules

**Usage:**
```php
// Validate username (alphanumeric + underscore)
if (!$this->validateRegex($username, '/^[a-zA-Z0-9_]+$/')) {
    throw new ValidationException('Invalid username format');
}

// Validate alphanumeric ID
if (!$this->validateRegex($id, '/^[A-Z]{2}[0-9]{6}$/')) {
    throw new ValidationException('Invalid ID format');
}
```

### 7. Command Injection Protection (New)

#### Command Sanitization

```php
protected function sanitizeCommand(string $command): string
```

**Prevents:**
- Command injection via shell commands
- Command chaining with `&&`, `||`, `;`
- Command substitution with `$()`, backticks

**Usage:**
```php
$safeCommand = $this->sanitizeCommand($userCommand);
system($safeCommand);
```

**Examples:**
- Input: `ls; rm -rf /`
- Output: `ls\; rm -rf /` (semicolon escaped)
- Input: `cat /etc/passwd && malicious`
- Output: `cat /etc/passwd \&\& malicious` (ampersands escaped)

#### Argument Sanitization

```php
protected function sanitizeCommandArg(string $arg): string
```

**Prevents:**
- Argument injection
- Command substitution in arguments

**Usage:**
```php
$safeArg = $this->sanitizeCommandArg($userArg);
exec('command ' . $safeArg, $output);
```

**Examples:**
- Input: `$(whoami)`
- Output: `'$(whoami)'` (fully quoted/escaped)
- Input: `file.txt; evil`
- Output: `'file.txt; evil'` (properly quoted)

**⚠️ Important:** Always use parameterized queries via ORM instead of raw SQL. This is a last resort.

**Usage:**
```php
// For table/column names in dynamic queries (RARE CASE ONLY!)
$safeTable = $this->escapeSqlIdentifier($userTable);

// Standard approach (PREFERRED)
User::where('email', $email)->first();
```

**Examples:**
- Input: `users`
- Output: ``users```
- Input: ``malicious``
- Output: ```malicious``` (backticks escaped)

### 9. Filename Sanitization (New)

```php
protected function sanitizeFilename(string $filename): string
```

**Prevents:**
- Path traversal attacks (`../../etc/passwd`)
- Command injection via filenames
- Dangerous characters in filenames

**Usage:**
```php
$safeFilename = $this->sanitizeFilename($userFilename);
move_uploaded_file($tmpPath, $uploadDir . $safeFilename);
```

**Examples:**
- Input: `../../../etc/passwd`
- Output: `etcpasswd` (traversal removed)
- Input: `file<script>.txt`
- Output: `file.txt` (script tags removed)
- Input: `file|pipe.txt`
- Output: `filepipe.txt` (pipe removed)

### 10. Enhanced File Upload Security (New)

```php
protected function validateSecureFileUpload(
    mixed $file,
    array $allowedMimes = [],
    ?int $maxSizeBytes = null
): array
```

**Validates:**
1. **Actual file upload** - Verifies `is_uploaded_file()`
2. **File size** - Enforces maximum size limit
3. **Filename safety** - Removes dangerous characters
4. **Dangerous extensions** - Blocks executable files (.php, .sh, .exe, .bat, etc.)
5. **MIME type detection** - Uses `finfo_file()` to detect actual content
6. **Extension spoofing** - Detects when file extension doesn't match content

**Usage:**
```php
$errors = $this->validateSecureFileUpload(
    $uploadedFile,
    ['image/jpeg', 'image/png', 'application/pdf'],
    5242880 // 5 MB
);

if (!empty($errors)) {
    throw new ValidationException(implode(', ', $errors));
}
```

**Blocked Extensions:**
- `.php`, `.php5`, `.phtml` - PHP files
- `.sh` - Shell scripts
- `.exe`, `.bat`, `.cmd`, `.com` - Windows executables
- `.scr`, `.cgi`, `.pl`, `.py` - Other scripts

**Examples:**
- ❌ `malicious.php` - Blocked (dangerous extension)
- ❌ `script.sh` - Blocked (dangerous extension)
- ❌ `file.php.jpg` - Blocked (extension spoofing detected)
- ❌ File > 5 MB - Blocked (size exceeded)
- ❌ `image.jpg` with MIME `application/octet-stream` - Blocked (MIME mismatch)
- ✅ `document.pdf` (5 MB, correct MIME) - Allowed

## Password Security (Existing Enhancement)

```php
protected function validatePasswordComplexity(string $password): array
```

**Requirements:**
1. Minimum 8 characters
2. At least 1 uppercase letter (A-Z)
3. At least 1 lowercase letter (a-z)
4. At least 1 number (0-9)
5. At least 1 special character (!@#$%^&*(),.?":{}|<>)
6. Not in common password blacklist

**Common Passwords Blocked:**
```
password, 123456, 12345678, qwerty, abc123, monkey, master,
dragon, 111111, baseball, iloveyou, trustno1, sunshine, princess,
admin, welcome, shadow, ashley, football, jesus, michael,
ninja, mustang, password1, password123, letmein, login, starwars
```

**Usage:**
```php
$errors = $this->validatePasswordComplexity($userPassword);
if (!empty($errors)) {
    throw new ValidationException(implode(' ', $errors));
}
```

## Security Best Practices

### 1. Always Use Parameterized Queries

**❌ Bad:**
```php
DB::select("SELECT * FROM users WHERE id = " . $userId);
```

**✅ Good:**
```php
DB::select("SELECT * FROM users WHERE id = ?", [$userId]);
// or
User::find($userId);
```

### 2. Validate Early and Fail Fast

```php
// Validate all input at the beginning of controller methods
$this->validateRequired($input, ['email', 'password']);
$this->validateEmail($input['email']);
$this->validatePasswordComplexity($input['password']);
```

### 3. Sanitize All User Input

```php
$sanitized = $this->sanitizeInput($userInput);
```

### 4. Use Type-Sensitive Validation

```php
protected function validateEmail(string $email): bool
protected function validateInteger(mixed $value): bool
protected function validateBoolean(mixed $value): bool
```

### 5. Implement Defense in Depth

- Validate input at multiple layers (controller, service, model)
- Use ORM-level validation (mass assignment protection)
- Sanitize output as well as input
- Use prepared statements for all database queries

### 6. Log Validation Failures

```php
try {
    $this->validateRequired($input, $fields);
} catch (ValidationException $e) {
    Log::warning('Validation failed', [
        'input' => $input,
        'errors' => $e->getMessage()
    ]);
    throw $e;
}
```

## Testing

Run the input validation security tests:

```bash
vendor/bin/co-phpunit tests/Feature/InputValidationSecurityTest.php
```

## Migration Notes

No database migrations required for these security enhancements. All changes are backward compatible and additive.

**Breaking Changes:** None - all new methods are additions to existing trait.

## References

- [OWASP Input Validation Cheat Sheet](https://cheatsheetseries.owasp.org/cheatsheets/Input_Validation_Cheat_Sheet)
- [OWASP Command Injection](https://owasp.org/www-community/attacks/command-injection)
- [OWASP File Upload](https://owasp.org/www-community/attacks/file_upload_vulnerabilities)
- [PHP Security Guide](https://www.php.net/manual/en/security.php)
