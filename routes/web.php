<?php

use App\Http\Controllers\Admin\ClassController;
use App\Http\Controllers\Admin\ClubController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\Model3dController as AdminModel3dController;
use App\Http\Controllers\Admin\SubjectController;
use App\Http\Controllers\Admin\UserController as AdminUserController;
use App\Http\Controllers\Client\ClubController as ClientClubController;
use App\Http\Controllers\Client\Model3dController as ClientModel3dController;
use App\Http\Controllers\Client\StudentController as ClientStudentController;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\ProfileController;
use App\Models\Model3d;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('client.front.welcome');
});

Route::get('/dashboard', function () {
    $latestModels = Model3d::with('subject')->latest()->take(6)->get();

    return view('client.back.dashboard', compact('latestModels'));
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::prefix('models/3d')->name('client.models.3d.')->group(function () {
        Route::get('/', [ClientModel3dController::class, 'index'])->name('index');
        Route::get('/{model3d}', [ClientModel3dController::class, 'show'])->name('show');
        
        // Question routes
        Route::get('/{model3d}/questions', [\App\Http\Controllers\Client\ModelQuestionController::class, 'getQuestions'])->name('questions.get');
        Route::post('/{model3d}/questions/submit', [\App\Http\Controllers\Client\ModelQuestionController::class, 'submitAnswers'])->name('questions.submit');
    });

    // Teacher-only routes
    Route::middleware('teacher')->group(function () {
        Route::get('/clubs', [ClientClubController::class, 'index'])->name('client.clubs.index');
        Route::get('/clubs/{club}/join', [ClientClubController::class, 'showJoinForm'])->name('client.clubs.join.form');
        Route::post('/clubs/{club}/join', [ClientClubController::class, 'join'])->name('client.clubs.join');
        
        // Student registration routes
        Route::get('/students', [ClientStudentController::class, 'index'])->name('client.students.index');
        Route::post('/students', [ClientStudentController::class, 'store'])->name('client.students.store');
    });
});

Route::prefix('admin')->name('admin.')->group(function () {
    Route::middleware('guest')->group(function () {
        Route::get('login', [AuthenticatedSessionController::class, 'create'])
            ->name('login');

        Route::post('login', [AuthenticatedSessionController::class, 'store'])
            ->name('login.store')
            ->defaults('admin_login', true);
    });

    Route::middleware(['auth', 'admin'])->group(function () {
        Route::get('dashboard', [AdminDashboardController::class, 'index'])->name('dashboard');
        Route::post('logout', [AuthenticatedSessionController::class, 'destroy'])->name('logout');
        
        Route::prefix('classes')->name('classes.')->group(function () {
            Route::get('/', [ClassController::class, 'index'])->name('index');
            Route::post('/', [ClassController::class, 'store'])->name('store');
            Route::put('/{schoolClass}', [ClassController::class, 'update'])->name('update');
            Route::post('/{schoolClass}/toggle-status', [ClassController::class, 'toggleStatus'])->name('toggle-status');
        });

        Route::prefix('clubs')->name('clubs.')->group(function () {
            Route::get('/', [ClubController::class, 'index'])->name('index');
            Route::post('/', [ClubController::class, 'store'])->name('store');
            Route::put('/{club}', [ClubController::class, 'update'])->name('update');
            Route::post('/{club}/toggle-status', [ClubController::class, 'toggleStatus'])->name('toggle-status');
        });

        // Progress & Tracking routes
        Route::get('progress', [\App\Http\Controllers\Admin\ProgressController::class, 'index'])->name('progress.index');
        Route::get('progress/student/{user}', [\App\Http\Controllers\Admin\ProgressController::class, 'showStudent'])->name('progress.student');
        Route::get('progress/model/{model3d}', [\App\Http\Controllers\Admin\ProgressController::class, 'showModel'])->name('progress.model');
        Route::get('progress/student/{user}/model/{model3d}', [\App\Http\Controllers\Admin\ProgressController::class, 'showDetail'])->name('progress.detail');
        
        // User Management routes
        Route::prefix('users')->name('users.')->group(function () {
            Route::get('/', [AdminUserController::class, 'index'])->name('index');
            Route::get('/students', [AdminUserController::class, 'students'])->name('students');
            Route::post('/', [AdminUserController::class, 'store'])->name('store');
            Route::put('/{user}', [AdminUserController::class, 'update'])->name('update');
            Route::post('/{user}/toggle-status', [AdminUserController::class, 'toggleStatus'])->name('toggle-status');
        });
    });
});

// Subjects routes - outside name prefix to use 'subjects.*' names
Route::prefix('admin')->middleware(['auth', 'admin'])->group(function () {
    Route::resource('subjects', SubjectController::class)->names('subjects');
    
    // 3D Models routes - with admin prefix in route names
    Route::prefix('models')->name('admin.models.')->group(function () {
        Route::get('3d', [AdminModel3dController::class, 'index'])->name('3d.index');
        Route::get('3d/create', [AdminModel3dController::class, 'create'])->name('3d.create');
        Route::post('3d', [AdminModel3dController::class, 'store'])->name('3d.store');
        Route::get('3d/{model3d}', [AdminModel3dController::class, 'show'])->name('3d.show');
        Route::get('3d/{model3d}/edit', [AdminModel3dController::class, 'edit'])->name('3d.edit');
        Route::put('3d/{model3d}', [AdminModel3dController::class, 'update'])->name('3d.update');
        Route::delete('3d/{model3d}', [AdminModel3dController::class, 'destroy'])->name('3d.destroy');
        
        // Questions routes
        Route::get('3d/{model3d}/questions', [\App\Http\Controllers\Admin\ModelQuestionController::class, 'index'])->name('3d.questions.index');
        Route::get('3d/{model3d}/questions/create', [\App\Http\Controllers\Admin\ModelQuestionController::class, 'create'])->name('3d.questions.create');
        Route::post('3d/{model3d}/questions', [\App\Http\Controllers\Admin\ModelQuestionController::class, 'store'])->name('3d.questions.store');
        Route::get('3d/{model3d}/questions/{question}/edit', [\App\Http\Controllers\Admin\ModelQuestionController::class, 'edit'])->name('3d.questions.edit');
        Route::put('3d/{model3d}/questions/{question}', [\App\Http\Controllers\Admin\ModelQuestionController::class, 'update'])->name('3d.questions.update');
        Route::delete('3d/{model3d}/questions/{question}', [\App\Http\Controllers\Admin\ModelQuestionController::class, 'destroy'])->name('3d.questions.destroy');
        Route::post('3d/{model3d}/questions/order', [\App\Http\Controllers\Admin\ModelQuestionController::class, 'updateOrder'])->name('3d.questions.order');
    });
});

require __DIR__.'/auth.php';
