<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\StudentController;
use App\Http\Controllers\QuizController;
use App\Http\Controllers\FavoriteController;
use App\Http\Controllers\ProgressController;
use App\Http\Controllers\WidgetController;
use App\Http\Controllers\Student\ExpertSystemController;
use App\Http\Controllers\TeacherController;
use App\Http\Controllers\ContentController;
use App\Http\Controllers\Teacher\FlashcardSetController;
use App\Http\Controllers\StudentManagementController;
use App\Http\Controllers\TopicController;
use App\Http\Controllers\ProfileController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// Authentication Routes
Auth::routes();

// Default Redirections
Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/home', [HomeController::class, 'index']);

// Profile Routes
Route::middleware(['auth'])->prefix('profile')->name('profile.')->group(function () {
    Route::get('/', [ProfileController::class, 'show'])->name('show');
    Route::get('/edit', [ProfileController::class, 'edit'])->name('edit');
    Route::put('/update', [ProfileController::class, 'update'])->name('update');
});

// Protected Student Web Routes
Route::middleware(['auth', 'student'])->prefix('student')->name('student.')->group(function () {
    // Dashboard
    Route::get('/dashboard', [StudentController::class, 'dashboard'])->name('dashboard');

    // Quizzes
    Route::get('/quizzes', [StudentController::class, 'quizzes'])->name('quizzes');
    Route::get('/quizzes/folder/{topic}', [StudentController::class, 'quizFolder'])->name('quizzes.folder');
    Route::get('/quiz/{quiz}/take', [QuizController::class, 'take'])->name('quiz.take');
    Route::post('/quiz/{quiz}/submit', [QuizController::class, 'submit'])->name('submit');

    // Learning Materials
    Route::get('/contents', [StudentController::class, 'contents'])->name('contents.index');
    Route::get('/contents/folder/{topic}', [StudentController::class, 'contentFolder'])->name('contents.folder');
    Route::get('/contents/{content}', [StudentController::class, 'showContent'])->name('contents.show');

    // Flashcards
    Route::get('/flashcards', [StudentController::class, 'flashcards'])->name('flashcards.index');
    Route::get('/flashcards/folder/{topic}', [StudentController::class, 'flashcardFolder'])->name('flashcards.folder');
    Route::get('/flashcards/{set}', [StudentController::class, 'showFlashcardSet'])->name('flashcards.show');
    Route::post('/flashcards/{set}/reset', [StudentController::class, 'resetFlashcardSet'])->name('flashcards.reset');
    Route::post('/flashcards/{flashcard}/review', [StudentController::class, 'reviewFlashcard'])->name('flashcards.review');

    // Daily Quran & Mood
    Route::get('/daily-quran', [StudentController::class, 'dailyQuran'])->name('daily_quran');
    Route::get('/quran/mood', [StudentController::class, 'quranMood'])->name('quran.mood');

    // Diagnosis / Expert System
    Route::get('/diagnosis', [ExpertSystemController::class, 'show'])->name('diagnosis.show');
    Route::get('/diagnosis/create', [ExpertSystemController::class, 'create'])->name('diagnosis.create');
    Route::post('/diagnosis/store', [ExpertSystemController::class, 'store'])->name('diagnosis.store');
    Route::post('/diagnosis/reset', [ExpertSystemController::class, 'reset'])->name('diagnosis.reset');

    // Progress
    Route::get('/progress', [ProgressController::class, 'index'])->name('progress');

    // Student Notes
    Route::post('/notes/save', [\App\Http\Controllers\Student\StudentNoteController::class, 'save'])->name('notes.save');
    Route::get('/notes/folder/{topic}', [\App\Http\Controllers\Student\StudentNoteController::class, 'showFolder'])->name('notes.folder');
    Route::delete('/notes/{note}', [\App\Http\Controllers\Student\StudentNoteController::class, 'destroy'])->name('notes.destroy');

    // Widgets management
    Route::get('/widgets', [WidgetController::class, 'index'])->name('widgets.index');
    Route::post('/widgets', [WidgetController::class, 'store'])->name('widgets.store');
    Route::get('/widgets/{widget}/edit', [WidgetController::class, 'edit'])->name('widgets.edit');
    Route::put('/widgets/{widget}', [WidgetController::class, 'update'])->name('widgets.update');
    Route::post('/widgets/reorder', [WidgetController::class, 'reorder'])->name('widgets.reorder');
    Route::post('/widgets/{widget}/destroy', [WidgetController::class, 'destroy'])->name('widgets.destroy');

    // Revision / Favorites
    Route::get('/revision', [FavoriteController::class, 'index'])->name('revision');
    Route::post('/favorites/{content}', [FavoriteController::class, 'store'])->name('favorites.add');
    Route::delete('/favorites/{content}', [FavoriteController::class, 'destroy'])->name('favorites.remove');
    Route::post('/favorites/flashcard/{flashcardSet}', [FavoriteController::class, 'storeFlashcard'])->name('favorites.flashcard.add');
    Route::delete('/favorites/flashcard/{flashcardSet}', [FavoriteController::class, 'destroyFlashcard'])->name('favorites.flashcard.remove');
});

