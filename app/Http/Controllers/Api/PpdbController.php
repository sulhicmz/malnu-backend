<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\ApiController;
use App\Models\PPDB\PpdbRegistration;
use Hypervel\Http\Request;
use Hypervel\Http\Response;

class PpdbController extends ApiController
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): array
    {
        try {
            $registrations = PpdbRegistration::with(['student', 'documents', 'tests'])->paginate(
                $request->get('per_page', 15)
            );

            return $this->success($registrations, 'PPDB Registrations retrieved successfully');
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
                'student_name' => 'required|string|max:255',
                'date_of_birth' => 'required|date',
                'place_of_birth' => 'required|string|max:255',
                'gender' => 'required|in:male,female',
                'address' => 'required|string',
                'phone' => 'required|string|max:20',
                'email' => 'required|email|unique:ppdb_registrations,email',
                'parent_name' => 'required|string|max:255',
                'parent_phone' => 'required|string|max:20',
                'previous_school' => 'required|string|max:255',
                'registration_year' => 'required|string|max:9',
                'registration_status' => 'required|in:pending,approved,rejected,completed',
                'payment_status' => 'required|in:pending,paid,overdue',
                'notes' => 'nullable|string',
            ]);

            $registration = PpdbRegistration::create($validated);

            return $this->success($registration, 'PPDB Registration created successfully', 201);
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
            $registration = PpdbRegistration::with(['student', 'documents', 'tests'])->findOrFail($id);

            return $this->success($registration, 'PPDB Registration retrieved successfully');
        } catch (\Exception $e) {
            return $this->error('PPDB Registration not found', 404);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, int $id): array
    {
        try {
            $registration = PpdbRegistration::findOrFail($id);

            $validated = $request->validate([
                'student_name' => 'sometimes|required|string|max:255',
                'date_of_birth' => 'sometimes|required|date',
                'place_of_birth' => 'sometimes|required|string|max:255',
                'gender' => 'sometimes|required|in:male,female',
                'address' => 'sometimes|required|string',
                'phone' => 'sometimes|required|string|max:20',
                'email' => 'sometimes|required|email|unique:ppdb_registrations,email,' . $id,
                'parent_name' => 'sometimes|required|string|max:255',
                'parent_phone' => 'sometimes|required|string|max:20',
                'previous_school' => 'sometimes|required|string|max:255',
                'registration_year' => 'sometimes|required|string|max:9',
                'registration_status' => 'sometimes|required|in:pending,approved,rejected,completed',
                'payment_status' => 'sometimes|required|in:pending,paid,overdue',
                'notes' => 'nullable|string',
            ]);

            $registration->update($validated);

            return $this->success($registration, 'PPDB Registration updated successfully');
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
            $registration = PpdbRegistration::findOrFail($id);
            $registration->delete();

            return $this->success(null, 'PPDB Registration deleted successfully');
        } catch (\Exception $e) {
            return $this->error('PPDB Registration not found or could not be deleted', 404);
        }
    }
}