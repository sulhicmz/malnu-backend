<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Models\SchoolManagement\Student;
use App\Models\User;
use App\Models\ParentPortal\ParentOrtu;
use App\Models\SchoolManagement\ClassModel;
use App\Models\Grading\Grade;
use App\Models\Grading\Competency;
use App\Models\Grading\Report;
use App\Models\Grading\StudentPortfolio;
use App\Models\OnlineExam\ExamResult;
use App\Models\CareerDevelopment\CareerAssessment;
use App\Models\CareerDevelopment\CounselingSession;
use Hyperf\Foundation\Testing\TestCase;

/**
 * @internal
 * @coversNothing
 */
class StudentModelTest extends TestCase
{
    /**
     * Test student model configuration.
     */
    public function testStudentModelConfiguration(): void
    {
        $student = new Student();
        
        $this->assertEquals('id', $student->getKeyName());
        $this->assertEquals('string', $student->getKeyType());
        $this->assertFalse($student->incrementing);
        
        $this->assertIsArray($student->getFillable());
        $this->assertContains('user_id', $student->getFillable());
        $this->assertContains('nisn', $student->getFillable());
        $this->assertContains('class_id', $student->getFillable());
        $this->assertContains('birth_date', $student->getFillable());
        $this->assertContains('birth_place', $student->getFillable());
        $this->assertContains('address', $student->getFillable());
        $this->assertContains('parent_id', $student->getFillable());
        $this->assertContains('enrollment_date', $student->getFillable());
        $this->assertContains('status', $student->getFillable());
        
        $this->assertIsArray($student->getCasts());
        $this->assertArrayHasKey('birth_date', $student->getCasts());
        $this->assertArrayHasKey('enrollment_date', $student->getCasts());
        $this->assertArrayHasKey('created_at', $student->getCasts());
        $this->assertArrayHasKey('updated_at', $student->getCasts());
    }

    /**
     * Test student user relationship.
     */
    public function testStudentUserRelationship(): void
    {
        $student = new Student();
        $relation = $student->user();
        
        $this->assertEquals('user_id', $relation->getForeignKeyName());
        $this->assertEquals('id', $relation->getLocalKeyName());
    }

    /**
     * Test student class relationship.
     */
    public function testStudentClassRelationship(): void
    {
        $student = new Student();
        $relation = $student->class();
        
        $this->assertEquals('class_id', $relation->getForeignKeyName());
        $this->assertEquals('id', $relation->getLocalKeyName());
    }

    /**
     * Test student parent relationship.
     */
    public function testStudentParentRelationship(): void
    {
        $student = new Student();
        $relation = $student->parent();
        
        $this->assertEquals('parent_id', $relation->getForeignKeyName());
        $this->assertEquals('id', $relation->getLocalKeyName());
    }

    /**
     * Test student grades relationship.
     */
    public function testStudentGradesRelationship(): void
    {
        $student = new Student();
        $relation = $student->grades();
        
        $this->assertEquals('student_id', $relation->getForeignKeyName());
        $this->assertEquals('id', $relation->getLocalKeyName());
    }

    /**
     * Test student competencies relationship.
     */
    public function testStudentCompetenciesRelationship(): void
    {
        $student = new Student();
        $relation = $student->competencies();
        
        $this->assertEquals('student_id', $relation->getForeignKeyName());
        $this->assertEquals('id', $relation->getLocalKeyName());
    }

    /**
     * Test student reports relationship.
     */
    public function testStudentReportsRelationship(): void
    {
        $student = new Student();
        $relation = $student->reports();
        
        $this->assertEquals('student_id', $relation->getForeignKeyName());
        $this->assertEquals('id', $relation->getLocalKeyName());
    }

    /**
     * Test student portfolios relationship.
     */
    public function testStudentPortfoliosRelationship(): void
    {
        $student = new Student();
        $relation = $student->portfolios();
        
        $this->assertEquals('student_id', $relation->getForeignKeyName());
        $this->assertEquals('id', $relation->getLocalKeyName());
    }

    /**
     * Test student exam results relationship.
     */
    public function testStudentExamResultsRelationship(): void
    {
        $student = new Student();
        $relation = $student->examResults();
        
        $this->assertEquals('student_id', $relation->getForeignKeyName());
        $this->assertEquals('id', $relation->getLocalKeyName());
    }

    /**
     * Test student career assessments relationship.
     */
    public function testStudentCareerAssessmentsRelationship(): void
    {
        $student = new Student();
        $relation = $student->careerAssessments();
        
        $this->assertEquals('student_id', $relation->getForeignKeyName());
        $this->assertEquals('id', $relation->getLocalKeyName());
    }

    /**
     * Test student counseling sessions relationship.
     */
    public function testStudentCounselingSessionsRelationship(): void
    {
        $student = new Student();
        $relation = $student->counselingSessions();
        
        $this->assertEquals('student_id', $relation->getForeignKeyName());
        $this->assertEquals('id', $relation->getLocalKeyName());
    }

    /**
     * Test student model can be instantiated.
     */
    public function testStudentCanBeInstantiated(): void
    {
        $student = new Student();
        $this->assertInstanceOf(Student::class, $student);
    }
}
