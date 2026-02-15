<?php
 
declare(strict_types=1);
 
namespace App\Http\Controllers\Api;
 
use App\Http\Controllers\Api\BaseController;
use App\Services\AttendanceService;
use Hypervel\Http\Request;
use OpenApi\Annotations as OA;

/**
 * @OA\Tag(
 *     name="Attendance",
 *     description="Attendance management endpoints"
 * )
 */
class AttendanceController extends BaseController
{
    private AttendanceService $attendanceService;

    public function __construct(RequestInterface $request, AttendanceService $attendanceService)
    {
        parent::__construct($request);
        $this->attendanceService = $attendanceService;
    }

    /**
     * Mark attendance
     * 
     * @OA\Post(
     *     path="/api/attendance/mark",
     *     tags={"Attendance"},
     *     summary="Mark attendance",
     *     description="Mark attendance for a single student",
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"student_id","class_id","status","marked_by"},
     *             @OA\Property(property="student_id", type="string", format="uuid", example="123e4567-e89b-12d3-a456-426614174000"),
     *             @OA\Property(property="class_id", type="string", format="uuid", example="987e6543-e89b-12d3-a456-426614174999"),
     *             @OA\Property(property="status", type="string", enum={"present","absent","late","excused"}, example="present"),
     *             @OA\Property(property="marked_by", type="string", format="uuid", example="456e7890-e89b-12d3-a456-426614174888"),
     *             @OA\Property(property="attendance_date", type="string", format="date", example="2026-01-17"),
     *             @OA\Property(property="notes", type="string", example="Student arrived on time"),
     *             @OA\Property(property="check_in_time", type="string", format="time", example="08:00:00"),
     *             @OA\Property(property="check_out_time", type="string", format="time", example="15:00:00")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Attendance marked successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Attendance marked successfully"),
     *             @OA\Property(property="data", type="object")
     *         )
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Forbidden - teacher not authorized",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Teacher is not authorized to mark attendance for this class")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Validation error"),
     *             @OA\Property(property="errors", type="object")
     *         )
     *     )
     * )
     */
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
            return $this->validationErrorResponse($validator->errors());
        }

        if (!$this->attendanceService->validateTeacherAccess($data['marked_by'], $data['class_id'])) {
            return $this->forbiddenResponse('Teacher is not authorized to mark attendance for this class');
        }

        $attendance = $this->attendanceService->markAttendance($data);

        return $this->successResponse($attendance, 'Attendance marked successfully');
    }

    /**
     * Mark bulk attendance
     * 
     * @OA\Post(
     *     path="/api/attendance/bulk",
     *     tags={"Attendance"},
     *     summary="Mark bulk attendance",
     *     description="Mark attendance for multiple students in a class",
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"class_id","teacher_id","marked_by","attendances"},
     *             @OA\Property(property="class_id", type="string", format="uuid"),
     *             @OA\Property(property="teacher_id", type="string", format="uuid"),
     *             @OA\Property(property="marked_by", type="string", format="uuid"),
     *             @OA\Property(property="attendance_date", type="string", format="date", example="2026-01-17"),
     *             @OA\Property(property="attendances", type="array",
     *                 @OA\Items(
     *                     required={"student_id","status"},
     *                     @OA\Property(property="student_id", type="string", format="uuid"),
     *                     @OA\Property(property="status", type="string", enum={"present","absent","late","excused"}),
     *                     @OA\Property(property="notes", type="string"),
     *                     @OA\Property(property="check_in_time", type="string", format="time"),
     *                     @OA\Property(property="check_out_time", type="string", format="time")
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Bulk attendance marked successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Bulk attendance marked successfully"),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="count", type="integer", example=25),
     *                 @OA\Property(property="attendances", type="array")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Forbidden",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Teacher is not authorized to mark attendance for this class")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error"
     *     )
     * )
     */
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
            return $this->validationErrorResponse($validator->errors());
        }

        if (!$this->attendanceService->validateTeacherAccess($data['teacher_id'], $data['class_id'])) {
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

    /**
     * Get student attendance
     * 
     * @OA\Get(
     *     path="/api/attendance/student/{studentId}",
     *     tags={"Attendance"},
     *     summary="Get student attendance",
     *     description="Retrieve attendance records for a specific student",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="studentId",
     *         in="path",
     *         required=true,
     *         description="Student UUID",
     *         @OA\Schema(type="string", format="uuid")
     *     ),
     *     @OA\Parameter(
     *         name="start_date",
     *         in="query",
     *         required=false,
     *         description="Start date (YYYY-MM-DD)",
     *         @OA\Schema(type="string", format="date")
     *     ),
     *     @OA\Parameter(
     *         name="end_date",
     *         in="query",
     *         required=false,
     *         description="End date (YYYY-MM-DD)",
     *         @OA\Schema(type="string", format="date")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Student attendance retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Student attendance retrieved successfully"),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="attendance_records", type="array")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error"
     *     )
     * )
     */
    public function getStudentAttendance(RequestInterface $request, string $studentId)
    {
        $validator = validator($request->all(), [
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
        ]);

        if ($validator->fails()) {
            return $this->validationErrorResponse($validator->errors());
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
            return $this->validationErrorResponse($validator->errors());
        }

        $date = $request->input('date');

        $result = $this->attendanceService->getClassAttendance($classId, $date);

        return $this->successResponse($result, 'Class attendance retrieved successfully');
    }

    /**
     * Get attendance statistics
     * 
     * @OA\Get(
     *     path="/api/attendance/statistics/{studentId}",
     *     tags={"Attendance"},
     *     summary="Get attendance statistics",
     *     description="Retrieve attendance statistics for a student",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="studentId",
     *         in="path",
     *         required=true,
     *         description="Student UUID",
     *         @OA\Schema(type="string", format="uuid")
     *     ),
     *     @OA\Parameter(
     *         name="start_date",
     *         in="query",
     *         required=false,
     *         @OA\Schema(type="string", format="date")
     *     ),
     *     @OA\Parameter(
     *         name="end_date",
     *         in="query",
     *         required=false,
     *         @OA\Schema(type="string", format="date")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Attendance statistics retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Attendance statistics retrieved successfully"),
     *             @OA\Property(property="data", type="object")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error"
     *     )
     * )
     */
    public function getAttendanceStatistics(RequestInterface $request, string $studentId)
    {
        $validator = validator($request->all(), [
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
        ]);

        if ($validator->fails()) {
            return $this->validationErrorResponse($validator->errors());
        }

        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');

        $statistics = $this->attendanceService->calculateAttendanceStatistics($studentId, $startDate, $endDate);

        return $this->successResponse($statistics, 'Attendance statistics retrieved successfully');
    }

    /**
     * Get attendance report
     * 
     * @OA\Get(
     *     path="/api/attendance/report",
     *     tags={"Attendance"},
     *     summary="Get attendance report",
     *     description="Generate attendance report for a class",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="class_id",
     *         in="query",
     *         required=true,
     *         description="Class UUID",
     *         @OA\Schema(type="string", format="uuid")
     *     ),
     *     @OA\Parameter(
     *         name="start_date",
     *         in="query",
     *         required=true,
     *         description="Start date (YYYY-MM-DD)",
     *         @OA\Schema(type="string", format="date")
     *     ),
     *     @OA\Parameter(
     *         name="end_date",
     *         in="query",
     *         required=true,
     *         description="End date (YYYY-MM-DD)",
     *         @OA\Schema(type="string", format="date")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Attendance report generated successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Attendance report generated successfully"),
     *             @OA\Property(property="data", type="object")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error"
     *     )
     * )
     */
    public function getAttendanceReport(RequestInterface $request)
    {
        $validator = validator($request->all(), [
            'class_id' => 'required|string',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
        ]);

        if ($validator->fails()) {
            return $this->validationErrorResponse($validator->errors());
        }

        $classId = $request->input('class_id');
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');

        $report = $this->attendanceService->generateAttendanceReport($classId, $startDate, $endDate);

        return $this->successResponse($report, 'Attendance report generated successfully');
    }

    /**
     * Get chronic absentees
     * 
     * @OA\Get(
     *     path="/api/attendance/chronic-absentees",
     *     tags={"Attendance"},
     *     summary="Get chronic absentees",
     *     description="Retrieve list of students with chronic absence issues",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="days",
     *         in="query",
     *         required=false,
     *         description="Number of days threshold (default: 30)",
     *         @OA\Schema(type="integer", minimum=1, maximum=365, example=30)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Chronic absentees retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Chronic absentees retrieved successfully"),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="chronic_absentees", type="array")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error"
     *     )
     * )
     */
    public function getChronicAbsentees(RequestInterface $request)
    {
        $validator = validator($request->all(), [
            'days' => 'nullable|integer|min:1|max:365',
        ]);

        if ($validator->fails()) {
            return $this->validationErrorResponse($validator->errors());
        }

        $days = $request->input('days', 30);

        $chronicAbsentees = $this->attendanceService->detectChronicAbsenteeism();

        return $this->successResponse($chronicAbsentees, 'Chronic absentees retrieved successfully');
    }
}