// Protected Teacher Web Routes
Route::middleware(['auth', 'teacher'])->prefix('teacher')->name('teacher.')->group(function () {
    // Dashboard
    Route::get('/dashboard', [TeacherController::class, 'dashboard'])->name('dashboard');

    // Quizzes Management
    Route::get('/quizzes', [QuizController::class, 'index'])->name('quizzes.index');
    Route::get('/quizzes/folder/{topic}', [QuizController::class, 'folder'])->name('quizzes.folder');
    Route::get('/quizzes/generate', [QuizController::class, 'generate'])->name('quizzes.generate');
    Route::get('/quizzes/create', [QuizController::class, 'create'])->name('quizzes.create');
    Route::post('/quizzes/store', [QuizController::class, 'store'])->name('quizzes.store');
    Route::get('/quizzes/{quiz}', [QuizController::class, 'show'])->name('quizzes.show');
    Route::get('/quizzes/{quiz}/edit', [QuizController::class, 'edit'])->name('quizzes.edit');
    Route::put('/quizzes/{quiz}/update', [QuizController::class, 'update'])->name('quizzes.update');
    Route::delete('/quizzes/{quiz}/destroy', [QuizController::class, 'destroy'])->name('quizzes.destroy');

    // AI Quiz Generation & Comparison
    Route::post('/quizzes/process-generate', [QuizController::class, 'processGenerate'])->name('quizzes.process_generate');
    Route::post('/quizzes/process-compare', [QuizController::class, 'processCompare'])->name('quizzes.process_compare');
    Route::post('/quizzes/save-selected', [QuizController::class, 'saveSelected'])->name('quizzes.save_selected');

    // Learning Materials (Contents)
    Route::get('/contents', [ContentController::class, 'index'])->name('contents.index');
    Route::get('/contents/folder/{topic}', [ContentController::class, 'folder'])->name('contents.folder');
    Route::get('/contents/selection', [ContentController::class, 'selection'])->name('contents.selection');
    Route::get('/contents/create', [ContentController::class, 'create'])->name('contents.create');
    Route::post('/contents/store', [ContentController::class, 'store'])->name('contents.store');
    Route::get('/contents/{content}', [ContentController::class, 'show'])->name('contents.show');
    Route::get('/contents/{content}/edit', [ContentController::class, 'edit'])->name('contents.edit');
    Route::put('/contents/{content}/update', [ContentController::class, 'update'])->name('contents.update');
    Route::delete('/contents/{content}/destroy', [ContentController::class, 'destroy'])->name('contents.destroy');
 
    // Flashcard Sets (hyphen-named to match view references)
    Route::get('/flashcard-sets', [FlashcardSetController::class, 'index'])->name('flashcard-sets.index');
    Route::get('/flashcard-sets/folder/{topic}', [FlashcardSetController::class, 'folder'])->name('flashcard-sets.folder');
    Route::get('/flashcard-sets/create', [FlashcardSetController::class, 'create'])->name('flashcard-sets.create');
    Route::post('/flashcard-sets/store', [FlashcardSetController::class, 'store'])->name('flashcard-sets.store');
    Route::post('/flashcard-sets/import-csv', [FlashcardSetController::class, 'importCsv'])->name('flashcards.import_csv');
    Route::get('/flashcard-sets/{flashcardSet}/edit', [FlashcardSetController::class, 'edit'])->name('flashcard-sets.edit');
    Route::put('/flashcard-sets/{flashcardSet}/update', [FlashcardSetController::class, 'update'])->name('flashcard-sets.update');
    Route::delete('/flashcard-sets/{flashcardSet}/destroy', [FlashcardSetController::class, 'destroy'])->name('flashcard-sets.destroy');

    // Student Management
    Route::get('/students', [StudentManagementController::class, 'index'])->name('students.index');
    Route::get('/students/create', [StudentManagementController::class, 'create'])->name('students.create');
    Route::post('/students/store', [StudentManagementController::class, 'store'])->name('students.store');
    Route::get('/students/{student}', [StudentManagementController::class, 'show'])->name('students.show');
    Route::get('/students/{student}/edit', [StudentManagementController::class, 'edit'])->name('students.edit');
    Route::put('/students/{student}/update', [StudentManagementController::class, 'update'])->name('students.update');
    Route::delete('/students/{student}/destroy', [StudentManagementController::class, 'destroy'])->name('students.destroy');
    Route::post('/students/progress/{progress}/grade', [StudentManagementController::class, 'grade'])->name('students.grade');

    // Topics Management
    Route::post('/topics', [TopicController::class, 'store'])->name('topics.store');
    Route::put('/topics/{topicName}', [TopicController::class, 'update'])->name('topics.update');
    Route::delete('/topics/{topic}', [TopicController::class, 'destroy'])->name('topics.destroy');
});

