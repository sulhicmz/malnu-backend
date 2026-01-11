<?php

declare(strict_types=1);

namespace App\Services;

class PasswordValidator
{
    private array $commonPasswords = [
        'password',
        '123456',
        'password123',
        '12345678',
        'qwerty',
        '123456789',
        '12345',
        '1234',
        '111111',
        '1234567',
        'dragon',
        '123123',
        'baseball',
        'abc123',
        'football',
        'monkey',
        'letmein',
        '696969',
        'shadow',
        'master',
        '666666',
        'qwertyuiop',
        '123321',
        'mustang',
        '1234567890',
        'michael',
        '654321',
        'pussy',
        'superman',
        '1qaz2wsx',
        '7777777',
        'fuckyou',
        '121212',
        '000000',
        'qazwsx',
        '123qwe',
        'killer',
        'trustno1',
        'jordan',
        'jennifer',
        'zxcvbnm',
        'asdfgh',
        'hunter',
        'buster',
        'soccer',
        'harley',
        'batman',
        'andrew',
        'tigger',
        'sunshine',
        'iloveyou',
        '2000',
        'charlie',
        'robert',
        'thomas',
        'hockey',
        'ranger',
        'daniel',
        'starwars',
        'klaster',
        '112233',
        'george',
        'computer',
        'michelle',
        'jessica',
        'pepper',
        '1111',
        'zxcvbn',
        '555555',
        '11111111',
        '131313',
        'freedom',
        '77777777',
        'pass',
        'maggie',
        '159753',
        'aaaaaa',
        'ginger',
        'princess',
        'joshua',
        'cheese',
        'amanda',
        'summer',
        'love',
        'ashley',
        'nicole',
        'chelsea',
        'biteme',
        'matthew',
        'access',
        'yankees',
        '987654321',
        'dallas',
        'austin',
        'thunder',
        'taylor',
        'matrix',
        'mobilemail',
        'mom',
        'monitor',
        'monitoring',
        'montana',
        'moon',
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
            $errors[] = 'Password is too common and easily guessable';
        }

        return $errors;
    }

    public function checkUppercase(string $password): bool
    {
        return preg_match('/[A-Z]/', $password) === 1;
    }

    public function checkLowercase(string $password): bool
    {
        return preg_match('/[a-z]/', $password) === 1;
    }

    public function checkNumber(string $password): bool
    {
        return preg_match('/[0-9]/', $password) === 1;
    }

    public function checkSpecialChar(string $password): bool
    {
        return preg_match('/[!@#$%^&*()_+\-=\[\]{};\':"\\|,.<>\/?]/', $password) === 1;
    }

    public function checkCommonPasswords(string $password): bool
    {
        $lowercasePassword = strtolower($password);

        return in_array($lowercasePassword, $this->commonPasswords, true);
    }

    public function isValid(string $password): bool
    {
        return count($this->validate($password)) === 0;
    }

    public function getErrorMessage(string $password): string
    {
        $errors = $this->validate($password);

        if (count($errors) === 0) {
            return '';
        }

        return implode('. ', $errors);
    }
}