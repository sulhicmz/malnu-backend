<?php

declare(strict_types=1);

namespace App\Exceptions;

use Exception;

class AuthenticationException extends Exception
{
    public static function invalidCredentials(): self
    {
        return new self('Invalid credentials');
    }

    public static function userNotFound(string $email): self
    {
        return new self("User not found: {$email}");
    }

    public static function tokenBlacklisted(): self
    {
        return new self('Token is blacklisted');
    }

    public static function currentPasswordIncorrect(): self
    {
        return new self('Current password is incorrect');
    }

    public static function invalidResetToken(): self
    {
        return new self('Invalid or expired reset token');
    }

    public static function resetTokenExpired(): self
    {
        return new self('Reset token has expired');
    }

    public static function invalidTokenHash(): self
    {
        return new self('Invalid reset token');
    }
}
