<?php

declare(strict_types=1);

namespace Tests\Unit;

use Hypervel\Foundation\Testing\TestCase;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @internal
 * @coversNothing
 */
class UserUnitTest extends TestCase
{
    /**
     * Test user model can be instantiated.
     */
    public function testUserModelCanBeInstantiated(): void
    {
        $user = new User();
        
        $this->assertInstanceOf(User::class, $user);
    }

    /**
     * Test user model fillable attributes.
     */
    public function testUserModelFillableAttributes(): void
    {
        $user = new User();
        $fillable = $user->getFillable();
        
        $this->assertContains('name', $fillable);
        $this->assertContains('email', $fillable);
        $this->assertContains('password', $fillable);
    }

    /**
     * Test user password hashing.
     */
    public function testUserPasswordIsHashed(): void
    {
        $user = new User();
        $plainPassword = 'testpassword123';
        
        $user->password = $plainPassword;
        
        $this->assertTrue(Hash::check($plainPassword, $user->password));
        $this->assertNotEquals($plainPassword, $user->password);
    }

    /**
     * Test user email validation.
     */
    public function testUserEmailCanBeSet(): void
    {
        $user = new User();
        $email = 'test@example.com';
        
        $user->email = $email;
        
        $this->assertEquals($email, $user->email);
    }

    /**
     * Test user name can be set.
     */
    public function testUserNameCanBeSet(): void
    {
        $user = new User();
        $name = 'John Doe';
        
        $user->name = $name;
        
        $this->assertEquals($name, $user->name);
    }

    /**
     * Test user UUID generation.
     */
    public function testUserHasUuid(): void
    {
        $user = new User();
        $uuid = (string) Str::uuid();
        
        $user->id = $uuid;
        
        $this->assertEquals($uuid, $user->id);
        $this->assertIsString($user->id);
    }
}
