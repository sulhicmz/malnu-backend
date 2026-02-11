<?php

declare(strict_types=1);

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Role;
use App\Models\ModelHasRole;
use App\Services\JWTService;

class RoleMiddlewareTest extends TestCase
{
    public function test_user_with_required_role_can_access_protected_route()
    {
        $user = User::create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => password_hash('password123', PASSWORD_DEFAULT),
            'is_active' => true,
        ]);

        $adminRole = Role::firstOrCreate(['name' => 'Super Admin']);

        ModelHasRole::create([
            'role_id' => $adminRole->id,
            'model_type' => User::class,
            'model_id' => $user->id,
        ]);

        $jwtService = new JWTService();
        $token = $jwtService->generateToken([
            'id' => $user->id,
            'email' => $user->email
        ]);

        $response = $this->get('/school/students', [
            'Authorization' => 'Bearer ' . $token
        ]);

        $this->assertNotEquals(403, $response->getStatusCode());
    }

    public function test_user_without_required_role_cannot_access_protected_route()
    {
        $user = User::create([
            'name' => 'Regular User',
            'email' => 'regular@example.com',
            'password' => password_hash('password123', PASSWORD_DEFAULT),
            'is_active' => true,
        ]);

        $studentRole = Role::firstOrCreate(['name' => 'Siswa']);

        ModelHasRole::create([
            'role_id' => $studentRole->id,
            'model_type' => User::class,
            'model_id' => $user->id,
        ]);

        $jwtService = new JWTService();
        $token = $jwtService->generateToken([
            'id' => $user->id,
            'email' => $user->email
        ]);

        $response = $this->get('/school/students', [
            'Authorization' => 'Bearer ' . $token
        ]);

        $this->assertEquals(403, $response->getStatusCode());
        $responseData = json_decode((string) $response->getBody(), true);
        $this->assertFalse($responseData['success']);
        $this->assertEquals('Insufficient permissions', $responseData['error']['message']);
    }

    public function test_user_without_token_cannot_access_protected_route()
    {
        $response = $this->get('/school/students');

        $this->assertEquals(401, $response->getStatusCode());
        $responseData = json_decode((string) $response->getBody(), true);
        $this->assertFalse($responseData['success']);
    }

    public function test_user_with_multiple_allowed_roles_can_access()
    {
        $user = User::create([
            'name' => 'Multi-role User',
            'email' => 'multi@example.com',
            'password' => password_hash('password123', PASSWORD_DEFAULT),
            'is_active' => true,
        ]);

        $roles = [
            Role::firstOrCreate(['name' => 'Guru']),
            Role::firstOrCreate(['name' => 'Kepala Sekolah'])
        ];

        foreach ($roles as $role) {
            ModelHasRole::create([
                'role_id' => $role->id,
                'model_type' => User::class,
                'model_id' => $user->id,
            ]);
        }

        $jwtService = new JWTService();
        $token = $jwtService->generateToken([
            'id' => $user->id,
            'email' => $user->email
        ]);

        $response = $this->get('/school/students', [
            'Authorization' => 'Bearer ' . $token
        ]);

        $this->assertNotEquals(403, $response->getStatusCode());
    }
}
