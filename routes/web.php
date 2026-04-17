<?php

use App\Http\Controllers\Admin\ActivityLogController;
use App\Http\Controllers\Admin\ClassController;
use App\Http\Controllers\Admin\ClubController;
use App\Http\Controllers\Admin\CompetitionController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\Model3dController as AdminModel3dController;
use App\Http\Controllers\Admin\PlatformController as AdminPlatformController;
use App\Http\Controllers\Admin\ProjectCategoryController as AdminProjectCategoryController;
use App\Http\Controllers\Admin\ProjectController as AdminProjectController;
use App\Http\Controllers\Admin\SchoolController;
use App\Http\Controllers\Admin\SettingsController;
use App\Http\Controllers\Admin\SubjectController;
use App\Http\Controllers\Admin\UserController as AdminUserController;
use App\Http\Controllers\Admin\VrExperimentController;
use App\Http\Controllers\Admin\WeeklySessionController;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Client\ClubController as ClientClubController;
use App\Http\Controllers\Client\CompetitionController as ClientCompetitionController;
use App\Http\Controllers\Client\DashboardController as ClientDashboardController;
use App\Http\Controllers\Client\Model3dController as ClientModel3dController;
use App\Http\Controllers\Client\ProjectController as ClientProjectController;
use App\Http\Controllers\Client\StudentController as ClientStudentController;
use App\Http\Controllers\Client\VrExperimentController as ClientVrExperimentController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\PublicCompetitionController;
use App\Http\Controllers\PublicCompetitionJoinController;
use App\Models\Model3d;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

// Public landing and explore routes (track visitors, block on admin subdomain)
Route::middleware(['block.client.on.admin', 'track.landing.visitor'])->group(function () {
    // Root: redirect admin subdomain to login; show landing page on main domain
    Route::get('/', function () {
        $competitions = \App\Http\Controllers\PublicCompetitionController::getFeaturedCompetitions(3);

        return view('client.front.welcome', compact('competitions'));
    });

    Route::get('/privacy-policy', function () {
        return view('client.front.policy');
    })->name('privacy.policy');

    Route::get('/terms-conditions', function () {
        return view('client.front.terms');
    })->name('terms.conditions');

    Route::get('/support', function () {
        return view('client.front.contact');
    })->name('support');
    Route::post('/support', function (\Illuminate\Http\Request $request) {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email',
            'message' => 'required|string|max:5000',
        ]);

        try {
            \Illuminate\Support\Facades\Mail::to('support@adilishastemlabs.com')
                ->send(new \App\Mail\ContactSupportMail(
                    $validated['name'],
                    $validated['email'],
                    $validated['message']
                ));
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('Contact form mail failed', ['error' => $e->getMessage()]);

            return redirect()->route('support')->with('error', 'We could not send your message. Please try again later or email us directly at support@adilishastemlabs.com.');
        }

        return redirect()->route('support')->with('success', 'Thank you! We have received your message and will get back to you soon.');
    })->name('support.submit');

    // Public Competitions (accessible without login)
    Route::get('/explore/competitions', [PublicCompetitionController::class, 'index'])->name('competitions.public');
    Route::get('/explore/competitions/{competition}', [PublicCompetitionController::class, 'show'])->name('competitions.public.show');
    Route::post('/explore/competitions/{competition}/join-as-teacher', [PublicCompetitionJoinController::class, 'store'])
        ->middleware(['guest', 'throttle:10,1'])
        ->name('competitions.public.join-as-teacher');
});

