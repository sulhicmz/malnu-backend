<?php

use Illuminate\Support\Facades\Route;
use Modules\SistemMonetisasi\app\Http\Controllers\SistemMonetisasiController;

Route::middleware(['auth', 'verified'])->group(function () {
    Route::resource('sistemmonetisasis', SistemMonetisasiController::class)->names('sistemmonetisasi');
});
