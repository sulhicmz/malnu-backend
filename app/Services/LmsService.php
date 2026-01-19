<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\LMS\Course;
use App\Models\LMS\CourseEnrollment;
use App\Models\LMS\LearningProgress;
use App\Models\SchoolManagement\Student;
use Hyperf\DbConnection\Db;

class LmsService
{
    public function createCourse(array $data): Course
    {
        return Course::create([
            'subject_id'      => $data['subject_id'],
            'teacher_id'      => $data['teacher_id'],
            'code'            => $data['code'],
            'name'            => $data['name'],
            'description'       => $data['description'] ?? null,
            'credits'          => $data['credits'] ?? 0,
            'duration_weeks'  => $data['duration_weeks'] ?? 12,
            'level'           => $data['level'] ?? 'beginner',
            'status'           => 'draft',
            'start_date'       => $data['start_date'] ?? null,
            'end_date'         => $data['end_date'] ?? null,
            'max_students'    => $data['max_students'] ?? null,
            'allow_enrollment' => $data['allow_enrollment'] ?? true,
            'is_active'        => true,
        ]);
    }

    public function updateCourse(string $courseId, array $data): Course
    {
        $course = Course::findOrFail($courseId);
        $course->update(array_intersect_key($data, $course->getFillable()));
        return $course->fresh();
    }

    public function publishCourse(string $courseId): Course
    {
        $course = Course::findOrFail($courseId);
        
        if (!$course->allow_enrollment) {
            throw new \Exception('Course does not allow enrollment');
        }
        
        $course->status = 'published';
        $course->save();
        return $course->fresh();
    }

    public function archiveCourse(string $courseId): Course
    {
        $course = Course::findOrFail($courseId);
        $course->status = 'archived';
        $course->is_active = false;
        $course->save();
        return $course->fresh();
    }

    public function enrollStudent(string $courseId, string $studentId): CourseEnrollment
    {
        $course = Course::active()->findOrFail($courseId);
        $student = Student::findOrFail($studentId);
        
        $existingEnrollment = CourseEnrollment::where('course_id', $courseId)
                                            ->where('student_id', $studentId)
                                            ->first();
        
        if ($existingEnrollment) {
            throw new \Exception('Student is already enrolled in this course');
        }
        
        if ($course->is_full) {
            throw new \Exception('Course is full');
        }
        
        return CourseEnrollment::create([
            'course_id'           => $courseId,
            'student_id'          => $studentId,
            'enrollment_status'   => 'pending',
            'progress_percentage' => 0,
            'lessons_completed'   => 0,
            'total_lessons'       => $course->duration_weeks * 4,
        ]);
    }

    public function activateEnrollment(string $enrollmentId): CourseEnrollment
    {
        $enrollment = CourseEnrollment::findOrFail($enrollmentId);
        
        if ($enrollment->enrollment_status !== 'pending') {
            throw new \Exception('Enrollment cannot be activated from current status');
        }
        
        $enrollment->markAsActive();
        return $enrollment->fresh();
    }

    public function dropCourse(string $enrollmentId): CourseEnrollment
    {
        $enrollment = CourseEnrollment::findOrFail($enrollmentId);
        
        if ($enrollment->enrollment_status === 'completed') {
            throw new \Exception('Cannot drop a completed course');
        }
        
        $enrollment->enrollment_status = 'dropped';
        $enrollment->save();
        return $enrollment->fresh();
    }

    public function recordLearningProgress(string $enrollmentId, string $type, string $itemId, array $data): LearningProgress
    {
        $enrollment = CourseEnrollment::findOrFail($enrollmentId);
        
        $progress = LearningProgress::firstOrCreate([
            'course_enrollment_id' => $enrollmentId,
            $type . '_id'           => $itemId,
        ]);
        
        if (isset($data['status'])) {
            $progress->status = $data['status'];
        }
        
        if (isset($data['score'])) {
            $progress->score = $data['score'];
        }
        
        if (isset($data['time_spent_minutes'])) {
            $progress->time_spent_minutes = $data['time_spent_minutes'];
        }
        
        if ($data['status'] === 'completed' || $data['status'] === 'in_progress') {
            $progress->last_accessed_at = now();
        }
        
        $progress->save();
        
        $this->updateEnrollmentProgress($enrollment);
        
        return $progress->fresh();
    }

    public function updateEnrollmentProgress(CourseEnrollment $enrollment): void
    {
        $progress = $enrollment->learningProgress;
        $totalItems = $progress->count();
        
        if ($totalItems === 0) {
            return;
        }
        
        $completedItems = $progress->where('status', 'completed')->count();
        $inProgressItems = $progress->where('status', 'in_progress')->count();
        
        $progressPercentage = ($completedItems / $totalItems) * 100;
        $lessonsCompleted = $completedItems;
        
        $enrollment->updateProgress($progressPercentage, $lessonsCompleted);
    }

