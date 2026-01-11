<?php

declare(strict_types=1);

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use App\Models\Model;
use App\Models\User;
use App\Models\SchoolManagement\Student;
use App\Models\SchoolManagement\Teacher;
use App\Models\Role;
use App\Models\Permission;

class UuidStandardizationTest extends TestCase
{
    public function test_base_model_has_uuid_configuration(): void
    {
        $model = new class extends Model {};

        $this->assertEquals('id', $model->getKeyName());
        $this->assertEquals('string', $model->getKeyType());
        $this->assertFalse($model->getIncrementing());
    }

    public function test_user_model_inherits_uuid_config(): void
    {
        $user = new User();

        $this->assertEquals('id', $user->getKeyName());
        $this->assertEquals('string', $user->getKeyType());
        $this->assertFalse($user->getIncrementing());
    }

    public function test_student_model_inherits_uuid_config(): void
    {
        $student = new Student();

        $this->assertEquals('id', $student->getKeyName());
        $this->assertEquals('string', $student->getKeyType());
        $this->assertFalse($student->getIncrementing());
    }

    public function test_teacher_model_inherits_uuid_config(): void
    {
        $teacher = new Teacher();

        $this->assertEquals('id', $teacher->getKeyName());
        $this->assertEquals('string', $teacher->getKeyType());
        $this->assertFalse($teacher->getIncrementing());
    }

    public function test_role_model_inherits_uuid_config(): void
    {
        $role = new Role();

        $this->assertEquals('id', $role->getKeyName());
        $this->assertEquals('string', $role->getKeyType());
        $this->assertFalse($role->getIncrementing());
    }

    public function test_permission_model_inherits_uuid_config(): void
    {
        $permission = new Permission();

        $this->assertEquals('id', $permission->getKeyName());
        $this->assertEquals('string', $permission->getKeyType());
        $this->assertFalse($permission->getIncrementing());
    }

    public function test_no_duplicate_uuid_properties_in_user_model(): void
    {
        $reflection = new \ReflectionClass(User::class);

        $this->assertNull($reflection->getProperty('primaryKey') ?? null);
        $this->assertNull($reflection->getProperty('keyType') ?? null);
        $this->assertNull($reflection->getProperty('incrementing') ?? null);
    }

    public function test_no_duplicate_uuid_properties_in_student_model(): void
    {
        $reflection = new \ReflectionClass(Student::class);

        $this->assertNull($reflection->getProperty('primaryKey') ?? null);
        $this->assertNull($reflection->getProperty('keyType') ?? null);
        $this->assertNull($reflection->getProperty('incrementing') ?? null);
    }
}
