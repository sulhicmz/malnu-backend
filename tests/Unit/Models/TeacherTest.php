<?php

declare(strict_types=1);

namespace Tests\Unit\Models;

use App\Models\SchoolManagement\Teacher;
use App\Models\User;
use Database\Factories\SchoolManagement\TeacherFactory;
use Database\Factories\UserFactory;
use Tests\TestCase;
use Hypervel\Foundation\Testing\RefreshDatabase;

class TeacherTest extends TestCase
{
    use RefreshDatabase;

    public function test_teacher_factory_creates_teacher(): void
    {
        $teacher = TeacherFactory::new()->create();

        $this->assertNotNull($teacher->id);
        $this->assertNotNull($teacher->nip);
        $this->assertNotNull($teacher->expertise);
        $this->assertEquals('active', $teacher->status);
    }

    public function test_teacher_has_correct_primary_key(): void
    {
        $teacher = new Teacher();

        $this->assertEquals('id', $teacher->getKeyName());
        $this->assertEquals('string', $teacher->getKeyType());
        $this->assertFalse($teacher->incrementing);
    }

    public function test_teacher_belongs_to_user(): void
    {
        $user = UserFactory::new()->create();
        $teacher = TeacherFactory::new()->create(['user_id' => $user->id]);

        $this->assertInstanceOf(User::class, $teacher->user);
        $this->assertEquals($user->id, $teacher->user->id);
    }

    public function test_teacher_fillable_attributes(): void
    {
        $teacher = new Teacher();

        $fillable = $teacher->getFillable();

        $this->assertContains('user_id', $fillable);
        $this->assertContains('nip', $fillable);
        $this->assertContains('expertise', $fillable);
        $this->assertContains('join_date', $fillable);
        $this->assertContains('status', $fillable);
    }

    public function test_teacher_casts_attributes(): void
    {
        $teacher = TeacherFactory::new()->create();

        $casts = $teacher->getCasts();
        
        $this->assertArrayHasKey('join_date', $casts);
        $this->assertArrayHasKey('created_at', $casts);
        $this->assertArrayHasKey('updated_at', $casts);
    }
}