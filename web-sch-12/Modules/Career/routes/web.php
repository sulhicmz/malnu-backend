<?php

use Illuminate\Support\Facades\Route;
use Modules\Career\app\Http\Controllers\CareerController;

Route::middleware(['auth', 'verified'])->group(function () {
    Route::resource('careers', CareerController::class)->names('career');
});
