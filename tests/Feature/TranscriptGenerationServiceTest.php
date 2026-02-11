<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Grading\Competency;
use App\Models\Grading\Grade;
use App\Models\Grading\StudentPortfolio;
use App\Models\SchoolManagement\ClassModel;
use App\Models\SchoolManagement\Student;
use App\Models\SchoolManagement\Subject;
use App\Models\User;
use App\Services\GPACalculationService;
use App\Services\TranscriptGenerationService;
use Exception;
use Tests\TestCase;

/**
 * @internal
 * @coversNothing
 */
class TranscriptGenerationServiceTest extends TestCase
{
    private TranscriptGenerationService $transcriptService;

    private Student $student;

    private ClassModel $class;

    private Subject $subject;

    protected function setUp(): void
    {
        parent::setUp();

        $gpaService = new GPACalculationService();
        $this->transcriptService = new TranscriptGenerationService($gpaService);

        $user = User::create([
            'name' => 'Test Student',
            'email' => 'student@example.com',
            'password' => password_hash('Password123!', PASSWORD_DEFAULT),
            'role' => 'student',
        ]);

        $this->class = ClassModel::create([
            'name' => 'Test Class 10A',
            'academic_year' => '2024/2025',
        ]);

        $this->student = Student::create([
            'user_id' => $user->id,
            'class_id' => $this->class->id,
            'nisn' => '1234567890',
            'birth_place' => 'Test City',
            'birth_date' => '2008-01-01',
            'enrollment_date' => '2023-07-01',
            'status' => 'active',
        ]);

        $this->subject = Subject::create([
            'name' => 'Mathematics',
            'code' => 'MATH101',
            'credit_hours' => 4,
        ]);
    }

    public function testGenerateTranscriptSuccessfully()
    {
        $this->createGrades();

        $transcript = $this->transcriptService->generateTranscript($this->student->id);

        $this->assertIsArray($transcript);
        $this->assertArrayHasKey('transcript_info', $transcript);
        $this->assertArrayHasKey('student_info', $transcript);
        $this->assertArrayHasKey('academic_summary', $transcript);
        $this->assertArrayHasKey('grades_by_semester', $transcript);
        $this->assertArrayHasKey('cumulative_statistics', $transcript);
        $this->assertArrayHasKey('competencies', $transcript);
        $this->assertArrayHasKey('awards_and_achievements', $transcript);
        $this->assertArrayHasKey('signatures', $transcript);
        $this->assertArrayHasKey('generated_at', $transcript);
    }

    public function testGenerateTranscriptWithNonexistentStudentThrowsException()
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Student not found');

