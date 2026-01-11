<?php

declare(strict_types=1);

namespace App\Services;

class PasswordValidator
{
    private const MIN_LENGTH = 8;

    private const COMMON_PASSWORDS = [
        'password',
        '123456',
        'password123',
        '12345678',
        'qwerty',
        '123456789',
        '1234567890',
        'letmein',
        '1234567',
        'admin',
        'welcome',
        'monkey',
        '12345678910',
        'football',
        '1234',
        'dragon',
        'baseball',
        '111111',
        'iloveyou',
        'master',
        'sunshine',
        'ashley',
        'bailey',
        'passw0rd',
        'shadow',
        '123123',
        '654321',
        'superman',
        'qazwsx',
        'trustno1',
        'joshua',
        'access14',
        '7777777',
        'princess',
        'adobe123',
        'photoshop',
        'Password1',
        '123qwe',
        '1q2w3e4r',
        'qwertyuiop',
        '55555',
        'lovely',
        '77777777',
        '888888',
        '123qweasdzxc',
        '123qweasdzxc',
        '123abc',
    ];

    public function validate(string $password): array
    {
        $errors = [];

        if (strlen($password) < self::MIN_LENGTH) {
            $errors[] = 'Password must be at least ' . self::MIN_LENGTH . ' characters';
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

        if (!preg_match('/[!@#$%^&*()_+\-=\[\]{};\':\"\\|,.<>\/?]/', $password)) {
            $errors[] = 'Password must contain at least one special character';
        }

        if (in_array(strtolower($password), array_map('strtolower', self::COMMON_PASSWORDS))) {
            $errors[] = 'Password is too common. Please choose a more secure password';
        }

        if (empty($errors)) {
            return ['valid' => true];
        }

        return ['valid' => false, 'errors' => $errors];
    }

    public function getMinimumLength(): int
    {
        return self::MIN_LENGTH;
    }

    public function getCommonPasswords(): array
    {
        return self::COMMON_PASSWORDS;
    }
}
