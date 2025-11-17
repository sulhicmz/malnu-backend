<?php

use Illuminate\Support\Facades\Route;
use Modules\Career\app\Http\Controllers\CareerController;

Route::middleware(['auth:sanctum'])->prefix('v1')->group(function () {
    Route::apiResource('careers', CareerController::class)->names('career');
});
