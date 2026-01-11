<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Club;
use App\Models\TeacherClub;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ClubController extends Controller
{
    public function index(): View
    {
        $clubs = Club::ordered()->get();

        return view('admin.clubs.index', compact('clubs'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:clubs,name'],
            'description' => ['nullable', 'string', 'max:1000'],
            'is_active' => ['nullable', 'boolean'],
            'image' => ['nullable', 'image', 'max:2048'],
        ]);

        if ($request->hasFile('image')) {
            $validated['image_path'] = $request->file('image')->store('clubs', 'public');
        }

        $validated['is_active'] = $request->boolean('is_active', true);

        unset($validated['image']);

        Club::create($validated);

        // Clear clubs cache
        Cache::forget('active_clubs');

        return redirect()->route('admin.clubs.index')->with('status', 'Club created successfully.');
    }

    public function update(Request $request, Club $club): RedirectResponse
    {
        $validated = $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('clubs', 'name')->ignore($club->id),
            ],
            'description' => ['nullable', 'string', 'max:1000'],
            'is_active' => ['required', 'boolean'],
            'image' => ['nullable', 'image', 'max:2048'],
        ]);

        if ($request->hasFile('image')) {
            if ($club->image_path && Storage::disk('public')->exists($club->image_path)) {
                Storage::disk('public')->delete($club->image_path);
            }

            $validated['image_path'] = $request->file('image')->store('clubs', 'public');
        }

        unset($validated['image']);

        $club->update($validated);

        // Clear clubs cache
        Cache::forget('active_clubs');

        return redirect()->route('admin.clubs.index')->with('status', 'Club updated successfully.');
    }

    public function toggleStatus(Club $club): RedirectResponse
    {
        $club->update([
            'is_active' => ! $club->is_active,
        ]);

        // Clear clubs cache
        Cache::forget('active_clubs');

        $message = $club->is_active ? 'Club enabled successfully.' : 'Club disabled successfully.';

        return redirect()->route('admin.clubs.index')->with('status', $message);
    }

    /**
     * Display the specified club with members and pending requests.
     */
    public function show(Club $club): View
    {
        // Load club with relationships
        $club->load([
            'teacherClubs.user',
            'teacherClubs.school',
        ]);

        // Get all approved members
        $approvedMembers = $club->teacherClubs()
            ->approved()
            ->with(['user', 'school'])
            ->get();

        // Get all leaders (one per school)
        $leaders = $club->teacherClubs()
            ->approved()
            ->leaders()
            ->with(['user', 'school'])
            ->get();

        // Get pending requests
        $pendingRequests = $club->pendingRequests()
            ->with(['user', 'school'])
            ->get();

        // Get approved members grouped by school for transfer leadership modal (formatted for JavaScript)
        $membersBySchoolJson = $approvedMembers->groupBy('school_id')->map(function ($members, $schoolId) {
            return [
                'school_id' => (string)$schoolId,
                'members' => $members->map(function ($member) {
                    return [
                        'id' => $member->id,
                        'name' => $member->user->name,
                        'email' => $member->user->email,
                    ];
                })->values()->all()
            ];
        })->values()->all();

        return view('admin.clubs.show', compact('club', 'approvedMembers', 'pendingRequests', 'leaders', 'membersBySchoolJson'));
    }

    /**
     * Transfer leadership to another approved teacher (school-specific).
     */
    public function transferLeadership(Request $request, Club $club): RedirectResponse
    {
        $validated = $request->validate([
            'teacher_club_id' => ['required', 'exists:teacher_clubs,id'],
            'school_id' => ['required', 'exists:schools,id'],
        ]);

        $newLeaderTeacherClub = TeacherClub::findOrFail($validated['teacher_club_id']);

        // Verify the teacher belongs to this club
        if ($newLeaderTeacherClub->club_id !== $club->id) {
            return redirect()->route('admin.clubs.show', $club)
                ->with('error', 'The selected teacher does not belong to this club.');
        }

        // Verify the teacher belongs to the specified school
        if ($newLeaderTeacherClub->school_id != $validated['school_id']) {
            return redirect()->route('admin.clubs.show', $club)
                ->with('error', 'The selected teacher does not belong to the specified school.');
        }

        // Verify the teacher is approved
        if ($newLeaderTeacherClub->status !== 'approved') {
            return redirect()->route('admin.clubs.show', $club)
                ->with('error', 'Only approved members can become school leaders.');
        }

        // Remove leadership from current leader for this school (if exists)
        TeacherClub::where('club_id', $club->id)
            ->where('school_id', $validated['school_id'])
            ->where('is_leader', true)
            ->update(['is_leader' => false]);

        // Set new leader
        $newLeaderTeacherClub->update(['is_leader' => true]);

        return redirect()->route('admin.clubs.show', $club)
            ->with('status', "Leadership for the school has been transferred to {$newLeaderTeacherClub->user->name}.");
    }

    /**
     * Approve a pending join request (admin).
     */
    public function approveRequest(TeacherClub $teacherClub): RedirectResponse
    {
        // Verify that the request is pending
        if ($teacherClub->status !== 'pending') {
            return redirect()->route('admin.clubs.show', $teacherClub->club)
                ->with('error', 'This request has already been processed.');
        }

        $teacherClub->approve();

        return redirect()->route('admin.clubs.show', $teacherClub->club)
            ->with('status', "Successfully approved {$teacherClub->user->name}'s request to join {$teacherClub->club->name}.");
    }

    /**
     * Reject a pending join request (admin).
     */
    public function rejectRequest(TeacherClub $teacherClub): RedirectResponse
    {
        // Verify that the request is pending
        if ($teacherClub->status !== 'pending') {
            return redirect()->route('admin.clubs.show', $teacherClub->club)
                ->with('error', 'This request has already been processed.');
        }

        $teacherClub->reject();

        return redirect()->route('admin.clubs.show', $teacherClub->club)
            ->with('status', "Successfully rejected {$teacherClub->user->name}'s request to join {$teacherClub->club->name}.");
    }
}

