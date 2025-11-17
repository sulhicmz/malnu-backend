<?php

use Illuminate\Support\Facades\Route;
use Modules\ERaport\app\Http\Controllers\ERaportController;

Route::middleware(['auth:sanctum'])->prefix('v1')->group(function () {
    Route::apiResource('eraports', ERaportController::class)->names('eraport');
});
