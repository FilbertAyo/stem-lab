<?php

use App\Http\Controllers\Admin\ClassController;
use App\Http\Controllers\Admin\ClubController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\Model3dController as AdminModel3dController;
use App\Http\Controllers\Admin\SchoolController;
use App\Http\Controllers\Admin\SubjectController;
use App\Http\Controllers\Admin\UserController as AdminUserController;
use App\Http\Controllers\Admin\VrExperimentController;
use App\Http\Controllers\Client\ClubController as ClientClubController;
use App\Http\Controllers\Client\Model3dController as ClientModel3dController;
use App\Http\Controllers\Client\StudentController as ClientStudentController;
use App\Http\Controllers\Client\VrExperimentController as ClientVrExperimentController;
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

    // VR Experiments routes
    Route::prefix('vr-experiments')->name('client.vr-experiments.')->group(function () {
        Route::get('/', [ClientVrExperimentController::class, 'index'])->name('index');
        Route::get('/{vrExperiment}', [ClientVrExperimentController::class, 'show'])->name('show');
    });

    // VR Assessment/Quiz routes
    Route::prefix('vr-quizzes')->name('client.vr-assessments.')->group(function () {
        Route::get('/', [\App\Http\Controllers\Client\VrAssessmentController::class, 'index'])->name('index');
        Route::get('/{questionBank}', [\App\Http\Controllers\Client\VrAssessmentController::class, 'show'])->name('show');
        Route::post('/{questionBank}/start', [\App\Http\Controllers\Client\VrAssessmentController::class, 'start'])->name('start');
        Route::get('/attempt/{attempt}', [\App\Http\Controllers\Client\VrAssessmentController::class, 'take'])->name('take');
        Route::post('/attempt/{attempt}/save-answer', [\App\Http\Controllers\Client\VrAssessmentController::class, 'saveAnswer'])->name('save-answer');
        Route::post('/attempt/{attempt}/submit', [\App\Http\Controllers\Client\VrAssessmentController::class, 'submit'])->name('submit');
        Route::get('/attempt/{attempt}/results', [\App\Http\Controllers\Client\VrAssessmentController::class, 'results'])->name('results');
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

        Route::prefix('schools')->name('schools.')->group(function () {
            Route::get('/', [SchoolController::class, 'index'])->name('index');
            Route::get('/{school}', [SchoolController::class, 'show'])->name('show');
            Route::post('/', [SchoolController::class, 'store'])->name('store');
            Route::put('/{school}', [SchoolController::class, 'update'])->name('update');
            Route::post('/{school}/toggle-status', [SchoolController::class, 'toggleStatus'])->name('toggle-status');
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

    // VR Experiments routes
    Route::prefix('vr-experiments')->name('admin.vr-experiments.')->group(function () {
        Route::get('/', [VrExperimentController::class, 'index'])->name('index');
        Route::get('/create', [VrExperimentController::class, 'create'])->name('create');
        Route::post('/', [VrExperimentController::class, 'store'])->name('store');
        Route::get('/{vrExperiment}/edit', [VrExperimentController::class, 'edit'])->name('edit');
        Route::put('/{vrExperiment}', [VrExperimentController::class, 'update'])->name('update');
        Route::delete('/{vrExperiment}', [VrExperimentController::class, 'destroy'])->name('destroy');
    });

    // VR Assessment routes
    Route::prefix('vr-assessments')->name('admin.vr-assessments.')->group(function () {
        // Question Banks routes
        Route::prefix('question-banks')->name('question-banks.')->group(function () {
            Route::get('/', [\App\Http\Controllers\Admin\VrQuestionBankController::class, 'index'])->name('index');
            Route::get('/create', [\App\Http\Controllers\Admin\VrQuestionBankController::class, 'create'])->name('create');
            Route::post('/', [\App\Http\Controllers\Admin\VrQuestionBankController::class, 'store'])->name('store');
            Route::get('/{questionBank}', [\App\Http\Controllers\Admin\VrQuestionBankController::class, 'show'])->name('show');
            Route::get('/{questionBank}/edit', [\App\Http\Controllers\Admin\VrQuestionBankController::class, 'edit'])->name('edit');
            Route::put('/{questionBank}', [\App\Http\Controllers\Admin\VrQuestionBankController::class, 'update'])->name('update');
            Route::delete('/{questionBank}', [\App\Http\Controllers\Admin\VrQuestionBankController::class, 'destroy'])->name('destroy');
            Route::post('/{questionBank}/toggle-status', [\App\Http\Controllers\Admin\VrQuestionBankController::class, 'toggleStatus'])->name('toggle-status');
        });

        // Questions routes (nested under question banks)
        Route::prefix('question-banks/{questionBank}/questions')->name('questions.')->group(function () {
            Route::get('/create', [\App\Http\Controllers\Admin\VrQuestionController::class, 'create'])->name('create');
            Route::post('/', [\App\Http\Controllers\Admin\VrQuestionController::class, 'store'])->name('store');
            Route::get('/{question}/edit', [\App\Http\Controllers\Admin\VrQuestionController::class, 'edit'])->name('edit');
            Route::put('/{question}', [\App\Http\Controllers\Admin\VrQuestionController::class, 'update'])->name('update');
            Route::delete('/{question}', [\App\Http\Controllers\Admin\VrQuestionController::class, 'destroy'])->name('destroy');
            Route::post('/order', [\App\Http\Controllers\Admin\VrQuestionController::class, 'updateOrder'])->name('order');
        });

        // Assessment Results routes
        Route::prefix('results')->name('results.')->group(function () {
            Route::get('/', [\App\Http\Controllers\Admin\VrAssessmentResultController::class, 'index'])->name('index');
            Route::get('/{attempt}', [\App\Http\Controllers\Admin\VrAssessmentResultController::class, 'show'])->name('show');
        });
    });
});

require __DIR__.'/auth.php';
