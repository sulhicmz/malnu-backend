<?php

declare(strict_types=1);

use App\Http\Controllers\IndexController;
use App\Http\Controllers\UserController;
use Hypervel\Support\Facades\Route;

Route::any('/', [IndexController::class, 'index']);

// User routes with caching and optimized queries
Route::prefix('users')->group(function () {
    Route::get('/', [UserController::class, 'index']);
    Route::get('/{id}', [UserController::class, 'show']);
    Route::get('/role/{role}', [UserController::class, 'byRole']);
});
