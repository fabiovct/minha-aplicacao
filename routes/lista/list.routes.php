<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\List\ListController;

// Route::middleware('auth:sanctum')->group(function () {
    Route::get('/', [ListController::class, 'list']);
    Route::post('/', [ListController::class, 'create']);
    Route::put('/{id}', [ListController::class, 'edit']);
    Route::delete('/{id}', [ListController::class, 'delete']);
// });