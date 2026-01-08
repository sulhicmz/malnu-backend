# Security Guide

## Quick Start

### 1. Generate JWT Secret

For a fresh installation or production deployment:

```bash
php artisan jwt:secret
```

This will generate a cryptographically secure 64-character JWT secret and add it to your `.env` file.

**⚠️ SECURITY WARNING**: Never commit `.env` file or JWT secrets to version control!

### 2. Security Headers

Security headers are automatically applied when `SECURITY_HEADERS_ENABLED=true` in your `.env` file.

Enabled headers:
- Content-Security-Policy (CSP)
- HTTP Strict-Transport-Security (HSTS)
- X-Frame-Options: DENY
- X-Content-Type-Options: nosniff
- Referrer-Policy: strict-origin-when-cross-origin
- Permissions-Policy
- X-XSS-Protection

### 3. Rate Limiting

Configure rate limiting in `.env`:

```bash
RATE_LIMIT_ENABLED=true
RATE_LIMIT_MAX_ATTEMPTS=60
RATE_LIMIT_DECAY_SECONDS=60
RATE_LIMIT_AUTH_ATTEMPTS=5
RATE_LIMIT_AUTH_DECAY_SECONDS=60
```

Apply to routes:

```php
Route::group(['middleware' => ['jwt', 'rate_limit']], function () {
    Route::apiResource('students', StudentController::class);
});
```

---

## JWT Authentication

### Secret Generation

**Option 1: Using Artisan Command (Recommended)**
```bash
php artisan jwt:secret
```

**Option 2: Manual Generation**
```bash
# Generate 64-character random string
openssl rand -base64 64 | tr -d '=+/' | cut -c1-64
```

Add to `.env`:
```bash
JWT_SECRET=your_generated_64_char_secret_here
```

### JWT Configuration

Edit `config/jwt.php` or use environment variables:

```bash
JWT_TTL=120              # Token lifetime in minutes (2 hours)
JWT_REFRESH_TTL=20160     # Refresh token lifetime in minutes (14 days)
```

### Token Usage

**Generate Token** (in AuthService):
```php
$token = $jwtService->generateToken([
    'id' => $user->id,
    'email' => $user->email
]);
```

**Validate Token** (in JWTMiddleware):
```php
$payload = $jwtService->decodeToken($token);
if (!$payload) {
    throw new UnauthorizedException('Invalid token');
}
```

**Refresh Token**:
```php
$newToken = $jwtService->refreshToken($currentToken);
```

---

## Input Validation

### Using InputValidationTrait

Controllers can use `InputValidationTrait` for common validations:

```php
use App\Traits\InputValidationTrait;

class StudentController extends BaseController
{
    use InputValidationTrait;

    public function store(Request $request)
    {
        $data = $request->all();

        // Validate required fields
        $errors = $this->validateRequired($data, ['name', 'email']);
        if (!empty($errors)) {
            return $this->errorResponse('Validation failed', ErrorCode::VALIDATION_ERROR, $errors);
        }

        // Sanitize input
        $sanitized = $this->sanitizeInput($data);

        // Validate email
        if (!$this->validateEmail($sanitized['email'])) {
            return $this->errorResponse('Invalid email', ErrorCode::VALIDATION_ERROR);
        }

        // Process...
    }
}
```

### Form Request Validators

Create form request classes for better validation:

```php
// app/Http/Requests/StudentStoreRequest.php
<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Hyperf\Foundation\Http\FormRequest;

class StudentStoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:students,email',
            'phone' => 'nullable|string|max:20',
            'class_id' => 'required|uuid|exists:class_models,id',
            'enrollment_date' => 'required|date_format:Y-m-d'
        ];
    }
}
```

Use in controller:
```php
public function store(StudentStoreRequest $request)
{
    $validated = $request->validated();
    // Validation is automatically handled before this point
    Student::create($validated);
}
```

---

## XSS Protection

### Input Sanitization

Use `InputValidationTrait::sanitizeInput()` for all user input:

```php
$sanitized = $this->sanitizeInput($request->all());
```

This recursively sanitizes strings using `htmlspecialchars()`.

### Output Encoding

For API responses, the framework handles JSON encoding automatically. For HTML output, use:

```php
$escaped = htmlspecialchars($userInput, ENT_QUOTES, 'UTF-8');
```

### Content Security Policy

Configure CSP in `.env`:

```bash
CSP_ENABLED=true
CSP_REPORT_ONLY=false
CSP_DEFAULT_SRC="'self'"
CSP_SCRIPT_SRC="'self' 'unsafe-inline'"
CSP_STYLE_SRC="'self' 'unsafe-inline'"
CSP_IMG_SRC="'self' data: https:"
```

---

## SQL Injection Prevention

### Use Eloquent ORM (Always)

✅ **GOOD**:
```php
$user = User::where('email', $email)->first();
```

❌ **BAD**:
```php
$user = DB::select("SELECT * FROM users WHERE email = '$email'");
```

