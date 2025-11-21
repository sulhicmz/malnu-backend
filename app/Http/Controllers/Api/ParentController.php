<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\ApiController;
use App\Models\ParentPortal\ParentOrtu;
use Hypervel\Http\Request;
use Hypervel\Http\Response;

class ParentController extends ApiController
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): array
    {
        try {
            $parents = ParentOrtu::with(['user', 'students'])->paginate(
                $request->get('per_page', 15)
            );

            return $this->success($parents, 'Parents retrieved successfully');
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
                'full_name' => 'required|string|max:255',
                'phone' => 'nullable|string|max:20',
                'address' => 'nullable|string',
                'occupation' => 'nullable|string|max:100',
                'relationship' => 'nullable|string|max:50',
            ]);

            $parent = ParentOrtu::create($validated);

            return $this->success($parent, 'Parent created successfully', 201);
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
            $parent = ParentOrtu::with(['user', 'students'])->findOrFail($id);

            return $this->success($parent, 'Parent retrieved successfully');
        } catch (\Exception $e) {
            return $this->error('Parent not found', 404);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, int $id): array
    {
        try {
            $parent = ParentOrtu::findOrFail($id);

            $validated = $request->validate([
                'user_id' => 'sometimes|required|exists:users,id',
                'full_name' => 'sometimes|required|string|max:255',
                'phone' => 'nullable|string|max:20',
                'address' => 'nullable|string',
                'occupation' => 'nullable|string|max:100',
                'relationship' => 'nullable|string|max:50',
            ]);

            $parent->update($validated);

            return $this->success($parent, 'Parent updated successfully');
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
            $parent = ParentOrtu::findOrFail($id);
            $parent->delete();

            return $this->success(null, 'Parent deleted successfully');
        } catch (\Exception $e) {
            return $this->error('Parent not found or could not be deleted', 404);
        }
    }
}