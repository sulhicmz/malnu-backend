<?php

declare(strict_types=1);

namespace Tests\Unit\Models;

use Tests\TestCase;
use App\Models\User;
use App\Models\ParentPortal\ParentOrtu;
use App\Models\SchoolManagement\Student;
use Hypervel\Support\Facades\Hash;

class ParentOrtuTest extends TestCase
{
    /**
     * Test ParentOrtu model can be created with required fields.
     */
    public function testParentOrtuCanBeCreated(): void
    {
        $parent = ParentOrtu::create([
            'user_id' => null,
            'full_name' => 'John Parent',
            'phone' => '1234567890',
            'email' => 'john@example.com',
        ]);

        $this->assertInstanceOf(ParentOrtu::class, $parent);
        $this->assertEquals('John Parent', $parent->full_name);
        $this->assertEquals('1234567890', $parent->phone);
    }

    /**
     * Test ParentOrtu has correct primary key configuration.
     */
    public function testParentOrtuPrimaryKeyConfiguration(): void
    {
        $parent = new ParentOrtu();
        
        $this->assertEquals('id', $parent->getKeyName());
        $this->assertEquals('string', $parent->getKeyType());
        $this->assertFalse($parent->incrementing);
    }

    /**
     * Test ParentOrtu belongs to user relationship.
     */
    public function testParentOrtuBelongsToUser(): void
    {
        $user = User::create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => Hash::make('password'),
            'username' => 'testuser',
        ]);

        $parent = ParentOrtu::create([
            'user_id' => $user->id,
            'full_name' => 'John Parent',
            'phone' => '1234567890',
            'email' => 'john@example.com',
        ]);

        $this->assertInstanceOf(User::class, $parent->user);
        $this->assertEquals($user->id, $parent->user->id);
    }

    /**
     * Test ParentOrtu has many students relationship.
     */
    public function testParentOrtuHasManyStudents(): void
    {
        $parent = ParentOrtu::create([
            'user_id' => null,
            'full_name' => 'John Parent',
            'phone' => '1234567890',
            'email' => 'john@example.com',
        ]);

        $student = Student::create([
            'user_id' => null,
            'parent_id' => $parent->id,
            'full_name' => 'Jane Student',
            'nis' => '12345',
            'class_id' => null,
        ]);

        $this->assertTrue($parent->students->contains($student));
        $this->assertEquals($parent->id, $student->parent_id);
    }

    /**
     * Test ParentOrtu model fillable attributes.
     */
    public function testParentOrtuFillableAttributes(): void
    {
        $parent = new ParentOrtu();
        $fillable = [
            'user_id',
            'full_name',
            'phone',
            'email',
            'address',
            'occupation',
            'emergency_contact',
        ];
        
        $this->assertEquals($fillable, $parent->getFillable());
    }
}