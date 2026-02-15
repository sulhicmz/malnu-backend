<?php

declare(strict_types=1);

namespace Tests\Traits;

use App\Models\User;
use App\Services\Auth\JWTService;

trait TestAuthenticationTrait
{
    protected function createAuthenticatedUser(string $role = 'student'): User
    {
        return User::factory()->$role()->create();
    }

    protected function createAdmin(): User
    {
        return $this->createAuthenticatedUser('admin');
    }

    protected function createTeacher(): User
    {
        return $this->createAuthenticatedUser('teacher');
    }

    protected function createStudent(): User
    {
        return $this->createAuthenticatedUser('student');
    }

    protected function createParent(): User
    {
        return $this->createAuthenticatedUser('parent');
    }

    protected function generateJwtToken(User $user): string
    {
        $jwtService = app(JWTService::class);
        return $jwtService->generateToken($user);
    }

    protected function getAuthHeaders(User $user): array
    {
        $token = $this->generateJwtToken($user);
        return [
            'Authorization' => 'Bearer ' . $token,
            'Accept' => 'application/json',
        ];
    }

    protected function actingAsUser(User $user): self
    {
        $this->actingAs($user);
        return $this;
    }
}
