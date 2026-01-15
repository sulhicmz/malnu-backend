<?php

declare(strict_types=1);

namespace App\Traits;

trait InputValidationTrait
{
    /**
     * Validate required fields in input data.
     */
    protected function validateRequired(array $input, array $requiredFields): array
    {
        $errors = [];
        
        foreach ($requiredFields as $field) {
            if (!isset($input[$field]) || empty($input[$field])) {
                $errors[$field] = ["The {$field} field is required."];
            }
        }
        
        return $errors;
    }

    /**
     * Sanitize string input to prevent XSS.
     */
    protected function sanitizeString(?string $value): ?string
    {
        if ($value === null) {
            return null;
        }
        
        // Trim whitespace and sanitize HTML
        return htmlspecialchars(trim($value), ENT_QUOTES, 'UTF-8');
    }

    /**
     * Sanitize input array recursively.
     */
    protected function sanitizeInput(array $input): array
    {
        $sanitized = [];
        
        foreach ($input as $key => $value) {
            if (is_string($value)) {
                $sanitized[$key] = $this->sanitizeString($value);
            } elseif (is_array($value)) {
                $sanitized[$key] = $this->sanitizeInput($value);
            } else {
                $sanitized[$key] = $value;
            }
        }
        
        return $sanitized;
    }

    /**
     * Validate email format.
     */
    protected function validateEmail(string $email): bool
    {
        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }

    /**
     * Validate numeric value.
     */
    protected function validateNumeric(mixed $value): bool
    {
        return is_numeric($value);
    }

    /**
     * Validate date format.
     */
    protected function validateDate(string $date, string $format = 'Y-m-d'): bool
    {
        $d = \DateTime::createFromFormat($format, $date);
        return $d && $d->format($format) === $date;
    }

     /**
      * Validate string length.
      */
     protected function validateStringLength(string $value, ?int $min = null, ?int $max = null): bool
     {
         $length = strlen($value);
         
         if ($min !== null && $length < $min) {
             return false;
         }
         
         if ($max !== null && $length > $max) {
             return false;
         }
         
         return true;
     }

    /**
     * Validate that start date is before or equal to end date.
     */
    protected function validateDateRange(string $startDate, string $endDate): bool
    {
        $start = strtotime($startDate);
        $end = strtotime($endDate);
        
        return $start !== false && $end !== false && $start <= $end;
    }

     /**
      * Validate file upload (basic validation).
      */
     protected function validateFileUpload(mixed $file, array $allowedTypes = [], ?int $maxSize = null): array
     {
         $errors = [];
         
         if ($file === null) {
             $errors[] = 'File is required';
             return $errors;
         }
         
         // Basic validation for file uploads
         if ($maxSize && ($file['size'] ?? 0) > $maxSize) {
             $errors[] = 'File size exceeds maximum allowed size';
         }
         
         if (!empty($allowedTypes) && !in_array($file['type'] ?? '', $allowedTypes)) {
             $errors[] = 'File type not allowed';
         }
         
         return $errors;
     }

    /**
     * Validate array of values.
     */
    protected function validateArray(mixed $value, array $rules = []): bool
    {
        if (!is_array($value)) {
            return false;
        }
        
        if (isset($rules['min']) && count($value) < $rules['min']) {
            return false;
        }
        
        if (isset($rules['max']) && count($value) > $rules['max']) {
            return false;
        }
        
        return true;
    }

    /**
     * Validate integer value.
     */
    protected function validateInteger(mixed $value): bool
    {
        return filter_var($value, FILTER_VALIDATE_INT) !== false;
    }

    /**
     * Validate boolean value.
     */
    protected function validateBoolean(mixed $value): bool
    {
        return filter_var($value, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE) !== null;
    }