### Parameterized Queries

When using raw queries (rare):

```php
use Hyperf\DbConnection\Db;

$users = Db::select('SELECT * FROM users WHERE id = :id', ['id' => $userId]);
```

---

## File Upload Security

### Basic Validation

Use `FileUploadService` for secure file uploads:

```php
use App\Services\FileUploadService;

$uploadService = new FileUploadService();

$result = $uploadService->uploadFile(
    $file,           // Uploaded file from request
    'students',       // Upload directory
    ['jpg', 'png'], // Allowed extensions
    5 * 1024 * 1024 // Max size (5MB)
);
```

### Manual Validation

```php
$allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
$maxSize = 5 * 1024 * 1024; // 5MB

$errors = $this->validateFileUpload($file, $allowedTypes, $maxSize);
if (!empty($errors)) {
    return $this->errorResponse('Invalid file', ErrorCode::FILE_UPLOAD_ERROR);
}
```

### Security Best Practices

1. **Validate File Type**: Check MIME type, not just extension
2. **Limit File Size**: Prevent DoS attacks
3. **Scan Content**: Use virus scanning if possible
4. **Store Outside Web Root**: `/storage/app` instead of `/public`
5. **Generate Safe Filenames**: Use UUIDs instead of original names
6. **Remove Metadata**: Strip EXIF data from images

---

## Password Security

### Password Hashing

Always use bcrypt (default):

```php
$hashedPassword = password_hash($password, PASSWORD_DEFAULT);
```

### Password Verification

```php
if (password_verify($inputPassword, $user->password)) {
    // Valid password
}
```

### Password Requirements

Implement in `AuthService`:

```php
if (strlen($newPassword) < 8) {
    throw new Exception('Password must be at least 8 characters');
}

// Add complexity checks
if (!preg_match('/[A-Z]/', $newPassword)) {
    throw new Exception('Password must contain uppercase letter');
}

if (!preg_match('/[0-9]/', $newPassword)) {
    throw new Exception('Password must contain number');
}
```

---

## Command Injection Prevention

### Using exec() Safely

Always use `escapeshellarg()`:

```php
$command = sprintf(
    'mysqldump --host=%s --user=%s %s',
    escapeshellarg($host),
    escapeshellarg($username),
    escapeshellarg($database)
);

exec($command, $output, $exitCode);
```

✅ **GOOD** - Arguments are escaped
❌ **BAD** - `exec("mysqldump --host=$host")`

---

## Security Monitoring

### Error Logs

Review logs regularly:

```bash
tail -f runtime/logs/hyperf.log
```

### Rate Limiting Alerts

Monitor for abuse:
```php
// In RateLimitMiddleware
$this->output->writeln('<comment>Rate limit exceeded: ' . $clientIp . '</comment>');
```

### Security Headers Test

Use curl to verify headers:

```bash
curl -I http://localhost:9501/api/students
```

Expected headers:
```
Content-Security-Policy: ...
X-Frame-Options: DENY
X-Content-Type-Options: nosniff
Strict-Transport-Security: max-age=31536000
```

---

## Known Issues

### Abandoned Package: laminas/laminas-mime

**Status**: Monitoring
**Impact**: Low (transitive dependency via Hyperf)
**Action**: Monitor Hyperf framework for updates to symfony/mime

See `docs/security-audit-report.md` for details.

---

## Dependency Updates

### Run Security Audits

```bash
# Composer (PHP)
composer audit

# NPM (Frontend)
cd frontend && npm audit
```

### Update Dependencies

```bash
# Update composer packages
composer update

# Update npm packages
cd frontend && npm update
```

---

## Security Checklist

### Before Production Deployment

- [ ] Generate JWT_SECRET with `php artisan jwt:secret`
- [ ] Set `APP_ENV=production` in `.env`
- [ ] Set `APP_DEBUG=false` in `.env`
- [ ] Configure secure database passwords
- [ ] Enable HTTPS (SSL/TLS)
- [ ] Review and update security headers
- [ ] Set appropriate rate limits
- [ ] Configure firewall rules
- [ ] Enable error monitoring
- [ ] Set up log rotation
- [ ] Run security audit: `composer audit && cd frontend && npm audit`

### After Production Deployment

- [ ] Monitor error logs
- [ ] Set up alerts for rate limit breaches
- [ ] Review access logs for suspicious activity
- [ ] Schedule regular dependency updates
- [ ] Plan quarterly security reviews

---

## Additional Resources

- **Security Audit Report**: `docs/security-audit-report.md`
- **OWASP Top 10**: https://owasp.org/Top10/
- **CSP Evaluator**: https://csp-evaluator.withgoogle.com/
- **JWT.io**: https://jwt.io/

---

## Security Contacts

For security issues or concerns:
- Create an issue with label `security`
- Email: security@example.com (replace with actual)

---

**Last Updated**: January 7, 2026
**Version**: 1.0.0