// Admin subdomain routes - MUST come before client routes to ensure proper domain matching
Route::domain(parse_url(config('app.admin_url'), PHP_URL_HOST) ?? 'admin.adilishastemlabs.com')->name('admin.')->group(function () {
    // Redirect root admin subdomain to login page (or dashboard if authenticated)
    Route::get('/', function () {
        if (Auth::check() && Auth::user()?->role === 'admin') {
            return redirect(config('app.admin_url', 'https://admin.adilishastemlabs.com').'/dashboard');
        }

        return redirect(config('app.admin_url', 'https://admin.adilishastemlabs.com').'/login');
    });

    Route::middleware('guest')->group(function () {
        Route::get('login', [AuthenticatedSessionController::class, 'create'])
            ->name('login');

        Route::post('login', [AuthenticatedSessionController::class, 'store'])
            ->name('login.store');
    });

    Route::middleware(['auth', 'admin', 'track.admin.activity'])->group(function () {
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
            Route::get('/{club}', [ClubController::class, 'show'])->name('show');
            Route::put('/{club}', [ClubController::class, 'update'])->name('update');
            Route::post('/{club}/toggle-status', [ClubController::class, 'toggleStatus'])->name('toggle-status');
            Route::post('/{club}/transfer-leadership', [ClubController::class, 'transferLeadership'])->name('transfer-leadership');
            Route::post('/{teacherClub}/approve-request', [ClubController::class, 'approveRequest'])->name('approve-request');
            Route::post('/{teacherClub}/reject-request', [ClubController::class, 'rejectRequest'])->name('reject-request');
        });

        // Projects routes
        Route::prefix('projects')->name('projects.')->group(function () {
            Route::get('/', [AdminProjectController::class, 'index'])->name('index');
            Route::get('/create', [AdminProjectController::class, 'create'])->name('create');
            Route::post('/', [AdminProjectController::class, 'store'])->name('store');
            Route::get('/{project}', [AdminProjectController::class, 'show'])->name('show');
            Route::get('/{project}/edit', [AdminProjectController::class, 'edit'])->name('edit');
            Route::put('/{project}', [AdminProjectController::class, 'update'])->name('update');
            Route::delete('/{project}', [AdminProjectController::class, 'destroy'])->name('destroy');
        });

        Route::delete('project-categories/{projectCategory}', [AdminProjectCategoryController::class, 'destroy'])
            ->name('project-categories.destroy');

        Route::resource('platforms', AdminPlatformController::class)->except(['show'])->names('platforms');

        Route::prefix('schools')->name('schools.')->group(function () {
            Route::get('/', [SchoolController::class, 'index'])->name('index');
            Route::delete('/teacher-clubs/{teacherClub}', [SchoolController::class, 'removeTeacherFromClub'])->name('remove-teacher-from-club');
            Route::get('/{school}', [SchoolController::class, 'show'])->name('show');
            Route::post('/', [SchoolController::class, 'store'])->name('store');
            Route::put('/{school}', [SchoolController::class, 'update'])->name('update');
            Route::delete('/{school}', [SchoolController::class, 'destroy'])->name('destroy');
            Route::post('/{school}/toggle-status', [SchoolController::class, 'toggleStatus'])->name('toggle-status');
        });

        // Competitions routes
        Route::prefix('competitions')->name('competitions.')->group(function () {
            Route::get('/', [CompetitionController::class, 'index'])->name('index');
            Route::get('/create', [CompetitionController::class, 'create'])->name('create');
            Route::post('/', [CompetitionController::class, 'store'])->name('store');
            Route::get('/{competition}', [CompetitionController::class, 'show'])->name('show');
            Route::get('/{competition}/edit', [CompetitionController::class, 'edit'])->name('edit');
            Route::put('/{competition}', [CompetitionController::class, 'update'])->name('update');
            Route::post('/{competition}/prototype-evidence-link', [CompetitionController::class, 'updatePrototypeEvidenceFormLink'])
                ->name('prototype-evidence-link.update');
            Route::post('/{competition}/prototype-modification-evidence-link', [CompetitionController::class, 'updatePrototypeModificationEvidenceFormLink'])
                ->name('prototype-modification-evidence-link.update');
            Route::get('/{competition}/prototype-shortlisting', [CompetitionController::class, 'prototypeShortlistingIndex'])
                ->name('prototype-shortlisting.index');
            Route::get('/{competition}/prototype-shortlisting/submissions/{submission}', [CompetitionController::class, 'prototypeShortlistingShow'])
                ->name('prototype-shortlisting.show');
            Route::post('/{competition}/prototype-shortlisting/submissions/{submission}/shortlist', [CompetitionController::class, 'shortlistPrototype'])
                ->name('prototype-shortlisting.shortlist');
            Route::post('/{competition}/prototype-shortlisting/submissions/{submission}/mentors', [CompetitionController::class, 'storePrototypeMentor'])
                ->name('prototype-shortlisting.mentors.store');
            Route::get('/{competition}/prototype-modification', [CompetitionController::class, 'prototypeModificationIndex'])
                ->name('prototype-modification.index');
            Route::get('/{competition}/prototype-modification/submissions/{modification}', [CompetitionController::class, 'prototypeModificationShow'])
                ->name('prototype-modification.show');
            Route::post('/{competition}/prototype-modification/submissions/{modification}/shortlist', [CompetitionController::class, 'shortlistPrototypeModification'])
                ->name('prototype-modification.shortlist');
            Route::get('/{competition}/pitch-winner', [CompetitionController::class, 'pitchWinnerIndex'])
                ->name('pitch-winner.index');
            Route::delete('/{competition}', [CompetitionController::class, 'destroy'])->name('destroy');
            Route::get('/{competition}/registrations', [CompetitionController::class, 'registrations'])->name('registrations.index');
            Route::get('/{competition}/registrations/{registration}', [CompetitionController::class, 'showRegistration'])->name('registrations.show');
            Route::post('/{competition}/registrations/{registration}/approve', [CompetitionController::class, 'approveRegistration'])->name('registrations.approve');
            Route::post('/{competition}/registrations/{registration}/reject', [CompetitionController::class, 'rejectRegistration'])->name('registrations.reject');
            Route::get('/{competition}/ideas', [CompetitionController::class, 'ideas'])->name('ideas.index');
            Route::get('/{competition}/ideas/{idea}', [CompetitionController::class, 'showIdea'])->name('ideas.show');
            Route::post('/{competition}/ideas/{idea}/score', [CompetitionController::class, 'scoreIdea'])->name('ideas.score');
            Route::post('/{competition}/ideas/{idea}/shortlist', [CompetitionController::class, 'shortlistIdea'])->name('ideas.shortlist');
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
            Route::get('/teachers', [AdminUserController::class, 'teachers'])->name('teachers');
            Route::post('/', [AdminUserController::class, 'store'])->name('store');
            Route::put('/{user}', [AdminUserController::class, 'update'])->name('update');
            Route::post('/{user}/toggle-status', [AdminUserController::class, 'toggleStatus'])->name('toggle-status');
        });

        // Database Backup routes
        Route::prefix('database-backup')->name('database-backup.')->group(function () {
            Route::get('/', [\App\Http\Controllers\Admin\DatabaseBackupController::class, 'index'])->name('index');
            Route::post('/create', [\App\Http\Controllers\Admin\DatabaseBackupController::class, 'create'])->name('create');
            Route::get('/download/{filename}', [\App\Http\Controllers\Admin\DatabaseBackupController::class, 'download'])->name('download');
            Route::delete('/delete/{filename}', [\App\Http\Controllers\Admin\DatabaseBackupController::class, 'destroy'])->name('destroy');
        });

        // Settings routes
        Route::prefix('settings')->name('settings.')->group(function () {
            Route::get('/', [SettingsController::class, 'index'])->name('index');
            Route::put('/', [SettingsController::class, 'update'])->name('update');
            Route::put('/legal', [SettingsController::class, 'updateLegal'])->name('legal');
            Route::get('/legal-notification-batch/{batchId}', [SettingsController::class, 'legalNotificationBatchStatus'])
                ->name('legal-notification-batch');
        });

        // Audit Logs
        Route::get('activity-logs', [ActivityLogController::class, 'index'])
            ->name('activity-logs.index');

        // Weekly Sessions routes
        Route::prefix('weekly-sessions')->name('weekly-sessions.')->group(function () {
            Route::get('/', [WeeklySessionController::class, 'index'])->name('index');
            Route::get('/create', [WeeklySessionController::class, 'create'])->name('create');
            Route::post('/', [WeeklySessionController::class, 'store'])->name('store');

            // Reports routes (must come before {weeklySession} route to avoid route conflicts)
            Route::prefix('reports')->name('reports.')->group(function () {
                Route::get('/', [WeeklySessionController::class, 'reportsIndex'])->name('index');
                Route::get('/{report}', [WeeklySessionController::class, 'showReport'])->name('show');
            });

            Route::get('/{weeklySession}', [WeeklySessionController::class, 'show'])->name('show');
            Route::get('/{weeklySession}/edit', [WeeklySessionController::class, 'edit'])->name('edit');
            Route::put('/{weeklySession}', [WeeklySessionController::class, 'update'])->name('update');
            Route::delete('/{weeklySession}', [WeeklySessionController::class, 'destroy'])->name('destroy');
            Route::post('/{weeklySession}/update-status', [WeeklySessionController::class, 'updateStatus'])->name('update-status');
        });

        // 3D Models routes - outer group already has 'admin.' prefix, so use 'models.' here
        Route::prefix('models')->name('models.')->group(function () {
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

        // VR Experiments routes - outer group already has 'admin.' prefix
        Route::prefix('vr-experiments')->name('vr-experiments.')->group(function () {
            Route::get('/', [VrExperimentController::class, 'index'])->name('index');
            Route::get('/create', [VrExperimentController::class, 'create'])->name('create');
            Route::post('/', [VrExperimentController::class, 'store'])->name('store');
            Route::get('/{vrExperiment}/edit', [VrExperimentController::class, 'edit'])->name('edit');
            Route::put('/{vrExperiment}', [VrExperimentController::class, 'update'])->name('update');
            Route::delete('/{vrExperiment}', [VrExperimentController::class, 'destroy'])->name('destroy');
        });

        // VR Assessment routes - outer group already has 'admin.' prefix
        Route::prefix('vr-assessments')->name('vr-assessments.')->group(function () {
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
});

// Subjects routes - separate domain group without name prefix to use 'subjects.*' names
Route::domain(parse_url(config('app.admin_url'), PHP_URL_HOST) ?? 'admin.adilishastemlabs.com')->group(function () {
    Route::middleware(['auth', 'admin', 'track.admin.activity'])->group(function () {
        Route::get('subjects', [SubjectController::class, 'index'])->name('subjects.index');
        Route::get('subjects/create', [SubjectController::class, 'create'])->name('subjects.create');
        Route::post('subjects', [SubjectController::class, 'store'])->name('subjects.store');
        Route::get('subjects/{subject}', [SubjectController::class, 'show'])->name('subjects.show');
        Route::get('subjects/{subject}/edit', [SubjectController::class, 'edit'])->name('subjects.edit');
        Route::put('subjects/{subject}', [SubjectController::class, 'update'])->name('subjects.update');
        Route::delete('subjects/{subject}', [SubjectController::class, 'destroy'])->name('subjects.destroy');
    });
});

// Client routes - only accessible on main domain (not admin subdomain)
Route::get('/dashboard', function () {
    // Prevent client dashboard from being accessed on admin subdomain
    $adminHost = parse_url(config('app.admin_url'), PHP_URL_HOST);
    if (request()->getHost() === $adminHost) {
        return redirect(config('app.admin_url').'/dashboard');
    }

    return app(ClientDashboardController::class)->index();
})->middleware(['auth', 'verified', 'track.client.activity'])->name('dashboard');

Route::middleware(['auth', 'track.client.activity'])->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::prefix('models/3d')->name('client.models.3d.')->group(function () {
        Route::get('/', [ClientModel3dController::class, 'index'])->name('index');

        // Question routes (must come before /{model3d} to avoid route conflicts)
        Route::get('/{model3d}/questions', [\App\Http\Controllers\Client\ModelQuestionController::class, 'getQuestions'])->name('questions.get');
        Route::post('/{model3d}/questions/submit', [\App\Http\Controllers\Client\ModelQuestionController::class, 'submitAnswers'])->name('questions.submit');

        Route::get('/{model3d}', [ClientModel3dController::class, 'show'])->name('show');
    });

    // Projects routes (client)
    Route::prefix('projects')->name('client.projects.')->group(function () {
        Route::get('/', [ClientProjectController::class, 'index'])->name('index');
        Route::get('/{project}', [ClientProjectController::class, 'show'])->name('show');
    });

    // VR Experiments routes
    Route::prefix('vr-experiments')->name('client.vr-experiments.')->group(function () {
        Route::get('/', [ClientVrExperimentController::class, 'index'])->name('index');
        Route::get('/math/practice', [ClientVrExperimentController::class, 'mathPractice'])->name('math.practice');
        Route::get('/math', [ClientVrExperimentController::class, 'math'])->name('math');
        Route::get('/experience/{slug}', [ClientVrExperimentController::class, 'showStatic'])->name('show.static');
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
        Route::prefix('clubs')->name('client.clubs.')->group(function () {
            Route::get('/', [ClientClubController::class, 'index'])->name('index');
            Route::post('/transfer-leadership', [ClientClubController::class, 'transferLeadership'])->name('transfer-leadership');
            Route::get('/{club}/join', [ClientClubController::class, 'showJoinForm'])->name('join.form');
            Route::post('/{club}/join', [ClientClubController::class, 'join'])->name('join');
            Route::get('/pending-requests', [ClientClubController::class, 'pendingRequests'])->name('pending-requests');
            Route::post('/{teacherClub}/approve', [ClientClubController::class, 'approveRequest'])->name('approve-request');
            Route::post('/{teacherClub}/reject', [ClientClubController::class, 'rejectRequest'])->name('reject-request');
        });

        // Student registration routes
        Route::get('/students', [ClientStudentController::class, 'index'])->name('client.students.index');
        Route::post('/students', [ClientStudentController::class, 'store'])->name('client.students.store');
        Route::put('/students/{student}', [ClientStudentController::class, 'update'])->name('client.students.update');
        Route::delete('/students/{student}', [ClientStudentController::class, 'destroy'])->name('client.students.destroy');
        Route::post('/students/bulk', [ClientStudentController::class, 'bulkStore'])->name('client.students.bulk-store');
        Route::post('/students/bulk-destroy', [ClientStudentController::class, 'bulkDestroy'])->name('client.students.bulk-destroy');

        // Weekly Sessions routes
        Route::prefix('weekly-sessions')->name('client.weekly-sessions.')->group(function () {
            Route::get('/', [\App\Http\Controllers\Client\WeeklySessionController::class, 'index'])->name('index');
            Route::get('/{weeklySession}', [\App\Http\Controllers\Client\WeeklySessionController::class, 'show'])->name('show');
            Route::get('/{weeklySession}/report', [\App\Http\Controllers\Client\WeeklySessionController::class, 'createReport'])->name('report.create');
            Route::post('/{weeklySession}/report', [\App\Http\Controllers\Client\WeeklySessionController::class, 'storeReport'])->name('report.store');
            Route::post('/{weeklySession}/report/upload-pdf', [\App\Http\Controllers\Client\WeeklySessionController::class, 'uploadPdfReport'])->name('report.upload-pdf');
            Route::get('/{weeklySession}/report/edit', [\App\Http\Controllers\Client\WeeklySessionController::class, 'editReport'])->name('report.edit');
            Route::put('/{weeklySession}/report', [\App\Http\Controllers\Client\WeeklySessionController::class, 'updateReport'])->name('report.update');
        });

        // Competition routes for teachers
        Route::prefix('competitions')->name('client.competitions.')->group(function () {
            Route::get('/', [ClientCompetitionController::class, 'index'])->name('index');
            Route::get('/{competition}/register/representatives', [ClientCompetitionController::class, 'registerRepresentatives'])->name('register.representatives');
            Route::post('/{competition}/register/representatives', [ClientCompetitionController::class, 'storeRegistrationRepresentatives'])->name('register.representatives.store');
            Route::post('/{competition}/register/bulk-students', [ClientCompetitionController::class, 'bulkImportStudents'])->name('register.bulk-students');
            Route::delete('/{competition}/register/pool-students', [ClientCompetitionController::class, 'clearPoolStudents'])->name('register.pool-students.clear');
            Route::get('/{competition}/register/csv-template', [ClientCompetitionController::class, 'downloadRegistrationCsvTemplate'])->name('register.csv-template');
            Route::get('/{competition}/register', [ClientCompetitionController::class, 'register'])->name('register');
            Route::post('/{competition}/register', [ClientCompetitionController::class, 'storeRegistration'])->name('register.store');
            Route::get('/{competition}/idea', [ClientCompetitionController::class, 'createIdea'])->name('idea');
            Route::post('/{competition}/idea', [ClientCompetitionController::class, 'storeIdea'])->name('idea.store');
            Route::get('/{competition}/prototype', [ClientCompetitionController::class, 'createPrototype'])->name('prototype');
            Route::post('/{competition}/prototype', [ClientCompetitionController::class, 'storePrototype'])->name('prototype.store');
            Route::get('/{competition}/prototype-modification', [ClientCompetitionController::class, 'createPrototypeModification'])->name('prototype-modification');
            Route::post('/{competition}/prototype-modification', [ClientCompetitionController::class, 'storePrototypeModification'])->name('prototype-modification.store');
            Route::delete('/{competition}/attachment', [ClientCompetitionController::class, 'deleteAttachment'])->name('attachment.delete');
            Route::get('/{competition}', [ClientCompetitionController::class, 'show'])->name('show');
        });
    });
});

// Redirect /admin/* from main domain to admin subdomain
Route::prefix('admin')->group(function () {
    Route::get('/', function () {
        return redirect(config('app.admin_url'), 301);
    });

    Route::any('{any}', function ($any) {
        return redirect(config('app.admin_url').'/'.$any, 301);
    })->where('any', '.*');
});

require __DIR__.'/auth.php';
