<?php

declare(strict_types=1);

namespace App\Contracts;

interface JWTServiceInterface
{
    public function generateToken(array $payload): string;

    public function decodeToken(string $token): ?array;

    public function refreshToken(string $token): string;

    public function getExpirationTime(): int;
}
