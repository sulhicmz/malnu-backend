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
      * Validate URL format.
      */
    protected function validateUrl(string $url): bool
    {
        return filter_var($url, FILTER_VALIDATE_URL) !== false;
    }

    /**
      * Validate phone number (supports international formats).
      */
    protected function validatePhone(string $phone): bool
    {
        $phone = preg_replace('/[^\d+]/', '', $phone);
        return strlen($phone) >= 10 && strlen($phone) <= 15;
    }

    /**
      * Validate IP address (IPv4 and IPv6).
      */
    protected function validateIp(string $ip): bool
    {
        return filter_var($ip, FILTER_VALIDATE_IP) !== false;
    }

    /**
      * Validate JSON structure.
      */
    protected function validateJson(string $json): bool
    {
        json_decode($json);
        return json_last_error() === JSON_ERROR_NONE;
    }

    /**
      * Validate input against custom regex pattern.
      */
    protected function validateRegex(string $value, string $pattern): bool
    {
        return preg_match($pattern, $value) === 1;
    }

    /**
      * Sanitize command to prevent command injection.
      */
    protected function sanitizeCommand(string $command): string
    {
        return escapeshellcmd($command);
    }

    /**
      * Sanitize command argument to prevent command injection.
      */
    protected function sanitizeCommandArg(string $arg): string
    {
        return escapeshellarg($arg);
    }

    /**
      * Escape SQL identifier to prevent SQL injection.
      */
    protected function escapeSqlIdentifier(string $identifier): string
    {
        return str_replace('`', '``', $identifier);
    }

    /**
      * Sanitize filename for safe file storage.
      */
    protected function sanitizeFilename(string $filename): string
    {
        $filename = preg_replace('/[^a-zA-Z0-9._-]/', '', $filename);
        $filename = trim($filename, '.-');
        return $filename ?: 'file';
    }

    /**
      * Validate file upload with enhanced security checks.
      */
    protected function validateSecureFileUpload(mixed $file, array $allowedMimes = [], ?int $maxSizeBytes = null): array
    {
        $errors = [];

        if ($file === null) {
            $errors[] = 'File is required';
            return $errors;
        }

        if (!isset($file['tmp_name']) || !is_uploaded_file($file['tmp_name'])) {
            $errors[] = 'Invalid file upload';
            return $errors;
        }

        if ($maxSizeBytes && isset($file['size']) && $file['size'] > $maxSizeBytes) {
            $errors[] = 'File size exceeds maximum allowed size';
        }

        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        if ($finfo) {
            $detectedMime = finfo_file($finfo, $file['tmp_name']);
            finfo_close($finfo);

            if (!empty($allowedMimes) && !in_array($detectedMime, $allowedMimes)) {
                $errors[] = 'File type not allowed';
            }
        }

        if (isset($file['name'])) {
            $sanitizedName = $this->sanitizeFilename($file['name']);
            if ($sanitizedName !== $file['name']) {
                $errors[] = 'Filename contains invalid characters';
            }
        }

        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $dangerousExtensions = ['php', 'phtml', 'php3', 'php4', 'php5', 'php7', 'phps', 'shtml', 'sh', 'cgi', 'pl', 'exe', 'dll', 'bat', 'cmd'];
        if (in_array($ext, $dangerousExtensions)) {
            $errors[] = 'File extension is not allowed';
        }

        return $errors;
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
}