    public function completeCourse(string $enrollmentId, ?float $finalGrade = null): CourseEnrollment
    {
        $enrollment = CourseEnrollment::active()->findOrFail($enrollmentId);
        
        $enrollment->enrollment_status = 'completed';
        $enrollment->completed_at = now();
        
        if ($finalGrade !== null) {
            $enrollment->final_grade = $finalGrade;
        }
        
        $enrollment->updateProgress(100, $enrollment->total_lessons);
        $enrollment->save();
        
        return $enrollment->fresh();
    }

    public function getCourses(array $filters = [])
    {
        $query = Course::query();
        
        if (isset($filters['subject_id'])) {
            $query->bySubject($filters['subject_id']);
        }
        
        if (isset($filters['teacher_id'])) {
            $query->byTeacher($filters['teacher_id']);
        }
        
        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        }
        
        if (isset($filters['is_active'])) {
            $query->where('is_active', $filters['is_active']);
        }
        
        if (isset($filters['search'])) {
            $query->where(function ($q) use ($filters) {
                $q->where('name', 'like', '%' . $filters['search'] . '%')
                  ->orWhere('code', 'like', '%' . $filters['search'] . '%')
                  ->orWhere('description', 'like', '%' . $filters['search'] . '%');
            });
        }
        
        return $query->with(['subject', 'teacher', 'activeEnrollments'])
                       ->orderBy('created_at', 'desc')
                       ->paginate($filters['per_page'] ?? 20);
    }

    public function getCourseDetails(string $courseId): array
    {
        $course = Course::with(['subject', 'teacher', 'activeEnrollments.student'])
                         ->findOrFail($courseId);
        
        $enrollments = $course->activeEnrollments;
        $totalProgress = 0;
        
        foreach ($enrollments as $enrollment) {
            $totalProgress += $enrollment->progress_percentage;
        }
        
        $averageProgress = $enrollments->count() > 0 
            ? $totalProgress / $enrollments->count() 
            : 0;
        
        return [
            'course' => $course,
            'enrollments_count' => $enrollments->count(),
            'average_progress' => round($averageProgress, 2),
            'available_slots' => $course->available_slots,
            'is_full' => $course->is_full,
        ];
    }

    public function getStudentEnrollments(string $studentId, array $filters = [])
    {
        $query = CourseEnrollment::byStudent($studentId);
        
        if (isset($filters['status'])) {
            $query->where('enrollment_status', $filters['status']);
        }
        
        return $query->with(['course.subject', 'course.teacher', 'learningProgress'])
                       ->orderBy('enrolled_at', 'desc')
                       ->paginate($filters['per_page'] ?? 20);
    }

    public function getStudentProgress(string $enrollmentId): array
    {
        $enrollment = CourseEnrollment::with(['course', 'student', 'learningProgress'])
                                           ->findOrFail($enrollmentId);
        
        $progressByType = [
            'learning_materials' => ['total' => 0, 'completed' => 0, 'in_progress' => 0],
            'assignments'       => ['total' => 0, 'completed' => 0, 'in_progress' => 0],
            'quizzes'          => ['total' => 0, 'completed' => 0, 'in_progress' => 0],
        ];
        
        foreach ($enrollment->learningProgress as $progress) {
            $type = $progress->type;
            if ($type && isset($progressByType[$type])) {
                $progressByType[$type]['total']++;
                
                if ($progress->status === 'completed') {
                    $progressByType[$type]['completed']++;
                } elseif ($progress->status === 'in_progress') {
                    $progressByType[$type]['in_progress']++;
                }
            }
        }
        
        return [
            'enrollment' => $enrollment,
            'progress_by_type' => $progressByType,
        ];
    }

    public function getCourseAnalytics(string $courseId): array
    {
        $course = Course::findOrFail($courseId);
        
        $enrollments = CourseEnrollment::where('course_id', $courseId)
                                         ->with('learningProgress')
                                         ->get();
        
        $activeEnrollments = $enrollments->where('enrollment_status', 'active');
        $completedEnrollments = $enrollments->where('enrollment_status', 'completed');
        $droppedEnrollments = $enrollments->where('enrollment_status', 'dropped');
        
        $averageProgress = 0;
        if ($activeEnrollments->count() > 0) {
            $totalProgress = $activeEnrollments->sum('progress_percentage');
            $averageProgress = $totalProgress / $activeEnrollments->count();
        }
        
        $atRiskCount = $activeEnrollments->filter(function ($enrollment) {
            return $enrollment->progress_percentage < 30 && $enrollment->enrolled_at->diffInDays(now()) > 7;
        })->count();
        
        return [
            'course_id' => $courseId,
            'total_enrollments' => $enrollments->count(),
            'active_enrollments' => $activeEnrollments->count(),
            'completed_enrollments' => $completedEnrollments->count(),
            'dropped_enrollments' => $droppedEnrollments->count(),
            'average_progress' => round($averageProgress, 2),
            'at_risk_students' => $atRiskCount,
            'completion_rate' => $enrollments->count() > 0 
                ? round(($completedEnrollments->count() / $enrollments->count()) * 100, 2)
                : 0,
        ];
    }
}
