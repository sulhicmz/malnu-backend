<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Models\User;
use Hypervel\Foundation\Testing\TestCase;

class UserFactoryTest extends TestCase
{
    public function testUserFactoryCreatesValidUser()
    {
        $user = User::factory()->make();
        
        $this->assertInstanceOf(User::class, $user);
        $this->assertNotNull($user->name);
        $this->assertNotNull($user->email);
        $this->assertIsString($user->id);
        $this->assertNotEmpty($user->id);
        $this->assertStringContainsString('@', $user->email);
    }

    public function testUserFactoryCreatesMultipleUsers()
    {
        $users = User::factory(3)->make();
        
        $this->assertCount(3, $users);
        
        foreach ($users as $user) {
            $this->assertInstanceOf(User::class, $user);
            $this->assertNotNull($user->name);
            $this->assertNotNull($user->email);
        }
    }

    public function testUserFactoryCreatesUserWithCustomAttributes()
    {
        $user = User::factory()->create([
            'name' => 'Custom Name',
            'email' => 'custom@example.com',
        ]);
        
        $this->assertEquals('Custom Name', $user->name);
        $this->assertEquals('custom@example.com', $user->email);
    }

    public function testUserFactoryCreatesUserWithUniqueEmails()
    {
        $user1 = User::factory()->make();
        $user2 = User::factory()->make();
        
        $this->assertNotEquals($user1->email, $user2->email);
    }

    public function testUserFactoryCreatesUserWithUniqueNames()
    {
        $user1 = User::factory()->make();
        $user2 = User::factory()->make();
        
        $this->assertNotEquals($user1->name, $user2->name);
    }
}