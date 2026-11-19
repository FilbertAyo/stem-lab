<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\SmileyTutorCredential;
use App\Models\User;
use App\Models\Visitor;
use App\Services\SubscriptionSelectionService;
use App\Support\TanzaniaPhone;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules;
use Illuminate\View\View;

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     */
    public function create(): View
    {
        return view('auth.register');
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'phone' => ['required', 'string'],
            'password' => [
                'required',
                'confirmed',
                'string',
                'min:8',
            ],
            'role' => ['required', 'string', 'in:teacher,student,parent'],
        ], [
            'password.min' => 'The password must be at least 8 characters.',
        ]);

        // Additional password strength validation with specific error messages
        $password = $request->password;
        $validator = Validator::make([], []);

        if (!preg_match('/[a-z]/', $password)) {
            $validator->errors()->add('password', 'The password must contain at least one lowercase letter.');
        }
        if (!preg_match('/[A-Z]/', $password)) {
            $validator->errors()->add('password', 'The password must contain at least one uppercase letter.');
        }
        if (!preg_match('/[0-9]/', $password)) {
            $validator->errors()->add('password', 'The password must contain at least one number.');
        }

        if ($validator->errors()->has('password')) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput($request->except('password', 'password_confirmation'));
        }

        $normalizedPhone = TanzaniaPhone::normalize($request->input('phone'));
        if (! $normalizedPhone) {
            return redirect()->back()
                ->withErrors([
                    'phone' => 'Enter a valid Tanzania phone number (e.g. +255765890921 or 0765890921).',
                ])
                ->withInput($request->except('password', 'password_confirmation'));
        }

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $normalizedPhone,
            'password' => Hash::make($request->password),
            'role' => $request->role,
        ]);

        app(SubscriptionSelectionService::class)->grantTrialOnSignup($user);

        // Assign a SmileyTutor credential so the learner can practice math (username/password for tutor-web.net).
        SmileyTutorCredential::assignToUser($user);

        event(new Registered($user));

        // Auto "remember me" for new registrations so they stay logged in (e.g. 14 days).
        Auth::login($user, true);

        $visitorToken = $request->cookie('stem_visitor');
        if ($visitorToken) {
            Visitor::where('visitor_token', $visitorToken)->update(['converted_at' => now()]);
        }

        return redirect(
            $user->role === 'student'
                ? route('student.onboarding.show', absolute: false)
                : route('dashboard', absolute: false)
        );
    }
}
