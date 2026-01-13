<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Hostel\Hostel;
use App\Models\Hostel\Room;
use App\Models\Hostel\RoomAssignment;
use App\Models\Hostel\MaintenanceRequest;
use App\Models\Hostel\Visitor;
use App\Models\Hostel\MealPlan;
use App\Models\Hostel\BoardingAttendance;
use App\Models\Hostel\HealthRecord;
use App\Models\Hostel\Incident;
use Exception;
use Carbon\Carbon;

class HostelManagementService
{
    public function createHostel(array $data): Hostel
    {
        return Hostel::create($data);
    }

    public function getHostel(string $id): ?Hostel
    {
        return Hostel::find($id);
    }

    public function updateHostel(string $id, array $data): bool
    {
        $hostel = Hostel::find($id);
        if (!$hostel) {
            return false;
        }
        return $hostel->update($data);
    }

    public function deleteHostel(string $id): bool
    {
        $hostel = Hostel::find($id);
        if (!$hostel) {
            return false;
        }
        return $hostel->delete();
    }

    public function createRoom(array $data): Room
    {
        $room = Room::create($data);
        $hostel = Hostel::find($data['hostel_id']);
        if ($hostel) {
            $hostel->increment('current_occupancy');
        }
        return $room;
    }

    public function getRoom(string $id): ?Room
    {
        return Room::find($id);
    }

    public function updateRoom(string $id, array $data): bool
    {
        $room = Room::find($id);
        if (!$room) {
            return false;
        }
        return $room->update($data);
    }

    public function deleteRoom(string $id): bool
    {
        $room = Room::find($id);
        if (!$room) {
            return false;
        }
        $hostel = $room->hostel;
        $result = $room->delete();
        if ($result && $hostel) {
            $hostel->decrement('current_occupancy', $room->current_occupancy);
        }
        return $result;
    }

    public function assignStudentToRoom(array $data): RoomAssignment
    {
        $hostel = Hostel::find($data['hostel_id']);
        $room = Room::find($data['room_id']);

        if (!$hostel || !$room) {
            throw new Exception('Hostel or room not found');
        }

        if ($room->current_occupancy >= $room->capacity) {
            throw new Exception('Room is at full capacity');
        }

        $assignment = RoomAssignment::create($data);
        
        $room->increment('current_occupancy');
        $hostel->increment('current_occupancy');

        return $assignment;
    }

    public function updateRoomAssignment(string $id, array $data): bool
    {
        $assignment = RoomAssignment::find($id);
        if (!$assignment) {
            return false;
        }
        return $assignment->update($data);
    }

    public function checkoutStudentFromRoom(string $assignmentId): bool
    {
        $assignment = RoomAssignment::find($assignmentId);
        if (!$assignment) {
            return false;
        }

        $room = $assignment->room;
        $hostel = $assignment->hostel;

        $result = $assignment->update([
            'status' => 'inactive',
            'checkout_date' => Carbon::now()->toDateString()
        ]);

        if ($result && $room) {
            $room->decrement('current_occupancy');
        }
        if ($result && $hostel) {
            $hostel->decrement('current_occupancy');
        }

        return $result;
    }

    public function createMaintenanceRequest(array $data): MaintenanceRequest
    {
        return MaintenanceRequest::create($data);
    }

    public function updateMaintenanceRequest(string $id, array $data): bool
    {
        $request = MaintenanceRequest::find($id);
        if (!$request) {
            return false;
        }
        return $request->update($data);
    }

    public function resolveMaintenanceRequest(string $id, array $resolutionData): bool
    {
        $request = MaintenanceRequest::find($id);
        if (!$request) {
            return false;
        }
        return $request->update([
            'status' => 'resolved',
            'resolution_notes' => $resolutionData['resolution_notes'],
            'resolved_at' => Carbon::now()->toDateString(),
            'resolved_by' => $resolutionData['resolved_by']
        ]);
    }

