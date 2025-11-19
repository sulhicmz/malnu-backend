<?php

declare(strict_types=1);

use App\Http\Controllers\IndexController;
use App\Http\Controllers\UserController;
use Hypervel\Support\Facades\Route;

Route::any('/', [IndexController::class, 'index']);

// User routes with performance optimizations
Route::prefix('users')->group(function () {
    Route::get('/', [UserController::class, 'index']);
    Route::get('/{id}', [UserController::class, 'show']);
    Route::get('/email/{email}', [UserController::class, 'showByEmail']);
    Route::get('/with-roles', [UserController::class, 'getUsersWithRoles']);
    Route::get('/paginated-with-relationships', [UserController::class, 'getPaginatedUsersWithRelationships']);
    Route::get('/specific-columns', [UserController::class, 'getUsersWithSpecificColumns']);
    Route::get('/role/{roleName}', [UserController::class, 'getUsersWithRoleCondition']);
    Route::post('/clear-cache', [UserController::class, 'clearCache']);
});
