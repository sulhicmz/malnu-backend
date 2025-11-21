<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\ApiController;
use App\Models\OnlineExam\Exam;
use Hypervel\Http\Request;
use Hypervel\Http\Response;

class OnlineExamController extends ApiController
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): array
    {
        try {
            $exams = Exam::with(['subject', 'teacher', 'classModel'])->paginate(
                $request->get('per_page', 15)
            );

            return $this->success($exams, 'Exams retrieved successfully');
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
                'exam_date' => 'required|date',
                'start_time' => 'required|date_format:H:i',
                'end_time' => 'required|date_format:H:i|after:start_time',
                'duration' => 'required|integer|min:1|max:360', // Duration in minutes
                'max_score' => 'required|integer|min:1|max:100',
                'exam_type' => 'required|in:quiz,midterm,final,practice',
                'instructions' => 'nullable|string',
                'is_published' => 'boolean',
                'is_online' => 'boolean',
            ]);

            $exam = Exam::create($validated);

            return $this->success($exam, 'Exam created successfully', 201);
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
            $exam = Exam::with(['subject', 'teacher', 'classModel'])->findOrFail($id);

            return $this->success($exam, 'Exam retrieved successfully');
        } catch (\Exception $e) {
            return $this->error('Exam not found', 404);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, int $id): array
    {
        try {
            $exam = Exam::findOrFail($id);

            $validated = $request->validate([
                'title' => 'sometimes|required|string|max:255',
                'description' => 'nullable|string',
                'subject_id' => 'sometimes|required|exists:subjects,id',
                'teacher_id' => 'sometimes|required|exists:teachers,id',
                'class_model_id' => 'sometimes|required|exists:class_models,id',
                'exam_date' => 'sometimes|required|date',
                'start_time' => 'sometimes|required|date_format:H:i',
                'end_time' => 'sometimes|required|date_format:H:i|after:start_time',
                'duration' => 'sometimes|required|integer|min:1|max:360',
                'max_score' => 'sometimes|required|integer|min:1|max:100',
                'exam_type' => 'sometimes|required|in:quiz,midterm,final,practice',
                'instructions' => 'nullable|string',
                'is_published' => 'boolean',
                'is_online' => 'boolean',
            ]);

            $exam->update($validated);

            return $this->success($exam, 'Exam updated successfully');
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
            $exam = Exam::findOrFail($id);
            $exam->delete();

            return $this->success(null, 'Exam deleted successfully');
        } catch (\Exception $e) {
            return $this->error('Exam not found or could not be deleted', 404);
        }
    }
}