    /**
     * Validate password complexity.
     * Requires minimum 8 characters, at least 1 uppercase, 1 lowercase, 1 number, 1 special character,
     * and not in common passwords list.
     *
     * @return array<string, string> Array of error messages (empty if valid)
     */
    protected function validatePasswordComplexity(string $password): array
    {
        $errors = [];

        if (strlen($password) < 8) {
            $errors[] = 'Password must be at least 8 characters long.';
        }

        if (!preg_match('/[A-Z]/', $password)) {
            $errors[] = 'Password must contain at least 1 uppercase letter.';
        }

        if (!preg_match('/[a-z]/', $password)) {
            $errors[] = 'Password must contain at least 1 lowercase letter.';
        }

        if (!preg_match('/[0-9]/', $password)) {
            $errors[] = 'Password must contain at least 1 number.';
        }

        if (!preg_match('/[!@#$%^&*(),.?":{}|<>]/', $password)) {
            $errors[] = 'Password must contain at least 1 special character (!@#$%^&*(),.?":{}|<>).';
        }

        $commonPasswords = [
            'password', '123456', '12345678', 'qwerty', 'abc123', 'monkey', 'master',
            'dragon', '111111', 'baseball', 'iloveyou', 'trustno1', 'sunshine', 'princess',
            'admin', 'welcome', 'shadow', 'ashley', 'football', 'jesus', 'michael',
            'ninja', 'mustang', 'password1', 'password123', 'letmein', 'login', 'starwars'
        ];

        if (in_array(strtolower($password), $commonPasswords, true)) {
            $errors[] = 'Password is too common. Please choose a stronger password.';
        }

        return $errors;
    }

    /**
     * Validate URL format and protocol.
     *
     * @param string $url URL to validate
     * @return bool True if URL is valid and uses allowed protocol
     */
    protected function validateUrl(string $url): bool
    {
        $result = filter_var($url, FILTER_VALIDATE_URL);
        if ($result === false) {
            return false;
        }

        // Only allow http and https protocols
        $parsed = parse_url($url);
        if ($parsed === false) {
            return false;
        }

        $scheme = strtolower($parsed['scheme'] ?? '');
        return in_array($scheme, ['http', 'https'], true);
    }

    /**
     * Validate phone number format.
     * Supports international format with optional country code prefix.
     *
     * @param string $phone Phone number to validate
     * @return bool True if phone number is valid
     */
    protected function validatePhone(string $phone): bool
    {
        // Remove all non-digit characters except +
        $cleaned = preg_replace('/[^0-9+]/', '', $phone);

        // Check length: 10-15 digits (including optional + prefix)
        $length = strlen($cleaned);

        return $length >= 10 && $length <= 15;
    }

    /**
     * Validate IP address (IPv4 or IPv6).
     *
     * @param string $ip IP address to validate
     * @return bool True if IP address is valid
     */
    protected function validateIp(string $ip): bool
    {
        return filter_var($ip, FILTER_VALIDATE_IP) !== false;
    }

    /**
     * Validate JSON structure.
     *
     * @param string $json JSON string to validate
     * @return bool True if JSON is valid
     */
    protected function validateJson(string $json): bool
    {
        json_decode($json);
        return json_last_error() === JSON_ERROR_NONE;
    }

    /**
     * Validate input against custom regex pattern.
     *
     * @param string $value Value to validate
     * @param string $pattern Regex pattern to match
     * @return bool True if value matches pattern
     */
    protected function validateRegex(string $value, string $pattern): bool
    {
        return preg_match($pattern, $value) === 1;
    }

    /**
     * Sanitize shell command to prevent injection.
     *
     * @param string $command Command to sanitize
     * @return string Sanitized command
     */
    protected function sanitizeCommand(string $command): string
    {
        return escapeshellcmd($command);
    }

    /**
     * Sanitize shell command argument to prevent injection.
     *
     * @param string $arg Argument to sanitize
     * @return string Sanitized argument
     */
    protected function sanitizeCommandArg(string $arg): string
    {
        return escapeshellarg($arg);
    }

    /**
     * Escape SQL identifier to prevent SQL injection.
     * Note: This is a last resort. Use parameterized queries via ORM whenever possible.
     *
     * @param string $identifier SQL identifier (table/column name)
     * @return string Escaped identifier wrapped in backticks
     */
    protected function escapeSqlIdentifier(string $identifier): string
    {
        return '`' . str_replace('`', '``', $identifier) . '`';
    }

