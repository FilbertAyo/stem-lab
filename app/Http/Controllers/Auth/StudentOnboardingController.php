<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\SchoolClass;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class StudentOnboardingController extends Controller
{
    public function show(Request $request): View|RedirectResponse
    {
        $user = $request->user();

        if ($user->role !== 'student') {
            return redirect()->route('dashboard');
        }

        if (! $user->needsStudentOnboarding() && $user->student_onboarding_completed_at !== null) {
            return redirect()->route('dashboard');
        }

        $schoolClasses = SchoolClass::active()->ordered()->get();

        return view('auth.student-onboarding', [
            'user' => $user,
            'step' => $user->studentOnboardingStep(),
            'schoolClasses' => $schoolClasses,
        ]);
    }

    public function storeGender(Request $request): RedirectResponse
    {
        $user = $request->user();

        if ($user->role !== 'student' || ! blank($user->gender)) {
            return redirect()->route('student.onboarding.show');
        }

        $validated = $request->validate([
            'gender' => ['required', 'string', 'in:male,female'],
        ]);

        $user->update(['gender' => $validated['gender']]);

        return redirect()->route('student.onboarding.show');
    }

    public function storeDateOfBirth(Request $request): RedirectResponse
    {
        $user = $request->user();

        if ($user->role !== 'student' || ! blank($user->date_of_birth)) {
            return redirect()->route('student.onboarding.show');
        }

        if (blank($user->gender)) {
            return redirect()->route('student.onboarding.show');
        }

        $validated = $request->validate([
            'date_of_birth' => ['required', 'date_format:Y-m-d', 'before_or_equal:today'],
        ]);

        $age = Carbon::parse($validated['date_of_birth'])->age;
        if ($age < 1 || $age > 150) {
            return redirect()
                ->route('student.onboarding.show')
                ->withErrors(['date_of_birth' => 'Date of birth must result in age between 1 and 150.']);
        }

        $user->update(['date_of_birth' => $validated['date_of_birth']]);

        return redirect()->route('student.onboarding.show');
    }

    public function storeClass(Request $request): RedirectResponse
    {
        $user = $request->user();

        if ($user->role !== 'student' || $user->student_onboarding_completed_at !== null) {
            return redirect()->route('dashboard');
        }

        if (blank($user->gender) || blank($user->date_of_birth)) {
            return redirect()->route('student.onboarding.show');
        }

        $validated = $request->validate([
            'school_class_id' => ['nullable', 'exists:school_classes,id'],
        ]);

        $user->update([
            'school_class_id' => $validated['school_class_id'] ?? null,
        ]);

        $user->completeStudentOnboarding();

        return redirect()->route('dashboard')->with('status', 'profile-complete');
    }

    public function skipClass(Request $request): RedirectResponse
    {
        $user = $request->user();

        if ($user->role !== 'student' || $user->student_onboarding_completed_at !== null) {
            return redirect()->route('dashboard');
        }

        if (blank($user->gender) || blank($user->date_of_birth)) {
            return redirect()->route('student.onboarding.show');
        }

        $user->completeStudentOnboarding();

        return redirect()->route('dashboard')->with('status', 'profile-complete');
    }
}
