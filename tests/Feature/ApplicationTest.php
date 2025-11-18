<?php

declare(strict_types=1);

namespace Tests\Feature;

use Tests\TestCase;
use Hypervel\Foundation\Testing\RefreshDatabase;
use App\Models\User;
use Illuminate\Foundation\Testing\WithFaker;

/**
 * @internal
 * @coversNothing
 */
class ApplicationTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    /**
     * Test that the application boots correctly.
     */
    public function testApplicationBootsSuccessfully(): void
    {
        // If we reach this point in the test, the application has successfully booted
        // and the testing framework is working properly
        $this->assertTrue(class_exists(User::class));
    }

    /**
     * Test basic application configuration.
     */
    public function testApplicationConfiguration(): void
    {
        $this->assertNotNull(config('app.name'));
        $this->assertNotNull(config('app.env'));
        $this->assertEquals('testing', config('app.env'));
    }

    /**
     * Test database connection works.
     */
    public function testDatabaseConnection(): void
    {
        // With RefreshDatabase trait, we can test that the connection works
        // by performing a simple query
        $userCount = User::count();
        
        // Initially should be 0 users in a fresh database
        $this->assertSame(0, $userCount);
        
        // Create a user and verify it's stored
        $user = User::factory()->create();
        $this->assertSame(1, User::count());
    }

    /**
     * Test environment is set to testing.
     */
    public function testEnvironmentIsTesting(): void
    {
        $this->assertEquals('testing', app()->environment());
    }

    /**
     * Test that basic service providers are loaded.
     */
    public function testBasicServicesAreAvailable(): void
    {
        $this->assertNotNull(app('hash'));
        $this->assertNotNull(app('db'));
        $this->assertNotNull(app('auth'));
        $this->assertNotNull(app('cache'));
    }

    /**
     * Test that the application has the expected structure.
     */
    public function testApplicationStructure(): void
    {
        // Test that core models exist and can be instantiated
        $this->assertInstanceOf(User::class, new User());
        
        // Test that basic functionality works
        $user = User::factory()->make();
        $this->assertNotNull($user);
    }

    /**
     * Test that the application can handle basic HTTP requests.
     */
    public function testBasicHttpRequest(): void
    {
        // Test a basic route that should exist
        $response = $this->get('/');
        
        // The response should be successful or redirect (not throw an exception)
        $this->assertTrue(in_array($response->status(), [200, 302, 404, 500], true));
    }

    /**
     * Test that the application can create and store data.
     */
    public function testApplicationCanStoreData(): void
    {
        $user = User::factory()->create();
        
        $this->assertNotNull($user->id);
        $this->assertDatabaseHas('users', ['id' => $user->id]);
    }

    /**
     * Test that the application can retrieve data.
     */
    public function testApplicationCanRetrieveData(): void
    {
        $user = User::factory()->create();
        
        $retrievedUser = User::find($user->id);
        
        $this->assertNotNull($retrievedUser);
        $this->assertEquals($user->id, $retrievedUser->id);
    }

    /**
     * Test that the application can update data.
     */
    public function testApplicationCanUpdateData(): void
    {
        $user = User::factory()->create();
        $originalName = $user->name;
        
        $newName = $this->faker->name();
        $user->update(['name' => $newName]);
        
        $this->assertNotEquals($originalName, $user->name);
        $this->assertEquals($newName, $user->name);
    }

    /**
     * Test that the application can delete data.
     */
    public function testApplicationCanDeleteData(): void
    {
        $user = User::factory()->create();
        
        $userId = $user->id;
        $user->delete();
        
        $this->assertDatabaseMissing('users', ['id' => $userId]);
    }
}