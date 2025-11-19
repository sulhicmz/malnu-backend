<?php

declare(strict_types=1);

namespace Tests\Unit\Models;

use App\Models\SchoolManagement\Student;
use App\Models\User;
use Database\Factories\SchoolManagement\StudentFactory;
use Database\Factories\UserFactory;
use Tests\TestCase;
use Hypervel\Foundation\Testing\RefreshDatabase;

class StudentTest extends TestCase
{
    use RefreshDatabase;

    public function test_student_factory_creates_student(): void
    {
        $student = StudentFactory::new()->create();

        $this->assertNotNull($student->id);
        $this->assertNotNull($student->nisn);
        $this->assertNotNull($student->birth_date);
        $this->assertEquals('active', $student->status);
    }

    public function test_student_has_correct_primary_key(): void
    {
        $student = new Student();

        $this->assertEquals('id', $student->getKeyName());
        $this->assertEquals('string', $student->getKeyType());
        $this->assertFalse($student->incrementing);
    }

    public function test_student_belongs_to_user(): void
    {
        $user = UserFactory::new()->create();
        $student = StudentFactory::new()->create(['user_id' => $user->id]);

        $this->assertInstanceOf(User::class, $student->user);
        $this->assertEquals($user->id, $student->user->id);
    }

    public function test_student_fillable_attributes(): void
    {
        $student = new Student();

        $fillable = $student->getFillable();

        $this->assertContains('user_id', $fillable);
        $this->assertContains('nisn', $fillable);
        $this->assertContains('class_id', $fillable);
        $this->assertContains('birth_date', $fillable);
        $this->assertContains('birth_place', $fillable);
        $this->assertContains('address', $fillable);
        $this->assertContains('parent_id', $fillable);
        $this->assertContains('enrollment_date', $fillable);
        $this->assertContains('status', $fillable);
    }

    public function test_student_casts_attributes(): void
    {
        $student = StudentFactory::new()->create();

        $casts = $student->getCasts();
        
        $this->assertArrayHasKey('birth_date', $casts);
        $this->assertArrayHasKey('enrollment_date', $casts);
        $this->assertArrayHasKey('created_at', $casts);
        $this->assertArrayHasKey('updated_at', $casts);
    }
}