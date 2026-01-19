<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\LMS\Course;
use App\Models\LMS\CourseEnrollment;
use App\Models\LMS\LearningProgress;
use App\Models\SchoolManagement\Student;
use App\Models\SchoolManagement\Teacher;
use App\Models\SchoolManagement\Subject;
use Hyperf\DbConnection\Db;

class LmsTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->createTestSubjects();
        $this->createTestTeachers();
        $this->createTestStudents();
    }

    private function createTestSubjects(): void
    {
        Subject::create([
            'id' => Db::raw('(UUID())'),
            'name' => 'Test Subject',
            'code' => 'SUB001',
            'description' => 'Test subject for LMS',
        ]);
    }

    private function createTestTeachers(): void
    {
        Teacher::create([
            'id' => Db::raw('(UUID())'),
            'user_id' => Db::raw('(UUID())'),
            'employee_id' => 'EMP001',
            'first_name' => 'John',
            'last_name' => 'Doe',
            'email' => 'john.doe@example.com',
            'phone' => '1234567890',
            'subject_id' => Subject::first()->id,
        ]);
    }

    private function createTestStudents(): void
    {
        Student::create([
            'id' => Db::raw('(UUID())'),
            'user_id' => Db::raw('(UUID())'),
            'nisn' => '1234567890',
            'first_name' => 'Jane',
            'last_name' => 'Smith',
            'class_id' => Db::raw('(UUID())'),
        ]);
    }

    public function test_create_course()
    {
        $teacher = Teacher::first();
        $subject = Subject::first();

        $courseData = [
            'subject_id' => $subject->id,
            'teacher_id' => $teacher->id,
            'code' => 'CS001',
            'name' => 'Introduction to Programming',
            'description' => 'Basic programming course',
            'credits' => 3,
            'duration_weeks' => 12,
            'level' => 'beginner',
        ];

        $this->post('/api/lms/courses', $courseData);
        $this->seeStatusCode(201);
        $this->seeJson([
            'success' => true,
            'message' => 'Course created successfully',
        ]);
        
        $this->assertDatabaseHas('courses', ['code' => 'CS001']);
    }

    public function test_publish_course()
    {
        $course = Course::create([
            'id' => Db::raw('(UUID())'),
            'subject_id' => Subject::first()->id,
            'teacher_id' => Teacher::first()->id,
            'code' => 'CS002',
            'name' => 'Test Course',
            'description' => 'Test description',
            'credits' => 2,
            'duration_weeks' => 8,
            'level' => 'intermediate',
            'status' => 'draft',
            'allow_enrollment' => true,
        ]);

        $this->post('/api/lms/courses/' . $course->id . '/publish');
        $this->seeStatusCode(200);
        $this->seeJson([
            'success' => true,
            'message' => 'Course published successfully',
        ]);
        
        $course->refresh();
        $this->assertEquals('published', $course->status);
    }

    public function test_enroll_student_in_course()
    {
        $course = Course::create([
            'id' => Db::raw('(UUID())'),
            'subject_id' => Subject::first()->id,
            'teacher_id' => Teacher::first()->id,
            'code' => 'CS003',
            'name' => 'Test Course for Enrollment',
            'description' => 'Test description',
            'credits' => 3,
            'duration_weeks' => 12,
            'level' => 'beginner',
            'status' => 'published',
            'allow_enrollment' => true,
            'max_students' => 30,
        ]);

        $student = Student::first();

        $enrollmentData = [
            'course_id' => $course->id,
            'student_id' => $student->id,
        ];

        $this->post('/api/lms/enroll', $enrollmentData);
        $this->seeStatusCode(201);
        $this->seeJson([
            'success' => true,
            'message' => 'Student enrolled successfully',
        ]);
        
        $this->assertDatabaseHas('course_enrollments', [
            'course_id' => $course->id,
            'student_id' => $student->id,
        ]);
    }

    public function test_activate_enrollment()
    {
        $course = Course::create([
            'id' => Db::raw('(UUID())'),
            'subject_id' => Subject::first()->id,
            'teacher_id' => Teacher::first()->id,
            'code' => 'CS004',
            'name' => 'Test Course',
            'description' => 'Test description',
            'credits' => 2,
            'duration_weeks' => 12,
            'level' => 'beginner',
            'status' => 'published',
            'allow_enrollment' => true,
        ]);

        $student = Student::first();
        $enrollment = CourseEnrollment::create([
            'id' => Db::raw('(UUID())'),
            'course_id' => $course->id,
            'student_id' => $student->id,
            'enrollment_status' => 'pending',
            'progress_percentage' => 0,
            'lessons_completed' => 0,
            'total_lessons' => 48,
        ]);

        $this->post('/api/lms/enrollments/' . $enrollment->id . '/activate');
        $this->seeStatusCode(200);
        $this->seeJson([
            'success' => true,
            'message' => 'Enrollment activated successfully',
        ]);
        
        $enrollment->refresh();
        $this->assertEquals('active', $enrollment->enrollment_status);
        $this->assertNotNull($enrollment->enrolled_at);
    }

    public function test_record_learning_progress()
    {
        $course = Course::create([
            'id' => Db::raw('(UUID())'),
            'subject_id' => Subject::first()->id,
            'teacher_id' => Teacher::first()->id,
            'code' => 'CS005',
            'name' => 'Test Course',
            'description' => 'Test description',
            'credits' => 2,
            'duration_weeks' => 12,
            'level' => 'beginner',
            'status' => 'published',
            'allow_enrollment' => true,
        ]);

        $student = Student::first();
        $enrollment = CourseEnrollment::create([
            'id' => Db::raw('(UUID())'),
            'course_id' => $course->id,
            'student_id' => $student->id,
            'enrollment_status' => 'active',
            'progress_percentage' => 0,
            'lessons_completed' => 0,
            'total_lessons' => 48,
            'enrolled_at' => now(),
        ]);

        $progressData = [
            'type' => 'learning_material',
            'item_id' => Db::raw('(UUID())'),
            'status' => 'in_progress',
            'time_spent_minutes' => 45,
        ];

        $this->post('/api/lms/enrollments/' . $enrollment->id . '/progress', $progressData);
        $this->seeStatusCode(200);
        $this->seeJson([
            'success' => true,
            'message' => 'Progress recorded successfully',
        ]);
        
        $this->assertDatabaseHas('learning_progress', [
            'course_enrollment_id' => $enrollment->id,
            'status' => 'in_progress',
        ]);
    }

    public function test_complete_course()
    {
        $course = Course::create([
            'id' => Db::raw('(UUID())'),
            'subject_id' => Subject::first()->id,
            'teacher_id' => Teacher::first()->id,
            'code' => 'CS006',
            'name' => 'Test Course',
            'description' => 'Test description',
            'credits' => 3,
            'duration_weeks' => 12,
            'level' => 'beginner',
            'status' => 'published',
            'allow_enrollment' => true,
        ]);

        $student = Student::first();
        $enrollment = CourseEnrollment::create([
            'id' => Db::raw('(UUID())'),
            'course_id' => $course->id,
            'student_id' => $student->id,
            'enrollment_status' => 'active',
            'progress_percentage' => 85.5,
            'lessons_completed' => 41,
            'total_lessons' => 48,
            'enrolled_at' => now()->subDays(60),
        ]);

        $completionData = [
            'final_grade' => 88.5,
        ];

        $this->post('/api/lms/enrollments/' . $enrollment->id . '/complete', $completionData);
        $this->seeStatusCode(200);
        $this->seeJson([
            'success' => true,
            'message' => 'Course completed successfully',
        ]);
        
        $enrollment->refresh();
        $this->assertEquals('completed', $enrollment->enrollment_status);
        $this->assertEquals(88.5, $enrollment->final_grade);
        $this->assertNotNull($enrollment->completed_at);
        $this->assertEquals(100, $enrollment->progress_percentage);
    }

    public function test_get_course_analytics()
    {
        $course = Course::create([
            'id' => Db::raw('(UUID())'),
            'subject_id' => Subject::first()->id,
            'teacher_id' => Teacher::first()->id,
            'code' => 'CS007',
            'name' => 'Test Course for Analytics',
            'description' => 'Test description',
            'credits' => 3,
            'duration_weeks' => 12,
            'level' => 'beginner',
            'status' => 'published',
            'allow_enrollment' => true,
        ]);

        $student1 = Student::first();
        $student2 = Student::create([
            'id' => Db::raw('(UUID())'),
            'user_id' => Db::raw('(UUID())'),
            'nisn' => '0987654321',
            'first_name' => 'Bob',
            'last_name' => 'Johnson',
            'class_id' => Db::raw('(UUID())'),
        ]);

        CourseEnrollment::create([
            'id' => Db::raw('(UUID())'),
            'course_id' => $course->id,
            'student_id' => $student1->id,
            'enrollment_status' => 'active',
            'progress_percentage' => 75.0,
            'lessons_completed' => 36,
            'total_lessons' => 48,
            'enrolled_at' => now(),
        ]);

        CourseEnrollment::create([
            'id' => Db::raw('(UUID())'),
            'course_id' => $course->id,
            'student_id' => $student2->id,
            'enrollment_status' => 'completed',
            'progress_percentage' => 100,
            'lessons_completed' => 48,
            'total_lessons' => 48,
            'enrolled_at' => now()->subDays(90),
            'completed_at' => now()->subDays(30),
        ]);

        $this->get('/api/lms/courses/' . $course->id . '/analytics');
        $this->seeStatusCode(200);
        $this->seeJson([
            'success' => true,
            'data' => [
                'course_id' => $course->id,
                'total_enrollments' => 2,
                'active_enrollments' => 1,
                'completed_enrollments' => 1,
                'average_progress' => 87.5,
                'at_risk_students' => 0,
                'completion_rate' => 50.0,
            ],
        ]);
    }

    public function test_get_student_enrollments()
    {
        $course1 = Course::create([
            'id' => Db::raw('(UUID())'),
            'subject_id' => Subject::first()->id,
            'teacher_id' => Teacher::first()->id,
            'code' => 'CS008',
            'name' => 'Student Enrollments Test Course 1',
            'description' => 'Test description',
            'credits' => 2,
            'duration_weeks' => 12,
            'level' => 'beginner',
            'status' => 'published',
            'allow_enrollment' => true,
        ]);

        $course2 = Course::create([
            'id' => Db::raw('(UUID())'),
            'subject_id' => Subject::first()->id,
            'teacher_id' => Teacher::first()->id,
            'code' => 'CS009',
            'name' => 'Student Enrollments Test Course 2',
            'description' => 'Test description',
            'credits' => 3,
            'duration_weeks' => 12,
            'level' => 'intermediate',
            'status' => 'published',
            'allow_enrollment' => true,
        ]);

        $student = Student::first();

        CourseEnrollment::create([
            'id' => Db::raw('(UUID())'),
            'course_id' => $course1->id,
            'student_id' => $student->id,
            'enrollment_status' => 'active',
            'progress_percentage' => 45.0,
            'lessons_completed' => 24,
            'total_lessons' => 48,
            'enrolled_at' => now()->subDays(30),
        ]);

        CourseEnrollment::create([
            'id' => Db::raw('(UUID())'),
            'course_id' => $course2->id,
            'student_id' => $student->id,
            'enrollment_status' => 'completed',
            'progress_percentage' => 100,
            'lessons_completed' => 48,
            'total_lessons' => 48,
            'enrolled_at' => now()->subDays(120),
            'completed_at' => now()->subDays(60),
        ]);

        $this->get('/api/lms/students/' . $student->id . '/enrollments');
        $this->seeStatusCode(200);
        $this->seeJson([
            'success' => true,
            'data' => $this->count(2),
        ]);
    }

    public function test_get_course_details()
    {
        $course = Course::create([
            'id' => Db::raw('(UUID())'),
            'subject_id' => Subject::first()->id,
            'teacher_id' => Teacher::first()->id,
            'code' => 'CS010',
            'name' => 'Course Details Test Course',
            'description' => 'Test description for course details',
            'credits' => 3,
            'duration_weeks' => 12,
            'level' => 'beginner',
            'status' => 'published',
            'allow_enrollment' => true,
        ]);

        $this->get('/api/lms/courses/' . $course->id);
        $this->seeStatusCode(200);
        $this->seeJson([
            'success' => true,
            'data' => [
                'course' => [
                    'id' => $course->id,
                    'code' => 'CS010',
                    'name' => 'Course Details Test Course',
                ],
                'enrollments_count' => 0,
                'average_progress' => 0,
                'available_slots' => null,
                'is_full' => false,
            ],
        ]);
    }

    public function test_course_not_found()
    {
        $nonExistentId = '00000000-0000-0000-0000-000000000001';

        $this->get('/api/lms/courses/' . $nonExistentId);
        $this->seeStatusCode(500);
        $this->seeJson([
            'success' => false,
            'message' => 'Failed to fetch course details',
        ]);
    }

    public function test_duplicate_course_code()
    {
        $teacher = Teacher::first();
        $subject = Subject::first();

        Course::create([
            'id' => Db::raw('(UUID())'),
            'subject_id' => $subject->id,
            'teacher_id' => $teacher->id,
            'code' => 'CS011',
            'name' => 'First Course',
            'description' => 'Test description',
            'credits' => 2,
            'duration_weeks' => 12,
            'level' => 'beginner',
            'status' => 'published',
            'allow_enrollment' => true,
        ]);

        $duplicateData = [
            'subject_id' => $subject->id,
            'teacher_id' => $teacher->id,
            'code' => 'CS011',
            'name' => 'Second Course',
            'description' => 'Test description',
            'credits' => 2,
            'duration_weeks' => 12,
            'level' => 'beginner',
        ];

        $this->post('/api/lms/courses', $duplicateData);
        $this->seeStatusCode(500);
    }

    public function test_course_capacity_limit()
    {
        $course = Course::create([
            'id' => Db::raw('(UUID())'),
            'subject_id' => Subject::first()->id,
            'teacher_id' => Teacher::first()->id,
            'code' => 'CS012',
            'name' => 'Capacity Test Course',
            'description' => 'Test description',
            'credits' => 2,
            'duration_weeks' => 12,
            'level' => 'beginner',
            'status' => 'published',
            'allow_enrollment' => true,
            'max_students' => 2,
        ]);

        $student1 = Student::first();
        $student2 = Student::create([
            'id' => Db::raw('(UUID())'),
            'user_id' => Db::raw('(UUID())'),
            'nisn' => '9876543210',
            'first_name' => 'Charlie',
            'last_name' => 'Brown',
            'class_id' => Db::raw('(UUID())'),
        ]);

        $student3 = Student::create([
            'id' => Db::raw('(UUID())'),
            'user_id' => Db::raw('(UUID())'),
            'nisn' => '8765432109',
            'first_name' => 'Diana',
            'last_name' => 'Prince',
            'class_id' => Db::raw('(UUID())'),
        ]);

        CourseEnrollment::create([
            'id' => Db::raw('(UUID())'),
            'course_id' => $course->id,
            'student_id' => $student1->id,
            'enrollment_status' => 'active',
            'progress_percentage' => 0,
            'lessons_completed' => 0,
            'total_lessons' => 48,
            'enrolled_at' => now(),
        ]);

        CourseEnrollment::create([
            'id' => Db::raw('(UUID())'),
            'course_id' => $course->id,
            'student_id' => $student2->id,
            'enrollment_status' => 'active',
            'progress_percentage' => 0,
            'lessons_completed' => 0,
            'total_lessons' => 48,
            'enrolled_at' => now(),
        ]);

        $enrollmentData = [
            'course_id' => $course->id,
            'student_id' => $student3->id,
        ];

        $this->post('/api/lms/enroll', $enrollmentData);
        $this->seeStatusCode(500);
        $this->seeJson([
            'success' => false,
            'message' => 'Course is full',
        ]);
    }
}
