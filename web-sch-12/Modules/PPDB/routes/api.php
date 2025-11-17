<?php

use Illuminate\Support\Facades\Route;
use Modules\PPDB\app\Http\Controllers\PPDBController;

Route::middleware(['auth:sanctum'])->prefix('v1')->group(function () {
    Route::apiResource('ppdbs', PPDBController::class)->names('ppdb');
});
