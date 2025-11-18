<?php

declare(strict_types=1);

namespace Tests\Unit\Models;

use App\Models\User;
use App\Models\Role;
use App\Models\SchoolManagement\Teacher;
use App\Models\SchoolManagement\Student;
use App\Models\SchoolManagement\Staff;
use App\Models\ParentPortal\ParentOrtu;
use Hypervel\Foundation\Testing\TestCase;
use Hypervel\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @internal
 * @coversNothing
 */
class UserModelTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    /**
     * Test user model can be created.
     */
    public function testUserCanBeCreated(): void
    {
        $user = User::factory()->create();

        $this->assertInstanceOf(User::class, $user);
        $this->assertNotNull($user->id);
        $this->assertDatabaseHas('users', ['id' => $user->id]);
    }

    /**
     * Test user factory creates valid users.
     */
    public function testUserFactoryCreatesValidUsers(): void
    {
        $user = User::factory()->make();

        $this->assertNotNull($user->name);
        $this->assertNotNull($user->email);
        $this->assertNotNull($user->password);
    }

    /**
     * Test user password is automatically hashed when set.
     */
    public function testUserPasswordIsAutomaticallyHashed(): void
    {
        $plainPassword = 'testpassword123';
        $user = User::factory()->make([
            'password' => $plainPassword
        ]);

        $this->assertTrue(Hash::check($plainPassword, $user->password));
        $this->assertNotEquals($plainPassword, $user->password);
    }

    /**
     * Test user can be assigned a role.
     */
    public function testUserCanAssignRole(): void
    {
        $user = User::factory()->create();
        $role = Role::factory()->create(['name' => 'teacher']);

        $user->assignRole('teacher');

        $this->assertTrue($user->roles()->where('name', 'teacher')->exists());
    }

    /**
     * Test user can sync roles.
     */
    public function testUserCanSyncRoles(): void
    {
        $user = User::factory()->create();
        $role1 = Role::factory()->create(['name' => 'teacher']);
        $role2 = Role::factory()->create(['name' => 'admin']);

        $user->syncRoles(['teacher']);
        $this->assertTrue($user->roles()->where('name', 'teacher')->exists());
        $this->assertFalse($user->roles()->where('name', 'admin')->exists());

        $user->syncRoles(['admin']);
        $this->assertFalse($user->roles()->where('name', 'teacher')->exists());
        $this->assertTrue($user->roles()->where('name', 'admin')->exists());
    }

    /**
     * Test user has correct primary key configuration.
     */
    public function testUserHasCorrectPrimaryKeyConfiguration(): void
    {
        $user = new User();

        $this->assertEquals('id', $user->getKeyName());
        $this->assertEquals('string', $user->getKeyType());
        $this->assertFalse($user->getIncrementing());
    }

    /**
     * Test user has timestamps.
     */
    public function testUserHasTimestamps(): void
    {
        $user = User::factory()->create();

        $this->assertNotNull($user->created_at);
        $this->assertNotNull($user->updated_at);
        $this->assertInstanceOf(\DateTime::class, $user->created_at);
        $this->assertInstanceOf(\DateTime::class, $user->updated_at);
    }

    /**
     * Test user UUID is properly generated.
     */
    public function testUserUuidIsProperlyGenerated(): void
    {
        $user = User::factory()->create();

        $this->assertIsString($user->id);
        $this->assertTrue(Str::isUuid($user->id));
    }

    /**
     * Test user can have teacher relationship.
     */
    public function testUserCanHaveTeacher(): void
    {
        $user = User::factory()->create();
        $teacher = Teacher::factory()->create(['user_id' => $user->id]);

        $this->assertInstanceOf(Teacher::class, $user->teacher);
        $this->assertEquals($teacher->id, $user->teacher->id);
    }

    /**
     * Test user can have student relationship.
     */
    public function testUserCanHaveStudent(): void
    {
        $user = User::factory()->create();
        $student = Student::factory()->create(['user_id' => $user->id]);

        $this->assertInstanceOf(Student::class, $user->student);
        $this->assertEquals($student->id, $user->student->id);
    }

    /**
     * Test user can have staff relationship.
     */
    public function testUserCanHaveStaff(): void
    {
        $user = User::factory()->create();
        $staff = Staff::factory()->create(['user_id' => $user->id]);

        $this->assertInstanceOf(Staff::class, $user->staff);
        $this->assertEquals($staff->id, $user->staff->id);
    }

    /**
     * Test user can have parent relationship.
     */
    public function testUserCanHaveParent(): void
    {
        $user = User::factory()->create();
        $parent = ParentOrtu::factory()->create(['user_id' => $user->id]);

        $this->assertInstanceOf(ParentOrtu::class, $user->parent);
        $this->assertEquals($parent->id, $user->parent->id);
    }

    /**
     * Test user email is unique.
     */
    public function testUserEmailIsUnique(): void
    {
        $email = $this->faker->unique()->safeEmail();
        $user1 = User::factory()->create(['email' => $email]);

        $this->expectException(\Exception::class);
        User::factory()->create(['email' => $email]);
    }
}