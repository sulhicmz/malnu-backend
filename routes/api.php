<?php

declare(strict_types=1);

use App\Http\Controllers\IndexController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\PerformanceController;
use Hypervel\Support\Facades\Route;

Route::any('/', [IndexController::class, 'index']);

// User routes with caching and optimization
Route::get('/users', [UserController::class, 'index']);
Route::get('/users/{id}', [UserController::class, 'show']);
Route::post('/users', [UserController::class, 'store']);
Route::put('/users/{id}', [UserController::class, 'update']);
Route::delete('/users/{id}', [UserController::class, 'destroy']);
Route::get('/users/paginated', [UserController::class, 'paginate']);

// Performance monitoring routes
Route::get('/performance/report', [PerformanceController::class, 'getPerformanceReport']);
Route::get('/performance/cache', [PerformanceController::class, 'getCacheStats']);
Route::get('/performance/query', [PerformanceController::class, 'getQueryStats']);
Route::post('/performance/reset', [PerformanceController::class, 'resetStats']);
