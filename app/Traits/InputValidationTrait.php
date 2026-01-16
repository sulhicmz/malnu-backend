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
     * Validate URL format.
     */
    protected function validateUrl(string $url): bool
    {
        return filter_var($url, FILTER_VALIDATE_URL) !== false;
    }

    /**
     * Validate phone number format.
     * Supports international formats with country code and various separators.
     */
    protected function validatePhoneNumber(string $phone): bool
    {
        $phone = preg_replace('/[\s\-\(\)]+/', '', $phone);
        return preg_match('/^\+?[1-9]\d{6,14}$/', $phone);
    }

    /**
     * Validate that a value is one of the allowed values.
     */
    protected function validateIn(mixed $value, array $allowedValues): bool
    {
        return in_array($value, $allowedValues, true);
    }

    /**
     * Sanitize input to prevent SQL injection.
     * Note: Parameterized queries should be used for database operations.
     * This is an additional layer of protection.
     */
    protected function sanitizeForSql(?string $value): ?string
    {
        if ($value === null) {
            return null;
        }

        return addslashes($value);
    }

    /**
     * Validate alphanumeric string.
     */
    protected function validateAlphanumeric(string $value): bool
    {
        return ctype_alnum($value);
    }

    /**
     * Validate JSON string.
     */
    protected function validateJson(string $value): bool
    {
        json_decode($value);
        return json_last_error() === JSON_ERROR_NONE;
    }

    /**
     * Sanitize filename to prevent directory traversal.
     */
    protected function sanitizeFilename(string $filename): string
    {
        $filename = basename($filename);
        $filename = preg_replace('/[^a-zA-Z0-9._-]/', '', $filename);
        return $filename;
    }

    /**
     * Detect potential SQL injection patterns.
     */
    protected function detectSqlInjection(string $value): bool
    {
        $patterns = [
            '/(\s|^)(SELECT|INSERT|UPDATE|DELETE|DROP|CREATE|ALTER|TRUNCATE)(\s|$)/i',
            '/(\s|^)(UNION|JOIN|WHERE|OR|AND)(\s|$)/i',
            '/[\'";\\]/',
            '/--/',
            '/\/\*/',
            '/\*\//',
        ];

        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $value)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Detect potential XSS patterns.
     */
    protected function detectXss(string $value): bool
    {
        $patterns = [
            '/<script\b[^>]*>(.*?)<\/script>/is',
            '/<iframe\b[^>]*>(.*?)<\/iframe>/is',
            '/<object\b[^>]*>(.*?)<\/object>/is',
            '/<embed\b[^>]*>(.*?)<\/embed>/is',
            '/on\w+\s*=\s*["\']?[^"\'\s>]+/i',
            '/javascript:/i',
            '/vbscript:/i',
        ];

        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $value)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Validate UUID format.
     */
    protected function validateUuid(string $value): bool
    {
        return preg_match('/^[0-9a-f]{8}-[0-9a-f]{4}-4[0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/i', $value);
    }

    /**
     * Validate IP address.
     */
    protected function validateIp(string $ip): bool
    {
        return filter_var($ip, FILTER_VALIDATE_IP) !== false;
    }

    /**
     * Validate that value matches regex pattern.
     */
    protected function validatePattern(string $value, string $pattern): bool
    {
        return preg_match($pattern, $value) === 1;
    }

    /**
     * Validate integer range.
     */
    protected function validateIntegerRange(int $value, ?int $min = null, ?int $max = null): bool
    {
        if ($min !== null && $value < $min) {
            return false;
        }

        if ($max !== null && $value > $max) {
            return false;
        }

        return true;
    }

    /**
     * Sanitize input by removing HTML tags.
     */
    protected function stripHtml(?string $value): ?string
    {
        if ($value === null) {
            return null;
        }

        return strip_tags($value);
    }

    /**
     * Validate that array contains only expected keys.
     */
    protected function validateArrayKeys(array $array, array $expectedKeys): bool
    {
        $actualKeys = array_keys($array);
        $unexpectedKeys = array_diff($actualKeys, $expectedKeys);

        return empty($unexpectedKeys);
    }
 }