<?php

namespace App\Http\Requests\Auth;

use App\Models\User;
use App\Support\StudentLoginId;
use Illuminate\Auth\Events\Lockout;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class LoginRequest extends FormRequest
{
    public const METHOD_EMAIL = 'email';

    public const METHOD_STUDENT_ID = 'student_id';

    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $isStudentId = $this->isStudentIdLogin();

        return [
            'login_method' => ['nullable', 'string', 'in:'.self::METHOD_EMAIL.','.self::METHOD_STUDENT_ID],
            'email' => $isStudentId
                ? ['nullable', 'string']
                : ['required', 'string', 'email'],
            'login_id' => $isStudentId
                ? ['required', 'string', 'max:32']
                : ['nullable', 'string'],
            'password' => ['required', 'string'],
            'remember' => ['nullable', 'boolean'],
            'admin_login' => ['sometimes', 'boolean'],
        ];
    }

    /**
     * Attempt to authenticate the request's credentials.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function authenticate(): void
    {
        $this->ensureIsNotRateLimited();

        $validated = $this->validated();
        $remember = (bool) Arr::get($validated, 'remember', false);
        $isAdminLogin = $this->isAdminDomain();
        $errorField = $this->isStudentIdLogin() ? 'login_id' : 'email';

        if (! $isAdminLogin) {
            $isAdminLogin = (bool) Arr::get($validated, 'admin_login', false);
        }

        if ($isAdminLogin) {
            $remember = false;
        }

        $credentials = $this->credentialsFromValidated($validated);

        if (! Auth::attempt($credentials, $remember)) {
            RateLimiter::hit($this->throttleKey());

            throw ValidationException::withMessages([
                $errorField => trans('auth.failed'),
            ]);
        }

        $authenticatedUser = Auth::user();

        if ($authenticatedUser instanceof User && $authenticatedUser->usesPinLogin()) {
            Auth::logout();
            Auth::login($authenticatedUser, false);
        }

        if ($isAdminLogin && (! $authenticatedUser instanceof User || $authenticatedUser->role !== 'admin')) {
            Auth::logout();
            RateLimiter::hit($this->throttleKey());

            throw ValidationException::withMessages([
                $errorField => trans('auth.failed'),
            ]);
        }

        if ($authenticatedUser instanceof User && ! $authenticatedUser->is_active) {
            Auth::logout();
            RateLimiter::hit($this->throttleKey());

            throw ValidationException::withMessages([
                $errorField => 'Your account has been disabled. Please contact an administrator.',
            ]);
        }

        RateLimiter::clear($this->throttleKey());
    }

    /**
     * Ensure the login request is not rate limited.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function ensureIsNotRateLimited(): void
    {
        if (! RateLimiter::tooManyAttempts($this->throttleKey(), 5)) {
            return;
        }

        event(new Lockout($this));

        $seconds = RateLimiter::availableIn($this->throttleKey());
        $errorField = $this->isStudentIdLogin() ? 'login_id' : 'email';

        throw ValidationException::withMessages([
            $errorField => trans('auth.throttle', [
                'seconds' => $seconds,
                'minutes' => ceil($seconds / 60),
            ]),
        ]);
    }

    /**
     * Get the rate limiting throttle key for the request.
     */
    public function throttleKey(): string
    {
        $identifier = $this->isStudentIdLogin()
            ? StudentLoginId::normalize((string) $this->input('login_id', ''))
            : Str::lower((string) $this->input('email', ''));
        $ipAddress = (string) request()->ip();

        return Str::transliterate($identifier.'|'.$ipAddress);
    }

    protected function prepareForValidation(): void
    {
        $method = $this->input('login_method', self::METHOD_EMAIL);

        if ($this->isAdminDomain() || $method !== self::METHOD_STUDENT_ID) {
            $method = self::METHOD_EMAIL;
        }

        $this->merge([
            'login_method' => $method,
        ]);

        if ($method === self::METHOD_STUDENT_ID && is_string($this->input('login_id'))) {
            $this->merge([
                'login_id' => StudentLoginId::normalize((string) $this->input('login_id')),
            ]);
        }
    }

    /**
     * @param  array<string, mixed>  $validated
     * @return array<string, mixed>
     */
    protected function credentialsFromValidated(array $validated): array
    {
        if ($this->isStudentIdLogin()) {
            return [
                'login_id' => Arr::get($validated, 'login_id'),
                'password' => Arr::get($validated, 'password'),
            ];
        }

        return Arr::only($validated, ['email', 'password']);
    }

    protected function isStudentIdLogin(): bool
    {
        return $this->input('login_method') === self::METHOD_STUDENT_ID && ! $this->isAdminDomain();
    }

    /**
     * Check if the request is from the admin subdomain.
     */
    protected function isAdminDomain(): bool
    {
        $host = $this->getHost();
        $adminHost = parse_url(config('app.admin_url'), PHP_URL_HOST);

        return $host === $adminHost;
    }
}
