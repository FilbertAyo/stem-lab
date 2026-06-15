<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

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
            'guest' => \App\Http\Middleware\RedirectIfAuthenticated::class,
            'block.client.on.admin' => \App\Http\Middleware\BlockClientOnAdmin::class,
            'track.landing.visitor' => \App\Http\Middleware\TrackLandingVisitor::class,
            'track.admin.activity' => \App\Http\Middleware\TrackAdminActivity::class,
            'track.client.activity' => \App\Http\Middleware\TrackClientActivity::class,
            'student.onboarded' => \App\Http\Middleware\EnsureStudentOnboardingComplete::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
