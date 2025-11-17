<?php

use Illuminate\Support\Facades\Route;
use Modules\ManajemenSekolah\app\Http\Controllers\ManajemenSekolahController;

Route::middleware(['auth', 'verified'])->group(function () {
    Route::resource('manajemensekolahs', ManajemenSekolahController::class)->names('manajemensekolah');
});
