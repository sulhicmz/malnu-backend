<?php

use Illuminate\Support\Facades\Route;
use Modules\PPDB\app\Http\Controllers\PPDBController;

Route::middleware(['auth', 'verified'])->group(function () {
    Route::resource('ppdbs', PPDBController::class)->names('ppdb');
});
