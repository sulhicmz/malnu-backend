<?php

declare(strict_types=1);

namespace Tests\Unit\Models;

use Tests\TestCase;
use App\Models\User;
use App\Models\SchoolManagement\Teacher;
use Hypervel\Support\Facades\Hash;

class TeacherTest extends TestCase
{
    /**
     * Test Teacher model can be created with required fields.
     */
    public function testTeacherCanBeCreated(): void
    {
        $teacher = Teacher::create([
            'user_id' => null,
            'full_name' => 'Mr. Smith',
            'nip' => '98765',
            'subject_id' => null,
        ]);

        $this->assertInstanceOf(Teacher::class, $teacher);
        $this->assertEquals('Mr. Smith', $teacher->full_name);
        $this->assertEquals('98765', $teacher->nip);
    }

    /**
     * Test Teacher has correct primary key configuration.
     */
    public function testTeacherPrimaryKeyConfiguration(): void
    {
        $teacher = new Teacher();
        
        $this->assertEquals('id', $teacher->getKeyName());
        $this->assertEquals('string', $teacher->getKeyType());
        $this->assertFalse($teacher->incrementing);
    }

    /**
     * Test Teacher belongs to user relationship.
     */
    public function testTeacherBelongsToUser(): void
    {
        $user = User::create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => Hash::make('password'),
            'username' => 'testuser',
        ]);

        $teacher = Teacher::create([
            'user_id' => $user->id,
            'full_name' => 'Mr. Smith',
            'nip' => '98765',
            'subject_id' => null,
        ]);

        $this->assertInstanceOf(User::class, $teacher->user);
        $this->assertEquals($user->id, $teacher->user->id);
    }

    /**
     * Test Teacher model fillable attributes.
     */
    public function testTeacherFillableAttributes(): void
    {
        $teacher = new Teacher();
        $fillable = [
            'user_id',
            'full_name',
            'nip',
            'subject_id',
            'nip_nuptk',
            'date_of_birth',
            'place_of_birth',
            'gender',
            'religion',
            'address',
            'phone',
            'photo_url',
            'employment_status',
            'education_level',
        ];
        
        $this->assertEquals($fillable, $teacher->getFillable());
    }
}