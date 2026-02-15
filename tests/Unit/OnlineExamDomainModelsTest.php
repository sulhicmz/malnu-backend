<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Models\OnlineExam\Exam;
use App\Models\OnlineExam\QuestionBank;
use App\Models\OnlineExam\ExamResult;
use App\Models\SchoolManagement\Student;
use App\Models\User;
use Hypervel\Foundation\Testing\TestCase;

/**
 * @internal
 * @coversNothing
 */
class OnlineExamDomainModelsTest extends TestCase
{
    /**
     * Test exam model configuration.
     */
    public function testExamModelConfiguration(): void
    {
        $exam = new Exam();
        
        $this->assertEquals('id', $exam->getKeyName());
        $this->assertIsArray($exam->getFillable());
        $this->assertIsArray($exam->getCasts());
    }

    /**
     * Test exam relationships.
     */
    public function testExamRelationships(): void
    {
        $exam = new Exam();
        
        $creatorRelation = $exam->creator();
        $this->assertEquals('created_by', $creatorRelation->getForeignKeyName());
        
        $questionsRelation = $exam->questions();
        $this->assertEquals('exam_id', $questionsRelation->getForeignKeyName());
        
        $resultsRelation = $exam->results();
        $this->assertEquals('exam_id', $resultsRelation->getForeignKeyName());
    }

    /**
     * Test question bank model configuration.
     */
    public function testQuestionBankModelConfiguration(): void
    {
        $questionBank = new QuestionBank();
        
        $this->assertEquals('id', $questionBank->getKeyName());
        $this->assertIsArray($questionBank->getFillable());
    }

    /**
     * Test question bank relationships.
     */
    public function testQuestionBankRelationships(): void
    {
        $questionBank = new QuestionBank();
        
        $creatorRelation = $questionBank->creator();
        $this->assertEquals('created_by', $creatorRelation->getForeignKeyName());
        
        $examRelation = $questionBank->exam();
        $this->assertEquals('exam_id', $examRelation->getForeignKeyName());
    }

    /**
     * Test exam result model configuration.
     */
    public function testExamResultModelConfiguration(): void
    {
        $examResult = new ExamResult();
        
        $this->assertEquals('id', $examResult->getKeyName());
        $this->assertIsArray($examResult->getFillable());
        $this->assertIsArray($examResult->getCasts());
    }

    /**
     * Test exam result relationships.
     */
    public function testExamResultRelationships(): void
    {
        $examResult = new ExamResult();
        
        $studentRelation = $examResult->student();
        $this->assertEquals('student_id', $studentRelation->getForeignKeyName());
        
        $examRelation = $examResult->exam();
        $this->assertEquals('exam_id', $examRelation->getForeignKeyName());
    }
}
