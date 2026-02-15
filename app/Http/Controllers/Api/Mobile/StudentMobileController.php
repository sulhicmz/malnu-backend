<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Mobile;

use App\Http\Controllers\Api\BaseController;
use App\Models\SchoolManagement\Student;
use App\Services\GPACalculationService;
use Exception;
use Hypervel\Support\Annotation\Inject;
use Hypervel\Http\Request;
use Hypervel\Http\Response;
use Psr\Container\ContainerInterface;

class StudentMobileController extends BaseController
{
    #[Inject]
    private GPACalculationService $gpaService;

    public function __construct(
        RequestInterface $request,
        ResponseInterface $response,
        ContainerInterface $container
    ) {
        parent::__construct($request, $response, $container);
    }

    public function getDashboard()
    {
        try {
            $user = $this->request->getAttribute('user');
            
            $student = Student::where('user_id', $user['id'])->first();
            
            if (!$student) {
                return $this->notFoundResponse('Student profile not found');
            }

            $dashboard = [
                'student' => [
                    'id' => $student->id,
                    'name' => $student->name,
                    'nisn' => $student->nisn,
                    'class' => $student->class->name ?? null,
                    'status' => $student->status,
                ],
                'gpa' => $this->gpaService->calculate($student->id),
                'attendance_summary' => [
                    'present' => 0,
                    'absent' => 0,
                    'late' => 0,
                ],
                'upcoming_assignments' => [],
                'recent_grades' => [],
                'notifications' => [],
            ];

            return $this->successResponse($dashboard, 'Dashboard retrieved successfully');
        } catch (Exception $e) {
            return $this->serverErrorResponse($e->getMessage());
        }
    }

    public function getGrades()
    {
        try {
            $user = $this->request->getAttribute('user');
            
            $student = Student::where('user_id', $user['id'])->first();
            
            if (!$student) {
                return $this->notFoundResponse('Student profile not found');
            }

            $grades = [];
            
            return $this->successResponse($grades, 'Grades retrieved successfully');
        } catch (Exception $e) {
            return $this->serverErrorResponse($e->getMessage());
        }
    }

    public function getAssignments()
    {
        try {
            $user = $this->request->getAttribute('user');
            
            $student = Student::where('user_id', $user['id'])->first();
            
            if (!$student) {
                return $this->notFoundResponse('Student profile not found');
            }

            $assignments = [];
            
            return $this->successResponse($assignments, 'Assignments retrieved successfully');
        } catch (Exception $e) {
            return $this->serverErrorResponse($e->getMessage());
        }
    }

    public function getSchedule()
    {
        try {
            $user = $this->request->getAttribute('user');
            
            $student = Student::where('user_id', $user['id'])->first();
            
            if (!$student) {
                return $this->notFoundResponse('Student profile not found');
            }

            $schedule = [];
            
            return $this->successResponse($schedule, 'Schedule retrieved successfully');
        } catch (Exception $e) {
            return $this->serverErrorResponse($e->getMessage());
        }
    }

    public function getAttendance()
    {
        try {
            $user = $this->request->getAttribute('user');
            
            $student = Student::where('user_id', $user['id'])->first();
            
            if (!$student) {
                return $this->notFoundResponse('Student profile not found');
            }

            $attendance = [];
            
            return $this->successResponse($attendance, 'Attendance retrieved successfully');
        } catch (Exception $e) {
            return $this->serverErrorResponse($e->getMessage());
        }
    }
}
