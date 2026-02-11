<?php

declare(strict_types=1);

namespace App\Services;

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

    public function isValid(string $password): bool
    {
        return empty($this->validate($password));
    }
}
