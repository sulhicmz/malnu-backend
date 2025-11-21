<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\ApiController;
use App\Models\ELearning\Assignment;
use Hypervel\Http\Request;
use Hypervel\Http\Response;

class ELearningController extends ApiController
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): array
    {
        try {
            $assignments = Assignment::with(['subject', 'teacher', 'classModel'])->paginate(
                $request->get('per_page', 15)
            );

            return $this->success($assignments, 'Assignments retrieved successfully');
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
                'title' => 'required|string|max:255',
                'description' => 'nullable|string',
                'subject_id' => 'required|exists:subjects,id',
                'teacher_id' => 'required|exists:teachers,id',
                'class_model_id' => 'required|exists:class_models,id',
                'due_date' => 'required|date',
                'max_score' => 'required|integer|min:1|max:100',
                'assignment_type' => 'required|in:homework,quiz,project,exam',
                'instructions' => 'nullable|string',
                'is_published' => 'boolean',
            ]);

            $assignment = Assignment::create($validated);

            return $this->success($assignment, 'Assignment created successfully', 201);
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
            $assignment = Assignment::with(['subject', 'teacher', 'classModel'])->findOrFail($id);

            return $this->success($assignment, 'Assignment retrieved successfully');
        } catch (\Exception $e) {
            return $this->error('Assignment not found', 404);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, int $id): array
    {
        try {
            $assignment = Assignment::findOrFail($id);

            $validated = $request->validate([
                'title' => 'sometimes|required|string|max:255',
                'description' => 'nullable|string',
                'subject_id' => 'sometimes|required|exists:subjects,id',
                'teacher_id' => 'sometimes|required|exists:teachers,id',
                'class_model_id' => 'sometimes|required|exists:class_models,id',
                'due_date' => 'sometimes|required|date',
                'max_score' => 'sometimes|required|integer|min:1|max:100',
                'assignment_type' => 'sometimes|required|in:homework,quiz,project,exam',
                'instructions' => 'nullable|string',
                'is_published' => 'boolean',
            ]);

            $assignment->update($validated);

            return $this->success($assignment, 'Assignment updated successfully');
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
            $assignment = Assignment::findOrFail($id);
            $assignment->delete();

            return $this->success(null, 'Assignment deleted successfully');
        } catch (\Exception $e) {
            return $this->error('Assignment not found or could not be deleted', 404);
        }
    }
}