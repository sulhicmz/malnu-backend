<?php

declare(strict_types=1);

namespace Tests\Unit\Models;

use App\Models\User;
use App\Models\SchoolManagement\Teacher;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TeacherTest extends TestCase
{
    use RefreshDatabase;

    public function test_teacher_belongs_to_user(): void
    {
        $user = User::factory()->create();
        $teacher = Teacher::factory()->create(['user_id' => $user->id]);

        $this->assertInstanceOf(User::class, $teacher->user);
        $this->assertEquals($user->id, $teacher->user->id);
    }

    public function test_teacher_has_many_classes(): void
    {
        $teacher = Teacher::factory()->create();
        
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\HasMany::class, $teacher->classes);
    }

    public function test_teacher_has_many_subjects(): void
    {
        $teacher = Teacher::factory()->create();
        
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\BelongsToMany::class, $teacher->subjects);
    }

    public function test_teacher_has_many_schedules(): void
    {
        $teacher = Teacher::factory()->create();
        
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\HasMany::class, $teacher->schedules);
    }
}