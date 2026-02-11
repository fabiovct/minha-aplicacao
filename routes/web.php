<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\api\AuthController;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/docs', function () {
    return view('scribe.index');
});

//Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.post');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
