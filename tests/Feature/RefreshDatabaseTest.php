<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\User;
use Database\Factories\UserFactory;
use Hypervel\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * @internal
 * @coversNothing
 */
class RefreshDatabaseTest extends TestCase
{
    use RefreshDatabase;

    public function testCreateUser()
    {
        $user = UserFactory::new()->create();

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
        ]);
    }

    public function testZeroUserAfterRefresh()
    {
        $this->assertSame(0, User::count());
    }
    
    public function testCreateMultipleUsers()
    {
        UserFactory::new()->count(3)->create();
        
        $this->assertEquals(3, User::count());
    }
}
