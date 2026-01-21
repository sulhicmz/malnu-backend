<?php

declare(strict_types=1);

namespace App\Exceptions;

use Exception;

class NotFoundException extends Exception
{
    public static function userNotFound(): self
    {
        return new self('User not found');
    }
}