// Protected Admin Web Routes
Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    // Dashboard
    Route::get('/dashboard', [App\Http\Controllers\Admin\AdminController::class, 'dashboard'])->name('dashboard');

    // User Management
    Route::get('/users', [App\Http\Controllers\Admin\UserController::class, 'index'])->name('users.index');
    Route::get('/users/create', [App\Http\Controllers\Admin\UserController::class, 'create'])->name('users.create');
    Route::post('/users/store', [App\Http\Controllers\Admin\UserController::class, 'store'])->name('users.store');
    Route::get('/users/{user}/edit', [App\Http\Controllers\Admin\UserController::class, 'edit'])->name('users.edit');
    Route::post('/users/{user}/update', [App\Http\Controllers\Admin\UserController::class, 'update'])->name('users.update');
    Route::post('/users/{user}/toggle-suspension', [App\Http\Controllers\Admin\UserController::class, 'toggleSuspension'])->name('users.toggle-suspension');
    Route::post('/users/{user}/toggle-approval', [App\Http\Controllers\Admin\UserController::class, 'toggleApproval'])->name('users.toggle-approval');
    Route::post('/users/{user}/reset-password', [App\Http\Controllers\Admin\UserController::class, 'resetPassword'])->name('users.reset-password');
    Route::delete('/users/{user}/destroy', [App\Http\Controllers\Admin\UserController::class, 'destroy'])->name('users.destroy');

    // Moderation (Content Control)
    Route::get('/moderation', [App\Http\Controllers\Admin\ModerationController::class, 'index'])->name('moderation.index');
    Route::post('/moderation/content/{content}/toggle-flag', [App\Http\Controllers\Admin\ModerationController::class, 'toggleContentFlag'])->name('moderation.content.toggle-flag');
    Route::delete('/moderation/content/{content}/destroy', [App\Http\Controllers\Admin\ModerationController::class, 'destroyContent'])->name('moderation.content.destroy');
    Route::post('/moderation/flashcard/{flashcardSet}/toggle-flag', [App\Http\Controllers\Admin\ModerationController::class, 'toggleFlashcardFlag'])->name('moderation.flashcard.toggle-flag');
    Route::delete('/moderation/flashcard/{flashcardSet}/destroy', [App\Http\Controllers\Admin\ModerationController::class, 'destroyFlashcardSet'])->name('moderation.flashcard.destroy');
    Route::post('/moderation/quiz/{quiz}/toggle-flag', [App\Http\Controllers\Admin\ModerationController::class, 'toggleQuizFlag'])->name('moderation.quiz.toggle-flag');
    Route::delete('/moderation/quiz/{quiz}/destroy', [App\Http\Controllers\Admin\ModerationController::class, 'destroyQuiz'])->name('moderation.quiz.destroy');
});
