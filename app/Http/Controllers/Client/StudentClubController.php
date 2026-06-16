<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Http\Requests\StudentClubLookupRequest;
use App\Services\StudentClubLinkService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class StudentClubController extends Controller
{
    public function __construct(
        private readonly StudentClubLinkService $linkService,
    ) {}

    public function index(Request $request): View
    {
        $user = $request->user();
        $match = $this->linkService->resolveMatchForUser($user);

        return view('client.back.student-club.index', [
            'match' => $match,
            'isLinked' => $user->isClubLinked(),
        ]);
    }

    public function lookup(StudentClubLookupRequest $request): RedirectResponse
    {
        $admissionNumber = $request->validated('admission_number');
        $clubCode = $request->validated('club_code');

        $match = $this->linkService->lookup($admissionNumber, $clubCode);

        if (! $match) {
            return back()
                ->withInput()
                ->withErrors([
                    'admission_number' => StudentClubLinkService::GENERIC_NOT_FOUND_MESSAGE,
                ]);
        }

        if ($match->student->isLinked() && $match->student->user_id !== $request->user()->id) {
            return back()
                ->withInput()
                ->withErrors([
                    'admission_number' => StudentClubLinkService::GENERIC_NOT_FOUND_MESSAGE,
                ]);
        }

        $request->session()->put('student_club_pending_link', [
            'admission_number' => $this->linkService->normalizeCode($admissionNumber),
            'club_code' => $this->linkService->normalizeCode($clubCode),
            'student_id' => $match->student->id,
        ]);

        return redirect()->route('client.student-club.confirm');
    }

    public function confirm(Request $request): View|RedirectResponse
    {
        $pending = $request->session()->get('student_club_pending_link');

        if (! is_array($pending) || empty($pending['admission_number']) || empty($pending['club_code'])) {
            return redirect()
                ->route('client.student-club.index')
                ->with('status', 'Enter your admission number and club code to find your school club.');
        }

        $match = $this->linkService->lookup(
            $pending['admission_number'],
            $pending['club_code'],
        );

        if (! $match || $match->student->id !== ($pending['student_id'] ?? null)) {
            $request->session()->forget('student_club_pending_link');

            return redirect()
                ->route('client.student-club.index')
                ->withErrors([
                    'admission_number' => StudentClubLinkService::GENERIC_NOT_FOUND_MESSAGE,
                ]);
        }

        return view('client.back.student-club.confirm', [
            'match' => $match,
        ]);
    }

    public function link(Request $request): RedirectResponse
    {
        $pending = $request->session()->get('student_club_pending_link');

        if (! is_array($pending) || empty($pending['admission_number']) || empty($pending['club_code'])) {
            return redirect()
                ->route('client.student-club.index')
                ->withErrors([
                    'admission_number' => 'Your confirmation session expired. Please search again.',
                ]);
        }

        $this->linkService->link(
            $request->user(),
            $pending['admission_number'],
            $pending['club_code'],
        );

        $request->session()->forget('student_club_pending_link');

        return redirect()
            ->route('client.student-club.index')
            ->with('status', 'Your account is now linked to your school club. You have free public secondary school access.');
    }
}
