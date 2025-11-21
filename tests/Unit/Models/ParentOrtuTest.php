<?php

declare(strict_types=1);

namespace Tests\Unit\Models;

use App\Models\User;
use App\Models\ParentPortal\ParentOrtu;
use App\Models\SchoolManagement\Student;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ParentOrtuTest extends TestCase
{
    use RefreshDatabase;

    public function test_parent_belongs_to_user(): void
    {
        $user = User::factory()->create();
        $parent = ParentOrtu::factory()->create(['user_id' => $user->id]);

        $this->assertInstanceOf(User::class, $parent->user);
        $this->assertEquals($user->id, $parent->user->id);
    }

    public function test_parent_has_many_students(): void
    {
        $parent = ParentOrtu::factory()->create();
        $student = Student::factory()->create(['parent_id' => $parent->id]);

        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\HasMany::class, $parent->students);
        $this->assertCount(1, $parent->students);
        $this->assertEquals($student->id, $parent->students->first()->id);
    }
}