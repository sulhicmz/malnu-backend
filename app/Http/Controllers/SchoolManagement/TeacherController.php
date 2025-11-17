<?php

declare(strict_types=1);

namespace App\Http\Controllers\SchoolManagement;

use App\Http\Controllers\AbstractController;
use App\Http\Requests\TeacherRequest;
use App\Models\SchoolManagement\Teacher;
use Hypervel\Http\Request;

class TeacherController extends AbstractController
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Teacher::query();
        
        // Apply filters if provided
        if ($request->has('search')) {
            $search = $request->get('search');
            $query->where(function ($q) use ($search) {
                $q->where('nip', 'like', "%{$search}%")
                  ->orWhereHas('user', function ($q) use ($search) {
                      $q->where('name', 'like', "%{$search}%");
                  })
                  ->orWhere('expertise', 'like', "%{$search}%");
            });
        }
        
        if ($request->has('status')) {
            $query->where('status', $request->get('status'));
        }
        
        $teachers = $query->with(['user', 'classes'])->paginate(
            $request->get('per_page', 15)
        );
        
        return response()->json([
            'success' => true,
            'data' => $teachers,
            'message' => 'Teachers retrieved successfully'
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(TeacherRequest $request)
    {
        try {
            $teacher = Teacher::create($request->validated());
            
            $teacher->load(['user', 'classes']);
            
            return response()->json([
                'success' => true,
                'data' => $teacher,
                'message' => 'Teacher created successfully'
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create teacher: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $teacher = Teacher::with(['user', 'classes', 'classSubjects', 'virtualClasses'])->find($id);
        
        if (!$teacher) {
            return response()->json([
                'success' => false,
                'message' => 'Teacher not found'
            ], 404);
        }
        
        return response()->json([
            'success' => true,
            'data' => $teacher,
            'message' => 'Teacher retrieved successfully'
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(TeacherRequest $request, string $id)
    {
        $teacher = Teacher::find($id);
        
        if (!$teacher) {
            return response()->json([
                'success' => false,
                'message' => 'Teacher not found'
            ], 404);
        }
        
        try {
            $teacher->update($request->validated());
            $teacher->refresh();
            $teacher->load(['user', 'classes']);
            
            return response()->json([
                'success' => true,
                'data' => $teacher,
                'message' => 'Teacher updated successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update teacher: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $teacher = Teacher::find($id);
        
        if (!$teacher) {
            return response()->json([
                'success' => false,
                'message' => 'Teacher not found'
            ], 404);
        }
        
        try {
            $teacher->delete();
            
            return response()->json([
                'success' => true,
                'message' => 'Teacher deleted successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete teacher: ' . $e->getMessage()
            ], 500);
        }
    }
}