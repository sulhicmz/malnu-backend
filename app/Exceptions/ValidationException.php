<?php

declare(strict_types=1);

namespace App\Exceptions;

use Exception;

class ValidationException extends Exception
{
    public static function passwordComplexity(array $errors): self
    {
        return new self('Password validation failed: ' . implode(', ', $errors));
    }

    public static function passwordTooShort(): self
    {
        return new self('Password must be at least 8 characters long');
    }

    public static function passwordMissingUppercase(): self
    {
        return new self('Password must contain at least 1 uppercase letter');
    }

    public static function passwordMissingLowercase(): self
    {
        return new self('Password must contain at least 1 lowercase letter');
    }

    public static function passwordMissingNumber(): self
    {
        return new self('Password must contain at least 1 number');
    }

    public static function passwordMissingSpecialCharacter(): self
    {
        return new self('Password must contain at least 1 special character');
    }

    public static function passwordTooCommon(): self
    {
        return new self('Password is too common. Please choose a stronger password.');
    }
}
