<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\ApiController;
use App\Models\SchoolManagement\ClassModel;
use Hypervel\Http\Request;
use Hypervel\Http\Response;

class ClassController extends ApiController
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): array
    {
        try {
            $classes = ClassModel::with(['teacher', 'students', 'subject'])->paginate(
                $request->get('per_page', 15)
            );

            return $this->success($classes, 'Classes retrieved successfully');
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
                'name' => 'required|string|max:255',
                'grade_level' => 'required|string|max:50',
                'teacher_id' => 'nullable|exists:teachers,id',
                'room' => 'nullable|string|max:100',
                'capacity' => 'nullable|integer|min:1',
                'description' => 'nullable|string',
            ]);

            $class = ClassModel::create($validated);

            return $this->success($class, 'Class created successfully', 201);
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
            $class = ClassModel::with(['teacher', 'students', 'subject'])->findOrFail($id);

            return $this->success($class, 'Class retrieved successfully');
        } catch (\Exception $e) {
            return $this->error('Class not found', 404);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, int $id): array
    {
        try {
            $class = ClassModel::findOrFail($id);

            $validated = $request->validate([
                'name' => 'sometimes|required|string|max:255',
                'grade_level' => 'sometimes|required|string|max:50',
                'teacher_id' => 'nullable|exists:teachers,id',
                'room' => 'nullable|string|max:100',
                'capacity' => 'nullable|integer|min:1',
                'description' => 'nullable|string',
            ]);

            $class->update($validated);

            return $this->success($class, 'Class updated successfully');
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
            $class = ClassModel::findOrFail($id);
            $class->delete();

            return $this->success(null, 'Class deleted successfully');
        } catch (\Exception $e) {
            return $this->error('Class not found or could not be deleted', 404);
        }
    }
}