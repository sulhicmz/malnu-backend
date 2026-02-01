<?php

declare(strict_types=1);

namespace Tests\Feature;

use Tests\TestCase;
use App\Services\LMSService;
use App\Models\LMS\Course;
use App\Models\LMS\LearningPath;
use App\Models\LMS\Enrollment;
use App\Models\LMS\CourseProgress;
use App\Models\SchoolManagement\Student;

class LMSTest extends TestCase
{
    private LMSService $lmsService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->lmsService = $this->app->get(LMSService::class);
    }

    public function test_create_course(): void
    {
        $data = [
            'name' => 'Introduction to Mathematics',
            'description' => 'Basic mathematics course for beginners',
            'level' => 'beginner',
            'duration_hours' => 40,
            'is_published' => true,
        ];

        $course = $this->lmsService->createCourse($data);

        $this->assertNotNull($course);
        $this->assertEquals('Introduction to Mathematics', $course->name);
        $this->assertEquals('beginner', $course->level);
        $this->assertDatabaseHas('courses', [
            'name' => 'Introduction to Mathematics',
            'code' => $course->code,
        ]);
    }

    public function test_get_course(): void
    {
        $course = Course::first();

        if (!$course) {
            $this->markTestSkipped('No course data available');
            return;
        }

        $retrieved = $this->lmsService->getCourse($course->id);

        $this->assertNotNull($retrieved);
        $this->assertEquals($course->id, $retrieved->id);
    }

    public function test_get_all_courses(): void
    {
        $courses = $this->lmsService->getAllCourses();

        $this->assertIsArray($courses);
        $this->assertIsObject($courses);
    }

    public function test_update_course(): void
    {
        $course = Course::first();

        if (!$course) {
            $this->markTestSkipped('No course data available');
            return;
        }

        $updatedData = [
            'name' => 'Updated Course Name',
            'level' => 'intermediate',
        ];

        $updated = $this->lmsService->updateCourse($course->id, $updatedData);

        $this->assertNotNull($updated);
        $this->assertEquals('Updated Course Name', $updated->name);
        $this->assertEquals('intermediate', $updated->level);
    }

    public function test_create_learning_path(): void
    {
        $data = [
            'name' => 'Mathematics Learning Path',
            'description' => 'Complete mathematics curriculum',
            'is_active' => true,
        ];

        $path = $this->lmsService->createLearningPath($data);

        $this->assertNotNull($path);
        $this->assertEquals('Mathematics Learning Path', $path->name);
        $this->assertDatabaseHas('learning_paths', [
            'name' => 'Mathematics Learning Path',
        ]);
    }

    public function test_add_course_to_path(): void
    {
        $path = LearningPath::first();
        $course = Course::first();

        if (!$path || !$course) {
            $this->markTestSkipped('No path or course data available');
            return;
        }

        $pathItem = $this->lmsService->addCourseToPath($path->id, $course->id, 0, true);

        $this->assertNotNull($pathItem);
        $this->assertEquals($path->id, $pathItem->learning_path_id);
        $this->assertEquals($course->id, $pathItem->course_id);
    }

    public function test_enroll_student(): void
    {
        $course = Course::first();
        $student = Student::first();

        if (!$course || !$student) {
            $this->markTestSkipped('No course or student data available');
            return;
        }

        $enrollment = $this->lmsService->enrollStudent($course->id, $student->id);

        $this->assertNotNull($enrollment);
        $this->assertEquals($course->id, $enrollment->course_id);
        $this->assertEquals($student->id, $enrollment->student_id);
        $this->assertEquals('active', $enrollment->status);
        $this->assertDatabaseHas('enrollments', [
            'course_id' => $course->id,
            'student_id' => $student->id,
        ]);
    }

    public function test_get_student_enrollments(): void
    {
        $student = Student::first();

        if (!$student) {
            $this->markTestSkipped('No student data available');
            return;
        }

        $enrollments = $this->lmsService->getStudentEnrollments($student->id);

        $this->assertIsArray($enrollments);
        $this->assertIsObject($enrollments);
    }

    public function test_update_progress(): void
    {
        $progress = CourseProgress::first();

        if (!$progress) {
            $this->markTestSkipped('No progress data available');
            return;
        }

        $updateData = [
            'completed_lessons' => 5,
            'completed_assignments' => 2,
            'completed_quizzes' => 1,
        ];

        $updated = $this->lmsService->updateProgress($progress->enrollment_id, $updateData);

        $this->assertNotNull($updated);
        $this->assertEquals(5, $updated->completed_lessons);
    }

    public function test_complete_course(): void
    {
        $enrollment = Enrollment::where('status', 'active')->first();

        if (!$enrollment) {
            $this->markTestSkipped('No active enrollment available');
            return;
        }

        $completed = $this->lmsService->completeCourse($enrollment->id);

        $this->assertNotNull($completed);
        $this->assertEquals('completed', $completed->status);
        $this->assertNotNull($completed->completed_at);
        $this->assertDatabaseHas('certificates', [
            'course_id' => $completed->course_id,
            'student_id' => $completed->student_id,
        ]);
    }

    public function test_get_certificates(): void
    {
        $certificates = $this->lmsService->getCertificates();

        $this->assertIsArray($certificates);
        $this->assertIsObject($certificates);
    }
}