    public function createVisitor(array $data): Visitor
    {
        return Visitor::create(array_merge($data, [
            'visit_date' => Carbon::now()->toDateTimeString(),
            'status' => 'pending'
        ]));
    }

    public function approveVisitor(string $id, string $approvedBy): bool
    {
        $visitor = Visitor::find($id);
        if (!$visitor) {
            return false;
        }
        return $visitor->update([
            'status' => 'approved',
            'approved_by' => $approvedBy
        ]);
    }

    public function checkInVisitor(string $id): bool
    {
        $visitor = Visitor::find($id);
        if (!$visitor) {
            return false;
        }
        return $visitor->update([
            'status' => 'checked_in',
            'check_in_time' => Carbon::now()->toDateTimeString()
        ]);
    }

    public function checkOutVisitor(string $id): bool
    {
        $visitor = Visitor::find($id);
        if (!$visitor) {
            return false;
        }
        return $visitor->update([
            'status' => 'checked_out',
            'check_out_time' => Carbon::now()->toDateTimeString()
        ]);
    }

    public function createMealPlan(array $data): MealPlan
    {
        return MealPlan::create($data);
    }

    public function updateMealPlan(string $id, array $data): bool
    {
        $plan = MealPlan::find($id);
        if (!$plan) {
            return false;
        }
        return $plan->update($data);
    }

    public function createAttendanceRecord(array $data): BoardingAttendance
    {
        return BoardingAttendance::create($data);
    }

    public function checkInStudent(array $data): BoardingAttendance
    {
        return BoardingAttendance::create(array_merge($data, [
            'check_in_time' => Carbon::now()->toDateTimeString(),
            'status' => 'present'
        ]));
    }

    public function checkOutStudent(string $attendanceId): bool
    {
        $attendance = BoardingAttendance::find($attendanceId);
        if (!$attendance) {
            return false;
        }
        return $attendance->update([
            'check_out_time' => Carbon::now()->toDateTimeString()
        ]);
    }

    public function markStudentOnLeave(array $data): bool
    {
        return BoardingAttendance::create(array_merge($data, [
            'status' => 'on_leave'
        ])) ? true : false;
    }

    public function createHealthRecord(array $data): HealthRecord
    {
        return HealthRecord::create($data);
    }

    public function updateHealthRecord(string $id, array $data): bool
    {
        $record = HealthRecord::find($id);
        if (!$record) {
            return false;
        }
        return $record->update($data);
    }

    public function createIncident(array $data): Incident
    {
        return Incident::create($data);
    }

    public function updateIncident(string $id, array $data): bool
    {
        $incident = Incident::find($id);
        if (!$incident) {
            return false;
        }
        return $incident->update($data);
    }

    public function resolveIncident(string $id, array $resolutionData): bool
    {
        $incident = Incident::find($id);
        if (!$incident) {
            return false;
        }
        return $incident->update([
            'status' => 'resolved',
            'action_taken' => $resolutionData['action_taken'] ?? null,
            'disciplinary_action' => $resolutionData['disciplinary_action'] ?? null,
            'resolved_by' => $resolutionData['resolved_by'],
            'resolved_at' => Carbon::now()->toDateString()
        ]);
    }

    public function getHostelOccupancy(string $hostelId): array
    {
        $hostel = Hostel::find($hostelId);
        if (!$hostel) {
            return [];
        }

        $rooms = Room::where('hostel_id', $hostelId)
            ->with('activeAssignments')
            ->get();

        return [
            'hostel_id' => $hostelId,
            'hostel_name' => $hostel->name,
            'capacity' => $hostel->capacity,
            'current_occupancy' => $hostel->current_occupancy,
            'available_capacity' => $hostel->capacity - $hostel->current_occupancy,
            'occupancy_percentage' => $hostel->capacity > 0 
                ? ($hostel->current_occupancy / $hostel->capacity) * 100 
                : 0,
            'rooms' => $rooms->map(function ($room) {
                return [
                    'room_id' => $room->id,
                    'room_number' => $room->room_number,
                    'capacity' => $room->capacity,
                    'current_occupancy' => $room->current_occupancy,
                    'available_beds' => $room->capacity - $room->current_occupancy,
                    'is_full' => $room->current_occupancy >= $room->capacity,
                    'students' => $room->activeAssignments->map(function ($assignment) {
                        return [
                            'student_id' => $assignment->student_id,
                            'assignment_date' => $assignment->assignment_date,
                            'bed_number' => $assignment->bed_number
                        ];
                    })
                ];
            })->toArray()
        ];
    }

