<?php

declare(strict_types=1);

use App\Http\Controllers\admin\HomeController;
use Hypervel\Support\Facades\Route;

Route::get('/', [HomeController::class, 'indexView']);