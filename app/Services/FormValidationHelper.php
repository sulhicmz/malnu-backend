<?php

declare(strict_types=1);

namespace App\Services;

class FormValidationHelper
{
    public static function validateAndSanitize(array $data, array $requiredFields = [], array $uniqueFields = []): array
    {
        $errors = [];
        $validatedData = [];

        foreach ($requiredFields as $field) {
            if (!isset($data[$field]) || $data[$field] === '') {
                $errors[$field] = "The {$field} field is required.";
            }
        }

        foreach ($uniqueFields as $field) {
            if (isset($data[$field]) && $data[$field] !== '') {
                $validatedData[$field] = htmlspecialchars(trim($data[$field]), ENT_QUOTES, 'UTF-8');
            }
        }

        return [
            'validated' => $validatedData,
            'errors' => $errors,
        ];
    }

    public static function validateUnique(string $table, string $field, mixed $value, ?string $excludeId = null): array
    {
        $queryClass = 'App\\Models\\' . str_replace('_', '', ucwords($table, '_'));
        
        if ($excludeId) {
            $exists = $queryClass::where($field, $value)->where('id', '!=', $excludeId)->exists();
        } else {
            $exists = $queryClass::where($field, $value)->exists();
        }

        if ($exists) {
            $fieldName = ucwords(str_replace('_', ' ', $field));
            return [$field => "The {$fieldName} has already been taken."];
        }

        return [];
    }

    public static function validateDateRange(string $startDate, string $endDate): array
    {
        $start = strtotime($startDate);
        $end = strtotime($endDate);
        
        if ($start === false || $end === false) {
            return ['date_range' => "Invalid date format. Please use Y-m-d format."];
        }
        
        if ($start > $end) {
            return ['date_range' => "Start date must be before or equal to end date."];
        }

        return [];
    }

    public static function validateEmail(string $email): array
    {
        if (empty($email)) {
            return [];
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return ['email' => "The email must be a valid email address."];
        }

        return [];
    }

    public static function validateInteger(mixed $value): array
    {
        if (!filter_var($value, FILTER_VALIDATE_INT)) {
            return ['field' => "This field must be an integer."];
        }

        return [];
    }

    public static function validateDateString(string $date): array
    {
        $d = \DateTime::createFromFormat('Y-m-d', $date);
        
        if (!$d || $d->format('Y-m-d') !== $date) {
            return ['date' => "This field must be a valid date (Y-m-d format)."];
        }

        return [];
    }

    public static function validateStringLength(string $value, ?int $min = null, ?int $max = null): array
    {
        $length = strlen($value);
        
        if ($min !== null && $length < $min) {
            return ['field' => "This field must be at least {$min} characters."];
        }
        
        if ($max !== null && $length > $max) {
            return ['field' => "This field must not exceed {$max} characters."];
        }

        return [];
    }
}
