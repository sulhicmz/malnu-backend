<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\LMS\Course;
use App\Models\LMS\LearningPath;
use App\Models\LMS\LearningPathItem;
use App\Models\LMS\Enrollment;
use App\Models\LMS\CourseProgress;
use App\Models\LMS\Certificate;
use App\Models\SchoolManagement\Student;
use Hypervel\Support\Facades\DB;

class LMSService
{
    public function createCourse(array $data): Course
    {
        $courseCode = $data['code'] ?? $this->generateCourseCode();

        return Course::create([
            'virtual_class_id' => $data['virtual_class_id'] ?? null,
            'name' => $data['name'],
            'description' => $data['description'] ?? null,
            'code' => $courseCode,
            'level' => $data['level'] ?? 'beginner',
            'duration_hours' => $data['duration_hours'] ?? null,
            'is_published' => $data['is_published'] ?? false,
            'start_date' => $data['start_date'] ?? null,
            'end_date' => $data['end_date'] ?? null,
        ]);
    }

    public function getCourse(string $courseId): ?Course
    {
        return Course::with(['enrollments', 'certificates'])->find($courseId);
    }

    public function getAllCourses(bool $publishedOnly = true)
    {
        $query = Course::query();

        if ($publishedOnly) {
            $query->where('is_published', true);
        }

        return $query->withCount('enrollments')->get();
    }

    public function updateCourse(string $courseId, array $data): ?Course
    {
        $course = Course::find($courseId);

        if (!$course) {
            return null;
        }

        $course->update($data);
        return $course->fresh();
    }

    public function deleteCourse(string $courseId): bool
    {
        $course = Course::find($courseId);

        if (!$course) {
            return false;
        }

        return $course->delete();
    }

    public function createLearningPath(array $data): LearningPath
    {
        return LearningPath::create([
            'name' => $data['name'],
            'description' => $data['description'] ?? null,
            'is_active' => $data['is_active'] ?? true,
        ]);
    }

    public function addCourseToPath(string $pathId, string $courseId, int $sortOrder = 0, bool $isRequired = true): LearningPathItem
    {
        return LearningPathItem::create([
            'learning_path_id' => $pathId,
            'course_id' => $courseId,
            'sort_order' => $sortOrder,
            'is_required' => $isRequired,
        ]);
    }

    public function getLearningPath(string $pathId)
    {
        return LearningPath::with(['items.course'])->find($pathId);
    }

    public function getAllLearningPaths()
    {
        return LearningPath::where('is_active', true)
            ->with(['items.course'])
            ->orderBy('created_at', 'desc')
            ->get();
    }

    public function enrollStudent(string $courseId, string $studentId): Enrollment
    {
        $enrollment = Enrollment::create([
            'course_id' => $courseId,
            'student_id' => $studentId,
            'enrolled_at' => now(),
            'status' => 'active',
        ]);

        CourseProgress::create([
            'enrollment_id' => $enrollment->id,
            'total_lessons' => 0,
            'completed_lessons' => 0,
            'total_assignments' => 0,
            'completed_assignments' => 0,
            'total_quizzes' => 0,
            'completed_quizzes' => 0,
            'progress_percentage' => 0.00,
        ]);

        return $enrollment->load('progress');
    }

    public function getEnrollments(string $courseId = null)
    {
        $query = Enrollment::with(['student', 'course', 'progress']);

        if ($courseId) {
            $query->where('course_id', $courseId);
        }

        return $query->orderBy('enrolled_at', 'desc')->get();
    }

    public function getStudentEnrollments(string $studentId)
    {
        return Enrollment::with(['course', 'progress'])
            ->where('student_id', $studentId)
            ->orderBy('enrolled_at', 'desc')
            ->get();
    }

    public function updateProgress(string $enrollmentId, array $data): ?CourseProgress
    {
        $progress = CourseProgress::where('enrollment_id', $enrollmentId)->first();

        if (!$progress) {
            return null;
        }

        $progress->update($data);
        $progress->updateProgress();

        return $progress->fresh();
    }

    public function completeCourse(string $enrollmentId): ?Enrollment
    {
        $enrollment = Enrollment::with('progress')->find($enrollmentId);

        if (!$enrollment) {
            return null;
        }

        Db::transaction(function () use ($enrollment) {
            $enrollment->update([
                'status' => 'completed',
                'completed_at' => now(),
            ]);

            Certificate::create([
                'course_id' => $enrollment->course_id,
                'student_id' => $enrollment->student_id,
                'certificate_number' => $this->generateCertificateNumber(),
                'issued_at' => now(),
            ]);
        });

        return $enrollment->fresh();
    }

    public function getCertificates(string $courseId = null, string $studentId = null)
    {
        $query = Certificate::with(['course', 'student']);

        if ($courseId) {
            $query->where('course_id', $courseId);
        }

        if ($studentId) {
            $query->where('student_id', $studentId);
        }

        return $query->orderBy('issued_at', 'desc')->get();
    }

    private function generateCourseCode(): string
    {
        do {
            $code = 'CRS-' . strtoupper(substr(uniqid(), -6));
        } while (Course::where('code', $code)->exists());

        return $code;
    }

    private function generateCertificateNumber(): string
    {
        do {
            $number = 'CERT-' . date('Y') . '-' . strtoupper(substr(uniqid(), -8));
        } while (Certificate::where('certificate_number', $number)->exists());

        return $number;
    }
}
