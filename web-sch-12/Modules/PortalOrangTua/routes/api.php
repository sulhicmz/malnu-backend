<?php

use Illuminate\Support\Facades\Route;
use Modules\PortalOrangTua\app\Http\Controllers\PortalOrangTuaController;

Route::middleware(['auth:sanctum'])->prefix('v1')->group(function () {
    Route::apiResource('portalorangtuas', PortalOrangTuaController::class)->names('portalorangtua');
});
