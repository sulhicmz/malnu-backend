<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\ApiController;
use App\Models\Grading\Grade;
use Hypervel\Http\Request;
use Hypervel\Http\Response;

class GradeController extends ApiController
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): array
    {
        try {
            $grades = Grade::with(['student', 'subject', 'teacher'])->paginate(
                $request->get('per_page', 15)
            );

            return $this->success($grades, 'Grades retrieved successfully');
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
                'student_id' => 'required|exists:students,id',
                'subject_id' => 'required|exists:subjects,id',
                'teacher_id' => 'required|exists:teachers,id',
                'class_model_id' => 'required|exists:class_models,id',
                'semester' => 'required|string|max:20',
                'academic_year' => 'required|string|max:9',
                'grade_value' => 'required|numeric|min:0|max:100',
                'grade_letter' => 'nullable|string|max:2',
                'assignment_score' => 'nullable|numeric|min:0|max:100',
                'midterm_score' => 'nullable|numeric|min:0|max:100',
                'final_score' => 'nullable|numeric|min:0|max:100',
                'comments' => 'nullable|string',
            ]);

            $grade = Grade::create($validated);

            return $this->success($grade, 'Grade created successfully', 201);
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
            $grade = Grade::with(['student', 'subject', 'teacher'])->findOrFail($id);

            return $this->success($grade, 'Grade retrieved successfully');
        } catch (\Exception $e) {
            return $this->error('Grade not found', 404);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, int $id): array
    {
        try {
            $grade = Grade::findOrFail($id);

            $validated = $request->validate([
                'student_id' => 'sometimes|required|exists:students,id',
                'subject_id' => 'sometimes|required|exists:subjects,id',
                'teacher_id' => 'sometimes|required|exists:teachers,id',
                'class_model_id' => 'sometimes|required|exists:class_models,id',
                'semester' => 'sometimes|required|string|max:20',
                'academic_year' => 'sometimes|required|string|max:9',
                'grade_value' => 'sometimes|required|numeric|min:0|max:100',
                'grade_letter' => 'nullable|string|max:2',
                'assignment_score' => 'nullable|numeric|min:0|max:100',
                'midterm_score' => 'nullable|numeric|min:0|max:100',
                'final_score' => 'nullable|numeric|min:0|max:100',
                'comments' => 'nullable|string',
            ]);

            $grade->update($validated);

            return $this->success($grade, 'Grade updated successfully');
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
            $grade = Grade::findOrFail($id);
            $grade->delete();

            return $this->success(null, 'Grade deleted successfully');
        } catch (\Exception $e) {
            return $this->error('Grade not found or could not be deleted', 404);
        }
    }
}