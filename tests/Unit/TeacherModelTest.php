<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Models\SchoolManagement\Teacher;
use App\Models\User;
use App\Models\SchoolManagement\ClassModel;
use App\Models\SchoolManagement\ClassSubject;
use App\Models\ELearning\VirtualClass;
use App\Models\CareerDevelopment\CounselingSession;
use Hyperf\Foundation\Testing\TestCase;

/**
 * @internal
 * @coversNothing
 */
class TeacherModelTest extends TestCase
{
    /**
     * Test teacher model configuration.
     */
    public function testTeacherModelConfiguration(): void
    {
        $teacher = new Teacher();
        
        $this->assertEquals('id', $teacher->getKeyName());
        $this->assertEquals('string', $teacher->getKeyType());
        $this->assertFalse($teacher->incrementing);
        
        $this->assertIsArray($teacher->getFillable());
        $this->assertContains('user_id', $teacher->getFillable());
        $this->assertContains('nip', $teacher->getFillable());
        $this->assertContains('expertise', $teacher->getFillable());
        $this->assertContains('join_date', $teacher->getFillable());
        $this->assertContains('status', $teacher->getFillable());
        
        $this->assertIsArray($teacher->getCasts());
        $this->assertArrayHasKey('join_date', $teacher->getCasts());
        $this->assertArrayHasKey('created_at', $teacher->getCasts());
        $this->assertArrayHasKey('updated_at', $teacher->getCasts());
    }

    /**
     * Test teacher user relationship.
     */
    public function testTeacherUserRelationship(): void
    {
        $teacher = new Teacher();
        $relation = $teacher->user();
        
        $this->assertEquals('user_id', $relation->getForeignKeyName());
        $this->assertEquals('id', $relation->getLocalKeyName());
    }

    /**
     * Test teacher classes relationship.
     */
    public function testTeacherClassesRelationship(): void
    {
        $teacher = new Teacher();
        $relation = $teacher->classes();
        
        $this->assertEquals('homeroom_teacher_id', $relation->getForeignKeyName());
        $this->assertEquals('id', $relation->getLocalKeyName());
    }

    /**
     * Test teacher class subjects relationship.
     */
    public function testTeacherClassSubjectsRelationship(): void
    {
        $teacher = new Teacher();
        $relation = $teacher->classSubjects();
        
        $this->assertEquals('teacher_id', $relation->getForeignKeyName());
        $this->assertEquals('id', $relation->getLocalKeyName());
    }

    /**
     * Test teacher virtual classes relationship.
     */
    public function testTeacherVirtualClassesRelationship(): void
    {
        $teacher = new Teacher();
        $relation = $teacher->virtualClasses();
        
        $this->assertEquals('teacher_id', $relation->getForeignKeyName());
        $this->assertEquals('id', $relation->getLocalKeyName());
    }

    /**
     * Test teacher counseling sessions relationship.
     */
    public function testTeacherCounselingSessionsRelationship(): void
    {
        $teacher = new Teacher();
        $relation = $teacher->counselingSessions();
        
        $this->assertEquals('counselor_id', $relation->getForeignKeyName());
        $this->assertEquals('id', $relation->getLocalKeyName());
    }

    /**
     * Test teacher model can be instantiated.
     */
    public function testTeacherCanBeInstantiated(): void
    {
        $teacher = new Teacher();
        $this->assertInstanceOf(Teacher::class, $teacher);
    }
}
