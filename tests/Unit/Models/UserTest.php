<?php

namespace Tests\Unit\Models;

use Tests\TestCase;
use App\Models\User;
use App\Models\Role;
use App\Models\ParentPortal\ParentOrtu;
use App\Models\SchoolManagement\Teacher;
use App\Models\SchoolManagement\Student;
use App\Models\SchoolManagement\Staff;
use Hypervel\Foundation\Testing\RefreshDatabase;

class UserTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_model_fillable_attributes(): void
    {
        $user = new User();
        
        $fillable = [
            'name',
            'username',
            'email',
            'password',
            'full_name',
            'phone',
            'avatar_url',
            'is_active',
            'last_login_time',
            'last_login_ip',
            'remember_token',
            'email_verified_at',
            'slug',
            'key_status',
        ];
        
        $this->assertEquals($fillable, $user->getFillable());
    }

    public function test_user_model_primary_key(): void
    {
        $user = new User();
        
        $this->assertEquals('id', $user->getKeyName());
        $this->assertEquals('string', $user->getKeyType());
        $this->assertFalse($user->incrementing);
    }

    public function test_user_has_parent_relationship(): void
    {
        $user = User::factory()->create();
        $parent = ParentOrtu::factory()->create(['user_id' => $user->id]);
        
        $this->assertInstanceOf(ParentOrtu::class, $user->parent);
    }

    public function test_user_has_teacher_relationship(): void
    {
        $user = User::factory()->create();
        $teacher = Teacher::factory()->create(['user_id' => $user->id]);
        
        $this->assertInstanceOf(Teacher::class, $user->teacher);
    }

    public function test_user_has_student_relationship(): void
    {
        $user = User::factory()->create();
        $student = Student::factory()->create(['user_id' => $user->id]);
        
        $this->assertInstanceOf(Student::class, $user->student);
    }

    public function test_user_has_staff_relationship(): void
    {
        $user = User::factory()->create();
        $staff = Staff::factory()->create(['user_id' => $user->id]);
        
        $this->assertInstanceOf(Staff::class, $user->staff);
    }

    public function test_user_can_be_assigned_role(): void
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
        
        $user->syncRoles(['admin']);
        $this->assertTrue($user->roles()->where('name', 'admin')->exists());
        $this->assertFalse($user->roles()->where('name', 'teacher')->exists());
        
        $user->syncRoles(['teacher']);
        $this->assertFalse($user->roles()->where('name', 'admin')->exists());
        $this->assertTrue($user->roles()->where('name', 'teacher')->exists());
    }
}