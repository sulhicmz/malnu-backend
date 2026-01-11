<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\SchoolManagement\Student;
use App\Models\SchoolManagement\ClassModel;
use App\Models\SchoolManagement\Subject;
use App\Models\Grading\Grade;
use App\Models\User;
use Tymon\JWTAuth\Facades\JWTAuth;

class StudentInformationSystemTest extends TestCase
{
    protected $user;
    protected $token;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        $this->token = JWTAuth::fromUser($this->user);
    }

    public function test_gpa_calculation()
    {
        $class = ClassModel::factory()->create();
        $subject = Subject::factory()->create();
        $student = Student::factory()->create([
            'class_id' => $class->id,
            'status' => 'active'
        ]);

        Grade::factory()->create([
            'student_id' => $student->id,
            'subject_id' => $subject->id,
            'class_id' => $class->id,
            'grade' => 90,
            'semester' => 1
        ]);

        Grade::factory()->create([
            'student_id' => $student->id,
            'subject_id' => $subject->id,
            'class_id' => $class->id,
            'grade' => 85,
            'semester' => 1
        ]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->getJson('/api/school/students/' . $student->id . '/gpa');

        $response->assertStatus(200)
                 ->assertJsonStructure([
                     'success',
                     'data' => [
                         'gpa',
                         'total_grades',
                         'grades'
                     ],
                     'message'
                 ]);

        $data = $response->json('data');
        $this->assertEquals(4.0, $data['gpa']);
        $this->assertEquals(2, $data['total_grades']);
    }

    public function test_gpa_calculation_for_specific_semester()
    {
        $class = ClassModel::factory()->create();
        $subject = Subject::factory()->create();
        $student = Student::factory()->create([
            'class_id' => $class->id,
            'status' => 'active'
        ]);

        Grade::factory()->create([
            'student_id' => $student->id,
            'subject_id' => $subject->id,
            'class_id' => $class->id,
            'grade' => 90,
            'semester' => 1
        ]);

        Grade::factory()->create([
            'student_id' => $student->id,
            'subject_id' => $subject->id,
            'class_id' => $class->id,
            'grade' => 75,
            'semester' => 2
        ]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->getJson('/api/school/students/' . $student->id . '/gpa?semester=1');

        $response->assertStatus(200);

        $data = $response->json('data');
        $this->assertEquals(4.0, $data['gpa']);
        $this->assertEquals(1, $data['total_grades']);
    }

    public function test_transcript_generation()
    {
        $class = ClassModel::factory()->create();
        $subject = Subject::factory()->create();
        $student = Student::factory()->create([
            'class_id' => $class->id,
            'status' => 'active'
        ]);

        Grade::factory()->create([
            'student_id' => $student->id,
            'subject_id' => $subject->id,
            'class_id' => $class->id,
            'grade' => 90,
            'semester' => 1
        ]);

        Grade::factory()->create([
            'student_id' => $student->id,
            'subject_id' => $subject->id,
            'class_id' => $class->id,
            'grade' => 85,
            'semester' => 2
        ]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->getJson('/api/school/students/' . $student->id . '/transcript');

        $response->assertStatus(200)
                 ->assertJsonStructure([
                     'success',
                     'data' => [
                         'student',
                         'overall_gpa',
                         'semesters',
                         'generated_at'
                     ],
                     'message'
                 ]);

        $data = $response->json('data');
        $this->assertArrayHasKey('student', $data);
        $this->assertArrayHasKey('semesters', $data);
        $this->assertCount(2, $data['semesters']);
    }

    public function test_student_progress()
    {
        $class = ClassModel::factory()->create();
        $subject = Subject::factory()->create();
        $student = Student::factory()->create([
            'class_id' => $class->id,
            'status' => 'active'
        ]);

        Grade::factory()->create([
            'student_id' => $student->id,
            'subject_id' => $subject->id,
            'class_id' => $class->id,
            'grade' => 90,
            'semester' => 1
        ]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->getJson('/api/school/students/' . $student->id . '/progress');

        $response->assertStatus(200)
                 ->assertJsonStructure([
                     'success',
                     'data' => [
                         'average_grade',
                         'total_subjects',
                         'progress_by_subject'
                     ],
                     'message'
                 ]);
    }

    public function test_update_enrollment_status()
    {
        $class = ClassModel::factory()->create();
        $student = Student::factory()->create([
            'class_id' => $class->id,
            'status' => 'active'
        ]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->putJson('/api/school/students/' . $student->id . '/enrollment-status', [
            'status' => 'graduated'
        ]);

        $response->assertStatus(200);

        $this->assertDatabaseHas('students', [
            'id' => $student->id,
            'status' => 'graduated'
        ]);
    }

    public function test_assign_to_class()
    {
        $class1 = ClassModel::factory()->create(['name' => 'Class A']);
        $class2 = ClassModel::factory()->create(['name' => 'Class B']);
        $student = Student::factory()->create([
            'class_id' => $class1->id,
            'status' => 'active'
        ]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->putJson('/api/school/students/' . $student->id . '/class-assignment', [
            'class_id' => $class2->id
        ]);

        $response->assertStatus(200);

        $data = $response->json('data');
        $this->assertEquals($class2->id, $data['class_id']);
    }

    public function test_enrollment_history()
    {
        $class = ClassModel::factory()->create();
        $student = Student::factory()->create([
            'class_id' => $class->id,
            'status' => 'active'
        ]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->getJson('/api/school/students/' . $student->id . '/enrollment-history');

        $response->assertStatus(200)
                 ->assertJsonStructure([
                     'success',
                     'data' => [
                         'student_id',
                         'student_name',
                         'nisn',
                         'current_class',
                         'enrollment_date',
                         'current_status',
                         'enrollment_years'
                     ],
                     'message'
                 ]);
    }

    public function test_enrollment_statistics()
    {
        $class = ClassModel::factory()->create();
        Student::factory()->count(5)->create([
            'class_id' => $class->id,
            'status' => 'active'
        ]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->getJson('/api/school/enrollment/stats');

        $response->assertStatus(200)
                 ->assertJsonStructure([
                     'success',
                     'data' => [
                         'total_students',
                         'active_students',
                         'inactive_students',
                         'graduated_students',
                         'transferred_students',
                         'suspended_students',
                         'new_students_this_year',
                         'enrollment_rate'
                     ],
                     'message'
                 ]);

        $data = $response->json('data');
        $this->assertGreaterThanOrEqual(5, $data['total_students']);
        $this->assertGreaterThanOrEqual(5, $data['active_students']);
    }

    public function test_class_enrollment()
    {
        $class = ClassModel::factory()->create();
        Student::factory()->count(3)->create([
            'class_id' => $class->id,
            'status' => 'active'
        ]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->getJson('/api/school/classes/' . $class->id . '/enrollment');

        $response->assertStatus(200)
                 ->assertJsonStructure([
                     'success',
                     'data' => [
                         'class_id',
                         'total_students',
                         'active_students',
                         'students'
                     ],
                     'message'
                 ]);

        $data = $response->json('data');
        $this->assertEquals(3, $data['total_students']);
        $this->assertEquals(3, $data['active_students']);
    }

    public function test_student_performance()
    {
        $class = ClassModel::factory()->create();
        $subject = Subject::factory()->create();
        $student = Student::factory()->create([
            'class_id' => $class->id,
            'status' => 'active'
        ]);

        Grade::factory()->create([
            'student_id' => $student->id,
            'subject_id' => $subject->id,
            'class_id' => $class->id,
            'grade' => 90,
            'semester' => 1
        ]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->getJson('/api/school/students/' . $student->id . '/performance');

        $response->assertStatus(200)
                 ->assertJsonStructure([
                     'success',
                     'data' => [
                         'student',
                         'has_data',
                         'performance',
                         'subject_performance'
                     ],
                     'message'
                 ]);

        $data = $response->json('data');
        $this->assertTrue($data['has_data']);
        $this->assertEquals(90, $data['performance']['overall_average']);
    }

    public function test_class_performance()
    {
        $class = ClassModel::factory()->create();
        $subject = Subject::factory()->create();

        $student1 = Student::factory()->create([
            'class_id' => $class->id,
            'status' => 'active'
        ]);

        $student2 = Student::factory()->create([
            'class_id' => $class->id,
            'status' => 'active'
        ]);

        Grade::factory()->create([
            'student_id' => $student1->id,
            'subject_id' => $subject->id,
            'class_id' => $class->id,
            'grade' => 90,
            'semester' => 1
        ]);

        Grade::factory()->create([
            'student_id' => $student2->id,
            'subject_id' => $subject->id,
            'class_id' => $class->id,
            'grade' => 80,
            'semester' => 1
        ]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->getJson('/api/school/classes/' . $class->id . '/performance');

        $response->assertStatus(200)
                 ->assertJsonStructure([
                     'success',
                     'data' => [
                         'class',
                         'has_data',
                         'class_average',
                         'total_students',
                         'students_with_grades',
                         'top_performers',
                         'needs_attention'
                     ],
                     'message'
                 ]);

        $data = $response->json('data');
        $this->assertTrue($data['has_data']);
        $this->assertEquals(85, $data['class_average']);
    }

    public function test_comparative_analysis()
    {
        $class = ClassModel::factory()->create();
        $subject = Subject::factory()->create();

        $student = Student::factory()->create([
            'class_id' => $class->id,
            'status' => 'active'
        ]);

        $otherStudent = Student::factory()->create([
            'class_id' => $class->id,
            'status' => 'active'
        ]);

        Grade::factory()->create([
            'student_id' => $student->id,
            'subject_id' => $subject->id,
            'class_id' => $class->id,
            'grade' => 90,
            'semester' => 1
        ]);

        Grade::factory()->create([
            'student_id' => $otherStudent->id,
            'subject_id' => $subject->id,
            'class_id' => $class->id,
            'grade' => 80,
            'semester' => 1
        ]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->getJson('/api/school/students/' . $student->id . '/comparative-analysis');

        $response->assertStatus(200)
                 ->assertJsonStructure([
                     'success',
                     'data' => [
                         'student',
                         'has_data',
                         'student_performance',
                         'class_comparison',
                         'recommendation'
                     ],
                     'message'
                 ]);

        $data = $response->json('data');
        $this->assertTrue($data['has_data']);
        $this->assertGreaterThan(0, $data['class_comparison']['difference']);
    }
}
