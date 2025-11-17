<?php

use Illuminate\Support\Facades\Route;
use Modules\LaporanAnalitik\app\Http\Controllers\LaporanAnalitikController;

Route::middleware(['auth', 'verified'])->group(function () {
    Route::resource('laporananalitiks', LaporanAnalitikController::class)->names('laporananalitik');
});