        $this->transcriptService->generateTranscript('non-existent-id');
    }

    public function testGenerateTranscriptWithNoGradesThrowsException()
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('No grades found for this student');

        $this->transcriptService->generateTranscript($this->student->id);
    }

    public function testGenerateTranscriptWithAcademicYearFilter()
    {
        $this->createGrades();

        $transcript = $this->transcriptService->generateTranscript($this->student->id, '2024/2025');

        $this->assertArrayHasKey('academic_summary', $transcript);
        $this->assertNotEmpty($transcript['grades_by_semester']);
    }

    public function testTranscriptStudentInfoStructure()
    {
        $this->createGrades();

        $transcript = $this->transcriptService->generateTranscript($this->student->id);

        $this->assertArrayHasKey('student_info', $transcript);
        $this->assertArrayHasKey('student_id', $transcript['student_info']);
        $this->assertArrayHasKey('nisn', $transcript['student_info']);
        $this->assertArrayHasKey('name', $transcript['student_info']);
        $this->assertArrayHasKey('birth_place', $transcript['student_info']);
        $this->assertArrayHasKey('birth_date', $transcript['student_info']);
        $this->assertArrayHasKey('class', $transcript['student_info']);
        $this->assertEquals('Test Student', $transcript['student_info']['name']);
        $this->assertEquals('1234567890', $transcript['student_info']['nisn']);
    }

    public function testTranscriptAcademicSummaryStructure()
    {
        $this->createGrades();

        $transcript = $this->transcriptService->generateTranscript($this->student->id);

        $this->assertArrayHasKey('academic_summary', $transcript);
        $this->assertArrayHasKey('cumulative_gpa', $transcript['academic_summary']);
        $this->assertArrayHasKey('total_credits_earned', $transcript['academic_summary']);
        $this->assertArrayHasKey('total_subjects_completed', $transcript['academic_summary']);
        $this->assertArrayHasKey('gpa_scale', $transcript['academic_summary']);
        $this->assertArrayHasKey('academic_standing', $transcript['academic_summary']);
        $this->assertEquals('4.0', $transcript['academic_summary']['gpa_scale']);
        $this->assertIsFloat($transcript['academic_summary']['cumulative_gpa']);
    }

    public function testTranscriptGradesBySemesterStructure()
    {
        $this->createGrades();

        $transcript = $this->transcriptService->generateTranscript($this->student->id);

        $this->assertIsArray($transcript['grades_by_semester']);
        $this->assertNotEmpty($transcript['grades_by_semester']);

        $semesterData = $transcript['grades_by_semester'][0];
        $this->assertArrayHasKey('semester', $semesterData);
        $this->assertArrayHasKey('academic_year', $semesterData);
        $this->assertArrayHasKey('semester_gpa', $semesterData);
        $this->assertArrayHasKey('credits', $semesterData);
        $this->assertArrayHasKey('subjects', $semesterData);
        $this->assertIsArray($semesterData['subjects']);
    }

    public function testTranscriptCumulativeStatisticsStructure()
    {
        $this->createGrades();

        $transcript = $this->transcriptService->generateTranscript($this->student->id);

        $this->assertArrayHasKey('cumulative_statistics', $transcript);
        $this->assertArrayHasKey('total_grades', $transcript['cumulative_statistics']);
        $this->assertArrayHasKey('grades_above_90', $transcript['cumulative_statistics']);
        $this->assertArrayHasKey('grades_80_to_89', $transcript['cumulative_statistics']);
        $this->assertArrayHasKey('grades_70_to_79', $transcript['cumulative_statistics']);
        $this->assertArrayHasKey('grades_60_to_69', $transcript['cumulative_statistics']);
        $this->assertArrayHasKey('grades_below_60', $transcript['cumulative_statistics']);
        $this->assertArrayHasKey('passed_subjects', $transcript['cumulative_statistics']);
        $this->assertArrayHasKey('failed_subjects', $transcript['cumulative_statistics']);
        $this->assertArrayHasKey('pass_rate', $transcript['cumulative_statistics']);
        $this->assertIsFloat($transcript['cumulative_statistics']['pass_rate']);
    }

    public function testTranscriptSignaturesStructure()
    {
        $this->createGrades();

        $transcript = $this->transcriptService->generateTranscript($this->student->id);

        $this->assertArrayHasKey('signatures', $transcript);
        $this->assertArrayHasKey('homeroom_teacher', $transcript['signatures']);
        $this->assertArrayHasKey('principal', $transcript['signatures']);
        $this->assertArrayHasKey('name', $transcript['signatures']['homeroom_teacher']);
        $this->assertArrayHasKey('nip', $transcript['signatures']['homeroom_teacher']);
        $this->assertArrayHasKey('title', $transcript['signatures']['homeroom_teacher']);
    }

    public function testGenerateReportCardSuccessfully()
    {
        $this->createGrades();

        $reportCard = $this->transcriptService->generateReportCard(
            $this->student->id,
            1,
            '2024/2025'
        );

        $this->assertIsArray($reportCard);
        $this->assertArrayHasKey('report_card_info', $reportCard);
        $this->assertArrayHasKey('student_info', $reportCard);
        $this->assertArrayHasKey('grades', $reportCard);
        $this->assertArrayHasKey('semester_summary', $reportCard);
        $this->assertArrayHasKey('remarks', $reportCard);
        $this->assertArrayHasKey('signatures', $reportCard);
        $this->assertArrayHasKey('generated_at', $reportCard);
    }

    public function testGenerateReportCardWithNonexistentStudentThrowsException()
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Student not found');

        $this->transcriptService->generateReportCard('non-existent-id', 1, '2024/2025');
    }

    public function testGenerateReportCardWithNoGradesThrowsException()
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('No grades found for the specified semester and academic year');

        $this->transcriptService->generateReportCard($this->student->id, 1, '2024/2025');
    }

    public function testReportCardSemesterSummaryStructure()
    {
        $this->createGrades();

        $reportCard = $this->transcriptService->generateReportCard(
            $this->student->id,
            1,
            '2024/2025'
        );

        $this->assertArrayHasKey('semester_summary', $reportCard);
        $this->assertArrayHasKey('semester_gpa', $reportCard['semester_summary']);
        $this->assertArrayHasKey('total_credits', $reportCard['semester_summary']);
        $this->assertArrayHasKey('total_subjects', $reportCard['semester_summary']);
        $this->assertIsFloat($reportCard['semester_summary']['semester_gpa']);
        $this->assertIsInt($reportCard['semester_summary']['total_credits']);
        $this->assertIsInt($reportCard['semester_summary']['total_subjects']);
    }

    public function testReportCardGradesIncludeGradePoints()
    {
        $this->createGrades();

        $reportCard = $this->transcriptService->generateReportCard(
            $this->student->id,
            1,
            '2024/2025'
        );

        $this->assertNotEmpty($reportCard['grades']);
        $gradeData = $reportCard['grades'][0];
        $this->assertArrayHasKey('grade_point', $gradeData);
        $this->assertArrayHasKey('remarks', $gradeData);
        $this->assertIsFloat($gradeData['grade_point']);
        $this->assertIsString($gradeData['remarks']);
    }

    public function testReportCardRemarksStructure()
    {
        $this->createGrades();

        $reportCard = $this->transcriptService->generateReportCard(
            $this->student->id,
            1,
            '2024/2025'
        );

        $this->assertArrayHasKey('remarks', $reportCard);
        $this->assertArrayHasKey('homeroom_notes', $reportCard['remarks']);
        $this->assertArrayHasKey('principal_notes', $reportCard['remarks']);
    }

    public function testTranscriptIncludesCompetencies()
    {
        $this->createGrades();
        $this->createCompetencies();

        $transcript = $this->transcriptService->generateTranscript($this->student->id);

        $this->assertArrayHasKey('competencies', $transcript);
        $this->assertIsArray($transcript['competencies']);
    }

    public function testTranscriptIncludesAchievements()
    {
        $this->createGrades();
        $this->createPortfolios();

        $transcript = $this->transcriptService->generateTranscript($this->student->id);

        $this->assertArrayHasKey('awards_and_achievements', $transcript);
        $this->assertIsArray($transcript['awards_and_achievements']);
    }

    public function testTranscriptSavesReportRecord()
    {
        $this->createGrades();

        $this->assertDatabaseMissing('reports', [
            'student_id' => $this->student->id,
        ]);

        $this->transcriptService->generateTranscript($this->student->id);

        $this->assertDatabaseHas('reports', [
            'student_id' => $this->student->id,
            'is_published' => true,
        ]);
    }

    public function testTranscriptHandlesMultipleSemesters()
    {
        $this->createGrades(1);
        $this->createGrades(2);

        $transcript = $this->transcriptService->generateTranscript($this->student->id);

        $this->assertGreaterThanOrEqual(1, count($transcript['grades_by_semester']));
    }

    public function testCumulativeStatisticsCalculatesCorrectPassRate()
    {
        $this->createGrades();

        $transcript = $this->transcriptService->generateTranscript($this->student->id);

        $totalGrades = $transcript['cumulative_statistics']['total_grades'];
        $passedGrades = $transcript['cumulative_statistics']['passed_subjects'];
        $expectedPassRate = $totalGrades > 0
            ? round(($passedGrades / $totalGrades) * 100, 2)
            : 0;

        $this->assertEquals(
            $expectedPassRate,
            $transcript['cumulative_statistics']['pass_rate']
        );
    }

    public function testAcademicStandingBasedOnGPA()
    {
        $this->createGrades(1, 95);

        $transcript = $this->transcriptService->generateTranscript($this->student->id);

        $this->assertEquals(
            'High Distinction',
            $transcript['academic_summary']['academic_standing']
        );
    }

    private function createGrades(int $semester = 1, int $gradeValue = 85): void
    {
        $subjects = Subject::where('class_id', $this->class->id)->get();

        if ($subjects->isEmpty()) {
            $subject = Subject::create([
                'name' => 'Test Subject',
                'code' => 'TS101',
                'credit_hours' => 3,
                'class_id' => $this->class->id,
            ]);
        } else {
            $subject = $subjects->first();
        }

        Grade::create([
            'student_id' => $this->student->id,
            'subject_id' => $subject->id,
            'class_id' => $this->class->id,
            'semester' => $semester,
            'grade' => $gradeValue,
            'grade_type' => 'final',
        ]);
    }

    private function createCompetencies(): void
    {
        Competency::create([
            'student_id' => $this->student->id,
            'subject_id' => $this->subject->id,
            'competency_code' => 'COMP001',
            'competency_name' => 'Mathematical Problem Solving',
            'achievement_level' => 'Excellent',
            'semester' => 1,
            'notes' => 'Strong performance',
        ]);
    }

    private function createPortfolios(): void
    {
        StudentPortfolio::create([
            'student_id' => $this->student->id,
            'title' => 'Math Competition Winner',
            'description' => 'First place in regional math competition',
            'portfolio_type' => 'achievement',
            'date_added' => now(),
            'is_public' => true,
            'file_url' => 'https://example.com/certificate.pdf',
        ]);
    }
}
