<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\v1\Admin;

use App\Http\Controllers\Api\v1\ApiController;
use App\Models\SchoolManagement\Student;
use Hypervel\Http\Request;

class StudentController extends ApiController
{
    public function index(Request $request)
    {
        $students = Student::with(['user', 'class'])
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return $this->successResponse([
            'students' => $students->items(),
            'pagination' => [
                'current_page' => $students->currentPage(),
                'per_page' => $students->perPage(),
                'total' => $students->total(),
                'last_page' => $students->lastPage(),
            ],
        ]);
    }
}