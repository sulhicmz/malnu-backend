<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\v1\Admin;

use App\Http\Controllers\Api\v1\ApiController;
use App\Models\User;
use Hypervel\Http\Request;

class UserController extends ApiController
{
    public function index(Request $request)
    {
        $users = User::with(['student', 'teacher', 'parent', 'staff'])
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return $this->successResponse([
            'users' => $users->items(),
            'pagination' => [
                'current_page' => $users->currentPage(),
                'per_page' => $users->perPage(),
                'total' => $users->total(),
                'last_page' => $users->lastPage(),
            ],
        ]);
    }
}