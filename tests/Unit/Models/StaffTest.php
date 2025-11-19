<?php

namespace Tests\Unit\Models;

use Tests\TestCase;
use App\Models\SchoolManagement\Staff;
use App\Models\User;
use Hypervel\Foundation\Testing\RefreshDatabase;

class StaffTest extends TestCase
{
    use RefreshDatabase;

    public function test_staff_model_fillable_attributes(): void
    {
        $staff = new Staff();
        
        $fillable = [
            'user_id',
            'position',
            'department',
            'join_date',
            'status',
        ];
        
        $this->assertEquals($fillable, $staff->getFillable());
    }

    public function test_staff_model_primary_key(): void
    {
        $staff = new Staff();
        
        $this->assertEquals('id', $staff->getKeyName());
        $this->assertEquals('string', $staff->getKeyType());
        $this->assertFalse($staff->incrementing);
    }

    public function test_staff_has_user_relationship(): void
    {
        $user = User::factory()->create();
        $staff = Staff::factory()->create(['user_id' => $user->id]);
        
        $this->assertInstanceOf(User::class, $staff->user);
        $this->assertEquals($user->id, $staff->user->id);
    }

    public function test_staff_casts_attributes(): void
    {
        $staff = new Staff();
        
        $casts = [
            'join_date' => 'date',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
        
        $this->assertEquals($casts, $staff->getCasts());
    }
}