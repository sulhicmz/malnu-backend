<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\v1\Admin;

use App\Http\Controllers\Api\v1\ApiController;
use App\Models\SchoolManagement\ClassModel;
use Hypervel\Http\Request;

class ClassController extends ApiController
{
    public function index(Request $request)
    {
        $classes = ClassModel::with(['teacher', 'subject', 'students'])
            ->orderBy('name', 'asc')
            ->paginate(20);

        return $this->successResponse([
            'classes' => $classes->items(),
            'pagination' => [
                'current_page' => $classes->currentPage(),
                'per_page' => $classes->perPage(),
                'total' => $classes->total(),
                'last_page' => $classes->lastPage(),
            ],
        ]);
    }
}