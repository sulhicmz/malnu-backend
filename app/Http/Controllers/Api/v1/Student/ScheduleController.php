<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\v1\Student;

use App\Http\Controllers\Api\v1\ApiController;
use App\Models\SchoolManagement\Schedule;
use Hypervel\Http\Request;

class ScheduleController extends ApiController
{
    public function index(Request $request)
    {
        $user = $request->user();
        $student = $user->student;
        
        if (!$student) {
            return $this->errorResponse('Student profile not found', 404);
        }

        $schedules = Schedule::where('class_id', $student->class_id)
            ->with(['subject', 'teacher'])
            ->orderBy('date', 'asc')
            ->orderBy('start_time', 'asc')
            ->get();

        return $this->successResponse([
            'schedules' => $schedules,
        ]);
    }
}