<?php

use App\Http\Controllers\List\ListController;

// Route::middleware('auth:sanctum')->group(function () {
    Route::get('/', [ListController::class, 'list']);
// });