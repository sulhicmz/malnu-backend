<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Mobile;

use App\Http\Controllers\Api\BaseController;
use App\Http\Controllers\Api\AttendanceController;
use Exception;
use Hyperf\Di\Annotation\Inject;
use Hyperf\HttpServer\Contract\RequestInterface;
use Hyperf\HttpServer\Contract\ResponseInterface;
use Psr\Container\ContainerInterface;

class TeacherMobileController extends BaseController
{
    #[Inject]
    private AttendanceController $attendanceController;

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
            
            $dashboard = [
                'teacher' => [
                    'id' => $user['id'],
                    'name' => $user['name'] ?? 'Teacher',
                ],
                'classes' => [],
                'today_schedule' => [],
                'pending_attendance' => [],
                'recent_activities' => [],
                'notifications' => [],
            ];
            
            return $this->successResponse($dashboard, 'Dashboard retrieved successfully');
        } catch (Exception $e) {
            return $this->serverErrorResponse($e->getMessage());
        }
    }

    public function getClasses()
    {
        try {
            $classes = [];
            
            return $this->successResponse($classes, 'Classes retrieved successfully');
        } catch (Exception $e) {
            return $this->serverErrorResponse($e->getMessage());
        }
    }

    public function getClassStudents(string $classId)
    {
        try {
            $students = [];
            
            return $this->successResponse($students, 'Class students retrieved successfully');
        } catch (Exception $e) {
            return $this->serverErrorResponse($e->getMessage());
        }
    }

    public function markAttendance()
    {
        try {
            $data = $this->request->all();
            
            $errors = [];
            if (empty($data['class_id'])) {
                $errors['class_id'] = ['The class_id field is required.'];
            }
            if (empty($data['date'])) {
                $errors['date'] = ['The date field is required.'];
            }
            if (empty($data['attendance'])) {
                $errors['attendance'] = ['The attendance field is required.'];
            } elseif (!is_array($data['attendance'])) {
                $errors['attendance'] = ['The attendance field must be an array.'];
            }

            if (!empty($errors)) {
                return $this->validationErrorResponse($errors);
            }

            return $this->successResponse(null, 'Attendance marked successfully');
        } catch (Exception $e) {
            return $this->serverErrorResponse($e->getMessage());
        }
    }

    public function getSchedule()
    {
        try {
            $schedule = [];
            
            return $this->successResponse($schedule, 'Schedule retrieved successfully');
        } catch (Exception $e) {
            return $this->serverErrorResponse($e->getMessage());
        }
    }
}
