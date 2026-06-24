<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\StudentApiController;

/*
|--------------------------------------------------------------------------
| API Routes — EzPAIzy Mobile App
|--------------------------------------------------------------------------
*/

// Public: login (no auth needed)
Route::post('/login', [StudentApiController::class, 'login']);

// Protected: require valid Sanctum token
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [StudentApiController::class, 'logout']);

    // Dashboard
    Route::get('/student/dashboard', [StudentApiController::class, 'dashboard']);

    // Quizzes
    Route::get('/student/quizzes',               [StudentApiController::class, 'quizzes']);
    Route::get('/student/quiz/{quiz}',            [StudentApiController::class, 'quizDetail']);
    Route::post('/student/quiz/{quiz}/submit',    [StudentApiController::class, 'submitQuiz']);

    // Learning Materials
    Route::get('/student/contents',              [StudentApiController::class, 'contents']);
    Route::get('/student/contents/{content}',    [StudentApiController::class, 'contentDetail']);

    // Flashcards
    Route::get('/student/flashcards',            [StudentApiController::class, 'flashcards']);
    Route::get('/student/flashcards/{set}',      [StudentApiController::class, 'flashcardDetail']);
    Route::get('/student/flashcards/{set}/study', [StudentApiController::class, 'studyFlashcards']);
    Route::post('/student/flashcards/{flashcard}/review', [StudentApiController::class, 'reviewFlashcard']);

    // Progress
    Route::get('/student/progress',                 [StudentApiController::class, 'progress']);
    Route::get('/student/progress/{progress}',      [StudentApiController::class, 'progressDetail']);

    // Revision / Favorites
    Route::get('/student/revision',                      [StudentApiController::class, 'revision']);
    Route::post('/student/favorites/{content}',          [StudentApiController::class, 'addFavorite']);
    Route::delete('/student/favorites/{content}',        [StudentApiController::class, 'removeFavorite']);

    // Daily Quran
    Route::get('/student/daily-quran',           [StudentApiController::class, 'dailyQuran']);

    // Learning Style / Diagnosis
    Route::get('/student/diagnosis',             [StudentApiController::class, 'getDiagnosis']);
    Route::post('/student/diagnosis',            [StudentApiController::class, 'storeDiagnosis']);
    Route::post('/student/diagnosis/reset',      [StudentApiController::class, 'resetDiagnosis']);
    Route::get('/student/quran-mood',            [StudentApiController::class, 'quranMood']);
});

