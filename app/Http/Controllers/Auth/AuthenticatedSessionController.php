<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login view.
     */
    public function create(Request $request): View
    {
        // Check if request is from admin subdomain
        if ($this->isAdminDomain($request)) {
            return view('admin.auth.login');
        }

        $this->rememberSafeIntendedUrl($request);

        return view('auth.login');
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        $request->authenticate();

        session()->regenerate();

        return redirect()->intended(
            $this->resolveRedirectPath($request)
        );
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $user = $request->user();

        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect(
            $user && $user->role === 'admin'
                ? config('app.admin_url').'/login'
                : route('login')
        );
    }

    protected function resolveRedirectPath(Request $request): string
    {
        $user = $request->user();

        if ($user && $user->role === 'admin') {
            // Use absolute URL with admin subdomain
            return config('app.admin_url').'/dashboard';
        }

        return route('dashboard', absolute: false);
    }

    /**
     * Store a return URL from ?redirect= for post-login navigation (same app only; prevents open redirects).
     */
    protected function rememberSafeIntendedUrl(Request $request): void
    {
        $target = $request->query('redirect');
        if (! is_string($target) || $target === '') {
            return;
        }

        $appUrl = rtrim((string) config('app.url'), '/');

        if (str_starts_with($target, '/') && ! str_starts_with($target, '//')) {
            $request->session()->put('url.intended', $appUrl.$target);

            return;
        }

        if (str_starts_with($target, $appUrl.'/') || $target === $appUrl) {
            $request->session()->put('url.intended', $target);
        }
    }

    /**
     * Check if the request is from the admin subdomain.
     */
    protected function isAdminDomain(Request $request): bool
    {
        $host = $request->getHost();
        $adminHost = parse_url(config('app.admin_url'), PHP_URL_HOST);

        return $host === $adminHost;
    }
}
