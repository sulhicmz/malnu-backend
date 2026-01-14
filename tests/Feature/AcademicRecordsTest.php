<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Grading\Grade;
use App\Models\SchoolManagement\ClassModel;
use App\Models\SchoolManagement\Student;
use App\Models\SchoolManagement\Subject;
use App\Models\User;
use Tests\TestCase;
use Tymon\JWTAuth\Facades\JWTAuth;

/**
 * @internal
 * @coversNothing
 */
class AcademicRecordsTest extends TestCase
{
    protected $user;

    protected $student;

    protected $class;

    protected $subject;

    protected $token;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        $this->user->assignRole('Super Admin');

        $this->token = JWTAuth::fromUser($this->user);

        $this->class = ClassModel::create([
            'name' => 'X-A',
            'level' => '10',
            'academic_year' => '2024/2025',
        ]);

        $this->subject = Subject::create([
            'code' => 'MAT101',
            'name' => 'Mathematics',
            'credit_hours' => 4,
        ]);

        $studentUser = User::factory()->create();
        $this->student = Student::create([
            'user_id' => $studentUser->id,
            'nisn' => '1234567890',
            'class_id' => $this->class->id,
            'enrollment_date' => now(),
            'status' => 'active',
        ]);

        Grade::create([
            'student_id' => $this->student->id,
            'subject_id' => $this->subject->id,
            'class_id' => $this->class->id,
            'grade' => 85.5,
            'semester' => 1,
            'grade_type' => 'midterm',
        ]);
    }

    public function testCalculateGpaEndpoint()
    {
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->getJson("/api/school/students/{$this->student->id}/gpa");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'student_id',
                    'gpa',
                    'gpa_scale',
                    'academic_year',
                    'semester',
                    'letter_grade',
                ],
                'message',
                'timestamp',
            ])
            ->assertJson([
                'success' => true,
                'data' => [
                    'student_id' => $this->student->id,
                    'gpa_scale' => '4.0',
                ],
            ]);
    }

    public function testGetAcademicPerformanceEndpoint()
    {
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->getJson("/api/school/students/{$this->student->id}/academic-performance");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'student_id',
                    'student_name',
                    'class_name',
                    'cumulative_gpa',
                    'total_credits',
                    'subjects_taken',
                    'semesters',
                    'subject_performance',
                ],
                'message',
                'timestamp',
            ])
            ->assertJson([
                'success' => true,
                'data' => [
                    'student_id' => $this->student->id,
                    'cumulative_gpa' => 3.3,
                    'total_credits' => 4,
                    'subjects_taken' => 1,
                ],
            ]);
    }

    public function testGenerateTranscriptEndpoint()
    {
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->getJson("/api/school/students/{$this->student->id}/transcript");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'transcript_info',
                    'student_info',
                    'academic_summary',
                    'grades_by_semester',
                    'cumulative_statistics',
                    'competencies',
                    'awards_and_achievements',
                    'signatures',
                    'generated_at',
                ],
                'message',
                'timestamp',
            ])
            ->assertJson([
                'success' => true,
            ]);
    }

    public function testGenerateReportCardEndpoint()
    {
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->getJson("/api/school/students/{$this->student->id}/report-card/1/2024/2025");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'report_card_info',
                    'student_info',
                    'grades',
                    'semester_summary',
                    'remarks',
                    'signatures',
                    'generated_at',
                ],
                'message',
                'timestamp',
            ])
            ->assertJson([
                'success' => true,
            ]);
    }

    public function testGetSubjectGradesEndpoint()
    {
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->getJson("/api/school/students/{$this->student->id}/subject-grades/{$this->subject->id}");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'student_id',
                    'subject_id',
                    'subject_gpa',
                    'academic_year',
                ],
                'message',
                'timestamp',
            ])
            ->assertJson([
                'success' => true,
                'data' => [
                    'student_id' => $this->student->id,
                    'subject_id' => $this->subject->id,
                ],
            ]);
    }

    public function testGetGradesHistoryEndpoint()
    {
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->getJson("/api/school/students/{$this->student->id}/grades-history");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'data',
                    'per_page',
                    'current_page',
                    'last_page',
                    'total',
                ],
                'message',
                'timestamp',
            ])
            ->assertJson([
                'success' => true,
            ]);
    }

    public function testGpaCalculationWithNoGrades()
    {
        $studentUser = User::factory()->create();
        $studentWithoutGrades = Student::create([
            'user_id' => $studentUser->id,
            'nisn' => '0987654321',
            'class_id' => $this->class->id,
            'enrollment_date' => now(),
            'status' => 'active',
        ]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->getJson("/api/school/students/{$studentWithoutGrades->id}/gpa");

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'student_id' => $studentWithoutGrades->id,
                    'gpa' => 0.0,
                ],
            ]);
    }

    public function testTranscriptForNonexistentStudent()
    {
        $nonExistentId = '00000000-0000-0000-0000-000000000000';

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->getJson("/api/school/students/{$nonExistentId}/transcript");

        $response->assertStatus(404)
            ->assertJson([
                'success' => false,
            ]);
    }

    public function testReportCardWithQueryParameters()
    {
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->getJson("/api/school/students/{$this->student->id}/gpa?academic_year=2024/2025&semester=1");

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'academic_year' => '2024/2025',
                    'semester' => '1',
                ],
            ]);
    }

    public function testUnauthorizedAccess()
    {
        $response = $this->getJson("/api/school/students/{$this->student->id}/gpa");

        $response->assertStatus(401);
    }
}
