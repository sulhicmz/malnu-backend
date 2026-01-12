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
        $parsed = parse_url($url);
        
        if ($parsed === false) {
            return false;
        }
        
        // Validate scheme (protocol)
        $allowedSchemes = ['http', 'https'];
        if (!isset($parsed['scheme']) || !in_array(strtolower($parsed['scheme']), $allowedSchemes)) {
            return false;
        }
        
        // Validate host
        if (!isset($parsed['host']) || empty($parsed['host'])) {
            return false;
        }
        
        return true;
    }

    /**
     * Validate phone number format.
     * Supports various formats: +1234567890, (123) 456-7890, 123-456-7890
     */
    protected function validatePhoneNumber(string $phone): bool
    {
        // Remove all non-numeric characters
        $digits = preg_replace('/[^0-9]/', '', $phone);
        
        // Check if we have between 10 and 15 digits (typical phone number lengths)
        return strlen($digits) >= 10 && strlen($digits) <= 15;
    }

    /**
     * Sanitize input for safe use in SQL queries (escape quotes).
     * Note: This is a defense-in-depth measure. Always use parameterized queries via Eloquent.
     */
    protected function sanitizeForSql(string $input): string
    {
        $search = ['\\', "'", '"', "\x00", "\n", "\r", "\x1a"];
        $replace = ['\\\\', "\\'", '\\"', '\\0', '\\n', '\\r', '\\Z'];
        
        return str_replace($search, $replace, $input);
    }

    /**
     * Sanitize input for safe use in system commands.
     */
    protected function sanitizeForCommand(string $input): string
    {
        $input = escapeshellarg($input);
        
        return $input;
    }

    /**
     * Detect common injection patterns in input.
     */
    protected function detectInjectionPatterns(string $input): array
    {
        $patterns = [
            'sql' => [
                '/(\b(SELECT|INSERT|UPDATE|DELETE|DROP|UNION|EXEC|ALTER|CREATE|TRUNCATE)\b)/i',
                "/('|\")\s*(OR|AND)\s*('|\")/i",
                "/(\-\-|\/\*|\*\/|;)/",
                "/(\b1\s*=\s*1\b|\b1\s*!=\s*0\b)/i"
            ],
            'command' => [
                '/[;&|`$()]/',
                '/\$\([^)]+\)/',
                '/`[^`]+`/',
            ],
            'ldap' => [
                '/(\(\|[^)]*\))/',
                '/(\*[)]+)/',
                '/([*]?\(.*\))/',
            ],
            'path' => [
                '/(\.\.\/|\.\.\\\)/',
            ]
        ];
        
        $detected = [];
        
        foreach ($patterns as $type => $typePatterns) {
            foreach ($typePatterns as $pattern) {
                if (preg_match($pattern, $input)) {
                    if (!in_array($type, $detected)) {
                        $detected[] = $type;
                    }
                    break;
                }
            }
        }
        
        return $detected;
    }

    /**
     * Sanitize file name for safe storage.
     */
    protected function sanitizeFileName(string $fileName): string
    {
        // Remove path information
        $fileName = basename($fileName);
        
        // Replace dangerous characters with underscore
        $fileName = preg_replace('/[^a-zA-Z0-9._-]/', '_', $fileName);
        
        // Prevent hidden files
        if (str_starts_with($fileName, '.')) {
            $fileName = 'file_' . $fileName;
        }
        
        // Prevent double extensions (e.g., file.php.jpg)
        $parts = explode('.', $fileName);
        if (count($parts) > 2) {
            $fileName = $parts[0] . '.' . end($parts);
        }
        
        return $fileName;
    }

    /**
     * Validate file upload with enhanced security checks.
     */
    protected function validateFileUploadEnhanced(mixed $file, array $allowedExtensions = [], ?int $maxSize = null): array
    {
        $errors = $this->validateFileUpload($file, [], $maxSize);
        
        if (!empty($errors)) {
            return $errors;
        }
        
        // File extension validation
        if (!empty($allowedExtensions)) {
            $fileName = $file['name'] ?? '';
            $extension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
            
            if (!in_array($extension, $allowedExtensions)) {
                $errors[] = 'File extension not allowed';
            }
        }
        
        // MIME type verification (double-check)
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        if ($finfo) {
            $mimeType = finfo_file($finfo, $file['tmp_name'] ?? '');
            finfo_close($finfo);
            
            // Check if file extension matches MIME type
            $extension = strtolower(pathinfo($file['name'] ?? '', PATHINFO_EXTENSION));
            $mimeMap = [
                'jpg' => ['image/jpeg'],
                'jpeg' => ['image/jpeg'],
                'png' => ['image/png'],
                'gif' => ['image/gif'],
                'pdf' => ['application/pdf'],
                'doc' => ['application/msword'],
                'docx' => ['application/vnd.openxmlformats-officedocument.wordprocessingml.document'],
                'xls' => ['application/vnd.ms-excel'],
                'xlsx' => ['application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'],
                'txt' => ['text/plain'],
                'csv' => ['text/csv', 'application/csv'],
            ];
            
            if (isset($mimeMap[$extension]) && $mimeType) {
                if (!in_array($mimeType, $mimeMap[$extension])) {
                    $errors[] = 'File content does not match file extension';
                }
            }
        }
        
        // Check for malicious file content (e.g., PHP code in images)
        $content = file_get_contents($file['tmp_name'] ?? '');
        if ($content !== false) {
            $dangerousPatterns = [
                '/<\?php/i',
                '/<script/i',
                '/javascript:/i',
                '/eval\(/i',
                '/base64_decode/i',
            ];
            
            foreach ($dangerousPatterns as $pattern) {
                if (preg_match($pattern, $content)) {
                    $errors[] = 'File contains potentially malicious content';
                    break;
                }
            }
        }
        
        return $errors;
    }
}