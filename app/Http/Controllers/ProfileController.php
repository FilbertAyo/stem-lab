<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use App\Models\SchoolClass;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\View\View;

class ProfileController extends Controller
{
    /**
     * Display the user's profile form.
     */
    public function edit(Request $request): View
    {
        $user = $request->user();

        // Load teacher-specific data if user is a teacher
        if ($user->role === 'teacher') {
            $user->load(['teacherClubs.club', 'teacherClubs.school', 'teacherClubs.students']);
        }

        if ($user->role === 'student') {
            $user->loadMissing('schoolClass');
        }

        $schoolClasses = $user->role === 'student'
            ? SchoolClass::active()->ordered()->get()
            : collect();

        return view('profile.edit', [
            'user' => $user,
            'schoolClasses' => $schoolClasses,
        ]);
    }

    /**
     * Update the user's profile information.
     */
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $user = $request->user();
        $validated = $request->validated();

        if ($user->role === 'student') {
            $validated['school_class_id'] = $validated['school_class_id'] ?? null;
        }

        $user->fill($validated);

        if ($request->user()->isDirty('email')) {
            $request->user()->email_verified_at = null;
        }

        $user->save();

        return Redirect::route('profile.edit')->with('status', 'profile-updated');
    }

    /**
     * Delete the user's account.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $request->validateWithBag('userDeletion', [
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();

        Auth::logout();

        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/');
    }
}
