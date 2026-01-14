<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Club;
use App\Models\School;
use App\Models\SchoolClass;
use App\Models\TeacherClub;
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
            : School::active()->ordered()->get();

        return view('client.back.clubs.join', compact('club', 'schools', 'existingSchool'));
    }

    /**
     * Process the club join request with school details.
     */
    public function join(Request $request, Club $club): RedirectResponse
    {
        $user = Auth::user();

        // Check if teacher already has any membership (approved, pending, or rejected) for this club
        if ($user->teacherClubs()->where('club_id', $club->id)->exists()) {
            return redirect()->route('client.clubs.index')
                ->with('error', 'You have already submitted a request to join this club.');
        }

        // Check if teacher already has a school
        $existingTeacherClub = $user->teacherClubs()->with('school')->first();
        $existingSchool = $existingTeacherClub?->school;
        $isNewSchool = false;

        // If teacher already has a school, use it automatically
        if ($existingSchool) {
            $schoolId = $existingSchool->id;
            $teacherClubData = [
                'user_id' => $user->id,
                'club_id' => $club->id,
                'school_id' => $schoolId,
            ];
        } else {
            // Teacher doesn't have a school yet, process school selection/creation
            $schoolId = $request->input('school_id');
            $isNewSchool = $request->boolean('is_new_school', false);

            if ($isNewSchool) {
                // Validate and create new school (as inactive for admin review)
                $validated = $request->validate([
                    'school_name' => ['required', 'string', 'max:255', 'unique:schools,name'],
                    'school_address' => ['nullable', 'string', 'max:500'],
                    'school_phone' => ['nullable', 'string', 'max:50'],
                    'school_email' => ['nullable', 'email', 'max:255'],
                    'school_district' => ['nullable', 'string', 'max:255'],
                    'school_region' => ['nullable', 'string', 'max:255'],
                ]);

                // Create new school as inactive (admin needs to approve)
                $school = School::create([
                    'name' => $validated['school_name'],
                    'address' => $validated['school_address'] ?? null,
                    'phone' => $validated['school_phone'] ?? null,
                    'email' => $validated['school_email'] ?? null,
                    'district' => $validated['school_district'] ?? null,
                    'region' => $validated['school_region'] ?? null,
                    'is_active' => false, // Inactive until admin approves
                ]);

                $schoolId = $school->id;
                // Create teacher_club with school_id
                $teacherClubData = [
                    'user_id' => $user->id,
                    'club_id' => $club->id,
                    'school_id' => $schoolId,
                ];
            } else {
                // Use existing school - validate school_id is provided
                $validated = $request->validate([
                    'school_id' => ['required', 'exists:schools,id'],
                ]);

                $schoolId = $validated['school_id'];
                // Create teacher_club with school_id
                $teacherClubData = [
                    'user_id' => $user->id,
                    'club_id' => $club->id,
                    'school_id' => $schoolId,
                ];
            }
        }

        // Check if there are any approved teachers from the same school in this club (first teacher check)
        $hasAnyApprovedTeacherFromSameSchool = TeacherClub::where('club_id', $club->id)
            ->where('school_id', $schoolId)
            ->approved()
            ->exists();

        // Set status and leader based on whether this is the first teacher from this school to join the club
        if (!$hasAnyApprovedTeacherFromSameSchool) {
            // First teacher from this school to join the club is automatically approved
            $teacherClubData['status'] = 'approved';
            $teacherClubData['is_leader'] = true;
            $message = "Successfully joined {$club->name}! You are now the leader for your school in this club.";
        } else {
            // Subsequent teachers from the same school need approval from their school leader
            $teacherClubData['status'] = 'pending';
            $teacherClubData['is_leader'] = false;
            $message = "Your request to join {$club->name} has been submitted and is pending approval from your school's leader.";
        }

        if ($isNewSchool) {
            $message .= " Your school registration is also pending admin approval.";
        }

        // Create the teacher-club relationship
        $teacherClub = TeacherClub::create($teacherClubData);

        return redirect()->route('client.clubs.index', ['teacherClub' => $teacherClub->id])
            ->with('status', $message);
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

        if (!$isSchoolLeader) {
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

        if (!$isSchoolLeader) {
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


