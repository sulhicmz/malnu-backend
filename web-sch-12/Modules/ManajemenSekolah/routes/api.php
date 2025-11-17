<?php

use Illuminate\Support\Facades\Route;
use Modules\ManajemenSekolah\app\Http\Controllers\ManajemenSekolahController;

Route::middleware(['auth:sanctum'])->prefix('v1')->group(function () {
    Route::apiResource('manajemensekolahs', ManajemenSekolahController::class)->names('manajemensekolah');
});
