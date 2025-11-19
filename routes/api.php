<?php

declare(strict_types=1);

use App\Http\Controllers\IndexController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\PerformanceController;
use Hypervel\Support\Facades\Route;

Route::any('/', [IndexController::class, 'index']);

// User routes with caching and optimization
Route::prefix('users')->group(function () {
    Route::get('/', [UserController::class, 'index']);
    Route::get('/active', [UserController::class, 'active']);
    Route::get('/{id}', [UserController::class, 'show']);
    Route::post('/', [UserController::class, 'store']);
    Route::put('/{id}', [UserController::class, 'update']);
    Route::delete('/{id}', [UserController::class, 'destroy']);
});

// Performance monitoring routes
Route::prefix('performance')->group(function () {
    Route::get('/cache/stats', [PerformanceController::class, 'cacheStats']);
    Route::get('/cache/health', [PerformanceController::class, 'cacheHealth']);
    Route::get('/cache/keys/{pattern?}', [PerformanceController::class, 'cacheKeys']);
    Route::post('/cache/flush', [PerformanceController::class, 'flushCache']);
});
