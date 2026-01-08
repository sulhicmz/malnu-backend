<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Services\RolePermissionService;
use Tests\TestCase;

/**
 * @internal
 * @coversNothing
 */
class RolePermissionServiceTest extends TestCase
{
    private RolePermissionService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new RolePermissionService();
    }

    public function testGetAllRolesReturnsExpectedRoles()
    {
        $roles = $this->service->getAllRoles();

        $this->assertIsArray($roles);
        $this->assertCount(4, $roles);
        $this->assertEquals('admin', $roles[0]['name']);
        $this->assertEquals('teacher', $roles[1]['name']);
        $this->assertEquals('student', $roles[2]['name']);
        $this->assertEquals('parent', $roles[3]['name']);
    }

    public function testGetRoleByNameReturnsCorrectRole()
    {
        $role = $this->service->getRoleByName('admin');

        $this->assertIsArray($role);
        $this->assertEquals('admin', $role['name']);
        $this->assertStringContainsString('Administrator', $role['description']);
    }

    public function testGetRoleByNameReturnsNullForNonexistentRole()
    {
        $role = $this->service->getRoleByName('nonexistent_role');

        $this->assertNull($role);
    }

    public function testGetRoleByNameCaseSensitive()
    {
        $role = $this->service->getRoleByName('Admin');

        $this->assertNull($role);
    }

    public function testGetAllPermissionsReturnsExpectedPermissions()
    {
        $permissions = $this->service->getAllPermissions();

        $this->assertIsArray($permissions);
        $this->assertGreaterThanOrEqual(8, count($permissions));
        $this->assertArrayHasKey('id', $permissions[0]);
        $this->assertArrayHasKey('name', $permissions[0]);
        $this->assertArrayHasKey('description', $permissions[0]);
    }

    public function testGetPermissionsForAdminRoleReturnsAllPermissions()
    {
        $permissions = $this->service->getPermissionsForRole('admin');

        $this->assertIsArray($permissions);
        $this->assertContains('view_users', $permissions);
        $this->assertContains('create_users', $permissions);
        $this->assertContains('edit_users', $permissions);
        $this->assertContains('delete_users', $permissions);
        $this->assertContains('view_courses', $permissions);
        $this->assertContains('create_courses', $permissions);
        $this->assertContains('edit_courses', $permissions);
        $this->assertContains('delete_courses', $permissions);
    }

    public function testGetPermissionsForTeacherRoleReturnsCorrectPermissions()
    {
        $permissions = $this->service->getPermissionsForRole('teacher');

        $this->assertIsArray($permissions);
        $this->assertContains('view_users', $permissions);
        $this->assertContains('view_courses', $permissions);
        $this->assertContains('create_courses', $permissions);
        $this->assertContains('edit_courses', $permissions);
        $this->assertNotContains('delete_users', $permissions);
        $this->assertNotContains('delete_courses', $permissions);
    }

    public function testGetPermissionsForStudentRoleReturnsLimitedPermissions()
    {
        $permissions = $this->service->getPermissionsForRole('student');

        $this->assertIsArray($permissions);
        $this->assertContains('view_courses', $permissions);
        $this->assertNotContains('create_users', $permissions);
        $this->assertNotContains('delete_users', $permissions);
    }

    public function testGetPermissionsForParentRoleReturnsLimitedPermissions()
    {
        $permissions = $this->service->getPermissionsForRole('parent');

        $this->assertIsArray($permissions);
        $this->assertContains('view_users', $permissions);
        $this->assertNotContains('create_courses', $permissions);
    }

    public function testGetPermissionsForNonexistentRoleReturnsEmptyArray()
    {
        $permissions = $this->service->getPermissionsForRole('nonexistent_role');

        $this->assertIsArray($permissions);
        $this->assertEmpty($permissions);
    }

    public function testRoleHasPermissionReturnsTrueForValidPermission()
    {
        $hasPermission = $this->service->roleHasPermission('admin', 'delete_users');

        $this->assertTrue($hasPermission);
    }

    public function testRoleHasPermissionReturnsFalseForInvalidPermission()
    {
        $hasPermission = $this->service->roleHasPermission('student', 'delete_users');

        $this->assertFalse($hasPermission);
    }

    public function testRoleHasPermissionReturnsFalseForNonexistentRole()
    {
        $hasPermission = $this->service->roleHasPermission('nonexistent_role', 'view_users');

        $this->assertFalse($hasPermission);
    }

    public function testAssignRoleToUserWithValidRole()
    {
        $result = $this->service->assignRoleToUser('user_123', 'admin');

        $this->assertTrue($result);
    }

    public function testAssignRoleToUserWithInvalidRole()
    {
        $result = $this->service->assignRoleToUser('user_123', 'invalid_role');

        $this->assertFalse($result);
    }

    public function testRemoveRoleFromUser()
    {
        $result = $this->service->removeRoleFromUser('user_123', 'admin');

        $this->assertTrue($result);
    }

    public function testGetRolesForUserReturnsDefaultStudentRole()
    {
        $roles = $this->service->getRolesForUser('user_123');

        $this->assertIsArray($roles);
        $this->assertContains('student', $roles);
    }

    public function testGetRolesForUserReturnsArray()
    {
        $roles = $this->service->getRolesForUser('any_user_id');

        $this->assertIsArray($roles);
    }

    public function testTeacherCanCreateCourses()
    {
        $hasPermission = $this->service->roleHasPermission('teacher', 'create_courses');

        $this->assertTrue($hasPermission);
    }

    public function testTeacherCannotDeleteUsers()
    {
        $hasPermission = $this->service->roleHasPermission('teacher', 'delete_users');

        $this->assertFalse($hasPermission);
    }

    public function testStudentCannotCreateUsers()
    {
        $hasPermission = $this->service->roleHasPermission('student', 'create_users');

        $this->assertFalse($hasPermission);
    }

    public function testAdminCanDeleteCourses()
    {
        $hasPermission = $this->service->roleHasPermission('admin', 'delete_courses');

        $this->assertTrue($hasPermission);
    }
}
