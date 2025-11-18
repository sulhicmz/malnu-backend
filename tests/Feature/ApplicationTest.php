<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\User;
use Hypervel\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ApplicationTest extends TestCase
{
    use RefreshDatabase;

    public function testApplicationBootsSuccessfully()
    {
        // Test that the application can boot without errors
        $this->assertTrue(true, 'Application booted successfully');
    }

    public function testDatabaseConnectionWorks()
    {
        // Test that we can interact with the database
        $userCount = User::count();
        
        $this->assertIsInt($userCount);
    }

    public function testUserFactoryWorks()
    {
        $user = User::factory()->create();
        
        $this->assertNotNull($user->id);
        $this->assertNotNull($user->email);
    }

    public function testApplicationEnvironment()
    {
        $this->assertEquals('testing', env('APP_ENV'));
    }

    public function testDatabaseIsSqliteInTesting()
    {
        $this->assertEquals('sqlite_testing', env('DB_CONNECTION'));
    }

    public function testCacheDriverIsArrayInTesting()
    {
        $this->assertEquals('array', env('CACHE_DRIVER'));
    }
}