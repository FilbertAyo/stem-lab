<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Club;
use App\Models\Region;
use App\Models\School;
use App\Models\SchoolClass;
use App\Models\Student;
use App\Models\TeacherClub;
use App\Services\TeacherClubJoinService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class ClubController extends Controller
{
    /**
     * Display a listing of available clubs for teachers.
     */
    public function index(): View
    {
        $user = Auth::user();

        $clubs = Club::active()->ordered()->get();

        $joinedClubIds = $user->clubs()->pluck('clubs.id')->toArray();

        $pendingRequestClubIds = $user->teacherClubs()
            ->pending()
            ->pluck('club_id')
            ->toArray();

        $teacherClubs = $user->teacherClubs()
            ->approved()
            ->with(['club', 'school'])
            ->get();

        $pendingRequests = $user->teacherClubs()
            ->pending()
            ->with('club')
            ->get();

        $isLeaderOfAnyClub = $user->teacherClubs()
            ->where('is_leader', true)
            ->exists();

        return view('client.back.clubs.index', [
            'clubs' => $clubs,
            'joinedClubIds' => $joinedClubIds,
            'pendingRequestClubIds' => $pendingRequestClubIds,
            'teacherClubs' => $teacherClubs,
            'pendingRequests' => $pendingRequests,
            'isLeaderOfAnyClub' => $isLeaderOfAnyClub,
        ]);
    }

    /**
     * Display a joined school club with members and details.
     */
    public function show(TeacherClub $teacherClub): View
    {
        $user = Auth::user();

        abort_unless(
            $user->approvedCoMemberTeacherClubIds()->contains($teacherClub->id),
            403,
        );

        $teacherClub->load(['club', 'school']);

        $clubId = $teacherClub->club_id;
        $schoolId = $teacherClub->school_id;

        $coMemberTeacherClubIds = TeacherClub::query()
            ->where('club_id', $clubId)
            ->where('school_id', $schoolId)
            ->approved()
            ->pluck('id');

        $students = Student::query()
            ->whereIn('teacher_club_id', $coMemberTeacherClubIds)
            ->with(['schoolClass'])
            ->orderBy('name')
            ->get();

        $classes = SchoolClass::active()->ordered()->get();

        $approvedTeachers = TeacherClub::query()
            ->where('club_id', $clubId)
            ->where('school_id', $schoolId)
            ->approved()
            ->with(['user', 'school'])
            ->get();

        $isLeader = $user->teacherClubs()
            ->where('club_id', $clubId)
            ->where('school_id', $schoolId)
            ->where('is_leader', true)
            ->approved()
            ->exists();

        $pendingRequests = collect();
        if ($isLeader) {
            $pendingRequests = TeacherClub::query()
                ->where('club_id', $clubId)
                ->where('school_id', $schoolId)
                ->pending()
                ->with(['user', 'school'])
                ->get();
        }

        $transferCandidates = $approvedTeachers->where('is_leader', false)->values();

        return view('client.back.clubs.show', [
            'teacherClub' => $teacherClub,
            'club' => $teacherClub->club,
            'school' => $teacherClub->school,
            'students' => $students,
            'classes' => $classes,
            'approvedTeachers' => $approvedTeachers,
            'pendingRequests' => $pendingRequests,
            'isLeader' => $isLeader,
            'transferCandidates' => $transferCandidates,
        ]);
    }

    /**
     * Show the form for joining a club (school details form).
     */
    public function showJoinForm(Club $club): View
    {
        $user = Auth::user();

        if ($user->teacherClubs()->where('club_id', $club->id)->exists()) {
            abort(403, 'You have already submitted a request to join this club.');
        }

        $existingTeacherClub = $user->teacherClubs()->with('school')->first();
        $existingSchool = $existingTeacherClub?->school;

        $schools = $existingSchool
            ? collect()
            : School::active()->with('regionModel')->ordered()->get();

        $regions = Region::query()->orderBy('name')->get(['id', 'name']);

        return view('client.back.clubs.join', compact('club', 'schools', 'existingSchool', 'regions'));
    }

    /**
     * Process the club join request with school details.
     */
    public function join(Request $request, Club $club, TeacherClubJoinService $joinService): RedirectResponse
    {
        $user = Auth::user();

        $result = $joinService->join($user, $club, $request);

        if (! $result['ok']) {
            return redirect()->route('client.clubs.index')
                ->with('error', $result['message']);
        }

        $teacherClub = $result['teacher_club'];

        if ($teacherClub->status === 'approved') {
            return redirect()->route('client.clubs.show', $teacherClub)
                ->with('status', $result['message']);
        }

        return redirect()->route('client.clubs.index')
            ->with('status', $result['message']);
    }

    /**
     * Show pending join requests for clubs where the current user is leader.
     */
    public function pendingRequests(): View
    {
        $user = Auth::user();

        $leaderTeacherClubs = $user->teacherClubs()
            ->where('is_leader', true)
            ->approved()
            ->with(['club', 'school'])
            ->get();

        $clubsWithPendingRequests = collect();
        foreach ($leaderTeacherClubs as $leaderTeacherClub) {
            $pendingRequests = TeacherClub::where('club_id', $leaderTeacherClub->club_id)
                ->where('school_id', $leaderTeacherClub->school_id)
                ->pending()
                ->with(['user', 'school'])
                ->get();

            if ($pendingRequests->isNotEmpty()) {
                $clubsWithPendingRequests->push([
                    'club' => $leaderTeacherClub->club,
                    'school' => $leaderTeacherClub->school,
                    'pendingRequests' => $pendingRequests,
                ]);
            }
        }

        return view('client.back.clubs.pending-requests', [
            'clubsWithPendingRequests' => $clubsWithPendingRequests,
        ]);
    }

    /**
     * Transfer school-specific club leadership to another approved member.
     */
    public function transferLeadership(Request $request): RedirectResponse
    {
        $user = Auth::user();

        $validated = $request->validate([
            'new_leader_teacher_club_id' => ['required', 'exists:teacher_clubs,id'],
            'redirect_teacher_club_id' => ['nullable', 'exists:teacher_clubs,id'],
        ]);

        $newLeaderMembership = TeacherClub::query()
            ->whereKey((int) $validated['new_leader_teacher_club_id'])
            ->where('status', 'approved')
            ->with('user')
            ->firstOrFail();

        $clubId = $newLeaderMembership->club_id;
        $schoolId = $newLeaderMembership->school_id;

        $currentMembership = $this->resolveRedirectTeacherClub($user, $clubId, $schoolId, $validated['redirect_teacher_club_id'] ?? null);

        $isCurrentSchoolLeader = $user->teacherClubs()
            ->where('club_id', $clubId)
            ->where('school_id', $schoolId)
            ->where('is_leader', true)
            ->approved()
            ->exists();

        if (! $isCurrentSchoolLeader) {
            abort(403, 'Only the club leader for this school can transfer leadership.');
        }

        if ($newLeaderMembership->user_id === $user->id) {
            return redirect()->route('client.clubs.show', [
                'teacherClub' => $currentMembership,
                'tab' => 'teachers',
            ])->with('error', 'You are already the leader. Choose another teacher.');
        }

        $newLeaderName = $newLeaderMembership->user->name;

        DB::transaction(function () use ($clubId, $schoolId, $newLeaderMembership): void {
            TeacherClub::query()
                ->where('club_id', $clubId)
                ->where('school_id', $schoolId)
                ->where('is_leader', true)
                ->update(['is_leader' => false]);

            $newLeaderMembership->update(['is_leader' => true]);
        });

        return redirect()->route('client.clubs.show', [
            'teacherClub' => $currentMembership,
            'tab' => 'teachers',
        ])->with('status', "Leadership transferred to {$newLeaderName}. You are now a member.");
    }

    /**
     * Approve a pending join request.
     */
    public function approveRequest(TeacherClub $teacherClub): RedirectResponse
    {
        $user = Auth::user();

        $isSchoolLeader = $user->teacherClubs()
            ->where('club_id', $teacherClub->club_id)
            ->where('school_id', $teacherClub->school_id)
            ->where('is_leader', true)
            ->approved()
            ->exists();

        if (! $isSchoolLeader) {
            abort(403, 'Only the school leader can approve join requests from the same school.');
        }

        $redirectMembership = $this->resolveRedirectTeacherClub($user, $teacherClub->club_id, $teacherClub->school_id);

        if ($teacherClub->status !== 'pending') {
            return redirect()->route('client.clubs.show', [
                'teacherClub' => $redirectMembership,
                'tab' => 'teachers',
            ])->with('error', 'This request has already been processed.');
        }

        $teacherClub->approve();

        return redirect()->route('client.clubs.show', [
            'teacherClub' => $redirectMembership,
            'tab' => 'teachers',
        ])->with('status', "Successfully approved {$teacherClub->user->name}'s request to join {$teacherClub->club->name}.");
    }

    /**
     * Reject a pending join request.
     */
    public function rejectRequest(TeacherClub $teacherClub): RedirectResponse
    {
        $user = Auth::user();

        $isSchoolLeader = $user->teacherClubs()
            ->where('club_id', $teacherClub->club_id)
            ->where('school_id', $teacherClub->school_id)
            ->where('is_leader', true)
            ->approved()
            ->exists();

        if (! $isSchoolLeader) {
            abort(403, 'Only the school leader can reject join requests from the same school.');
        }

        $redirectMembership = $this->resolveRedirectTeacherClub($user, $teacherClub->club_id, $teacherClub->school_id);

        if ($teacherClub->status !== 'pending') {
            return redirect()->route('client.clubs.show', [
                'teacherClub' => $redirectMembership,
                'tab' => 'teachers',
            ])->with('error', 'This request has already been processed.');
        }

        $teacherClub->reject();

        return redirect()->route('client.clubs.show', [
            'teacherClub' => $redirectMembership,
            'tab' => 'teachers',
        ])->with('status', "Successfully rejected {$teacherClub->user->name}'s request to join {$teacherClub->club->name}.");
    }

    private function resolveRedirectTeacherClub($user, int $clubId, int $schoolId, ?int $preferredId = null): TeacherClub
    {
        if ($preferredId !== null) {
            $preferred = TeacherClub::query()->find($preferredId);
            if ($preferred
                && (int) $preferred->club_id === $clubId
                && (int) $preferred->school_id === $schoolId
                && $user->approvedCoMemberTeacherClubIds()->contains($preferred->id)
            ) {
                return $preferred;
            }
        }

        return TeacherClub::query()
            ->where('user_id', $user->id)
            ->where('club_id', $clubId)
            ->where('school_id', $schoolId)
            ->approved()
            ->firstOrFail();
    }
}
