<?php

declare(strict_types=1);

namespace App\Contracts;

interface AuthServiceInterface
{
    public function register(array $data): array;

    public function login(string $email, string $password): array;

    public function getUserFromToken(string $token): ?array;

    public function refreshToken(string $token): array;

    public function logout(string $token): void;

    public function requestPasswordReset(string $email): array;

    public function resetPassword(string $token, string $newPassword): array;

    public function changePassword(string $userId, string $currentPassword, string $newPassword): array;
}
