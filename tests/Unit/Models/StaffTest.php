<?php

declare(strict_types=1);

namespace Tests\Unit\Models;

use App\Models\SchoolManagement\Staff;
use App\Models\User;
use Database\Factories\SchoolManagement\StaffFactory;
use Database\Factories\UserFactory;
use Tests\TestCase;
use Hypervel\Foundation\Testing\RefreshDatabase;

class StaffTest extends TestCase
{
    use RefreshDatabase;

    public function test_staff_factory_creates_staff(): void
    {
        $staff = StaffFactory::new()->create();

        $this->assertNotNull($staff->id);
        $this->assertNotNull($staff->position);
        $this->assertNotNull($staff->department);
        $this->assertEquals('active', $staff->status);
    }

    public function test_staff_has_correct_primary_key(): void
    {
        $staff = new Staff();

        $this->assertEquals('id', $staff->getKeyName());
        $this->assertEquals('string', $staff->getKeyType());
        $this->assertFalse($staff->incrementing);
    }

    public function test_staff_belongs_to_user(): void
    {
        $user = UserFactory::new()->create();
        $staff = StaffFactory::new()->create(['user_id' => $user->id]);

        $this->assertInstanceOf(User::class, $staff->user);
        $this->assertEquals($user->id, $staff->user->id);
    }

    public function test_staff_fillable_attributes(): void
    {
        $staff = new Staff();

        $fillable = $staff->getFillable();

        $this->assertContains('user_id', $fillable);
        $this->assertContains('position', $fillable);
        $this->assertContains('department', $fillable);
        $this->assertContains('join_date', $fillable);
        $this->assertContains('status', $fillable);
    }

    public function test_staff_casts_attributes(): void
    {
        $staff = StaffFactory::new()->create();

        $casts = $staff->getCasts();
        
        $this->assertArrayHasKey('join_date', $casts);
        $this->assertArrayHasKey('created_at', $casts);
        $this->assertArrayHasKey('updated_at', $casts);
    }
}