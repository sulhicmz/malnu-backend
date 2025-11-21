<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\ApiController;
use App\Models\SchoolManagement\Teacher;
use Hypervel\Http\Request;
use Hypervel\Http\Response;

class TeacherController extends ApiController
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): array
    {
        try {
            $teachers = Teacher::with(['user', 'subject', 'classModels'])->paginate(
                $request->get('per_page', 15)
            );

            return $this->success($teachers, 'Teachers retrieved successfully');
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
                'user_id' => 'required|exists:users,id',
                'nip' => 'required|string|unique:teachers,nip',
                'full_name' => 'required|string|max:255',
                'date_of_birth' => 'required|date',
                'place_of_birth' => 'required|string|max:255',
                'gender' => 'required|in:male,female',
                'address' => 'nullable|string',
                'phone' => 'nullable|string|max:20',
                'subject_id' => 'nullable|exists:subjects,id',
                'class_advisor_id' => 'nullable|exists:class_models,id',
            ]);

            $teacher = Teacher::create($validated);

            return $this->success($teacher, 'Teacher created successfully', 201);
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
            $teacher = Teacher::with(['user', 'subject', 'classModels'])->findOrFail($id);

            return $this->success($teacher, 'Teacher retrieved successfully');
        } catch (\Exception $e) {
            return $this->error('Teacher not found', 404);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, int $id): array
    {
        try {
            $teacher = Teacher::findOrFail($id);

            $validated = $request->validate([
                'user_id' => 'sometimes|required|exists:users,id',
                'nip' => 'sometimes|required|string|unique:teachers,nip,' . $id,
                'full_name' => 'sometimes|required|string|max:255',
                'date_of_birth' => 'sometimes|required|date',
                'place_of_birth' => 'sometimes|required|string|max:255',
                'gender' => 'sometimes|required|in:male,female',
                'address' => 'nullable|string',
                'phone' => 'nullable|string|max:20',
                'subject_id' => 'nullable|exists:subjects,id',
                'class_advisor_id' => 'nullable|exists:class_models,id',
            ]);

            $teacher->update($validated);

            return $this->success($teacher, 'Teacher updated successfully');
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
            $teacher = Teacher::findOrFail($id);
            $teacher->delete();

            return $this->success(null, 'Teacher deleted successfully');
        } catch (\Exception $e) {
            return $this->error('Teacher not found or could not be deleted', 404);
        }
    }
}