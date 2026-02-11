<?php

use App\Http\Controllers\Dashboard\DashboardController;
use Illuminate\Support\Facades\Route;

// Route::middleware('auth:sanctum')->group(function () {
    Route::get('/data', [DashboardController::class, 'getData']);