    public function getMaintenanceSummary(string $hostelId): array
    {
        $requests = MaintenanceRequest::where('hostel_id', $hostelId)->get();

        return [
            'total_requests' => $requests->count(),
            'pending' => $requests->where('status', 'pending')->count(),
            'in_progress' => $requests->where('status', 'in_progress')->count(),
            'resolved' => $requests->where('status', 'resolved')->count(),
            'high_priority' => $requests->whereIn('priority', ['high', 'critical'])->count(),
            'recent_requests' => $requests->sortByDesc('created_at')->take(10)->values()
        ];
    }

    public function getAttendanceReport(string $hostelId, string $startDate, string $endDate): array
    {
        $attendance = BoardingAttendance::where('hostel_id', $hostelId)
            ->whereBetween('attendance_date', [$startDate, $endDate])
            ->get();

        return [
            'period' => [
                'start_date' => $startDate,
                'end_date' => $endDate
            ],
            'summary' => [
                'total_records' => $attendance->count(),
                'present' => $attendance->where('status', 'present')->count(),
                'absent' => $attendance->where('status', 'absent')->count(),
                'on_leave' => $attendance->where('status', 'on_leave')->count(),
                'late' => $attendance->where('status', 'late')->count()
            ],
            'daily_breakdown' => $attendance->groupBy('attendance_date')
                ->map(function ($records) {
                    return [
                        'date' => $records->first()->attendance_date,
                        'present' => $records->where('status', 'present')->count(),
                        'absent' => $records->where('status', 'absent')->count(),
                        'on_leave' => $records->where('status', 'on_leave')->count()
                    ];
                })->values()
        ];
    }

    public function getWellnessReport(string $hostelId, int $days = 30): array
    {
        $records = HealthRecord::where('hostel_id', $hostelId)
            ->where('checkup_date', '>=', Carbon::now()->subDays($days)->toDateString())
            ->get();

        return [
            'period_days' => $days,
            'total_records' => $records->count(),
            'by_severity' => [
                'critical' => $records->where('severity', 'critical')->count(),
                'high' => $records->where('severity', 'high')->count(),
                'medium' => $records->where('severity', 'medium')->count(),
                'low' => $records->where('severity', 'low')->count()
            ],
            'by_type' => $records->groupBy('record_type')
                ->map(function ($records, $type) {
                    return [
                        'type' => $type,
                        'count' => $records->count()
                    ];
                }),
            'critical_records' => $records->where('severity', 'critical')->sortByDesc('checkup_date')->take(10)->values()
        ];
    }

    public function getIncidentReport(string $hostelId, int $days = 30): array
    {
        $incidents = Incident::where('hostel_id', $hostelId)
            ->where('incident_date', '>=', Carbon::now()->subDays($days)->toDateTimeString())
            ->get();

        return [
            'period_days' => $days,
            'total_incidents' => $incidents->count(),
            'by_status' => [
                'open' => $incidents->where('status', 'open')->count(),
                'in_progress' => $incidents->where('status', 'in_progress')->count(),
                'resolved' => $incidents->where('status', 'resolved')->count()
            ],
            'by_severity' => [
                'critical' => $incidents->where('severity', 'critical')->count(),
                'high' => $incidents->where('severity', 'high')->count(),
                'medium' => $incidents->where('severity', 'medium')->count(),
                'low' => $incidents->where('severity', 'low')->count()
            ],
            'by_type' => $incidents->groupBy('incident_type')
                ->map(function ($incidents, $type) {
                    return [
                        'type' => $type,
                        'count' => $incidents->count()
                    ];
                }),
            'recent_incidents' => $incidents->sortByDesc('incident_date')->take(10)->values()
        ];
    }

