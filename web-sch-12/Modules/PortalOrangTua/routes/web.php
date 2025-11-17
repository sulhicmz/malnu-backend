<?php

use Illuminate\Support\Facades\Route;
use Modules\PortalOrangTua\app\Http\Controllers\PortalOrangTuaController;

Route::middleware(['auth', 'verified'])->group(function () {
    Route::resource('portalorangtuas', PortalOrangTuaController::class)->names('portalorangtua');
});
