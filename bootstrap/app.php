<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Exceptions\PostTooLargeException;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'admin' => \App\Http\Middleware\AdminOnly::class,
            'teacher' => \App\Http\Middleware\TeacherOnly::class,
            'student' => \App\Http\Middleware\StudentOnly::class,
            'guest' => \App\Http\Middleware\RedirectIfAuthenticated::class,
            'block.client.on.admin' => \App\Http\Middleware\BlockClientOnAdmin::class,
            'track.landing.visitor' => \App\Http\Middleware\TrackLandingVisitor::class,
            'track.admin.activity' => \App\Http\Middleware\TrackAdminActivity::class,
            'track.client.activity' => \App\Http\Middleware\TrackClientActivity::class,
            'student.onboarded' => \App\Http\Middleware\EnsureStudentOnboardingComplete::class,
            'club.linked.for.learning' => \App\Http\Middleware\EnsureClubLinkedForLearning::class,
            'redirect.legacy.model3d' => \App\Http\Middleware\RedirectLegacyModel3dUrls::class,
            'redirect.legacy.public-id' => \App\Http\Middleware\RedirectLegacyPublicIdUrls::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->render(function (PostTooLargeException $e, Request $request) {
            $message = 'One or more uploaded files are too large. Please reduce the file size and try again.';

            if ($request->expectsJson()) {
                return response()->json(['message' => $message], 413);
            }

            return back()->withErrors(['upload' => $message])->withInput();
        });
    })->create();
