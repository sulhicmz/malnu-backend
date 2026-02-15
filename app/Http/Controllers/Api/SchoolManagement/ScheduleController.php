<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\SchoolManagement;

use App\Http\Controllers\Api\BaseController;
use App\Models\SchoolManagement\Schedule;
use App\Services\SchoolManagement\ScheduleConflictService;
use Exception;
use Hypervel\Http\Request;
use Hypervel\Http\Response;
use Psr\Container\ContainerInterface;

class ScheduleController extends BaseController
{
    protected ScheduleConflictService $conflictService;

    public function __construct(
        RequestInterface $request,
        ResponseInterface $response,
        ContainerInterface $container,
        ScheduleConflictService $conflictService
    ) {
        parent::__construct($request, $response, $container);
        $this->conflictService = $conflictService;
    }

    public function index()
    {
        try {
            $query = Schedule::with(['classSubject', 'classSubject.class', 'classSubject.subject', 'classSubject.teacher']);

            $classId = $this->request->query('class_id');
            $dayOfWeek = $this->request->query('day_of_week');
            $teacherId = $this->request->query('teacher_id');
            $roomId = $this->request->query('room');
            $page = (int) $this->request->query('page', 1);
            $limit = (int) $this->request->query('limit', 15);

            if ($classId) {
                $query->whereHas('classSubject', function ($q) use ($classId) {
                    $q->where('class_id', $classId);
                });
            }

            if ($dayOfWeek) {
                $query->where('day_of_week', $dayOfWeek);
            }

            if ($teacherId) {
                $query->whereHas('classSubject', function ($q) use ($teacherId) {
                    $q->where('teacher_id', $teacherId);
                });
            }

            if ($roomId) {
                $query->where('room', $roomId);
            }

            $schedules = $query->orderBy('day_of_week')->orderBy('start_time')->paginate($limit, ['*'], 'page', $page);

            return $this->successResponse($schedules, 'Schedules retrieved successfully');
        } catch (Exception $e) {
            return $this->serverErrorResponse($e->getMessage());
        }
    }

    public function show(string $id)
    {
        try {
            $schedule = Schedule::with(['classSubject', 'classSubject.class', 'classSubject.subject', 'classSubject.teacher'])
                ->find($id);

            if (! $schedule) {
                return $this->notFoundResponse('Schedule not found');
            }

            return $this->successResponse($schedule, 'Schedule retrieved successfully');
        } catch (Exception $e) {
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

            if (! empty($errors)) {
                return $this->validationErrorResponse($errors);
            }

            $conflicts = $this->conflictService->detectConflicts($data);
            if (! empty($conflicts)) {
                return $this->errorResponse('Schedule conflicts detected', 'SCHEDULE_CONFLICT', ['conflicts' => $conflicts], 409);
            }

            $schedule = new Schedule();
            $schedule->class_subject_id = $data['class_subject_id'];
            $schedule->day_of_week = (int) $data['day_of_week'];
            $schedule->start_time = $data['start_time'];
            $schedule->end_time = $data['end_time'];
            $schedule->room = $data['room'] ?? null;
            $schedule->save();

            return $this->successResponse($schedule->load(['classSubject', 'classSubject.class', 'classSubject.subject', 'classSubject.teacher']), 'Schedule created successfully', 201);
        } catch (Exception $e) {
            return $this->serverErrorResponse($e->getMessage());
        }
    }

    public function update(string $id)
    {
        try {
            $schedule = Schedule::find($id);
            if (! $schedule) {
                return $this->notFoundResponse('Schedule not found');
            }

            $data = $this->request->all();

            if (isset($data['class_subject_id'])) {
                $schedule->class_subject_id = $data['class_subject_id'];
            }
            if (isset($data['day_of_week'])) {
                $schedule->day_of_week = (int) $data['day_of_week'];
            }
            if (isset($data['start_time'])) {
                $schedule->start_time = $data['start_time'];
            }
            if (isset($data['end_time'])) {
                $schedule->end_time = $data['end_time'];
            }
            if (isset($data['room'])) {
                $schedule->room = $data['room'];
            }

            $conflicts = $this->conflictService->detectConflicts(array_merge($schedule->toArray(), $data), $id);
            if (! empty($conflicts)) {
                return $this->errorResponse('Schedule conflicts detected', 'SCHEDULE_CONFLICT', ['conflicts' => $conflicts], 409);
            }

            $schedule->save();

            return $this->successResponse($schedule->load(['classSubject', 'classSubject.class', 'classSubject.subject', 'classSubject.teacher']), 'Schedule updated successfully');
        } catch (Exception $e) {
            return $this->serverErrorResponse($e->getMessage());
        }
    }

    public function destroy(string $id)
    {
        try {
            $schedule = Schedule::find($id);
            if (! $schedule) {
                return $this->notFoundResponse('Schedule not found');
            }

            $schedule->delete();

            return $this->successResponse(null, 'Schedule deleted successfully');
        } catch (Exception $e) {
            return $this->serverErrorResponse($e->getMessage());
        }
    }
}
