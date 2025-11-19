<?php

declare(strict_types=1);

use App\Http\Controllers\IndexController;
use App\Http\Controllers\Api\UserController;
use Hypervel\Support\Facades\Route;

Route::any('/', [IndexController::class, 'index']);

// Optimized API routes with caching and query optimization
Route::prefix('v1')->group(function () {
    Route::apiResource('users', UserController::class);
    Route::get('users/{id}', [UserController::class, 'show']);
    Route::get('users/{id}/count', [UserController::class, 'count']);
});
