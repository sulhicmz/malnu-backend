<?php

declare(strict_types=1);

namespace Tests\Unit\Models;

use Tests\TestCase;
use App\Models\User;
use App\Models\SchoolManagement\Staff;
use Hypervel\Support\Facades\Hash;

class StaffTest extends TestCase
{
    /**
     * Test Staff model can be created with required fields.
     */
    public function testStaffCanBeCreated(): void
    {
        $staff = Staff::create([
            'user_id' => null,
            'full_name' => 'John Staff',
            'nip' => '54321',
            'position' => 'Administrator',
        ]);

        $this->assertInstanceOf(Staff::class, $staff);
        $this->assertEquals('John Staff', $staff->full_name);
        $this->assertEquals('54321', $staff->nip);
        $this->assertEquals('Administrator', $staff->position);
    }

    /**
     * Test Staff has correct primary key configuration.
     */
    public function testStaffPrimaryKeyConfiguration(): void
    {
        $staff = new Staff();
        
        $this->assertEquals('id', $staff->getKeyName());
        $this->assertEquals('string', $staff->getKeyType());
        $this->assertFalse($staff->incrementing);
    }

    /**
     * Test Staff belongs to user relationship.
     */
    public function testStaffBelongsToUser(): void
    {
        $user = User::create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => Hash::make('password'),
            'username' => 'testuser',
        ]);

        $staff = Staff::create([
            'user_id' => $user->id,
            'full_name' => 'John Staff',
            'nip' => '54321',
            'position' => 'Administrator',
        ]);

        $this->assertInstanceOf(User::class, $staff->user);
        $this->assertEquals($user->id, $staff->user->id);
    }

    /**
     * Test Staff model fillable attributes.
     */
    public function testStaffFillableAttributes(): void
    {
        $staff = new Staff();
        $fillable = [
            'user_id',
            'full_name',
            'nip',
            'position',
            'date_of_birth',
            'place_of_birth',
            'gender',
            'religion',
            'address',
            'phone',
            'photo_url',
            'employment_status',
        ];
        
        $this->assertEquals($fillable, $staff->getFillable());
    }
}