<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\v1\Admin;

use App\Http\Controllers\Api\v1\ApiController;
use App\Models\User;
use App\Models\SchoolManagement\Student;
use App\Models\SchoolManagement\Teacher;
use App\Models\SchoolManagement\ClassModel;
use Hypervel\Http\Request;

class DashboardController extends ApiController
{
    public function index(Request $request)
    {
        $user = $request->user();
        
        // Count various entities for the dashboard
        $userCount = User::count();
        $studentCount = Student::count();
        $teacherCount = Teacher::count();
        $classCount = ClassModel::count();
        
        // Get recent activity
        $recentUsers = User::orderBy('created_at', 'desc')->limit(5)->get();
        $recentStudents = Student::with('user')->orderBy('created_at', 'desc')->limit(5)->get();
        $recentTeachers = Teacher::with('user')->orderBy('created_at', 'desc')->limit(5)->get();

        return $this->successResponse([
            'summary' => [
                'total_users' => $userCount,
                'total_students' => $studentCount,
                'total_teachers' => $teacherCount,
                'total_classes' => $classCount,
            ],
            'recent_activity' => [
                'recent_users' => $recentUsers,
                'recent_students' => $recentStudents,
                'recent_teachers' => $recentTeachers,
            ],
        ]);
    }
}