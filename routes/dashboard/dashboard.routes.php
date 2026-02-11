<?php

use App\Http\Controllers\Dashboard\DashboardController;
use Illuminate\Support\Facades\Route;

    Route::get('/data', [DashboardController::class, 'getData']);