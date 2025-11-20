<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Role;
use App\Models\Permission;
use App\Models\ModelHasRole;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class PerformanceTestSeeder extends Seeder
{
    /**
     * Run the database seeds for performance testing.
     */
    public function run(): void
    {
        // Create some roles for testing
        $adminRole = Role::create([
            'name' => 'admin',
            'guard_name' => 'web'
        ]);
        
        $teacherRole = Role::create([
            'name' => 'teacher',
            'guard_name' => 'web'
        ]);
        
        $studentRole = Role::create([
            'name' => 'student',
            'guard_name' => 'web'
        ]);

        // Create some permissions
        $createUsersPermission = Permission::create([
            'name' => 'create-users',
            'guard_name' => 'web'
        ]);
        
        $editUsersPermission = Permission::create([
            'name' => 'edit-users',
            'guard_name' => 'web'
        ]);
        
        $deleteUsersPermission = Permission::create([
            'name' => 'delete-users',
            'guard_name' => 'web'
        ]);

        // Create test users with relationships
        for ($i = 1; $i <= 50; $i++) {
            $user = User::create([
                'name' => "Test User {$i}",
                'username' => "user{$i}",
                'email' => "user{$i}@example.com",
                'password' => Hash::make('password'),
                'full_name' => "Full Name {$i}",
                'is_active' => true,
            ]);

            // Assign roles randomly to create relationship data for testing
            if ($i % 3 === 0) {
                // Assign admin role to every 3rd user
                ModelHasRole::create([
                    'role_id' => $adminRole->id,
                    'model_type' => User::class,
                    'model_id' => $user->id
                ]);
            } elseif ($i % 2 === 0) {
                // Assign teacher role to every even-numbered user not already admin
                ModelHasRole::create([
                    'role_id' => $teacherRole->id,
                    'model_type' => User::class,
                    'model_id' => $user->id
                ]);
            } else {
                // Assign student role to remaining users
                ModelHasRole::create([
                    'role_id' => $studentRole->id,
                    'model_type' => User::class,
                    'model_id' => $user->id
                ]);
            }
        }
    }
}