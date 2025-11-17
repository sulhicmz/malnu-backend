<?php

use App\Http\Controllers\admin\HomeController;
use App\Http\Controllers\admin\UserController;
use App\Http\Controllers\frontend\FeController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::get('/', [FeController::class, 'indexFe'])->name('landingpage');

// auth users
Route::get('/login-session', [App\Http\Controllers\Auth\AuthLoginCustomController::class, 'showLoginForm'])->name('login.session');
Route::post('/login-session', [App\Http\Controllers\Auth\AuthLoginCustomController::class, 'login']);
Route::post('/logout-session', [App\Http\Controllers\Auth\AuthLoginCustomController::class, 'logout'])->name('logout.session');

Route::middleware(['auth'])->prefix('home')->group(function () {
    Route::get('/', [HomeController::class, 'indexPage'])->name('home');

    // Users Resource Routes
    Route::get('/users', [UserController::class, 'indexView'])->name('users.index');
    Route::get('/users/data', [UserController::class, 'index'])->name('users.data');
    Route::resource('users', UserController::class)->except(['index']);

    // Permissions
    Route::resource('permissions', App\Http\Controllers\admin\PermissionController::class);
    Route::post('permissions/{permission}/assign', [App\Http\Controllers\admin\PermissionController::class, 'assignToUser'])->name('permissions.assign');
    Route::post('permissions/{permission}/revoke', [App\Http\Controllers\admin\PermissionController::class, 'revokeFromUser'])->name('permissions.revoke');
    Route::post('permissions/{permission}/assign-role', [App\Http\Controllers\admin\PermissionController::class, 'assignToRole'])->name('permissions.assignRole');
    Route::post('permissions/{permission}/revoke-role', [App\Http\Controllers\admin\PermissionController::class, 'revokeFromRole'])->name('permissions.revokeRole');
    Route::get('/datatable', [App\Http\Controllers\admin\PermissionController::class, 'datatable'])->name('permissions.datatable');
});
Auth::routes();

// Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');
