<?php

namespace App\Http\Controllers;

use App\Http\Requests\PublicCompetitionJoinAsTeacherRequest;
use App\Models\Competition;
use App\Models\SmileyTutorCredential;
use App\Models\User;
use App\Models\Visitor;
use App\Services\AdminActivityNotifier;
use App\Services\TeacherClubJoinService;
use DomainException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class PublicCompetitionJoinController extends Controller
{
    /**
     * Teacher signup from public competition page: creates user without {@see \Illuminate\Auth\Events\Registered}
     * so {@see \App\Listeners\SendWelcomeEmail} does not run (no welcome email for this path).
     */
    public function store(
        PublicCompetitionJoinAsTeacherRequest $request,
        Competition $competition,
        TeacherClubJoinService $joinService
    ): RedirectResponse {
        if (! $competition->allowsPublicTeacherRegistration()) {
            return redirect()
                ->route('competitions.public.show', $competition)
                ->with('error', 'Teacher registration is not open yet, or the registration phase is closed. Please try again when registration is active.');
        }

        $competition->loadMissing('club');
        if (! $competition->club) {
            return redirect()
                ->route('competitions.public.show', $competition)
                ->with('error', 'This competition is not linked to a club.');
        }

        $user = null;

        try {
            DB::transaction(function () use ($request, $competition, $joinService, &$user): void {
                $user = User::create([
                    'name' => $request->validated('name'),
                    'email' => $request->validated('email'),
                    'phone' => $request->validated('phone'),
                    'password' => Hash::make($request->validated('password')),
                    'role' => 'teacher',
                ]);

                SmileyTutorCredential::assignToUser($user);

                $result = $joinService->join($user, $competition->club, $request, $competition);

                if (! $result['ok']) {
                    throw new DomainException($result['message']);
                }

                DB::afterCommit(function () use ($user): void {
                    app(AdminActivityNotifier::class)->newUser($user);
                });
            });
        } catch (ValidationException $e) {
            return redirect()
                ->route('competitions.public.show', [
                    'competition' => $competition,
                    'eligible' => 'yes',
                ])
                ->withErrors($e->errors())
                ->withInput($request->except('password', 'password_confirmation'));
        } catch (DomainException $e) {
            return redirect()
                ->route('competitions.public.show', [
                    'competition' => $competition,
                    'eligible' => 'yes',
                ])
                ->with('error', $e->getMessage())
                ->withInput($request->except('password', 'password_confirmation'));
        }

        Auth::login($user, true);

        $visitorToken = $request->cookie('stem_visitor');
        if ($visitorToken) {
            Visitor::where('visitor_token', $visitorToken)->update(['converted_at' => now()]);
        }

        $request->session()->put('onboarding_competition_ulid', $competition->ulid);

        return redirect()
            ->route('client.competitions.index')
            ->with('status', 'Account created and club join submitted. Complete the next steps below for this competition.');
    }
}
