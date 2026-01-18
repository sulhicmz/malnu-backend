<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Models\SchoolManagement\Staff;
use App\Models\SchoolManagement\ClassModel;
use App\Models\SchoolManagement\ClassSubject;
use App\Models\SchoolManagement\Subject;
use App\Models\SchoolManagement\SchoolInventory;
use App\Models\SchoolManagement\AssetCategory;
use App\Models\SchoolManagement\AssetAssignment;
use App\Models\SchoolManagement\AssetMaintenance;
use App\Models\User;
use Hyperf\Foundation\Testing\TestCase;

/**
 * @internal
 * @coversNothing
 */
class SchoolManagementDomainModelsTest extends TestCase
{
    /**
     * Test staff model configuration.
     */
    public function testStaffModelConfiguration(): void
    {
        $staff = new Staff();
        
        $this->assertEquals('id', $staff->getKeyName());
        $this->assertEquals('string', $staff->getKeyType());
        $this->assertFalse($staff->incrementing);
        
        $this->assertIsArray($staff->getFillable());
    }

    /**
     * Test staff relationship.
     */
    public function testStaffUserRelationship(): void
    {
        $staff = new Staff();
        $relation = $staff->user();
        
        $this->assertEquals('user_id', $relation->getForeignKeyName());
        $this->assertEquals('id', $relation->getLocalKeyName());
    }

    /**
     * Test class model configuration.
     */
    public function testClassModelConfiguration(): void
    {
        $class = new ClassModel();
        
        $this->assertEquals('id', $class->getKeyName());
        $this->assertEquals('string', $class->getKeyType());
        $this->assertFalse($class->incrementing);
        
        $this->assertIsArray($class->getFillable());
    }

    /**
     * Test class model relationships.
     */
    public function testClassModelRelationships(): void
    {
        $class = new ClassModel();
        
        $homeroomTeacherRelation = $class->homeroomTeacher();
        $this->assertEquals('homeroom_teacher_id', $homeroomTeacherRelation->getForeignKeyName());
        
        $subjectRelation = $class->subject();
        $this->assertEquals('subject_id', $subjectRelation->getForeignKeyName());
        
        $studentsRelation = $class->students();
        $this->assertEquals('class_id', $studentsRelation->getForeignKeyName());
    }

    /**
     * Test class subject model configuration.
     */
    public function testClassSubjectModelConfiguration(): void
    {
        $classSubject = new ClassSubject();
        
        $this->assertEquals('id', $classSubject->getKeyName());
        $this->assertIsArray($classSubject->getFillable());
    }

    /**
     * Test class subject relationships.
     */
    public function testClassSubjectRelationships(): void
    {
        $classSubject = new ClassSubject();
        
        $classRelation = $classSubject->class();
        $this->assertEquals('class_id', $classRelation->getForeignKeyName());
        
        $subjectRelation = $classSubject->subject();
        $this->assertEquals('subject_id', $subjectRelation->getForeignKeyName());
        
        $teacherRelation = $classSubject->teacher();
        $this->assertEquals('teacher_id', $teacherRelation->getForeignKeyName());
    }

    /**
     * Test subject model configuration.
     */
    public function testSubjectModelConfiguration(): void
    {
        $subject = new Subject();
        
        $this->assertEquals('id', $subject->getKeyName());
        $this->assertIsArray($subject->getFillable());
    }

    /**
     * Test subject relationships.
     */
    public function testSubjectRelationships(): void
    {
        $subject = new Subject();
        
        $classesRelation = $subject->classes();
        $this->assertEquals('subject_id', $classesRelation->getForeignKeyName());
    }

    /**
     * Test school inventory model configuration.
     */
    public function testSchoolInventoryModelConfiguration(): void
    {
        $inventory = new SchoolInventory();
        
        $this->assertEquals('id', $inventory->getKeyName());
        $this->assertIsArray($inventory->getFillable());
    }

    /**
     * Test asset category model configuration.
     */
    public function testAssetCategoryModelConfiguration(): void
    {
        $assetCategory = new AssetCategory();
        
        $this->assertEquals('id', $assetCategory->getKeyName());
        $this->assertIsArray($assetCategory->getFillable());
    }

    /**
     * Test asset assignment model configuration.
     */
    public function testAssetAssignmentModelConfiguration(): void
    {
        $assetAssignment = new AssetAssignment();
        
        $this->assertEquals('id', $assetAssignment->getKeyName());
        $this->assertIsArray($assetAssignment->getFillable());
    }

    /**
     * Test asset maintenance model configuration.
     */
    public function testAssetMaintenanceModelConfiguration(): void
    {
        $assetMaintenance = new AssetMaintenance();
        
        $this->assertEquals('id', $assetMaintenance->getKeyName());
        $this->assertIsArray($assetMaintenance->getFillable());
        $this->assertIsArray($assetMaintenance->getCasts());
    }
}
