<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class PermissionSeeder extends Seeder
{
    private const PERMISSIONS = [
        'view',
        'edit',
        'delete',
    ];

    private const ROLE_PERMISSIONS = [
        'Super Admin' => ['view', 'edit', 'delete'],
        'Admin' => ['view', 'edit', 'delete'],
        'Kepala Sekolah' => ['view', 'edit'],
        'Guru' => ['view', 'edit'],
        'Siswa SMP' => ['view'],
        'Siswa SD' => ['view'],
        'Siswa SMA' => ['view'],
    ];

    /**
     * Seed the permissions table and assign permissions to roles.
     */
    public function run(): void
    {
        $this->createPermissions();
        $this->assignPermissionsToRoles();
    }

    /**
     * Create permissions if they don't exist.
     */
    private function createPermissions(): void
    {
        foreach (self::PERMISSIONS as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }
    }

    /**
     * Assign permissions to each role based on predefined rules.
     */
    private function assignPermissionsToRoles(): void
    {
        foreach (self::ROLE_PERMISSIONS as $roleName => $permissions) {
            $role = Role::findByName($roleName);
            $role->givePermissionTo(
                $roleName === 'Super Admin' ? Permission::all() : $permissions
            );
        }
    }
}