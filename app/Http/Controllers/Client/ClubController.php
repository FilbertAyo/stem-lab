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

        // Get clubs the teacher has already joined
        $joinedClubIds = $user->clubs()->pluck('clubs.id')->toArray();

        // Teacher's club records with students
        $teacherClubs = $user->teacherClubs()
            ->with(['club', 'school', 'students.schoolClass'])
            ->get();

        $requestedTeacherClubId = $request->input('teacherClub');
        $oldTeacherClubId = old('teacher_club_id');
        $selectedTeacherClubId = $requestedTeacherClubId ?? $oldTeacherClubId ?? $teacherClubs->first()?->id;
        $selectedTeacherClub = $teacherClubs->firstWhere('id', (int) $selectedTeacherClubId);
        $selectedStudents = $selectedTeacherClub
            ? $selectedTeacherClub->students->sortBy('name')->values()
            : collect();

        $classes = SchoolClass::active()->ordered()->get();

        return view('client.back.clubs.index', [
            'clubs' => $clubs,
            'joinedClubIds' => $joinedClubIds,
            'teacherClubs' => $teacherClubs,
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

        // Check if teacher already joined this club
        if ($user->clubs()->where('club_id', $club->id)->exists()) {
            abort(403, 'You have already joined this club.');
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

        // Check if teacher already joined this club
        if ($user->clubs()->where('club_id', $club->id)->exists()) {
            return redirect()->route('client.clubs.index')
                ->with('error', 'You have already joined this club.');
        }

        // Check if teacher already has a school
        $existingTeacherClub = $user->teacherClubs()->with('school')->first();
        $existingSchool = $existingTeacherClub?->school;

        // If teacher already has a school, use it automatically
        if ($existingSchool) {
            $teacherClubData = [
                'user_id' => $user->id,
                'club_id' => $club->id,
                'school_id' => $existingSchool->id,
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

                // Create teacher_club with school_id
                $teacherClubData = [
                    'user_id' => $user->id,
                    'club_id' => $club->id,
                    'school_id' => $school->id,
                ];
            } else {
                // Use existing school - validate school_id is provided
                $validated = $request->validate([
                    'school_id' => ['required', 'exists:schools,id'],
                ]);

                // Create teacher_club with school_id
                $teacherClubData = [
                    'user_id' => $user->id,
                    'club_id' => $club->id,
                    'school_id' => $validated['school_id'],
                ];
            }
        }

        // Create the teacher-club relationship with school_id
        $teacherClub = TeacherClub::create($teacherClubData);

        $message = "Successfully joined {$club->name}!";
        if ($isNewSchool) {
            $message .= " Your school registration is pending admin approval.";
        }

        return redirect()->route('client.clubs.index', ['teacherClub' => $teacherClub->id])
            ->with('status', $message);
    }
}


