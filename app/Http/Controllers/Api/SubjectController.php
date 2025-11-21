<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\ApiController;
use App\Models\SchoolManagement\Subject;
use Hypervel\Http\Request;
use Hypervel\Http\Response;

class SubjectController extends ApiController
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): array
    {
        try {
            $subjects = Subject::with(['teacher', 'classModels'])->paginate(
                $request->get('per_page', 15)
            );

            return $this->success($subjects, 'Subjects retrieved successfully');
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
                'code' => 'required|string|unique:subjects,code',
                'description' => 'nullable|string',
                'credit_hours' => 'nullable|integer|min:1',
                'teacher_id' => 'nullable|exists:teachers,id',
                'grade_level' => 'nullable|string|max:50',
                'semester' => 'nullable|string|max:20',
            ]);

            $subject = Subject::create($validated);

            return $this->success($subject, 'Subject created successfully', 201);
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
            $subject = Subject::with(['teacher', 'classModels'])->findOrFail($id);

            return $this->success($subject, 'Subject retrieved successfully');
        } catch (\Exception $e) {
            return $this->error('Subject not found', 404);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, int $id): array
    {
        try {
            $subject = Subject::findOrFail($id);

            $validated = $request->validate([
                'name' => 'sometimes|required|string|max:255',
                'code' => 'sometimes|required|string|unique:subjects,code,' . $id,
                'description' => 'nullable|string',
                'credit_hours' => 'nullable|integer|min:1',
                'teacher_id' => 'nullable|exists:teachers,id',
                'grade_level' => 'nullable|string|max:50',
                'semester' => 'nullable|string|max:20',
            ]);

            $subject->update($validated);

            return $this->success($subject, 'Subject updated successfully');
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
            $subject = Subject::findOrFail($id);
            $subject->delete();

            return $this->success(null, 'Subject deleted successfully');
        } catch (\Exception $e) {
            return $this->error('Subject not found or could not be deleted', 404);
        }
    }
}