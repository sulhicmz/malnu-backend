<?php

declare(strict_types=1);

namespace App\Services;

class PasswordValidator
{
    private array $commonPasswords = [
        'password', '123456', 'password123', 'admin', 'qwerty',
        'letmein', 'welcome', 'monkey', '123456789', 'abc123',
        '111111', 'password1', 'sunshine', 'master', 'password!',
        'shadow', '12345678', 'princess', 'adobe123', 'iloveyou',
        'admin123', 'hello', 'football', 'trustno1', 'whatever',
        'superman', 'starwars', '123123', 'freedom', 'charlie',
        'andrew', 'michael', 'matthew', 'jessica', 'danielle',
        'solo', '666666', 'batman', 'joshua', 'amanda',
        'access', 'william', 'nicole', 'samantha', 'jordan',
        'harley', 'justin', 'bailey', 'hunter', 'ranger',
    ];

    public function validate(string $password): array
    {
        $errors = [];

        if (strlen($password) < 8) {
            $errors[] = 'Password must be at least 8 characters';
        }

        if (!$this->checkUppercase($password)) {
            $errors[] = 'Password must contain at least one uppercase letter';
        }

        if (!$this->checkLowercase($password)) {
            $errors[] = 'Password must contain at least one lowercase letter';
        }

        if (!$this->checkNumber($password)) {
            $errors[] = 'Password must contain at least one number';
        }

        if (!$this->checkSpecialChar($password)) {
            $errors[] = 'Password must contain at least one special character';
        }

        if ($this->checkCommonPasswords($password)) {
            $errors[] = 'Password is too common. Please choose a more secure password';
        }

        return $errors;
    }

    private function checkUppercase(string $password): bool
    {
        return preg_match('/[A-Z]/', $password) === 1;
    }

    private function checkLowercase(string $password): bool
    {
        return preg_match('/[a-z]/', $password) === 1;
    }

    private function checkNumber(string $password): bool
    {
        return preg_match('/[0-9]/', $password) === 1;
    }

    private function checkSpecialChar(string $password): bool
    {
        return preg_match('/[!@#$%^&*()_+\-=\[\]{};\':"\\|,.<>\/?]/', $password) === 1;
    }

    private function checkCommonPasswords(string $password): bool
    {
        return in_array(strtolower($password), $this->commonPasswords, true);
    }
}
