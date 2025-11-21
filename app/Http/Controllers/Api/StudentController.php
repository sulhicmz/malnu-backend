<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\ApiController;
use App\Models\SchoolManagement\Student;
use Hypervel\Http\Request;
use Hypervel\Http\Response;

class StudentController extends ApiController
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): array
    {
        try {
            $students = Student::with(['user', 'classModel', 'parent'])->paginate(
                $request->get('per_page', 15)
            );

            return $this->success($students, 'Students retrieved successfully');
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
                'class_model_id' => 'required|exists:class_models,id',
                'nis' => 'required|string|unique:students,nis',
                'nisn' => 'nullable|string|unique:students,nisn',
                'full_name' => 'required|string|max:255',
                'date_of_birth' => 'required|date',
                'place_of_birth' => 'required|string|max:255',
                'gender' => 'required|in:male,female',
                'address' => 'nullable|string',
                'phone' => 'nullable|string|max:20',
                'parent_id' => 'nullable|exists:parent_ortus,id',
            ]);

            $student = Student::create($validated);

            return $this->success($student, 'Student created successfully', 201);
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
            $student = Student::with(['user', 'classModel', 'parent'])->findOrFail($id);

            return $this->success($student, 'Student retrieved successfully');
        } catch (\Exception $e) {
            return $this->error('Student not found', 404);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, int $id): array
    {
        try {
            $student = Student::findOrFail($id);

            $validated = $request->validate([
                'user_id' => 'sometimes|required|exists:users,id',
                'class_model_id' => 'sometimes|required|exists:class_models,id',
                'nis' => 'sometimes|required|string|unique:students,nis,' . $id,
                'nisn' => 'nullable|string|unique:students,nisn,' . $id,
                'full_name' => 'sometimes|required|string|max:255',
                'date_of_birth' => 'sometimes|required|date',
                'place_of_birth' => 'sometimes|required|string|max:255',
                'gender' => 'sometimes|required|in:male,female',
                'address' => 'nullable|string',
                'phone' => 'nullable|string|max:20',
                'parent_id' => 'nullable|exists:parent_ortus,id',
            ]);

            $student->update($validated);

            return $this->success($student, 'Student updated successfully');
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
            $student = Student::findOrFail($id);
            $student->delete();

            return $this->success(null, 'Student deleted successfully');
        } catch (\Exception $e) {
            return $this->error('Student not found or could not be deleted', 404);
        }
    }
}