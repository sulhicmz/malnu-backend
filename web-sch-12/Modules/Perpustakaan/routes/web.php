<?php

use Illuminate\Support\Facades\Route;
use Modules\Perpustakaan\app\Http\Controllers\PerpustakaanController;

Route::middleware(['auth', 'verified'])->group(function () {
    Route::resource('perpustakaans', PerpustakaanController::class)->names('perpustakaan');
});
