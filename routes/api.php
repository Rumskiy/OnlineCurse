<?php

use App\Http\Controllers\admin\DashboardController;
use App\Http\Controllers\AuthenticationController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\AccountController;
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


Route::get('/categories', [CategoryController::class, 'index']);
Route::post('/categories', [CategoryController::class, 'store']);
Route::get('/categories/{category}', [CategoryController::class, 'show']);
Route::put('/categories/{category}', [CategoryController::class, 'update']);
Route::delete('/categories/{category}', [CategoryController::class, 'destroy']);


Route::get('/courses', [CourseController::class, 'index']);

Route::apiResource('courses', CourseController::class);
Route::apiResource('sections', \App\Http\Controllers\SectionController::class);
Route::apiResource('tests', \App\Http\Controllers\TestController::class);
Route::middleware('auth:sanctum')->get('/user/courses', [CourseController::class, 'userCourses']);



