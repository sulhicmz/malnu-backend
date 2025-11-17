<?php

use Illuminate\Support\Facades\Route;
use Modules\SistemMonetisasi\app\Http\Controllers\SistemMonetisasiController;

Route::middleware(['auth:sanctum'])->prefix('v1')->group(function () {
    Route::apiResource('sistemmonetisasis', SistemMonetisasiController::class)->names('sistemmonetisasi');
});
