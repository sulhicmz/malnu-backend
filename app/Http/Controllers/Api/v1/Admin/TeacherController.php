<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\v1\Admin;

use App\Http\Controllers\Api\v1\ApiController;
use App\Models\SchoolManagement\Teacher;
use Hypervel\Http\Request;

class TeacherController extends ApiController
{
    public function index(Request $request)
    {
        $teachers = Teacher::with(['user'])
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return $this->successResponse([
            'teachers' => $teachers->items(),
            'pagination' => [
                'current_page' => $teachers->currentPage(),
                'per_page' => $teachers->perPage(),
                'total' => $teachers->total(),
                'last_page' => $teachers->lastPage(),
            ],
        ]);
    }
}