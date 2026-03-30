<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Club;
use App\Models\Region;
use App\Models\School;
use App\Models\SchoolClass;
use App\Models\TeacherClub;
use App\Services\TeacherClubJoinService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class ClubController extends Controller
{
    /**
     * Display a listing of available clubs for teachers.
     */
    public function index(Request $request): View
    {
        $user = Auth::user();

        // Get all active clubs
        $clubs = Club::active()->ordered()->get();

        // Get clubs the teacher has already joined (approved)
        $joinedClubIds = $user->clubs()->pluck('clubs.id')->toArray();

        // Get clubs with pending requests
        $pendingRequestClubIds = $user->teacherClubs()
            ->pending()
            ->pluck('club_id')
            ->toArray();

        // Teacher's club records with students (only approved memberships)
        $teacherClubs = $user->teacherClubs()
            ->approved()
            ->with(['club', 'school', 'students.schoolClass'])
            ->get();

        // Get pending requests for display
        $pendingRequests = $user->teacherClubs()
            ->pending()
            ->with('club')
            ->get();

        $requestedTeacherClubId = $request->input('teacherClub');
        $oldTeacherClubId = old('teacher_club_id');
        $selectedTeacherClubId = $requestedTeacherClubId ?? $oldTeacherClubId ?? $teacherClubs->first()?->id;
        $selectedTeacherClub = $teacherClubs->firstWhere('id', (int) $selectedTeacherClubId);
        $selectedStudents = $selectedTeacherClub
            ? $selectedTeacherClub->students->sortBy('name')->values()
            : collect();

        $classes = SchoolClass::active()->ordered()->get();

        // Check if user is a leader of any clubs
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
            'selectedTeacherClubId' => $selectedTeacherClub?->id,
            'selectedTeacherClub' => $selectedTeacherClub,
            'selectedStudents' => $selectedStudents,
            'classes' => $classes,
        ]);
    }

    /**
     * Show the form for joining a club (school details form).
     */
    public function showJoinForm(Club $club): View
    {
        $user = Auth::user();

        // Check if teacher already has any membership (approved, pending, or rejected) for this club
        if ($user->teacherClubs()->where('club_id', $club->id)->exists()) {
            abort(403, 'You have already submitted a request to join this club.');
        }

        // Check if teacher already has a school (from any existing teacher_club)
        $existingTeacherClub = $user->teacherClubs()->with('school')->first();
        $existingSchool = $existingTeacherClub?->school;

        // Get all active schools for dropdown (only if teacher doesn't have a school yet)
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

        return redirect()->route('client.clubs.index', ['teacherClub' => $result['teacher_club']->id])
            ->with('status', $result['message']);
    }

    /**
     * Show pending join requests for clubs where the current user is leader.
     */
    public function pendingRequests(): View
    {
        $user = Auth::user();

        // Get all teacher-club records where user is leader (includes school info)
        $leaderTeacherClubs = $user->teacherClubs()
            ->where('is_leader', true)
            ->approved()
            ->with(['club', 'school'])
            ->get();

        // Get pending requests for each club-school combination where user is leader
        $clubsWithPendingRequests = collect();
        foreach ($leaderTeacherClubs as $leaderTeacherClub) {
            // Get pending requests from the same school in the same club
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
     * Approve a pending join request.
     */
    public function approveRequest(TeacherClub $teacherClub): RedirectResponse
    {
        $user = Auth::user();

        // Verify that the current user is the leader for this school in this club
        $isSchoolLeader = $user->teacherClubs()
            ->where('club_id', $teacherClub->club_id)
            ->where('school_id', $teacherClub->school_id)
            ->where('is_leader', true)
            ->approved()
            ->exists();

        if (! $isSchoolLeader) {
            abort(403, 'Only the school leader can approve join requests from the same school.');
        }

        // Verify that the request is pending
        if ($teacherClub->status !== 'pending') {
            return redirect()->route('client.students.index')
                ->with('error', 'This request has already been processed.');
        }

        $teacherClub->approve();

        return redirect()->route('client.students.index', ['tab' => 'teachers'])
            ->with('status', "Successfully approved {$teacherClub->user->name}'s request to join {$teacherClub->club->name}.");
    }

    /**
     * Reject a pending join request.
     */
    public function rejectRequest(TeacherClub $teacherClub): RedirectResponse
    {
        $user = Auth::user();

        // Verify that the current user is the leader for this school in this club
        $isSchoolLeader = $user->teacherClubs()
            ->where('club_id', $teacherClub->club_id)
            ->where('school_id', $teacherClub->school_id)
            ->where('is_leader', true)
            ->approved()
            ->exists();

        if (! $isSchoolLeader) {
            abort(403, 'Only the school leader can reject join requests from the same school.');
        }

        // Verify that the request is pending
        if ($teacherClub->status !== 'pending') {
            return redirect()->route('client.students.index')
                ->with('error', 'This request has already been processed.');
        }

        $teacherClub->reject();

        return redirect()->route('client.students.index', ['tab' => 'teachers'])
            ->with('status', "Successfully rejected {$teacherClub->user->name}'s request to join {$teacherClub->club->name}.");
    }
}
