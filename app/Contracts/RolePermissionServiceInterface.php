<?php

declare(strict_types=1);

namespace App\Contracts;

interface RolePermissionServiceInterface
{
    public function getAllRoles(): array;

    public function getRoleByName(string $roleName): ?array;

    public function getAllPermissions(): array;

    public function getPermissionsForRole(string $roleName): array;

    public function roleHasPermission(string $roleName, string $permission): bool;

    public function assignRoleToUser(string $userId, string $roleName): bool;

    public function removeRoleFromUser(string $userId, string $roleName): bool;

    public function getRolesForUser(string $userId): array;
}
