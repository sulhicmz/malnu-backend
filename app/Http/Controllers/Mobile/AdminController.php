<?php

declare(strict_types=1);

namespace App\Http\Controllers\Mobile;

use App\Http\Controllers\AbstractController;
use App\Models\User;
use App\Models\SchoolManagement\Student;
use App\Models\SchoolManagement\Teacher;
use App\Models\SchoolManagement\Staff;
use App\Models\Grading\Report;
use Hypervel\Http\Request;

class AdminController extends AbstractController
{
    /**
     * Get admin dashboard data
     */
    public function dashboard(Request $request)
    {
        $user = $request->user();

        // Check if user has admin privileges
        if (!$this->hasAdminPrivileges($user)) {
            return response()->json(['error' => 'Access denied'], 403);
        }

        $totalStudents = Student::count();
        $totalTeachers = Teacher::count();
        $totalUsers = User::count();
        $recentReports = Report::orderBy('created_at', 'desc')
            ->limit(5)
            ->get(['id', 'title', 'created_by', 'created_at']);

        return response()->json([
            'admin' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
            ],
            'statistics' => [
                'total_users' => $totalUsers,
                'total_students' => $totalStudents,
                'total_teachers' => $totalTeachers,
            ],
            'recent_reports' => $recentReports,
        ]);
    }

    /**
     * Get users list
     */
    public function users(Request $request)
    {
        $user = $request->user();

        if (!$this->hasAdminPrivileges($user)) {
            return response()->json(['error' => 'Access denied'], 403);
        }

        $users = User::with(['student', 'teacher', 'parent', 'staff'])
            ->paginate(20);

        return response()->json([
            'users' => $users,
        ]);
    }

    /**
     * Get reports
     */
    public function reports(Request $request)
    {
        $user = $request->user();

        if (!$this->hasAdminPrivileges($user)) {
            return response()->json(['error' => 'Access denied'], 403);
        }

        $reports = Report::with(['creator', 'student'])
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return response()->json([
            'reports' => $reports,
        ]);
    }

    /**
     * Check if user has admin privileges
     */
    private function hasAdminPrivileges($user): bool
    {
        // Check if user is a staff member or has admin role
        if ($user->staff || $user->teacher) {
            return true;
        }
        
        // Additional role-based check could go here
        return false;
    }
}