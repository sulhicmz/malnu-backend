<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\ApiController;
use App\Models\SchoolManagement\Schedule;
use Hypervel\Http\Request;
use Hypervel\Http\Response;

class ScheduleController extends ApiController
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): array
    {
        try {
            $schedules = Schedule::with(['classModel', 'subject', 'teacher'])->paginate(
                $request->get('per_page', 15)
            );

            return $this->success($schedules, 'Schedules retrieved successfully');
        } catch (\Exception $e) {
            return $this->error($e->getMessage(), 500);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): array
    {
        try {
            // Basic validation
            $validated = $request->validate([
                'class_model_id' => 'required|exists:class_models,id',
                'subject_id' => 'required|exists:subjects,id',
                'teacher_id' => 'required|exists:teachers,id',
                'day_of_week' => 'required|in:monday,tuesday,wednesday,thursday,friday,saturday,sunday',
                'start_time' => 'required|date_format:H:i',
                'end_time' => 'required|date_format:H:i|after:start_time',
                'room' => 'nullable|string|max:100',
                'semester' => 'nullable|string|max:20',
            ]);

            $schedule = Schedule::create($validated);

            return $this->success($schedule, 'Schedule created successfully', 201);
        } catch (\Exception $e) {
            return $this->error($e->getMessage(), 422, $e->getMessage());
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(int $id): array
    {
        try {
            $schedule = Schedule::with(['classModel', 'subject', 'teacher'])->findOrFail($id);

            return $this->success($schedule, 'Schedule retrieved successfully');
        } catch (\Exception $e) {
            return $this->error('Schedule not found', 404);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, int $id): array
    {
        try {
            $schedule = Schedule::findOrFail($id);

            $validated = $request->validate([
                'class_model_id' => 'sometimes|required|exists:class_models,id',
                'subject_id' => 'sometimes|required|exists:subjects,id',
                'teacher_id' => 'sometimes|required|exists:teachers,id',
                'day_of_week' => 'sometimes|required|in:monday,tuesday,wednesday,thursday,friday,saturday,sunday',
                'start_time' => 'sometimes|required|date_format:H:i',
                'end_time' => 'sometimes|required|date_format:H:i|after:start_time',
                'room' => 'nullable|string|max:100',
                'semester' => 'nullable|string|max:20',
            ]);

            $schedule->update($validated);

            return $this->success($schedule, 'Schedule updated successfully');
        } catch (\Exception $e) {
            return $this->error($e->getMessage(), 422);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(int $id): array
    {
        try {
            $schedule = Schedule::findOrFail($id);
            $schedule->delete();

            return $this->success(null, 'Schedule deleted successfully');
        } catch (\Exception $e) {
            return $this->error('Schedule not found or could not be deleted', 404);
        }
    }
}