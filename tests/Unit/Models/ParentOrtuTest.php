<?php

namespace Tests\Unit\Models;

use Tests\TestCase;
use App\Models\ParentPortal\ParentOrtu;
use App\Models\User;
use App\Models\SchoolManagement\Student;
use Hypervel\Foundation\Testing\RefreshDatabase;

class ParentOrtuTest extends TestCase
{
    use RefreshDatabase;

    public function test_parent_ortu_model_fillable_attributes(): void
    {
        $parent = new ParentOrtu();
        
        $fillable = [
            'user_id',
            'occupation',
            'address',
        ];
        
        $this->assertEquals($fillable, $parent->getFillable());
    }

    public function test_parent_ortu_model_primary_key(): void
    {
        $parent = new ParentOrtu();
        
        $this->assertEquals('id', $parent->getKeyName());
        $this->assertEquals('string', $parent->getKeyType());
        $this->assertFalse($parent->incrementing);
    }

    public function test_parent_ortu_has_user_relationship(): void
    {
        $user = User::factory()->create();
        $parent = ParentOrtu::factory()->create(['user_id' => $user->id]);
        
        $this->assertInstanceOf(User::class, $parent->user);
        $this->assertEquals($user->id, $parent->user->id);
    }

    public function test_parent_ortu_has_students_relationship(): void
    {
        $parent = ParentOrtu::factory()->create();
        $student = Student::factory()->create(['parent_id' => $parent->id]);
        
        $this->assertTrue($parent->students->contains($student));
    }

    public function test_parent_ortu_casts_attributes(): void
    {
        $parent = new ParentOrtu();
        
        $casts = [
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
        
        $this->assertEquals($casts, $parent->getCasts());
    }
}