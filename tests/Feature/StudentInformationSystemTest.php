<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\SchoolManagement\Student;
use App\Models\Grading\Grade;
use App\Models\Grading\Report;
use App\Models\SchoolManagement\ClassModel;
use App\Models\SchoolManagement\Subject;
use App\Models\User;
use Tymon\JWTAuth\Facades\JWTAuth;

class StudentInformationSystemTest extends TestCase
{
    protected $user;
    protected $student;
    protected $class;
    protected $subject;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Create a test user
        $this->user = User::factory()->create();
        
        // Create a test class
        $this->class = ClassModel::create([
            'name' => 'Test Class',
            'level' => '10',
            'academic_year' => '2024/2025',
        ]);
        
        // Create a test subject
        $this->subject = Subject::create([
            'code' => 'MATH101',
            'name' => 'Mathematics',
            'credit_hours' => 3,
        ]);
        
        // Create a test student
        $this->student = Student::create([
            'name' => 'Test Student',
            'nisn' => '1234567890',
            'class_id' => $this->class->id,
            'enrollment_date' => '2024-01-01',
            'status' => 'active',
        ]);
    }

    public function test_academic_record_retrieval()
    {
        // Get JWT token for the test user
        $token = JWTAuth::fromUser($this->user);

        // Add some test grades for the student
        Grade::create([
            'id' => 'test-grade-1',
            'student_id' => $this->student->id,
            'subject_id' => $this->subject->id,
            'class_id' => $this->class->id,
            'grade' => 85.5,
            'semester' => 1,
            'grade_type' => 'exam',
        ]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->getJson("/api/sis/students/{$this->student->id}/academic-record");

        $response->assertStatus(200)
                 ->assertJson([
                     'success' => true,
                     'message' => 'Academic record retrieved successfully'
                 ])
                 ->assertJsonStructure([
                     'success',
                     'data' => [
                         'student',
                         'cumulative_gpa',
                         'grades',
                         'reports',
                         'academic_history'
                     ],
                     'message',
                     'timestamp'
                 ]);
    }

    public function test_transcript_generation()
    {
        // Get JWT token for the test user
        $token = JWTAuth::fromUser($this->user);

        // Add some test grades for the student
        Grade::create([
            'id' => 'test-grade-2',
            'student_id' => $this->student->id,
            'subject_id' => $this->subject->id,
            'class_id' => $this->class->id,
            'grade' => 92.0,
            'semester' => 1,
            'grade_type' => 'exam',
        ]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->getJson("/api/sis/students/{$this->student->id}/transcript");

        $response->assertStatus(200)
                 ->assertJson([
                     'success' => true,
                     'message' => 'Transcript generated successfully'
                 ])
                 ->assertJsonStructure([
                     'success',
                     'data' => [
                         'student_info',
                         'cumulative_gpa',
                         'academic_history',
                         'total_credits',
                         'class_rankings'
                     ],
                     'message',
                     'timestamp'
                 ]);
    }

    public function test_enrollment_details_retrieval()
    {
        // Get JWT token for the test user
        $token = JWTAuth::fromUser($this->user);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->getJson("/api/sis/students/{$this->student->id}/enrollment");

        $response->assertStatus(200)
                 ->assertJson([
                     'success' => true,
                     'message' => 'Enrollment details retrieved successfully'
                 ])
                 ->assertJsonStructure([
                     'success',
                     'data' => [
                         'student',
                         'enrollment_status',
                         'enrollment_date',
                         'academic_year',
                         'current_class',
                         'progression_history'
                     ],
                     'message',
                     'timestamp'
                 ]);
    }

    public function test_enrollment_status_update()
    {
        // Get JWT token for the test user
        $token = JWTAuth::fromUser($this->user);

        $updateData = [
            'status' => 'graduated'
        ];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->putJson("/api/sis/students/{$this->student->id}/enrollment-status", $updateData);

        $response->assertStatus(200)
                 ->assertJson([
                     'success' => true,
                     'message' => 'Enrollment status updated successfully'
                 ]);
    }

    public function test_student_analytics_retrieval()
    {
        // Get JWT token for the test user
        $token = JWTAuth::fromUser($this->user);

        // Add some test grades for the student
        Grade::create([
            'id' => 'test-grade-3',
            'student_id' => $this->student->id,
            'subject_id' => $this->subject->id,
            'class_id' => $this->class->id,
            'grade' => 78.5,
            'semester' => 1,
            'grade_type' => 'exam',
        ]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->getJson("/api/sis/students/{$this->student->id}/analytics");

        $response->assertStatus(200)
                 ->assertJson([
                     'success' => true,
                     'message' => 'Student performance analytics retrieved successfully'
                 ])
                 ->assertJsonStructure([
                     'success',
                     'data' => [
                         'student',
                         'performance_metrics',
                         'trend_analysis',
                         'comparative_analysis',
                         'recommendations'
                     ],
                     'message',
                     'timestamp'
                 ]);
    }

    public function test_student_documents_retrieval()
    {
        // Get JWT token for the test user
        $token = JWTAuth::fromUser($this->user);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->getJson("/api/sis/students/{$this->student->id}/documents");

        $response->assertStatus(200)
                 ->assertJson([
                     'success' => true,
                     'message' => 'Student documents retrieved successfully'
                 ])
                 ->assertJsonStructure([
                     'success',
                     'data' => [
                         'student',
                         'portfolios',
                         'document_count',
                         'document_types'
                     ],
                     'message',
                     'timestamp'
                 ]);
    }

    public function test_certificate_generation()
    {
        // Get JWT token for the test user
        $token = JWTAuth::fromUser($this->user);

        // Add some test grades for the student
        Grade::create([
            'id' => 'test-grade-4',
            'student_id' => $this->student->id,
            'subject_id' => $this->subject->id,
            'class_id' => $this->class->id,
            'grade' => 95.0,
            'semester' => 1,
            'grade_type' => 'exam',
        ]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->postJson("/api/sis/students/{$this->student->id}/certificates/generate");

        $response->assertStatus(200)
                 ->assertJson([
                     'success' => true,
                     'message' => 'Certificate generated successfully'
                 ])
                 ->assertJsonStructure([
                     'success',
                     'data' => [
                         'student',
                         'certificate_type',
                         'issue_date',
                         'academic_info',
                         'achievement_level',
                         'certificate_number'
                     ],
                     'message',
                     'timestamp'
                 ]);
    }
}