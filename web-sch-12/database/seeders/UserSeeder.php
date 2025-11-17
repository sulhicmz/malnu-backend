<?php
namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role;

class UserSeeder extends Seeder
{
    private const USERS = [
        [
            'name'     => 'Super Admin',
            'username' => 'superadmin',
            'email'    => 'superadmin@super.com',
            'password' => 'password',
            'role'     => 'Super Admin',
        ],
        [
            'name'     => 'Admin',
            'username' => 'admin',
            'email'    => 'admin@admin.com',
            'password' => 'password',
            'role'     => 'Admin',
        ],
        [
            'name'     => 'Kepala Sekolah',
            'username' => 'kepsek',
            'email'    => 'kepsek@admin.com',
            'password' => 'password',
            'role'     => 'Kepala Sekolah',
        ],
        [
            'name'     => 'Guru',
            'username' => 'guru',
            'email'    => 'guru@admin.com',
            'password' => 'password',
            'role'     => 'Guru',
        ],
        [
            'name'     => 'Siswa SMP',
            'username' => 'smpstudent',
            'email'    => 'smpstudent@user.com',
            'password' => 'password',
            'role'     => 'Siswa SMP',
        ],
        [
            'name'     => 'Siswa SD',
            'username' => 'sdstudent',
            'email'    => 'sdstudent@user.com',
            'password' => 'password',
            'role'     => 'Siswa SD',
        ],
        [
            'name'     => 'Siswa SMA',
            'username' => 'smastudent',
            'email'    => 'smastudent@user.com',
            'password' => 'password',
            'role'     => 'Siswa SMA',
        ],
    ];

    /**
     * Seed the users table and assign roles.
     */
    public function run(): void
    {
        foreach (self::USERS as $userData) {
            $this->createUser($userData);
        }
    }

    /**
     * Create a user and assign their role if they don't already exist.
     */
    private function createUser(array $userData): void
    {
        // Check if user already exists by email or username
        $existingUser = User::where('email', $userData['email'])
            ->orWhere('username', $userData['username'])
            ->first();

        if ($existingUser) {
            // Optionally update the existing user's role if needed
            $existingUser->syncRoles([$userData['role']]);
            return;
        }

        $user = User::create([
            'name'              => $userData['name'],
            'username'          => $userData['username'],
            'email'             => $userData['email'],
            'email_verified_at' => now(),
            'password'          => Hash::make($userData['password']),
            'last_login_ip'     => null,
            'last_login_time'   => null,
            'login_count'       => 0,
            'key_status'        => null,
            'slug'              => Str::slug($userData['username']),
            'foto_profile'      => null,
            'phone'             => null,
            'avatar_url'        => null,
            'is_active'         => true,
        ]);

        $user->assignRole($userData['role']);
    }
}