    public function getStudentBoardingInfo(string $studentId): array
    {
        $assignment = RoomAssignment::where('student_id', $studentId)->active()->first();
        
        if (!$assignment) {
            return [
                'student_id' => $studentId,
                'assigned' => false,
                'message' => 'Student is not currently assigned to a room'
            ];
        }

        $mealPlan = MealPlan::where('student_id', $studentId)
            ->where('is_active', true)
            ->where(function ($q) {
                $q->whereNull('end_date')
                    ->orWhere('end_date', '>=', Carbon::now()->toDateString());
            })
            ->first();
        
        $recentAttendance = BoardingAttendance::where('student_id', $studentId)
            ->where('attendance_date', '>=', Carbon::now()->subDays(7)->toDateString())
            ->get();
        
        $recentHealthRecords = HealthRecord::where('student_id', $studentId)
            ->where('checkup_date', '>=', Carbon::now()->subDays(30)->toDateString())
            ->get();

        return [
            'student_id' => $studentId,
            'assigned' => true,
            'hostel' => [
                'id' => $assignment->hostel_id,
                'name' => $assignment->hostel->name,
                'code' => $assignment->hostel->code,
                'warden_name' => $assignment->hostel->warden_name,
                'warden_contact' => $assignment->hostel->warden_contact
            ],
            'room' => [
                'id' => $assignment->room_id,
                'room_number' => $assignment->room->room_number,
                'floor' => $assignment->room->floor,
                'bed_number' => $assignment->bed_number,
                'assignment_date' => $assignment->assignment_date
            ],
            'meal_plan' => $mealPlan ? [
                'id' => $mealPlan->id,
                'plan_type' => $mealPlan->plan_type,
                'dietary_requirements' => $mealPlan->dietary_requirements,
                'allergies' => $mealPlan->allergies
            ] : null,
            'attendance_summary' => [
                'last_7_days' => $recentAttendance->count(),
                'present_days' => $recentAttendance->where('status', 'present')->count(),
                'absent_days' => $recentAttendance->where('status', 'absent')->count(),
                'leave_days' => $recentAttendance->where('status', 'on_leave')->count()
            ],
            'health_summary' => [
                'recent_records' => $recentHealthRecords->count(),
                'critical_records' => $recentHealthRecords->where('severity', 'critical')->count(),
                'last_checkup' => $recentHealthRecords->sortByDesc('checkup_date')->first()
            ]
        ];
    }

    public function getAvailableRooms(string $hostelId, array $filters = []): array
    {
        $query = Room::where('hostel_id', $hostelId)
            ->where('is_available', true)
            ->whereRaw('current_occupancy < capacity');

        if (!empty($filters['room_type'])) {
            $query->where('room_type', $filters['room_type']);
        }

        if (!empty($filters['floor'])) {
            $query->where('floor', $filters['floor']);
        }

        if (!empty($filters['min_beds'])) {
            $query->whereRaw('capacity - current_occupancy >= ?', [$filters['min_beds']]);
        }

        $rooms = $query->with('hostel')->get();

        return $rooms->map(function ($room) {
            return [
                'room_id' => $room->id,
                'room_number' => $room->room_number,
                'floor' => $room->floor,
                'room_type' => $room->room_type,
                'capacity' => $room->capacity,
                'available_beds' => $room->capacity - $room->current_occupancy,
                'amenities' => $room->amenities,
                'hostel' => [
                    'id' => $room->hostel->id,
                    'name' => $room->hostel->name,
                    'code' => $room->hostel->code
                ]
            ];
        })->toArray();
    }
}
