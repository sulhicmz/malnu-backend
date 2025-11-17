<?php

use Illuminate\Support\Facades\Route;
use Modules\LaporanAnalitik\app\Http\Controllers\LaporanAnalitikController;

Route::middleware(['auth:sanctum'])->prefix('v1')->group(function () {
    Route::apiResource('laporananalitiks', LaporanAnalitikController::class)->names('laporananalitik');
});
