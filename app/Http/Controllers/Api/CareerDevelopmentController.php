<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\ApiController;
use App\Models\CareerDevelopment\CareerAssessment;
use Hypervel\Http\Request;
use Hypervel\Http\Response;

class CareerDevelopmentController extends ApiController
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): array
    {
        try {
            $assessments = CareerAssessment::with(['student', 'counselor'])->paginate(
                $request->get('per_page', 15)
            );

            return $this->success($assessments, 'Career Assessments retrieved successfully');
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
                'counselor_id' => 'required|exists:users,id',
                'assessment_type' => 'required|in:aptitude,interest,personality,career_guidance',
                'assessment_date' => 'required|date',
                'score' => 'required|numeric|min:0|max:100',
                'grade' => 'required|string|max:2',
                'strengths' => 'nullable|string',
                'weaknesses' => 'nullable|string',
                'recommendations' => 'nullable|string',
                'status' => 'required|in:pending,completed,reviewed',
                'notes' => 'nullable|string',
            ]);

            $assessment = CareerAssessment::create($validated);

            return $this->success($assessment, 'Career Assessment created successfully', 201);
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
            $assessment = CareerAssessment::with(['student', 'counselor'])->findOrFail($id);

            return $this->success($assessment, 'Career Assessment retrieved successfully');
        } catch (\Exception $e) {
            return $this->error('Career Assessment not found', 404);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, int $id): array
    {
        try {
            $assessment = CareerAssessment::findOrFail($id);

            $validated = $request->validate([
                'student_id' => 'sometimes|required|exists:students,id',
                'counselor_id' => 'sometimes|required|exists:users,id',
                'assessment_type' => 'sometimes|required|in:aptitude,interest,personality,career_guidance',
                'assessment_date' => 'sometimes|required|date',
                'score' => 'sometimes|required|numeric|min:0|max:100',
                'grade' => 'sometimes|required|string|max:2',
                'strengths' => 'nullable|string',
                'weaknesses' => 'nullable|string',
                'recommendations' => 'nullable|string',
                'status' => 'sometimes|required|in:pending,completed,reviewed',
                'notes' => 'nullable|string',
            ]);

            $assessment->update($validated);

            return $this->success($assessment, 'Career Assessment updated successfully');
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
            $assessment = CareerAssessment::findOrFail($id);
            $assessment->delete();

            return $this->success(null, 'Career Assessment deleted successfully');
        } catch (\Exception $e) {
            return $this->error('Career Assessment not found or could not be deleted', 404);
        }
    }
}