<?php

declare(strict_types=1);

namespace Tests\Unit\Models;

use App\Models\User;
use App\Models\Role;
use App\Models\Permission;
use App\Models\ParentPortal\ParentOrtu;
use App\Models\SchoolManagement\Teacher;
use App\Models\SchoolManagement\Student;
use App\Models\SchoolManagement\Staff;
use Tests\TestCase;

class UserTest extends TestCase
{
    public function test_user_has_correct_primary_key_configuration(): void
    {
        $user = new User();
        
        $this->assertEquals('id', $user->getKeyName());
        $this->assertEquals('string', $user->getKeyType());
        $this->assertFalse($user->incrementing);
    }

    public function test_user_fillable_attributes(): void
    {
        $user = new User();
        $fillable = $user->getFillable();
        
        $this->assertContains('name', $fillable);
        $this->assertContains('email', $fillable);
        $this->assertContains('password', $fillable);
        $this->assertContains('full_name', $fillable);
        $this->assertContains('phone', $fillable);
        $this->assertContains('is_active', $fillable);
    }

    public function test_user_can_be_created(): void
    {
        $user = User::factory()->create();
        
        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'email' => $user->email,
        ]);
    }

    public function test_user_can_assign_role(): void
    {
        $user = User::factory()->create();
        $role = Role::factory()->create(['name' => 'admin']);
        
        $user->assignRole('admin');
        
        $this->assertTrue($user->roles()->where('name', 'admin')->exists());
    }

    public function test_user_can_sync_roles(): void
    {
        $user = User::factory()->create();
        $role1 = Role::factory()->create(['name' => 'admin']);
        $role2 = Role::factory()->create(['name' => 'teacher']);
        
        $user->syncRoles(['admin', 'teacher']);
        
        $this->assertTrue($user->roles()->where('name', 'admin')->exists());
        $this->assertTrue($user->roles()->where('name', 'teacher')->exists());
        $this->assertEquals(2, $user->roles()->count());
    }

    public function test_user_parent_relationship(): void
    {
        $user = User::factory()->create();
        $parent = ParentOrtu::factory()->create(['user_id' => $user->id]);
        
        $this->assertInstanceOf(ParentOrtu::class, $user->parent);
        $this->assertEquals($parent->id, $user->parent->id);
    }

    public function test_user_teacher_relationship(): void
    {
        $user = User::factory()->create();
        $teacher = Teacher::factory()->create(['user_id' => $user->id]);
        
        $this->assertInstanceOf(Teacher::class, $user->teacher);
        $this->assertEquals($teacher->id, $user->teacher->id);
    }

    public function test_user_student_relationship(): void
    {
        $user = User::factory()->create();
        $student = Student::factory()->create(['user_id' => $user->id]);
        
        $this->assertInstanceOf(Student::class, $user->student);
        $this->assertEquals($student->id, $user->student->id);
    }

    public function test_user_staff_relationship(): void
    {
        $user = User::factory()->create();
        $staff = Staff::factory()->create(['user_id' => $user->id]);
        
        $this->assertInstanceOf(Staff::class, $user->staff);
        $this->assertEquals($staff->id, $user->staff->id);
    }

    public function test_user_attributes_are_casted_correctly(): void
    {
        $user = User::factory()->create();
        
        $this->assertIsString($user->id);
        $this->assertIsString($user->name);
        $this->assertIsString($user->email);
        $this->assertIsBool($user->is_active);
    }
}