<?php

use App\Http\Controllers\admin\DashboardController;
use App\Http\Controllers\AuthenticationController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\AccountController;
use App\Http\Controllers\QuizAttemptController;
use App\Http\Controllers\SectionController;
use App\Http\Controllers\TestController;
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
Route::middleware('auth:sanctum')->post('account', [AccountController::class, 'update']);

Route::prefix('categories')->group(function () {
    Route::get('/', [CategoryController::class, 'index']);
    Route::post('/', [CategoryController::class, 'store']);
    Route::get('/{category}', [CategoryController::class, 'show']);
    Route::put('/{category}', [CategoryController::class, 'update']);
    Route::delete('/{category}', [CategoryController::class, 'destroy']);
});

Route::prefix('courses')->group(function () {
    //Course
    Route::get('/', [CourseController::class, 'index']);
    Route::post('/', [CourseController::class, 'store'])->middleware('auth:sanctum');
    Route::get('/{course}', [CourseController::class, 'show']);
    Route::get('/category/{course}', [CourseController::class, 'byCategoryId']);
    Route::post('/edit/{course}', [CourseController::class, 'update'])->middleware('auth:sanctum');
    //Section
    Route::get('{course}/sections', [SectionController::class, 'index']); // Отримати секції курсу
    Route::get('/sections/{section}', [SectionController::class, 'show']); // Отримати конкретну секцію
    Route::post('/sections', [SectionController::class, 'store'])->middleware('auth:sanctum'); // Створити секцію
    Route::post('/sections/{section}', [SectionController::class, 'update'])->middleware('auth:sanctum'); // Оновити секцію
    Route::delete('/sections/{section}', [SectionController::class, 'destroy'])->middleware('auth:sanctum'); // Видалити секцію
    //Tests
    Route::post('/section/tests', [TestController::class, 'store'])->middleware('auth:sanctum');
    Route::post('/section/tests/{test}', [TestController::class, 'update'])->middleware('auth:sanctum');
    Route::delete('/section/tests/{test}', [TestController::class, 'destroy'])->middleware('auth:sanctum');
    Route::get('/tests/{id}', [TestController::class, 'show'])->middleware('auth:sanctum');
    Route::get('/section/tests/{test}', [TestController::class, 'sectionTest']);
});

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/quiz/attempts', [QuizAttemptController::class, 'store'])->name('quiz.attempts.store');
    Route::get('/quiz/attempts', [QuizAttemptController::class, 'index'])->name('quiz.attempts.index'); // Отримати історію спроб
    Route::get('/quiz/attempts/{id}', [QuizAttemptController::class, 'show'])->name('quiz.attempts.show'); // Отримати деталі спроби
});



Route::middleware('auth:sanctum')->get('/user/courses', [CourseController::class, 'userCourses']);



