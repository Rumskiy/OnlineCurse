<?php

use App\Http\Controllers\admin\DashboardController;
use App\Http\Controllers\AuthenticationController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\AccountController;
use App\Http\Controllers\CertificateController;
use App\Http\Controllers\QuizAttemptController;
use App\Http\Controllers\SectionController;
use App\Http\Controllers\TestController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CourseController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\ProgressController;
use App\Http\Controllers\ClassAssignmentController;

Route::post('authenticate', [AuthenticationController::class, 'authenticate'])->middleware('throttle:5,1');

Route::middleware('auth:sanctum')->group(function () {
    Route::get('dashboard', [DashboardController::class, 'index']);
    Route::post('logout', [AuthenticationController::class, 'logout']);
    
    // Progress
    Route::post('progress/section/{sectionId}/complete', [ProgressController::class, 'markAsCompleted']);
    Route::get('progress/course/{courseId}', [ProgressController::class, 'getProgress']);
    Route::get('progress/stats', [ProgressController::class, 'getUserStats']);

    // Class Course Assignments
    Route::get('class-assignments', [ClassAssignmentController::class, 'index']);
    Route::post('class-assignments', [ClassAssignmentController::class, 'assign']);
    Route::delete('class-assignments/{id}', [ClassAssignmentController::class, 'unassign']);
});

use App\Http\Controllers\Auth\PasswordResetController;

Route::post('authenticate', [AuthenticationController::class, 'authenticate']);
// Реєстрація тепер виконується виключно через Filament Admin Panel. Громадська реєстрація відключена:
// Route::post('register', [RegisterController::class, 'register']);

// Відновлення пароля:
Route::post('forgot-password', [PasswordResetController::class, 'sendResetToken']);
Route::post('reset-password', [PasswordResetController::class, 'resetPassword']);

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
    Route::delete('/{course}', [CourseController::class, 'destroy'])->middleware('auth:sanctum');
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
    Route::get('/quiz/attempts/by-course', [QuizAttemptController::class, 'userAttemptsByCourse'])->name('quiz.attempts.byCourse'); // НОВИЙ МАРШРУТ
    Route::get('/quiz/attempts/{id}', [QuizAttemptController::class, 'show'])->name('quiz.attempts.show'); // Отримати деталі спроби
});



Route::middleware('auth:sanctum')->get('/user/courses', [CourseController::class, 'userCourses']);
Route::middleware('auth:sanctum')->get('/courses/{course}/certificate', [CertificateController::class, 'generateCertificate'])
    ->name('courses.certificate.generate');


