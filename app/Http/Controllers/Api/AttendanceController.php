<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Services\AttendanceService;
use Hyperf\HttpServer\Contract\RequestInterface;
use Hyperf\HttpServer\Contract\ResponseInterface;
use Psr\Container\ContainerInterface;

class AttendanceController extends BaseController
{
    private AttendanceService $attendanceService;

    public function __construct(
        RequestInterface $request,
        ResponseInterface $response,
        ContainerInterface $container,
        AttendanceService $attendanceService
    ) {
        parent::__construct($request, $response, $container);
        $this->attendanceService = $attendanceService;
    }

    public function markAttendance(RequestInterface $request)
    {
        $data = $request->all();

        $validator = validator($data, [
            'student_id' => 'required|string',
            'class_id' => 'required|string',
            'status' => 'required|in:present,absent,late,excused',
            'marked_by' => 'required|string',
            'attendance_date' => 'nullable|date',
            'notes' => 'nullable|string|max:500',
            'check_in_time' => 'nullable|date_format:H:i:s',
            'check_out_time' => 'nullable|date_format:H:i:s',
        ]);

        if ($validator->fails()) {
            return $this->validationErrorResponse($validator->errors()->all());
        }

        if (! $this->attendanceService->validateTeacherAccess($data['marked_by'], $data['class_id'])) {
            return $this->forbiddenResponse('Teacher is not authorized to mark attendance for this class');
        }

        $attendance = $this->attendanceService->markAttendance($data);

        return $this->successResponse($attendance, 'Attendance marked successfully');
    }

    public function markBulkAttendance(RequestInterface $request)
    {
        $data = $request->all();

        $validator = validator($data, [
            'class_id' => 'required|string',
            'teacher_id' => 'required|string',
            'marked_by' => 'required|string',
            'attendance_date' => 'nullable|date',
            'attendances' => 'required|array',
            'attendances.*.student_id' => 'required|string',
            'attendances.*.status' => 'required|in:present,absent,late,excused',
            'attendances.*.notes' => 'nullable|string|max:500',
            'attendances.*.check_in_time' => 'nullable|date_format:H:i:s',
            'attendances.*.check_out_time' => 'nullable|date_format:H:i:s',
        ]);

        if ($validator->fails()) {
            return $this->validationErrorResponse($validator->errors()->all());
        }

        if (! $this->attendanceService->validateTeacherAccess($data['teacher_id'], $data['class_id'])) {
            return $this->forbiddenResponse('Teacher is not authorized to mark attendance for this class');
        }

        $records = $this->attendanceService->markBulkAttendance(
            $data['class_id'],
            $data['attendances'],
            $data['teacher_id'],
            $data['marked_by']
        );

        return $this->successResponse([
            'count' => count($records),
            'attendances' => $records,
        ], 'Bulk attendance marked successfully');
    }

    public function getStudentAttendance(RequestInterface $request, string $studentId)
    {
        $validator = validator($request->all(), [
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
        ]);

        if ($validator->fails()) {
            return $this->validationErrorResponse($validator->errors()->all());
        }

        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');

        $result = $this->attendanceService->getStudentAttendance($studentId, $startDate, $endDate);

        return $this->successResponse($result, 'Student attendance retrieved successfully');
    }

    public function getClassAttendance(RequestInterface $request, string $classId)
    {
        $validator = validator($request->all(), [
            'date' => 'nullable|date',
        ]);

        if ($validator->fails()) {
            return $this->validationErrorResponse($validator->errors()->all());
        }

        $date = $request->input('date');

        $result = $this->attendanceService->getClassAttendance($classId, $date);

        return $this->successResponse($result, 'Class attendance retrieved successfully');
    }

    public function getAttendanceStatistics(RequestInterface $request, string $studentId)
    {
        $validator = validator($request->all(), [
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
        ]);

        if ($validator->fails()) {
            return $this->validationErrorResponse($validator->errors()->all());
        }

        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');

        $statistics = $this->attendanceService->calculateAttendanceStatistics($studentId, $startDate, $endDate);

        return $this->successResponse($statistics, 'Attendance statistics retrieved successfully');
    }

    public function getAttendanceReport(RequestInterface $request)
    {
        $validator = validator($request->all(), [
            'class_id' => 'required|string',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
        ]);

        if ($validator->fails()) {
            return $this->validationErrorResponse($validator->errors()->all());
        }

        $classId = $request->input('class_id');
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');

        $report = $this->attendanceService->generateAttendanceReport($classId, $startDate, $endDate);

        return $this->successResponse($report, 'Attendance report generated successfully');
    }

    public function getChronicAbsentees(RequestInterface $request)
    {
        $validator = validator($request->all(), [
            'days' => 'nullable|integer|min:1|max:365',
        ]);

        if ($validator->fails()) {
            return $this->validationErrorResponse($validator->errors()->all());
        }

        $days = $request->input('days', 30);

        $chronicAbsentees = $this->attendanceService->detectChronicAbsenteeism();

        return $this->successResponse($chronicAbsentees, 'Chronic absentees retrieved successfully');
    }
}
