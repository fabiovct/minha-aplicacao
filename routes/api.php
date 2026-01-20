<?php

use App\Http\Controllers\Api\AuthController;

Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me', [AuthController::class, 'me']);

    Route::prefix('list')
    ->group(base_path('routes/lista/list.routes.php'));

    Route::prefix('product')
    ->group(base_path('routes/product/product.routes.php'));
});
