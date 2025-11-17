<?php

use Illuminate\Support\Facades\Route;
use Modules\ERaport\app\Http\Controllers\ERaportController;

Route::middleware(['auth', 'verified'])->group(function () {
    Route::resource('eraports', ERaportController::class)->names('eraport');
});
