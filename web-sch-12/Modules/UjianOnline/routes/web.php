<?php

use Illuminate\Support\Facades\Route;
use Modules\UjianOnline\app\Http\Controllers\UjianOnlineController;

Route::middleware(['auth', 'verified'])->group(function () {
    Route::resource('ujianonlines', UjianOnlineController::class)->names('ujianonline');
});
