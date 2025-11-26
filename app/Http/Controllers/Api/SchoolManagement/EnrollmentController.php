<?php

namespace App\Http\Controllers\Api\SchoolManagement;

use App\Http\Controllers\Api\BaseController;
use App\Models\SchoolManagement\Student;
use App\Models\SchoolManagement\ClassModel;
use Hyperf\HttpServer\Contract\RequestInterface;
use Hyperf\HttpServer\Contract\ResponseInterface;
use Psr\Container\ContainerInterface;

class EnrollmentController extends BaseController
{
    public function __construct(
        RequestInterface $request,
        ResponseInterface $response,
        ContainerInterface $container
    ) {
        parent::__construct($request, $response, $container);
    }

    /**
     * Get student enrollment details
     */
    public function getEnrollmentDetails(string $studentId)
    {
        try {
            $student = Student::with(['class', 'user'])->find($studentId);

            if (!$student) {
                return $this->notFoundResponse('Student not found');
            }

            $enrollmentDetails = [
                'student' => $student,
                'enrollment_status' => $student->status,
                'enrollment_date' => $student->enrollment_date,
                'academic_year' => $this->getAcademicYear($student->enrollment_date),
                'current_class' => $student->class,
                'progression_history' => $this->getProgressionHistory($student)
            ];

            return $this->successResponse($enrollmentDetails, 'Enrollment details retrieved successfully');
        } catch (\Exception $e) {
            return $this->serverErrorResponse($e->getMessage());
        }
    }

    /**
     * Get academic year from date
     */
    private function getAcademicYear($enrollmentDate)
    {
        $year = date('Y', strtotime($enrollmentDate));
        $nextYear = $year + 1;
        return $year . '/' . $nextYear;
    }

    /**
     * Get student's class progression history
     */
    private function getProgressionHistory($student)
    {
        // In a real implementation, this would track all class changes over time
        // For now, we'll return the current class as the history
        return [
            [
                'class' => $student->class,
                'academic_year' => $this->getAcademicYear($student->enrollment_date),
                'status' => 'current',
                'start_date' => $student->enrollment_date
            ]
        ];
    }

    /**
     * Update enrollment status
     */
    public function updateEnrollmentStatus(string $studentId)
    {
        try {
            $student = Student::find($studentId);

            if (!$student) {
                return $this->notFoundResponse('Student not found');
            }

            $data = $this->request->all();

            // Validate status
            $validStatuses = ['active', 'inactive', 'graduated', 'transferred', 'suspended'];
            if (isset($data['status']) && !in_array($data['status'], $validStatuses)) {
                return $this->validationErrorResponse([
                    'status' => ['Status must be one of: ' . implode(', ', $validStatuses)]
                ]);
            }

            $student->update($data);

            return $this->successResponse($student, 'Enrollment status updated successfully');
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 'ENROLLMENT_UPDATE_ERROR', null, 400);
        }
    }

    /**
     * Assign student to a class
     */
    public function assignToClass(string $studentId)
    {
        try {
            $student = Student::find($studentId);

            if (!$student) {
                return $this->notFoundResponse('Student not found');
            }

            $data = $this->request->all();

            if (empty($data['class_id'])) {
                return $this->validationErrorResponse([
                    'class_id' => ['Class ID is required']
                ]);
            }

            $class = ClassModel::find($data['class_id']);
            if (!$class) {
                return $this->notFoundResponse('Class not found');
            }

            $student->update(['class_id' => $data['class_id']]);

            return $this->successResponse([
                'student' => $student->refresh(),
                'assigned_class' => $class
            ], 'Student assigned to class successfully');
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 'CLASS_ASSIGNMENT_ERROR', null, 400);
        }
    }

    /**
     * Get all students in an academic year
     */
    public function getStudentsByAcademicYear(string $academicYear)
    {
        try {
            $students = Student::with(['class', 'user'])
                ->whereHas('class', function($query) use ($academicYear) {
                    $query->where('academic_year', $academicYear);
                })
                ->get();

            $studentList = $students->map(function($student) {
                return [
                    'id' => $student->id,
                    'name' => $student->name,
                    'nisn' => $student->nisn,
                    'class' => $student->class,
                    'status' => $student->status,
                    'enrollment_date' => $student->enrollment_date
                ];
            });

            return $this->successResponse($studentList, 'Students retrieved successfully');
        } catch (\Exception $e) {
            return $this->serverErrorResponse($e->getMessage());
        }
    }

    /**
     * Get enrollment statistics
     */
    public function getEnrollmentStats()
    {
        try {
            $totalStudents = Student::count();
            $activeStudents = Student::where('status', 'active')->count();
            $inactiveStudents = Student::where('status', 'inactive')->count();
            $graduatedStudents = Student::where('status', 'graduated')->count();
            $transferredStudents = Student::where('status', 'transferred')->count();

            $stats = [
                'total_students' => $totalStudents,
                'active_students' => $activeStudents,
                'inactive_students' => $inactiveStudents,
                'graduated_students' => $graduatedStudents,
                'transferred_students' => $transferredStudents,
                'enrollment_rate' => $totalStudents > 0 ? round(($activeStudents / $totalStudents) * 100, 2) : 0
            ];

            return $this->successResponse($stats, 'Enrollment statistics retrieved successfully');
        } catch (\Exception $e) {
            return $this->serverErrorResponse($e->getMessage());
        }
    }
}