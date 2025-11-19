<?php

namespace Tests\Unit\Models;

use Tests\TestCase;
use App\Models\SchoolManagement\Teacher;
use App\Models\User;
use App\Models\SchoolManagement\ClassModel;
use Hypervel\Foundation\Testing\RefreshDatabase;

class TeacherTest extends TestCase
{
    use RefreshDatabase;

    public function test_teacher_model_fillable_attributes(): void
    {
        $teacher = new Teacher();
        
        $fillable = [
            'user_id',
            'nip',
            'expertise',
            'join_date',
            'status',
        ];
        
        $this->assertEquals($fillable, $teacher->getFillable());
    }

    public function test_teacher_model_primary_key(): void
    {
        $teacher = new Teacher();
        
        $this->assertEquals('id', $teacher->getKeyName());
        $this->assertEquals('string', $teacher->getKeyType());
        $this->assertFalse($teacher->incrementing);
    }

    public function test_teacher_has_user_relationship(): void
    {
        $user = User::factory()->create();
        $teacher = Teacher::factory()->create(['user_id' => $user->id]);
        
        $this->assertInstanceOf(User::class, $teacher->user);
        $this->assertEquals($user->id, $teacher->user->id);
    }

    public function test_teacher_casts_attributes(): void
    {
        $teacher = new Teacher();
        
        $casts = [
            'join_date' => 'date',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
        
        $this->assertEquals($casts, $teacher->getCasts());
    }
}