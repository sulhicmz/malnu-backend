<?php

declare(strict_types=1);

namespace App\Http\Controllers\SchoolManagement;

use App\Http\Controllers\AbstractController;
use App\Http\Requests\StudentRequest;
use App\Models\SchoolManagement\Student;
use Hypervel\Http\Request;

class StudentController extends AbstractController
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Student::query();
        
        // Apply filters if provided
        if ($request->has('search')) {
            $search = $request->get('search');
            $query->where(function ($q) use ($search) {
                $q->where('nisn', 'like', "%{$search}%")
                  ->orWhereHas('user', function ($q) use ($search) {
                      $q->where('name', 'like', "%{$search}%");
                  })
                  ->orWhereHas('class', function ($q) use ($search) {
                      $q->where('name', 'like', "%{$search}%");
                  });
            });
        }
        
        if ($request->has('status')) {
            $query->where('status', $request->get('status'));
        }
        
        if ($request->has('class_id')) {
            $query->where('class_id', $request->get('class_id'));
        }
        
        $students = $query->with(['user', 'class'])->paginate(
            $request->get('per_page', 15)
        );
        
        return response()->json([
            'success' => true,
            'data' => $students,
            'message' => 'Students retrieved successfully'
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StudentRequest $request)
    {
        try {
            $student = Student::create($request->validated());
            
            $student->load(['user', 'class']);
            
            return response()->json([
                'success' => true,
                'data' => $student,
                'message' => 'Student created successfully'
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create student: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $student = Student::with(['user', 'class', 'parent'])->find($id);
        
        if (!$student) {
            return response()->json([
                'success' => false,
                'message' => 'Student not found'
            ], 404);
        }
        
        return response()->json([
            'success' => true,
            'data' => $student,
            'message' => 'Student retrieved successfully'
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(StudentRequest $request, string $id)
    {
        $student = Student::find($id);
        
        if (!$student) {
            return response()->json([
                'success' => false,
                'message' => 'Student not found'
            ], 404);
        }
        
        try {
            $student->update($request->validated());
            $student->refresh();
            $student->load(['user', 'class']);
            
            return response()->json([
                'success' => true,
                'data' => $student,
                'message' => 'Student updated successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update student: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $student = Student::find($id);
        
        if (!$student) {
            return response()->json([
                'success' => false,
                'message' => 'Student not found'
            ], 404);
        }
        
        try {
            $student->delete();
            
            return response()->json([
                'success' => true,
                'message' => 'Student deleted successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete student: ' . $e->getMessage()
            ], 500);
        }
    }
}