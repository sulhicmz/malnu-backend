<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\v1\Student;

use App\Http\Controllers\Api\v1\ApiController;
use Hypervel\Http\Request;

class ProfileController extends ApiController
{
    public function show(Request $request)
    {
        $user = $request->user();
        $student = $user->student;
        
        if (!$student) {
            return $this->errorResponse('Student profile not found', 404);
        }

        return $this->successResponse([
            'id' => $student->id,
            'user_id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'student_id' => $student->student_id,
            'class_id' => $student->class_id,
            'phone' => $user->phone,
            'avatar_url' => $user->avatar_url,
            'created_at' => $student->created_at,
        ]);
    }

    public function update(Request $request)
    {
        $request->validate([
            'name' => 'sometimes|string|max:255',
            'phone' => 'sometimes|string|max:20',
        ]);

        $user = $request->user();
        
        $user->update([
            'name' => $request->name ?? $user->name,
            'phone' => $request->phone ?? $user->phone,
        ]);

        return $this->successResponse([
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'phone' => $user->phone,
        ], 'Profile updated successfully');
    }
}