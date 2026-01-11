<?php

declare(strict_types=1);

namespace App\Traits;

use DateTime;

trait InputValidationTrait
{
    /**
     * Validate required fields in input data.
     */
    protected function validateRequired(array $input, array $requiredFields): array
    {
        $errors = [];

        foreach ($requiredFields as $field) {
            if (! isset($input[$field]) || empty($input[$field])) {
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
        $d = DateTime::createFromFormat($format, $date);
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

        if (! empty($allowedTypes) && ! in_array($file['type'] ?? '', $allowedTypes)) {
            $errors[] = 'File type not allowed';
        }

        return $errors;
    }

    /**
     * Validate array of values.
     */
    protected function validateArray(mixed $value, array $rules = []): bool
    {
        if (! is_array($value)) {
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
}
