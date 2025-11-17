<?php

use Illuminate\Support\Facades\Route;
use Modules\SPP\app\Http\Controllers\SPPController;

Route::middleware(['auth:sanctum'])->prefix('v1')->group(function () {
    Route::apiResource('spps', SPPController::class)->names('spp');
});
