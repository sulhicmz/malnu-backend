<?php

declare(strict_types=1);

namespace App\Exceptions;

use Exception;

class BusinessLogicException extends Exception
{
    public static function userAlreadyExists(string $email): self
    {
        return new self("User with email {$email} already exists");
    }
}
