<?php

declare(strict_types=1);

namespace App\Http\Controllers\SchoolManagement;

use App\Http\Controllers\AbstractController;
use App\Http\Requests\ClassRequest;
use App\Models\SchoolManagement\ClassModel;
use Hypervel\Http\Request;

class ClassController extends AbstractController
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = ClassModel::query();
        
        // Apply filters if provided
        if ($request->has('search')) {
            $search = $request->get('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('level', 'like', "%{$search}%")
                  ->orWhere('academic_year', 'like', "%{$search}%")
                  ->orWhereHas('homeroomTeacher.user', function ($q) use ($search) {
                      $q->where('name', 'like', "%{$search}%");
                  });
            });
        }
        
        if ($request->has('level')) {
            $query->where('level', $request->get('level'));
        }
        
        if ($request->has('academic_year')) {
            $query->where('academic_year', $request->get('academic_year'));
        }
        
        $classes = $query->with(['homeroomTeacher.user', 'students'])->paginate(
            $request->get('per_page', 15)
        );
        
        return response()->json([
            'success' => true,
            'data' => $classes,
            'message' => 'Classes retrieved successfully'
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(ClassRequest $request)
    {
        try {
            $class = ClassModel::create($request->validated());
            
            $class->load(['homeroomTeacher.user', 'students']);
            
            return response()->json([
                'success' => true,
                'data' => $class,
                'message' => 'Class created successfully'
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create class: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $class = ClassModel::with([
            'homeroomTeacher.user', 
            'students.user', 
            'classSubjects.teacher.user',
            'virtualClasses'
        ])->find($id);
        
        if (!$class) {
            return response()->json([
                'success' => false,
                'message' => 'Class not found'
            ], 404);
        }
        
        return response()->json([
            'success' => true,
            'data' => $class,
            'message' => 'Class retrieved successfully'
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(ClassRequest $request, string $id)
    {
        $class = ClassModel::find($id);
        
        if (!$class) {
            return response()->json([
                'success' => false,
                'message' => 'Class not found'
            ], 404);
        }
        
        try {
            $class->update($request->validated());
            $class->refresh();
            $class->load(['homeroomTeacher.user', 'students']);
            
            return response()->json([
                'success' => true,
                'data' => $class,
                'message' => 'Class updated successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update class: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $class = ClassModel::find($id);
        
        if (!$class) {
            return response()->json([
                'success' => false,
                'message' => 'Class not found'
            ], 404);
        }
        
        try {
            $class->delete();
            
            return response()->json([
                'success' => true,
                'message' => 'Class deleted successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete class: ' . $e->getMessage()
            ], 500);
        }
    }
}