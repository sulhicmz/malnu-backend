<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Models\Grading\Grade;
use App\Models\Grading\Report;
use App\Models\Grading\Competency;
use App\Models\Grading\StudentPortfolio;
use App\Models\SchoolManagement\Student;
use App\Models\User;
use Hypervel\Foundation\Testing\TestCase;

/**
 * @internal
 * @coversNothing
 */
class GradingDomainModelsTest extends TestCase
{
    /**
     * Test grade model configuration.
     */
    public function testGradeModelConfiguration(): void
    {
        $grade = new Grade();
        
        $this->assertEquals('id', $grade->getKeyName());
        $this->assertIsArray($grade->getFillable());
        $this->assertIsArray($grade->getCasts());
    }

    /**
     * Test grade relationships.
     */
    public function testGradeRelationships(): void
    {
        $grade = new Grade();
        
        $studentRelation = $grade->student();
        $this->assertEquals('student_id', $studentRelation->getForeignKeyName());
        
        $creatorRelation = $grade->creator();
        $this->assertEquals('created_by', $creatorRelation->getForeignKeyName());
    }

    /**
     * Test report model configuration.
     */
    public function testReportModelConfiguration(): void
    {
        $report = new Report();
        
        $this->assertEquals('id', $report->getKeyName());
        $this->assertIsArray($report->getFillable());
        $this->assertIsArray($report->getCasts());
    }

    /**
     * Test report relationships.
     */
    public function testReportRelationships(): void
    {
        $report = new Report();
        
        $studentRelation = $report->student();
        $this->assertEquals('student_id', $studentRelation->getForeignKeyName());
        
        $creatorRelation = $report->creator();
        $this->assertEquals('created_by', $creatorRelation->getForeignKeyName());
    }

    /**
     * Test competency model configuration.
     */
    public function testCompetencyModelConfiguration(): void
    {
        $competency = new Competency();
        
        $this->assertEquals('id', $competency->getKeyName());
        $this->assertIsArray($competency->getFillable());
    }

    /**
     * Test competency relationships.
     */
    public function testCompetencyRelationships(): void
    {
        $competency = new Competency();
        
        $studentRelation = $competency->student();
        $this->assertEquals('student_id', $studentRelation->getForeignKeyName());
        
        $creatorRelation = $competency->creator();
        $this->assertEquals('created_by', $creatorRelation->getForeignKeyName());
    }

    /**
     * Test student portfolio model configuration.
     */
    public function testStudentPortfolioModelConfiguration(): void
    {
        $portfolio = new StudentPortfolio();
        
        $this->assertEquals('id', $portfolio->getKeyName());
        $this->assertIsArray($portfolio->getFillable());
    }

    /**
     * Test student portfolio relationships.
     */
    public function testStudentPortfolioRelationships(): void
    {
        $portfolio = new StudentPortfolio();
        
        $studentRelation = $portfolio->student();
        $this->assertEquals('student_id', $studentRelation->getForeignKeyName());
    }
}
