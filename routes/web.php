<?php

declare(strict_types=1);

use App\Http\Controllers\admin\HomeController;
use Hypervel\Support\Facades\Route;

Route::get('/', [HomeController::class, 'indexView']);

// Test route for security headers
Route::get('/test-headers', function () {
    return response()->json(['message' => 'Security headers test']);
});