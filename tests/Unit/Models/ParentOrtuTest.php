<?php

declare(strict_types=1);

namespace Tests\Unit\Models;

use App\Models\ParentPortal\ParentOrtu;
use App\Models\User;
use Database\Factories\ParentPortal\ParentOrtuFactory;
use Database\Factories\UserFactory;
use Tests\TestCase;
use Hypervel\Foundation\Testing\RefreshDatabase;

class ParentOrtuTest extends TestCase
{
    use RefreshDatabase;

    public function test_parent_ortu_factory_creates_parent(): void
    {
        $parent = ParentOrtuFactory::new()->create();

        $this->assertNotNull($parent->id);
        $this->assertNotNull($parent->occupation);
        $this->assertNotNull($parent->address);
    }

    public function test_parent_ortu_has_correct_primary_key(): void
    {
        $parent = new ParentOrtu();

        $this->assertEquals('id', $parent->getKeyName());
        $this->assertEquals('string', $parent->getKeyType());
        $this->assertFalse($parent->incrementing);
    }

    public function test_parent_ortu_belongs_to_user(): void
    {
        $user = UserFactory::new()->create();
        $parent = ParentOrtuFactory::new()->create(['user_id' => $user->id]);

        $this->assertInstanceOf(User::class, $parent->user);
        $this->assertEquals($user->id, $parent->user->id);
    }

    public function test_parent_ortu_fillable_attributes(): void
    {
        $parent = new ParentOrtu();

        $fillable = $parent->getFillable();

        $this->assertContains('user_id', $fillable);
        $this->assertContains('occupation', $fillable);
        $this->assertContains('address', $fillable);
    }

    public function test_parent_ortu_casts_attributes(): void
    {
        $parent = ParentOrtuFactory::new()->create();

        $casts = $parent->getCasts();
        
        $this->assertArrayHasKey('created_at', $casts);
        $this->assertArrayHasKey('updated_at', $casts);
    }
}