<?php

use App\Http\Controllers\Product\ProductController;
use Illuminate\Support\Facades\Route;

// Route::middleware('auth:sanctum')->group(function () {
    Route::get('/', [ProductController::class, 'list']);
    Route::post('/', [ProductController::class, 'create']);
    Route::put('/{id}', [ProductController::class, 'edit']);
    Route::delete('/{id}', [ProductController::class, 'delete']);