<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Models\User;
use App\Models\ParentPortal\ParentOrtu;
use Hypervel\Foundation\Testing\TestCase;

class UserRelationshipsTest extends TestCase
{
    public function testUserParentRelationship()
    {
        $user = new User();
        $relation = $user->parent();
        
        $this->assertInstanceOf(\Hyperf\Database\Model\Relations\BelongsTo::class, $relation);
        $this->assertEquals('user_id', $relation->getForeignKeyName());
        $this->assertEquals('id', $relation->getLocalKeyName());
    }

    public function testUserTeacherRelationship()
    {
        $user = new User();
        $relation = $user->teacher();
        
        $this->assertInstanceOf(\Hyperf\Database\Model\Relations\HasOne::class, $relation);
        $this->assertEquals('user_id', $relation->getForeignKeyName());
        $this->assertEquals('id', $relation->getLocalKeyName());
    }

    public function testUserStudentRelationship()
    {
        $user = new User();
        $relation = $user->student();
        
        $this->assertInstanceOf(\Hyperf\Database\Model\Relations\HasOne::class, $relation);
        $this->assertEquals('user_id', $relation->getForeignKeyName());
        $this->assertEquals('id', $relation->getLocalKeyName());
    }

    public function testUserStaffRelationship()
    {
        $user = new User();
        $relation = $user->staff();
        
        $this->assertInstanceOf(\Hyperf\Database\Model\Relations\HasOne::class, $relation);
        $this->assertEquals('user_id', $relation->getForeignKeyName());
        $this->assertEquals('id', $relation->getLocalKeyName());
    }

    public function testParentOrtuUserRelationship()
    {
        $parent = new ParentOrtu();
        $relation = $parent->user();
        
        $this->assertInstanceOf(\Hyperf\Database\Model\Relations\BelongsTo::class, $relation);
        $this->assertEquals('user_id', $relation->getForeignKeyName());
        $this->assertEquals('id', $relation->getLocalKeyName());
    }
}