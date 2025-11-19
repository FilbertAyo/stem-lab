<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Club;
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
        $clubs = Club::where('is_active', true)
            ->orderBy('name')
            ->get();

        // Get clubs the teacher has already joined
        $joinedClubIds = $user->clubs()->pluck('clubs.id')->toArray();

        // Teacher's club records with students
        $teacherClubs = $user->teacherClubs()
            ->with(['club', 'students.schoolClass'])
            ->get();

        $requestedTeacherClubId = $request->input('teacherClub');
        $oldTeacherClubId = old('teacher_club_id');
        $selectedTeacherClubId = $requestedTeacherClubId ?? $oldTeacherClubId ?? $teacherClubs->first()?->id;
        $selectedTeacherClub = $teacherClubs->firstWhere('id', (int) $selectedTeacherClubId);
        $selectedStudents = $selectedTeacherClub
            ? $selectedTeacherClub->students->sortBy('name')->values()
            : collect();

        $classes = SchoolClass::where('is_active', true)
            ->orderBy('name')
            ->get();

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

        return view('client.back.clubs.join', compact('club'));
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

        $validated = $request->validate([
            'school_name' => ['required', 'string', 'max:255'],
            'school_address' => ['nullable', 'string', 'max:500'],
            'school_phone' => ['nullable', 'string', 'max:50'],
            'school_email' => ['nullable', 'email', 'max:255'],
            'school_district' => ['nullable', 'string', 'max:255'],
            'school_region' => ['nullable', 'string', 'max:255'],
        ]);

        // Create the teacher-club relationship with school details
        $teacherClub = TeacherClub::create([
            'user_id' => $user->id,
            'club_id' => $club->id,
            ...$validated,
        ]);

        return redirect()->route('client.clubs.index', ['teacherClub' => $teacherClub->id])
            ->with('status', "Successfully joined {$club->name}! You can now register students.");
    }
}


