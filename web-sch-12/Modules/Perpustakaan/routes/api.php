<?php

use Illuminate\Support\Facades\Route;
use Modules\Perpustakaan\app\Http\Controllers\PerpustakaanController;

Route::middleware(['auth:sanctum'])->prefix('v1')->group(function () {
    Route::apiResource('perpustakaans', PerpustakaanController::class)->names('perpustakaan');
});
