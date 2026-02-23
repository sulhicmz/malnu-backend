<?php

declare(strict_types=1);

namespace App\Services;

/**
 * Validates user passwords against security requirements.
 *
 * This service enforces password complexity rules including:
 * - Minimum length of 8 characters
 * - At least one uppercase letter
 * - At least one lowercase letter
 * - At least one number
 * - At least one special character
 * - Not a common/weak password
 */
class PasswordValidator
{
    private array $commonPasswords;

    public function __construct()
    {
        $this->commonPasswords = [
            'password',
            'password123',
            '12345678',
            'qwerty123',
            'abc123',
            '123456789',
            '11111111',
            '123123123',
            'admin123',
            'letmein',
            'welcome1',
            'iloveyou',
            'monkey123',
            'dragon123',
            'sunshine1',
            'princess1',
            'football1',
            'baseball1',
            'whatever1',
            'superman1',
        ];
    }

    /**
     * Validates a password against security requirements.
     *
     * @param string $password The password to validate
     * @return array<int, string> Array of validation error messages (empty if valid)
     */
    public function validate(string $password): array
    {
        $errors = [];

        if (strlen($password) < 8) {
            $errors[] = 'Password must be at least 8 characters';
        }

        if (!preg_match('/[A-Z]/', $password)) {
            $errors[] = 'Password must contain at least one uppercase letter';
        }

        if (!preg_match('/[a-z]/', $password)) {
            $errors[] = 'Password must contain at least one lowercase letter';
        }

        if (!preg_match('/[0-9]/', $password)) {
            $errors[] = 'Password must contain at least one number';
        }

        if (!preg_match('/[^A-Za-z0-9]/', $password)) {
            $errors[] = 'Password must contain at least one special character';
        }

        if (in_array(strtolower($password), array_map('strtolower', $this->commonPasswords), true)) {
            $errors[] = 'Password is too common. Please choose a stronger password.';
        }

        return $errors;
    }

    /**
     * Checks if a password meets all security requirements.
     *
     * @param string $password The password to check
     * @return bool True if password is valid, false otherwise
     */
    public function isValid(string $password): bool
    {
        return empty($this->validate($password));
    }
}
