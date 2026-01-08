<?php

namespace App\Http\Controllers\Api\SchoolManagement;

use App\Http\Controllers\Api\BaseController;
use App\Services\TimetableService;
use Hyperf\HttpServer\Contract\RequestInterface;
use Hyperf\HttpServer\Contract\ResponseInterface;
use Psr\Container\ContainerInterface;

class TimetableController extends BaseController
{
    protected TimetableService $timetableService;

    public function __construct(
        RequestInterface $request,
        ResponseInterface $response,
        ContainerInterface $container,
        TimetableService $timetableService
    ) {
        parent::__construct($request, $response, $container);
        $this->timetableService = $timetableService;
    }

    public function generate()
    {
        try {
            $data = $this->request->all();

            if (empty($data['class_id']) && empty($data['teacher_id'])) {
                return $this->validationErrorResponse(['general' => ['Either class_id or teacher_id is required']]);
            }

            $schedules = $this->timetableService->generateTimetable($data);

            return $this->successResponse($schedules, 'Timetable generated successfully');
        } catch (\Exception $e) {
            return $this->serverErrorResponse($e->getMessage());
        }
    }

    public function validate()
    {
        try {
            $data = $this->request->all();

            if (empty($data['class_subject_id']) && empty($data['id'])) {
                return $this->validationErrorResponse(['schedule_data' => ['Schedule data is required for validation']]);
            }

            $validation = $this->timetableService->validateSchedule($data);

            return $this->successResponse($validation, 'Schedule validation completed');
        } catch (\Exception $e) {
            return $this->serverErrorResponse($e->getMessage());
        }
    }

    public function detectConflicts()
    {
        try {
            $data = $this->request->all();

            if (empty($data['class_subject_id']) && empty($data['id'])) {
                return $this->validationErrorResponse(['schedule_data' => ['Schedule data is required for conflict detection']]);
            }

            $conflicts = $this->timetableService->detectConflicts($data);

            return $this->successResponse($conflicts, 'Conflict detection completed');
        } catch (\Exception $e) {
            return $this->serverErrorResponse($e->getMessage());
        }
    }

    public function store()
    {
        try {
            $data = $this->request->all();

            $requiredFields = ['class_subject_id', 'day_of_week', 'start_time', 'end_time'];
            $errors = [];

            foreach ($requiredFields as $field) {
                if (empty($data[$field])) {
                    $errors[$field] = ["The {$field} field is required."];
                }
            }

            if (!empty($errors)) {
                return $this->validationErrorResponse($errors);
            }

            $schedule = $this->timetableService->createSchedule($data);

            return $this->successResponse($schedule, 'Schedule created successfully', 201);
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 'SCHEDULE_CREATION_ERROR', null, 400);
        }
    }

    public function update(string $id)
    {
        try {
            $data = $this->request->all();

            $schedule = $this->timetableService->updateSchedule($id, $data);

            return $this->successResponse($schedule, 'Schedule updated successfully');
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 'SCHEDULE_UPDATE_ERROR', null, 400);
        }
    }

    public function destroy(string $id)
    {
        try {
            $this->timetableService->deleteSchedule($id);

            return $this->successResponse(null, 'Schedule deleted successfully');
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 'SCHEDULE_DELETION_ERROR', null, 400);
        }
    }

    public function getClassSchedule(string $classId)
    {
        try {
            $filters = [
                'day_of_week' => $this->request->query('day_of_week')
            ];

            $schedules = $this->timetableService->getScheduleByClass($classId, $filters);

            return $this->successResponse($schedules, 'Class schedule retrieved successfully');
        } catch (\Exception $e) {
            return $this->serverErrorResponse($e->getMessage());
        }
    }

    public function getTeacherSchedule(string $teacherId)
    {
        try {
            $filters = [
                'day_of_week' => $this->request->query('day_of_week')
            ];

            $schedules = $this->timetableService->getScheduleByTeacher($teacherId, $filters);

            return $this->successResponse($schedules, 'Teacher schedule retrieved successfully');
        } catch (\Exception $e) {
            return $this->serverErrorResponse($e->getMessage());
        }
    }

    public function getAvailableSlots()
    {
        try {
            $constraints = [
                'day_of_week' => (int) $this->request->query('day_of_week', 1),
                'class_id' => $this->request->query('class_id'),
                'teacher_id' => $this->request->query('teacher_id')
            ];

            $availableSlots = $this->timetableService->findAvailableSlots($constraints);

            return $this->successResponse($availableSlots, 'Available slots retrieved successfully');
        } catch (\Exception $e) {
            return $this->serverErrorResponse($e->getMessage());
        }
    }
}
