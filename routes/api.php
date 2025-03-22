<?php

use App\Http\Controllers\admin\DashboardController;
use App\Http\Controllers\AuthenticationController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\AccountController;
use App\Http\Controllers\SectionController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CourseController;
use App\Http\Controllers\CategoryController;

Route::post('authenticate', [AuthenticationController::class, 'authenticate'])->middleware('throttle:5,1');


Route::middleware('auth:sanctum')->group(function () {
    Route::get('dashboard', [DashboardController::class, 'index']);
    Route::post('logout', [AuthenticationController::class, 'logout']);
});

Route::post('authenticate', [AuthenticationController::class, 'authenticate']);
Route::post('register', [RegisterController::class, 'register']);

Route::middleware('auth:sanctum')->get('account', [AccountController::class, 'getUser']);
Route::middleware('auth:sanctum')->put('account', [AccountController::class, 'update']);

Route::prefix('categories')->group(function () {
    Route::get('/', [CategoryController::class, 'index']);
    Route::post('/', [CategoryController::class, 'store']);
    Route::get('/{category}', [CategoryController::class, 'show']);
    Route::put('/{category}', [CategoryController::class, 'update']);
    Route::delete('/{category}', [CategoryController::class, 'destroy']);
});

Route::prefix('courses')->group(function () {
    Route::get('/', [CourseController::class, 'index']);
    Route::post('/', [CourseController::class, 'store'])->middleware('auth:sanctum');
    Route::get('/{course}', [CourseController::class, 'show']);
    Route::put('/edit/{course}', [CourseController::class, 'update'])->middleware('auth:sanctum');
    Route::get('/sections/{course}', [SectionController::class, 'index']);
});

Route::post('/sections', [SectionController::class, 'store']);
Route::put('/sections/{section}', [SectionController::class, 'update']);
Route::delete('/sections/{section}', [SectionController::class, 'destroy']);

Route::middleware('auth:sanctum')->get('/user/courses', [CourseController::class, 'userCourses']);



