<?php

use App\Http\Controllers\admin\DashboardController;
use App\Http\Controllers\AuthenticationController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\AccountController;
use Illuminate\Support\Facades\Route;

Route::post('authenticate', [AuthenticationController::class, 'authenticate'])->middleware('throttle:5,1');


Route::middleware('auth:sanctum')->group(function () {
    Route::get('dashboard', [DashboardController::class, 'index']);
    Route::post('logout', [AuthenticationController::class, 'logout']);
});

Route::post('authenticate', [AuthenticationController::class, 'authenticate']);
Route::post('register', [RegisterController::class, 'register']);

Route::middleware('auth:sanctum')->get('account', [AccountController::class, 'getUser']);
Route::middleware('auth:sanctum')->put('account', [AccountController::class, 'update']);

