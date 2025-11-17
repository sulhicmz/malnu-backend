<?php

use Illuminate\Support\Facades\Route;
use Modules\SPP\app\Http\Controllers\SPPController;

Route::middleware(['auth', 'verified'])->group(function () {
    Route::resource('spps', SPPController::class)->names('spp');
});