    /**
     * Sanitize filename to prevent path traversal and command injection.
     *
     * @param string $filename Original filename
     * @return string Sanitized safe filename
     */
    protected function sanitizeFilename(string $filename): string
    {
        // Remove path traversal sequences
        $sanitized = str_replace(['../', '..\\', './', '.\\'], '', $filename);

        // Remove dangerous characters
        $sanitized = preg_replace('/[^a-zA-Z0-9._-]/', '', $sanitized);

        // Remove leading/trailing dots and spaces
        $sanitized = trim($sanitized, '. ');

        return $sanitized;
    }

    /**
     * Validate secure file upload with enhanced security checks.
     *
     * @param mixed $file File array from $_FILES
     * @param array $allowedMimes Allowed MIME types (empty = allow all)
     * @param int|null $maxSizeBytes Maximum file size in bytes (null = no limit)
     * @return array<string, string> Array of error messages (empty if valid)
     */
    protected function validateSecureFileUpload(mixed $file, array $allowedMimes = [], ?int $maxSizeBytes = null): array
    {
        $errors = [];

        if ($file === null || !is_array($file)) {
            $errors[] = 'File is required';
            return $errors;
        }

        if (!isset($file['tmp_name']) || !isset($file['name'])) {
            $errors[] = 'Invalid file upload structure';
            return $errors;
        }

        // Check for actual file upload
        if (!is_uploaded_file($file['tmp_name'])) {
            $errors[] = 'Invalid file upload';
            return $errors;
        }

        // Check file size
        if ($maxSizeBytes !== null && isset($file['size']) && $file['size'] > $maxSizeBytes) {
            $errors[] = 'File size exceeds maximum allowed size of ' . $this->formatBytes($maxSizeBytes);
        }

        // Validate filename
        $sanitizedFilename = $this->sanitizeFilename($file['name']);
        if ($sanitizedFilename !== $file['name']) {
            $errors[] = 'Filename contains invalid characters or path traversal sequences';
        }

        // Check for dangerous file extensions
        $dangerousExtensions = ['.php', '.php5', '.phtml', '.sh', '.exe', '.bat', '.cmd', '.com', '.scr', '.cgi', '.pl', '.py'];
        $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

        if (in_array('.' . $extension, $dangerousExtensions, true)) {
            $errors[] = 'Dangerous file extension detected: ' . $extension;
        }

        // Validate MIME type (more secure than checking extension only)
        if (function_exists('finfo_open')) {
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $mimeType = finfo_file($finfo, $file['tmp_name']);
            finfo_close($finfo);

            if ($mimeType === false) {
                $errors[] = 'Unable to determine file type';
            }

            // Check MIME type against allowed list if specified
            if (!empty($allowedMimes) && !in_array($mimeType, $allowedMimes, true)) {
                $errors[] = 'File MIME type not allowed. Allowed types: ' . implode(', ', $allowedMimes);
            }

            // Check for MIME type mismatch (extension spoofing detection)
            $extensionToMime = [
                'jpg' => 'image/jpeg',
                'jpeg' => 'image/jpeg',
                'png' => 'image/png',
                'gif' => 'image/gif',
                'pdf' => 'application/pdf',
                'doc' => 'application/msword',
                'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                'xls' => 'application/vnd.ms-excel',
                'xlsx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            ];

            $expectedMime = $extensionToMime[$extension] ?? null;
            if ($expectedMime !== null && !str_starts_with($mimeType, explode('/', $expectedMime)[0])) {
                $errors[] = 'File extension does not match actual content type';
            }
        }

        return $errors;
    }

    /**
     * Format bytes to human-readable format.
     *
     * @param int $bytes File size in bytes
     * @return string Formatted size (e.g., "5 MB")
     */
    private function formatBytes(int $bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);

        $bytes /= pow(1024, $pow);

        return round($bytes, 2) . ' ' . $units[$pow];
    }
}