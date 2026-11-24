<?php

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Exceptions\PostTooLargeException;
use Illuminate\Http\Request;
use Illuminate\Session\TokenMismatchException;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpKernel\Exception\HttpException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withSchedule(function (Schedule $schedule): void {
        $schedule->command('admin:send-attention-digest')
            ->dailyAt('08:00')
            ->timezone('Africa/Dar_es_Salaam');

        $schedule->command('subscriptions:send-renewal-reminders')
            ->dailyAt('09:00')
            ->timezone('Africa/Dar_es_Salaam');

        $schedule->command('subscriptions:process-expiries')
            ->dailyAt('09:15')
            ->timezone('Africa/Dar_es_Salaam');

        $schedule->command('subscriptions:send-payment-reminders')
            ->hourly()
            ->timezone('Africa/Dar_es_Salaam');
    })
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->validateCsrfTokens(except: [
            'webhooks/snippe',
        ]);

        $middleware->web(append: [
            \App\Http\Middleware\ForceApplicationUrlRoot::class,
        ]);

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
            'platform.access' => \App\Http\Middleware\EnsurePlatformAccess::class,
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

        // TokenMismatchException is prepared into HttpException(419) before render callbacks run.
        $exceptions->render(function (HttpException $e, Request $request) {
            if ($e->getStatusCode() !== 419) {
                return null;
            }

            if (! $e->getPrevious() instanceof TokenMismatchException) {
                return null;
            }

            if (! $request->isMethod('POST') || ! $request->is('login')) {
                return null;
            }

            if ($request->expectsJson()) {
                return response()->json(['message' => 'Page expired. Please refresh and try again.'], 419);
            }

            $user = Auth::user() ?? $request->user();

            if ($user) {
                if ($user->role === 'admin') {
                    return redirect()->intended(config('app.admin_url').'/dashboard');
                }

                if ($user->role === 'student' && method_exists($user, 'needsStudentOnboarding') && $user->needsStudentOnboarding()) {
                    return redirect()->intended(route('student.onboarding.show', absolute: false));
                }

                return redirect()->intended(route('dashboard', absolute: false));
            }

            return redirect('/login')
                ->with('status', 'Your session expired. Please refresh the page and try signing in again.');
        });
    })->create();
